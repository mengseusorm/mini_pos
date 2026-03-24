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

    <StockCreateComponent
      v-model:open="showModal"
      :items="allItems"
      @saved="fetchMovements"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Plus } from 'lucide-vue-next';
import api from '@/api';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card } from '@/components/ui/card';
import { Table, TableHeader, TableBody, TableRow, TableHead, TableCell } from '@/components/ui/table';
import StockCreateComponent from './StockCreateComponent.vue';

const movements = ref([]);
const allItems  = ref([]);
const loading   = ref(true);
const showModal = ref(false);

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
  showModal.value = true;
}

const fmtDate = (d) => new Date(d).toLocaleDateString('id-ID');
</script>
