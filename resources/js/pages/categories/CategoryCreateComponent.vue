
<script setup>
import { ref, watch } from 'vue';
import api from '@/api';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';

const props = defineProps({
  open:    { type: Boolean, default: false },
  editing: { type: Object,  default: null },
});

const emit = defineEmits(['update:open', 'saved']);

const formError       = ref('');
const form            = ref({ name: '' });
const selectedFile    = ref(null);
const previewUrl      = ref(null);
const currentImageUrl = ref(null);
const removeImage     = ref(false);

watch(() => props.open, (val) => {
  if (val) {
    formError.value       = '';
    selectedFile.value    = null;
    previewUrl.value      = null;
    removeImage.value     = false;
    currentImageUrl.value = props.editing?.image_url ?? null;
    form.value            = { name: props.editing?.name || '' };
  }
});

function onFileChange(e) {
  const file = e.target.files?.[0];
  if (!file) return;
  selectedFile.value = file;
  previewUrl.value   = URL.createObjectURL(file);
}

function clearFile() {
  selectedFile.value = null;
  previewUrl.value   = null;
}

function removeCurrentImage() {
  removeImage.value     = true;
  currentImageUrl.value = null;
}

async function save() {
  formError.value = '';
  if (!form.value.name.trim()) {
    formError.value = 'Name is required.';
    return;
  }
  try {
    let catId;
    if (props.editing) {
      await api.put(`/categories/${props.editing.id}`, form.value);
      catId = props.editing.id;
    } else {
      const { data } = await api.post('/categories', form.value);
      catId = data.id;
    }

    if (selectedFile.value) {
      const fd = new FormData();
      fd.append('image', selectedFile.value);
      await api.post(`/categories/${catId}/image`, fd, { headers: { 'Content-Type': 'multipart/form-data' } });
    } else if (removeImage.value) {
      await api.delete(`/categories/${catId}/image`);
    }

    emit('update:open', false);
    emit('saved');
  } catch (e) {
    formError.value = e.response?.data?.message || 'Save failed.';
  }
}
</script>

<template>
  <Dialog :open="open" @update:open="$emit('update:open', $event)">
    <DialogContent class="max-w-sm">
      <DialogHeader>
        <DialogTitle>{{ editing ? 'Edit' : 'Add' }} Category</DialogTitle>
      </DialogHeader>
      <form @submit.prevent="save" class="space-y-4 pt-2">
        <div class="space-y-2">
          <Label>Name</Label>
          <Input v-model="form.name" placeholder="Category name" />
        </div>
        <!-- Image upload -->
        <div class="space-y-2">
          <Label>Image <span class="text-muted-foreground font-normal">(optional, max 2 MB)</span></Label>
          <div v-if="currentImageUrl && !previewUrl" class="flex items-center gap-3">
            <img :src="currentImageUrl" alt="Current" class="h-14 w-14 object-cover rounded border" />
            <Button type="button" variant="ghost" size="sm" class="text-destructive" @click="removeCurrentImage">Remove</Button>
          </div>
          <div v-if="previewUrl" class="flex items-center gap-3">
            <img :src="previewUrl" alt="Preview" class="h-14 w-14 object-cover rounded border" />
            <Button type="button" variant="ghost" size="sm" @click="clearFile">Clear</Button>
          </div>
          <Input type="file" accept="image/jpeg,image/png,image/webp" @change="onFileChange" />
        </div>

        <p v-if="formError" class="text-sm text-destructive">{{ formError }}</p>
        <DialogFooter class="gap-2 pt-2">
          <Button type="button" variant="outline" @click="$emit('update:open', false)">Cancel</Button>
          <Button type="submit">Save</Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>
