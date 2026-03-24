<template>
  <div class="p-6 space-y-6">
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold tracking-tight">Categories</h1>
        <p class="text-muted-foreground text-sm">Organize your product categories</p>
      </div>
      <Button @click="openModal()">
        <Plus class="mr-2 h-4 w-4" /> Add Category
      </Button>
    </div>

    <Card>
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead class="pl-6 w-16">#</TableHead>
            <TableHead>Name</TableHead>
            <TableHead>Items</TableHead>
            <TableHead class="pr-6">Actions</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow v-if="loading">
            <TableCell colspan="4" class="text-center py-8 text-muted-foreground">Loading…</TableCell>
          </TableRow>
          <TableRow v-else v-for="cat in categories" :key="cat.id">
            <TableCell class="pl-6 text-muted-foreground">{{ cat.id }}</TableCell>
            <TableCell class="font-medium">{{ cat.name }}</TableCell>
            <TableCell>
              <Badge variant="secondary">{{ cat.items_count }}</Badge>
            </TableCell>
            <TableCell class="pr-6">
              <div class="flex gap-2">
                <Button variant="ghost" size="sm" @click="openModal(cat)">
                  <Pencil class="h-3.5 w-3.5" />
                </Button>
                <Button variant="ghost" size="sm" class="text-destructive hover:text-destructive" @click="deleteCategory(cat)">
                  <Trash2 class="h-3.5 w-3.5" />
                </Button>
              </div>
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </Card>

    <!-- Add / Edit Dialog -->
    <Dialog :open="showModal" @update:open="val => showModal = val">
      <DialogContent class="max-w-sm">
        <DialogHeader>
          <DialogTitle>{{ editing ? 'Edit' : 'Add' }} Category</DialogTitle>
        </DialogHeader>
        <form @submit.prevent="saveCategory" class="space-y-4 pt-2">
          <div class="space-y-2">
            <Label>Name</Label>
            <Input v-model="form.name" required placeholder="Category name" />
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

const categories = ref([]);
const loading    = ref(true);
const showModal  = ref(false);
const editing    = ref(null);
const formError  = ref('');
const form       = ref({ name: '' });

async function fetchCategories() {
  loading.value    = true;
  const res        = await api.get('/categories');
  categories.value = res.data;
  loading.value    = false;
}

onMounted(fetchCategories);

function openModal(cat = null) {
  editing.value   = cat;
  formError.value = '';
  form.value      = { name: cat?.name || '' };
  showModal.value = true;
}

async function saveCategory() {
  formError.value = '';
  try {
    if (editing.value) {
      await api.put(`/categories/${editing.value.id}`, form.value);
    } else {
      await api.post('/categories', form.value);
    }
    showModal.value = false;
    fetchCategories();
  } catch (e) {
    formError.value = e.response?.data?.message || 'Save failed.';
  }
}

async function deleteCategory(cat) {
  if (!confirm(`Delete "${cat.name}"?`)) return;
  await api.delete(`/categories/${cat.id}`);
  fetchCategories();
}
</script>
