<template>
  <Dialog :open="open" @update:open="$emit('update:open', $event)">
    <DialogContent class="max-w-md">
      <DialogHeader>
        <DialogTitle>{{ editing ? 'Edit' : 'Add' }} Item</DialogTitle>
      </DialogHeader>
      <form @submit.prevent="save" class="space-y-4 pt-2">
        <div class="space-y-2">
          <Label>Name</Label>
          <Input v-model="form.name" required placeholder="Item name" />
        </div>
        <div class="space-y-2">
          <Label>Category</Label>
          <Select v-model="form.category_id">
            <SelectTrigger>
              <SelectValue placeholder="Select category" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem v-for="cat in categories" :key="cat.id" :value="cat.id">
                {{ cat.name }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div class="space-y-2">
            <Label>Price</Label>
            <Input v-model.number="form.price" type="number" min="0" required />
          </div>
          <div class="space-y-2">
            <Label>Stock</Label>
            <Input v-model.number="form.stock" type="number" min="0" required />
          </div>
        </div>
        <div class="space-y-2">
          <Label>Barcode <span class="text-muted-foreground font-normal">(optional)</span></Label>
          <Input v-model="form.barcode" />
        </div>

        <!-- Image upload -->
        <div class="space-y-2">
          <Label>Image <span class="text-muted-foreground font-normal">(optional, max 2 MB)</span></Label>
          <!-- Current image preview -->
          <div v-if="currentImageUrl && !previewUrl" class="flex items-center gap-3">
            <img :src="currentImageUrl" alt="Current" class="h-16 w-16 object-cover rounded border" />
            <Button type="button" variant="ghost" size="sm" class="text-destructive" @click="removeCurrentImage">Remove</Button>
          </div>
          <!-- New file preview -->
          <div v-if="previewUrl" class="flex items-center gap-3">
            <img :src="previewUrl" alt="Preview" class="h-16 w-16 object-cover rounded border" />
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

<script setup>
import { ref, watch } from 'vue';
import api from '@/api';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

const props = defineProps({
  open:       { type: Boolean, default: false },
  editing:    { type: Object,  default: null },
  categories: { type: Array,   default: () => [] },
});

const emit = defineEmits(['update:open', 'saved']);

const formError      = ref('');
const form           = ref({ name: '', category_id: '', price: 0, stock: 0, barcode: '' });
const selectedFile   = ref(null);
const previewUrl     = ref(null);
const currentImageUrl = ref(null);
const removeImage    = ref(false);

watch(() => props.open, (val) => {
  if (val) {
    formError.value     = '';
    selectedFile.value  = null;
    previewUrl.value    = null;
    removeImage.value   = false;
    currentImageUrl.value = props.editing?.image_url ?? null;
    form.value = props.editing
      ? { name: props.editing.name, category_id: props.editing.category_id, price: props.editing.price, stock: props.editing.stock, barcode: props.editing.barcode || '' }
      : { name: '', category_id: '', price: 0, stock: 0, barcode: '' };
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
  removeImage.value   = true;
  currentImageUrl.value = null;
}

async function save() {
  formError.value = '';
  try {
    let itemId;
    if (props.editing) {
      await api.put(`/items/${props.editing.id}`, form.value);
      itemId = props.editing.id;
    } else {
      const { data } = await api.post('/items', form.value);
      itemId = data.id;
    }

    // Handle image
    if (selectedFile.value) {
      const fd = new FormData();
      fd.append('image', selectedFile.value);
      await api.post(`/items/${itemId}/image`, fd, { headers: { 'Content-Type': 'multipart/form-data' } });
    } else if (removeImage.value) {
      await api.delete(`/items/${itemId}/image`);
    }

    emit('update:open', false);
    emit('saved');
  } catch (e) {
    formError.value = Object.values(e.response?.data?.errors || {}).flat().join(' ') || 'Save failed.';
  }
}
</script>
