<template>
  <div class="p-6 space-y-6">
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold tracking-tight">Users</h1>
        <p class="text-muted-foreground text-sm">Manage system users and roles</p>
      </div>
      <Button @click="openModal()">
        <Plus class="mr-2 h-4 w-4" /> Add User
      </Button>
    </div>

    <Card>
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead class="pl-6 w-16">#</TableHead>
            <TableHead>Name</TableHead>
            <TableHead>Email</TableHead>
            <TableHead>Role</TableHead>
            <TableHead class="pr-6">Actions</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow v-if="loading">
            <TableCell colspan="5" class="text-center py-8 text-muted-foreground">Loading…</TableCell>
          </TableRow>
          <TableRow v-else v-for="u in users" :key="u.id">
            <TableCell class="pl-6 text-muted-foreground">{{ u.id }}</TableCell>
            <TableCell class="font-medium">{{ u.name }}</TableCell>
            <TableCell class="text-muted-foreground">{{ u.email }}</TableCell>
            <TableCell>
              <Badge :variant="u.role === 'admin' ? 'default' : 'secondary'" class="capitalize">
                {{ u.role }}
              </Badge>
            </TableCell>
            <TableCell class="pr-6">
              <div class="flex gap-2">
                <Button variant="ghost" size="sm" @click="openModal(u)">
                  <Pencil class="h-3.5 w-3.5" />
                </Button>
                <Button variant="ghost" size="sm" class="text-destructive hover:text-destructive" @click="deleteUser(u)">
                  <Trash2 class="h-3.5 w-3.5" />
                </Button>
              </div>
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </Card>

    <!-- Add / Edit User Dialog -->
    <Dialog :open="showModal" @update:open="val => showModal = val">
      <DialogContent class="max-w-sm">
        <DialogHeader>
          <DialogTitle>{{ editing ? 'Edit' : 'Add' }} User</DialogTitle>
        </DialogHeader>
        <form @submit.prevent="saveUser" class="space-y-4 pt-2">
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
            <Button type="button" variant="outline" @click="showModal = false">Cancel</Button>
            <Button type="submit">Save</Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Plus, Pencil, Trash2 } from 'lucide-vue-next';
import api from '@/api';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { Card } from '@/components/ui/card';
import { Table, TableHeader, TableBody, TableRow, TableHead, TableCell } from '@/components/ui/table';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

const users     = ref([]);
const loading   = ref(true);
const showModal = ref(false);
const editing   = ref(null);
const formError = ref('');
const form      = ref({ name: '', email: '', password: '', role: 'cashier' });

async function fetchUsers() {
  loading.value = true;
  const res     = await api.get('/users');
  users.value   = res.data;
  loading.value = false;
}

onMounted(fetchUsers);

function openModal(user = null) {
  editing.value   = user;
  formError.value = '';
  form.value      = user
    ? { name: user.name, email: user.email, password: '', role: user.role }
    : { name: '', email: '', password: '', role: 'cashier' };
  showModal.value = true;
}

async function saveUser() {
  formError.value = '';
  const payload   = { ...form.value };
  if (editing.value && !payload.password) delete payload.password;
  try {
    if (editing.value) {
      await api.put(`/users/${editing.value.id}`, payload);
    } else {
      await api.post('/users', payload);
    }
    showModal.value = false;
    fetchUsers();
  } catch (e) {
    formError.value = Object.values(e.response?.data?.errors || {}).flat().join(' ') || 'Save failed.';
  }
}

async function deleteUser(user) {
  if (!confirm(`Delete user "${user.name}"?`)) return;
  await api.delete(`/users/${user.id}`);
  fetchUsers();
}
</script>
