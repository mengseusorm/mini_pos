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

    <CategoryCreateComponent
      v-model:open="showModal"
      :editing="editingCat"
      @saved="fetchCategories"
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
import CategoryCreateComponent from './CategoryCreateComponent.vue';

const categories = ref([]);
const loading    = ref(true);
const showModal  = ref(false);
const editingCat = ref(null);

async function fetchCategories() {
  loading.value    = true;
  const res        = await api.get('/categories');
  categories.value = res.data;
  loading.value    = false;
}

onMounted(fetchCategories);

function openModal(cat = null) {
  editingCat.value = cat;
  showModal.value  = true;
}

async function deleteCategory(cat) {
  if (!confirm(`Delete "${cat.name}"?`)) return;
  await api.delete(`/categories/${cat.id}`);
  fetchCategories();
}
</script>
