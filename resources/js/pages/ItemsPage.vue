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
          <SelectItem value="">All Categories</SelectItem>
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
              <Badge :variant="item.stock <= 10 ? 'destructive' : 'secondary'">
                {{ item.stock }}
              </Badge>
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

    <!-- Add / Edit Dialog -->
    <Dialog :open="showModal" @update:open="val => showModal = val">
      <DialogContent class="max-w-md">
        <DialogHeader>
          <DialogTitle>{{ editing ? 'Edit' : 'Add' }} Item</DialogTitle>
        </DialogHeader>
        <form @submit.prevent="saveItem" class="space-y-4 pt-2">
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
                <SelectItem v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</SelectItem>
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
import { Plus, Search, Pencil, Trash2 } from 'lucide-vue-next';
import api from '@/api';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { Checkbox } from '@/components/ui/checkbox';
import { Card } from '@/components/ui/card';
import { Table, TableHeader, TableBody, TableRow, TableHead, TableCell } from '@/components/ui/table';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

const items          = ref([]);
const categories     = ref([]);
const loading        = ref(true);
const search         = ref('');
const categoryFilter = ref('');
const lowStock       = ref(false);
const showModal      = ref(false);
const editing        = ref(null);
const formError      = ref('');
const form           = ref({ name: '', category_id: '', price: 0, stock: 0, barcode: '' });

let searchTimer = null;
function debouncedFetch() {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(fetchItems, 300);
}

async function fetchItems() {
  loading.value = true;
  const params  = { search: search.value, category_id: categoryFilter.value, low_stock: lowStock.value ? 1 : '' };
  const res     = await api.get('/items', { params });
  items.value   = res.data.data;
  loading.value = false;
}

onMounted(async () => {
  const [, catRes] = await Promise.all([fetchItems(), api.get('/categories')]);
  categories.value = catRes.data;
});

function openModal(item = null) {
  editing.value   = item;
  formError.value = '';
  form.value      = item
    ? { name: item.name, category_id: item.category_id, price: item.price, stock: item.stock, barcode: item.barcode || '' }
    : { name: '', category_id: '', price: 0, stock: 0, barcode: '' };
  showModal.value = true;
}

async function saveItem() {
  formError.value = '';
  try {
    if (editing.value) {
      await api.put(`/items/${editing.value.id}`, form.value);
    } else {
      await api.post('/items', form.value);
    }
    showModal.value = false;
    fetchItems();
  } catch (e) {
    formError.value = Object.values(e.response?.data?.errors || {}).flat().join(' ') || 'Save failed.';
  }
}

async function deleteItem(item) {
  if (!confirm(`Delete "${item.name}"?`)) return;
  await api.delete(`/items/${item.id}`);
  fetchItems();
}

const fmt = (n) => Number(n || 0).toLocaleString('id-ID');
</script>
