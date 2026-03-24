<template>
  <Dialog :open="open" @update:open="$emit('update:open', $event)">
    <DialogContent class="max-w-sm">
      <DialogHeader>
        <DialogTitle>{{ editing ? 'Edit' : 'Add' }} User</DialogTitle>
      </DialogHeader>
      <form @submit.prevent="save" class="space-y-4 pt-2">
        <div class="space-y-2">
          <Label>Name</Label>
          <Input v-model="form.name" required placeholder="Full name" />
        </div>
        <div class="space-y-2">
          <Label>Email</Label>
          <Input v-model="form.email" type="email" required placeholder="email@example.com" />
        </div>
        <div class="space-y-2">
          <Label>
            Password
            <span v-if="editing" class="text-muted-foreground font-normal text-xs"> (leave blank to keep)</span>
          </Label>
          <Input v-model="form.password" type="password" :required="!editing" placeholder="••••••••" />
        </div>
        <div class="space-y-2">
          <Label>Role</Label>
          <Select v-model="form.role">
            <SelectTrigger>
              <SelectValue />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="cashier">Cashier</SelectItem>
              <SelectItem value="admin">Admin</SelectItem>
            </SelectContent>
          </Select>
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
  open:    { type: Boolean, default: false },
  editing: { type: Object,  default: null },
});

const emit = defineEmits(['update:open', 'saved']);

const formError = ref('');
const form      = ref({ name: '', email: '', password: '', role: 'cashier' });

watch(() => props.open, (val) => {
  if (val) {
    formError.value = '';
    form.value = props.editing
      ? { name: props.editing.name, email: props.editing.email, password: '', role: props.editing.role }
      : { name: '', email: '', password: '', role: 'cashier' };
  }
});

async function save() {
  formError.value = '';
  const payload   = { ...form.value };
  if (props.editing && !payload.password) delete payload.password;
  try {
    if (props.editing) {
      await api.put(`/users/${props.editing.id}`, payload);
    } else {
      await api.post('/users', payload);
    }
    emit('update:open', false);
    emit('saved');
  } catch (e) {
    formError.value = Object.values(e.response?.data?.errors || {}).flat().join(' ') || 'Save failed.';
  }
}
</script>
