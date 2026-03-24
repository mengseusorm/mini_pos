<template>
  <div class="p-6 space-y-8 max-w-2xl">

    <!-- Header -->
    <div>
      <h1 class="text-2xl font-bold tracking-tight">Settings</h1>
      <p class="text-muted-foreground">Manage appearance and site configuration.</p>
    </div>

    <!-- Appearance -->
    <Card>
      <CardHeader>
        <CardTitle>Appearance</CardTitle>
        <CardDescription>Customize how the app looks.</CardDescription>
      </CardHeader>
      <CardContent class="space-y-6">
        <!-- Dark / Light -->
        <div class="flex items-center justify-between">
          <div>
            <p class="font-medium">Dark Mode</p>
            <p class="text-sm text-muted-foreground">Switch between light and dark theme.</p>
          </div>
          <Switch :checked="theme.dark" @update:checked="theme.toggleDark()" />
        </div>

        <!-- Theme color -->
        <div class="space-y-2">
          <p class="font-medium">Theme Color</p>
          <p class="text-sm text-muted-foreground">Applied as the accent color across the UI.</p>
          <div class="flex flex-wrap gap-2 pt-1">
            <button
              v-for="color in THEME_COLORS"
              :key="color.name"
              :title="color.label"
              :style="{ background: colorHex(color.name) }"
              :class="[
                'w-7 h-7 rounded-full border-2 transition-transform hover:scale-110',
                theme.themeColor === color.name ? 'border-foreground scale-110' : 'border-transparent',
              ]"
              @click="theme.setColor(color.name)"
            />
          </div>
          <p class="text-xs text-muted-foreground">Selected: <strong>{{ theme.themeColor }}</strong></p>
        </div>
      </CardContent>
    </Card>

    <!-- Site Configuration -->
    <Card>
      <CardHeader>
        <CardTitle>Site Configuration</CardTitle>
        <CardDescription>Update your site name and description.</CardDescription>
      </CardHeader>
      <CardContent class="space-y-4">
        <div v-if="loadingSettings" class="text-sm text-muted-foreground">Loading…</div>
        <template v-else>
          <div class="space-y-1">
            <Label for="site-name">Site Name</Label>
            <Input id="site-name" v-model="form.site_name" placeholder="Mini POS" />
          </div>
          <div class="space-y-1">
            <Label for="site-desc">Description</Label>
            <Input id="site-desc" v-model="form.site_description" placeholder="Point of Sale System" />
          </div>
        </template>
      </CardContent>
      <CardFooter>
        <Button :disabled="savingConfig" @click="saveConfig">
          {{ savingConfig ? 'Saving…' : 'Save Changes' }}
        </Button>
      </CardFooter>
    </Card>

    <!-- Logo -->
    <Card>
      <CardHeader>
        <CardTitle>Logo</CardTitle>
        <CardDescription>Upload your site logo (JPEG, PNG or WebP, max 2 MB).</CardDescription>
      </CardHeader>
      <CardContent class="space-y-4">
        <!-- Preview -->
        <div v-if="logoUrl" class="flex items-center gap-4">
          <img :src="logoUrl" alt="Logo" class="h-20 w-20 object-contain rounded border" />
          <Button variant="destructive" size="sm" :disabled="deletingLogo" @click="deleteLogo">
            {{ deletingLogo ? 'Removing…' : 'Remove Logo' }}
          </Button>
        </div>

        <!-- Upload -->
        <div class="space-y-1">
          <Label for="logo-upload">{{ logoUrl ? 'Replace Logo' : 'Upload Logo' }}</Label>
          <Input id="logo-upload" type="file" accept="image/jpeg,image/png,image/webp" @change="onLogoSelected" />
        </div>

        <Button v-if="selectedFile" :disabled="uploadingLogo" @click="uploadLogo">
          {{ uploadingLogo ? 'Uploading…' : 'Upload' }}
        </Button>
      </CardContent>
    </Card>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/api/index.js'
import { useThemeStore, THEME_COLORS } from '@/stores/theme.js'

import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Switch } from '@/components/ui/switch'

const theme = useThemeStore()

// State
const loadingSettings = ref(true)
const savingConfig    = ref(false)
const uploadingLogo   = ref(false)
const deletingLogo    = ref(false)
const selectedFile    = ref(null)
const logoUrl         = ref(null)
const form            = ref({ site_name: '', site_description: '' })

// Color swatch hex approximations (for display only)
const COLOR_HEX = {
  zinc:   '#71717a',
  slate:  '#64748b',
  stone:  '#78716c',
  gray:   '#6b7280',
  red:    '#ef4444',
  rose:   '#f43f5e',
  orange: '#f97316',
  amber:  '#f59e0b',
  yellow: '#eab308',
  lime:   '#84cc16',
  green:  '#22c55e',
  teal:   '#14b8a6',
  cyan:   '#06b6d4',
  sky:    '#0ea5e9',
  blue:   '#3b82f6',
  indigo: '#6366f1',
  violet: '#8b5cf6',
  purple: '#a855f7',
  pink:   '#ec4899',
}
function colorHex(name) {
  return COLOR_HEX[name] ?? '#888'
}

async function loadSettings() {
  try {
    loadingSettings.value = true
    const { data } = await api.get('/settings')
    form.value.site_name        = data.site_name        ?? ''
    form.value.site_description = data.site_description ?? ''
    logoUrl.value               = data.logo_url         ?? null
  } finally {
    loadingSettings.value = false
  }
}

async function saveConfig() {
  try {
    savingConfig.value = true
    await api.put('/settings', form.value)
  } finally {
    savingConfig.value = false
  }
}

function onLogoSelected(e) {
  selectedFile.value = e.target.files?.[0] ?? null
}

async function uploadLogo() {
  if (!selectedFile.value) return
  try {
    uploadingLogo.value = true
    const fd = new FormData()
    fd.append('image', selectedFile.value)
    const { data } = await api.post('/settings/logo', fd, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    logoUrl.value    = data.logo_url
    selectedFile.value = null
  } finally {
    uploadingLogo.value = false
  }
}

async function deleteLogo() {
  try {
    deletingLogo.value = true
    await api.delete('/settings/logo')
    logoUrl.value = null
  } finally {
    deletingLogo.value = false
  }
}

onMounted(loadSettings)
</script>
