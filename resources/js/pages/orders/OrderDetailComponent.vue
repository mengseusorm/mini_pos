<template>
  <Dialog :open="!!order" @update:open="val => { if (!val) emit('update:order', null) }">
    <DialogContent class="max-w-md max-h-[90vh] overflow-y-auto">
      <DialogHeader>
        <DialogTitle>Order #{{ order?.id }}</DialogTitle>
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
          <TableRow v-for="i in order?.items" :key="i.id">
            <TableCell>{{ i.item?.name }}</TableCell>
            <TableCell class="text-right">{{ i.quantity }}</TableCell>
            <TableCell class="text-right">Rp {{ fmt(i.subtotal) }}</TableCell>
          </TableRow>
        </TableBody>
      </Table>
      <Separator />
      <div class="space-y-1.5 text-sm">
        <div class="flex justify-between">
          <span class="text-muted-foreground">Subtotal</span>
          <span>Rp {{ fmt(order?.subtotal) }}</span>
        </div>
        <div v-if="order?.discount > 0" class="flex justify-between text-destructive">
          <span>Discount</span><span>− Rp {{ fmt(order?.discount) }}</span>
        </div>
        <div v-if="order?.tax > 0" class="flex justify-between">
          <span class="text-muted-foreground">Tax</span>
          <span>Rp {{ fmt(order?.tax) }}</span>
        </div>
        <div class="flex justify-between font-bold">
          <span>Total</span><span>Rp {{ fmt(order?.total) }}</span>
        </div>
      </div>
      <DialogFooter>
        <Button variant="outline" @click="emit('update:order', null)" class="w-full">Close</Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup>
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { Table, TableHeader, TableBody, TableRow, TableHead, TableCell } from '@/components/ui/table';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';

defineProps({
  order: { type: Object, default: null },
});

const emit = defineEmits(['update:order']);

const fmt = (n) => Number(n || 0).toLocaleString('id-ID');
</script>
