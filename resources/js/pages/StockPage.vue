<template>
  <div class="p-6 space-y-6">
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold tracking-tight">Stock Movements</h1>
        <p class="text-muted-foreground text-sm">Track inventory changes</p>
      </div>
      <Button @click="openModal()">
        <Plus class="mr-2 h-4 w-4" /> Add Movement
      </Button>
    </div>

    <Card>
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead class="pl-6">Item</TableHead>
            <TableHead>Type</TableHead>
            <TableHead>Quantity</TableHead>
            <TableHead>Note</TableHead>
            <TableHead>By</TableHead>
            <TableHead class="pr-6">Date</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow v-if="loading">
            <TableCell colspan="6" class="text-center py-8 text-muted-foreground">Loading…</TableCell>
          </TableRow>
          <TableRow v-else v-for="m in movements" :key="m.id">
            <TableCell class="pl-6 font-medium">{{ m.item?.name }}</TableCell>
            <TableCell>
              <Badge :variant="m.type === 'in' ? 'success' : 'destructive'" class="capitalize">
                {{ m.type === 'in' ? 'Stock In' : 'Stock Out' }}
              </Badge>
            </TableCell>
            <TableCell class="font-medium">{{ m.quantity }}</TableCell>
            <TableCell class="text-muted-foreground">{{ m.note || '—' }}</TableCell>
            <TableCell>{{ m.user?.name }}</TableCell>
            <TableCell class="pr-6 text-muted-foreground">{{ fmtDate(m.created_at) }}</TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </Card>

    <!-- Add Movement Dialog -->
    <Dialog :open="showModal" @update:open="val => showModal = val">
      <DialogContent class="max-w-sm">
        <DialogHeader>
          <DialogTitle>Add Stock Movement</DialogTitle>
        </DialogHeader>
        <form @submit.prevent="saveMovement" class="space-y-4 pt-2">
          <div class="space-y-2">
            <Label>Item</Label>
            <Select v-model="form.item_id">
              <SelectTrigger>
                <SelectValue placeholder="Select item" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem v-for="item in allItems" :key="item.id" :value="item.id">
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
import { Plus } from 'lucide-vue-next';
import api from '@/api';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { Card } from '@/components/ui/card';
import { Table, TableHeader, TableBody, TableRow, TableHead, TableCell } from '@/components/ui/table';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

const movements = ref([]);
const allItems  = ref([]);
const loading   = ref(true);
const showModal = ref(false);
const formError = ref('');
const form      = ref({ item_id: '', type: 'in', quantity: 1, note: '' });

async function fetchMovements() {
  loading.value   = true;
  const res       = await api.get('/stock-movements');
  movements.value = res.data.data;
  loading.value   = false;
}

onMounted(async () => {
  const [, itemsRes] = await Promise.all([fetchMovements(), api.get('/items?per_page=100')]);
  allItems.value = itemsRes.data.data;
});

function openModal() {
  formError.value = '';
  form.value      = { item_id: '', type: 'in', quantity: 1, note: '' };
  showModal.value = true;
}

async function saveMovement() {
  formError.value = '';
  try {
    await api.post('/stock-movements', form.value);
    showModal.value = false;
    fetchMovements();
  } catch (e) {
    formError.value = e.response?.data?.message || 'Save failed.';
  }
}

const fmtDate = (d) => new Date(d).toLocaleDateString('id-ID');
</script>
