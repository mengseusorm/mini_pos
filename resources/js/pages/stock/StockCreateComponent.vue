<template>
  <Dialog :open="open" @update:open="$emit('update:open', $event)">
    <DialogContent class="max-w-sm">
      <DialogHeader>
        <DialogTitle>Add Stock Movement</DialogTitle>
      </DialogHeader>
      <form @submit.prevent="save" class="space-y-4 pt-2">
        <div class="space-y-2">
          <Label>Item</Label>
          <Select v-model="form.item_id">
            <SelectTrigger>
              <SelectValue placeholder="Select item" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem v-for="item in items" :key="item.id" :value="item.id">
                {{ item.name }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>
        <div class="space-y-2">
          <Label>Type</Label>
          <Select v-model="form.type">
            <SelectTrigger>
              <SelectValue />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="in">Stock In</SelectItem>
              <SelectItem value="out">Stock Out</SelectItem>
            </SelectContent>
          </Select>
        </div>
        <div class="space-y-2">
          <Label>Quantity</Label>
          <Input v-model.number="form.quantity" type="number" min="1" required />
        </div>
        <div class="space-y-2">
          <Label>Note <span class="text-muted-foreground font-normal">(optional)</span></Label>
          <Input v-model="form.note" placeholder="Reason for movement" />
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
  open:  { type: Boolean, default: false },
  items: { type: Array,   default: () => [] },
});

const emit = defineEmits(['update:open', 'saved']);

const formError = ref('');
const form      = ref({ item_id: '', type: 'in', quantity: 1, note: '' });

watch(() => props.open, (val) => {
  if (val) {
    formError.value = '';
    form.value = { item_id: '', type: 'in', quantity: 1, note: '' };
  }
});

async function save() {
  formError.value = '';
  try {
    await api.post('/stock-movements', form.value);
    emit('update:open', false);
    emit('saved');
  } catch (e) {
    formError.value = e.response?.data?.message || 'Save failed.';
  }
}
</script>
