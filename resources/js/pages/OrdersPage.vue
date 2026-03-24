<template>
  <div class="p-6 space-y-6">
    <div>
      <h1 class="text-2xl font-bold tracking-tight">Orders</h1>
      <p class="text-muted-foreground text-sm">View and manage all orders</p>
    </div>

    <!-- Filters -->
    <div class="flex gap-3 flex-wrap items-end">
      <div class="space-y-1">
        <Label class="text-xs">From</Label>
        <Input v-model="filters.date_from" type="date" class="h-9" />
      </div>
      <div class="space-y-1">
        <Label class="text-xs">To</Label>
        <Input v-model="filters.date_to" type="date" class="h-9" />
      </div>
      <Select v-model="filters.payment_method">
        <SelectTrigger class="w-40 h-9">
          <SelectValue placeholder="All Methods" />
        </SelectTrigger>
        <SelectContent>
          <SelectItem value="">All Methods</SelectItem>
          <SelectItem value="cash">Cash</SelectItem>
          <SelectItem value="card">Card</SelectItem>
        </SelectContent>
      </Select>
      <Button size="sm" @click="fetchOrders">
        <Search class="mr-2 h-4 w-4" /> Filter
      </Button>
    </div>

    <Card>
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead class="pl-6">#</TableHead>
            <TableHead>Cashier</TableHead>
            <TableHead>Items</TableHead>
            <TableHead>Total</TableHead>
            <TableHead>Method</TableHead>
            <TableHead>Status</TableHead>
            <TableHead class="pr-6">Date</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow v-if="loading">
            <TableCell colspan="7" class="text-center py-8 text-muted-foreground">Loading…</TableCell>
          </TableRow>
          <TableRow v-else-if="orders.length === 0">
            <TableCell colspan="7" class="text-center py-8 text-muted-foreground">No orders found.</TableCell>
          </TableRow>
          <TableRow
            v-else
            v-for="order in orders"
            :key="order.id"
            class="cursor-pointer"
            @click="selected = order"
          >
            <TableCell class="pl-6 font-medium">{{ order.id }}</TableCell>
            <TableCell>{{ order.user?.name }}</TableCell>
            <TableCell>{{ order.items?.length }}</TableCell>
            <TableCell>Rp {{ fmt(order.total) }}</TableCell>
            <TableCell class="capitalize">{{ order.payment_method }}</TableCell>
            <TableCell>
              <Badge :variant="statusVariant(order.status)" class="capitalize">{{ order.status }}</Badge>
            </TableCell>
            <TableCell class="pr-6 text-muted-foreground">{{ fmtDate(order.created_at) }}</TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </Card>

    <!-- Pagination -->
    <div v-if="pagination" class="flex justify-between items-center text-sm">
      <span class="text-muted-foreground">Total: {{ pagination.total }}</span>
      <div class="flex items-center gap-2">
        <Button variant="outline" size="sm" :disabled="pagination.current_page <= 1" @click="page--; fetchOrders()">
          Previous
        </Button>
        <span class="text-muted-foreground">{{ pagination.current_page }} / {{ pagination.last_page }}</span>
        <Button variant="outline" size="sm" :disabled="pagination.current_page >= pagination.last_page" @click="page++; fetchOrders()">
          Next
        </Button>
      </div>
    </div>

    <!-- Order Detail Dialog -->
    <Dialog :open="!!selected" @update:open="val => { if (!val) selected = null }">
      <DialogContent class="max-w-md max-h-[90vh] overflow-y-auto">
        <DialogHeader>
          <DialogTitle>Order #{{ selected?.id }}</DialogTitle>
        </DialogHeader>
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>Item</TableHead>
              <TableHead class="text-right">Qty</TableHead>
              <TableHead class="text-right">Price</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="i in selected?.items" :key="i.id">
              <TableCell>{{ i.item?.name }}</TableCell>
              <TableCell class="text-right">{{ i.quantity }}</TableCell>
              <TableCell class="text-right">Rp {{ fmt(i.subtotal) }}</TableCell>
            </TableRow>
          </TableBody>
        </Table>
        <Separator />
        <div class="space-y-1.5 text-sm">
          <div class="flex justify-between"><span class="text-muted-foreground">Subtotal</span><span>Rp {{ fmt(selected?.subtotal) }}</span></div>
          <div v-if="selected?.discount > 0" class="flex justify-between text-destructive"><span>Discount</span><span>− Rp {{ fmt(selected?.discount) }}</span></div>
          <div v-if="selected?.tax > 0" class="flex justify-between"><span class="text-muted-foreground">Tax</span><span>Rp {{ fmt(selected?.tax) }}</span></div>
          <div class="flex justify-between font-bold"><span>Total</span><span>Rp {{ fmt(selected?.total) }}</span></div>
        </div>
        <DialogFooter>
          <Button variant="outline" @click="selected = null" class="w-full">Close</Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Search } from 'lucide-vue-next';
import api from '@/api';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { Card } from '@/components/ui/card';
import { Table, TableHeader, TableBody, TableRow, TableHead, TableCell } from '@/components/ui/table';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

const orders     = ref([]);
const loading    = ref(true);
const pagination = ref(null);
const selected   = ref(null);
const page       = ref(1);
const filters    = ref({ date_from: '', date_to: '', payment_method: '' });

async function fetchOrders() {
  loading.value    = true;
  const params     = { page: page.value, ...filters.value };
  const res        = await api.get('/orders', { params });
  orders.value     = res.data.data;
  pagination.value = res.data;
  loading.value    = false;
}

onMounted(fetchOrders);

const fmt          = (n) => Number(n || 0).toLocaleString('id-ID');
const fmtDate      = (d) => new Date(d).toLocaleDateString('id-ID');
const statusVariant = (s) => ({ completed: 'success', pending: 'warning', cancelled: 'destructive' }[s] || 'secondary');
</script>
