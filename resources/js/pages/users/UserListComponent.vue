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

    <UserCreateComponent
      v-model:open="showModal"
      :editing="editingUser"
      @saved="fetchUsers"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Plus, Pencil, Trash2 } from 'lucide-vue-next';
import api from '@/api';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card } from '@/components/ui/card';
import { Table, TableHeader, TableBody, TableRow, TableHead, TableCell } from '@/components/ui/table';
import UserCreateComponent from './UserCreateComponent.vue';

const users       = ref([]);
const loading     = ref(true);
const showModal   = ref(false);
const editingUser = ref(null);

async function fetchUsers() {
  loading.value = true;
  const res     = await api.get('/users');
  users.value   = res.data;
  loading.value = false;
}

onMounted(fetchUsers);

function openModal(user = null) {
  editingUser.value = user;
  showModal.value   = true;
}

async function deleteUser(user) {
  if (!confirm(`Delete user "${user.name}"?`)) return;
  await api.delete(`/users/${user.id}`);
  fetchUsers();
}
</script>
