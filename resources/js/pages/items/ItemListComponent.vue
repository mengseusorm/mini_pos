<template>
  <div class="p-6 space-y-6">
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold tracking-tight">Items</h1>
        <p class="text-muted-foreground text-sm">Manage your product catalog</p>
      </div>
      <Button @click="openModal()">
        <Plus class="mr-2 h-4 w-4" /> Add Item
      </Button>
    </div>

    <!-- Filters -->
    <div class="flex gap-3 flex-wrap">
      <div class="relative flex-1 min-w-48">
        <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
        <Input v-model="search" placeholder="Search items…" class="pl-9" @input="debouncedFetch" />
      </div>
      <Select v-model="categoryFilter" @update:modelValue="fetchItems">
        <SelectTrigger class="w-48">
          <SelectValue placeholder="All Categories" />
        </SelectTrigger>
        <SelectContent>
          <SelectItem value="all">All Categories</SelectItem>
          <SelectItem v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</SelectItem>
        </SelectContent>
      </Select>
      <label class="flex items-center gap-2 text-sm cursor-pointer border rounded-md px-3 h-10">
        <Checkbox :checked="lowStock" @update:checked="val => { lowStock = val; fetchItems() }" />
        Low stock only
      </label>
    </div>

    <Card>
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead class="pl-6">Name</TableHead>
            <TableHead>Category</TableHead>
            <TableHead>Price</TableHead>
            <TableHead>Stock</TableHead>
            <TableHead>Barcode</TableHead>
            <TableHead class="pr-6">Actions</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow v-if="loading">
            <TableCell colspan="6" class="text-center py-8 text-muted-foreground">Loading…</TableCell>
          </TableRow>
          <TableRow v-else v-for="item in items" :key="item.id">
            <TableCell class="pl-6 font-medium">{{ item.name }}</TableCell>
            <TableCell>{{ item.category?.name }}</TableCell>
            <TableCell>Rp {{ fmt(item.price) }}</TableCell>
            <TableCell>
              <Badge :variant="item.stock <= 10 ? 'destructive' : 'secondary'">{{ item.stock }}</Badge>
            </TableCell>
            <TableCell class="text-muted-foreground">{{ item.barcode || '—' }}</TableCell>
            <TableCell class="pr-6">
              <div class="flex gap-2">
                <Button variant="ghost" size="sm" @click="openModal(item)">
                  <Pencil class="h-3.5 w-3.5" />
                </Button>
                <Button variant="ghost" size="sm" class="text-destructive hover:text-destructive" @click="deleteItem(item)">
                  <Trash2 class="h-3.5 w-3.5" />
                </Button>
              </div>
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </Card>

    <ItemCreateComponent
      v-model:open="showModal"
      :editing="editingItem"
      :categories="categories"
      @saved="fetchItems"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Plus, Search, Pencil, Trash2 } from 'lucide-vue-next';
import api from '@/api';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Checkbox } from '@/components/ui/checkbox';
import { Card } from '@/components/ui/card';
import { Table, TableHeader, TableBody, TableRow, TableHead, TableCell } from '@/components/ui/table';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import ItemCreateComponent from './ItemCreateComponent.vue';

const items          = ref([]);
const categories     = ref([]);
const loading        = ref(true);
const search         = ref('');
const categoryFilter = ref('all');
const lowStock       = ref(false);
const showModal      = ref(false);
const editingItem    = ref(null);

let searchTimer = null;
function debouncedFetch() {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(fetchItems, 300);
}

async function fetchItems() {
  loading.value = true;
  const params  = { search: search.value, category_id: categoryFilter.value === 'all' ? '' : categoryFilter.value, low_stock: lowStock.value ? 1 : '' };
  const res     = await api.get('/items', { params });
  items.value   = res.data.data;
  loading.value = false;
}

onMounted(async () => {
  const [, catRes] = await Promise.all([fetchItems(), api.get('/categories')]);
  categories.value = catRes.data;
});

function openModal(item = null) {
  editingItem.value = item;
  showModal.value   = true;
}

async function deleteItem(item) {
  if (!confirm(`Delete "${item.name}"?`)) return;
  await api.delete(`/items/${item.id}`);
  fetchItems();
}

const fmt = (n) => Number(n || 0).toLocaleString('id-ID');
</script>
