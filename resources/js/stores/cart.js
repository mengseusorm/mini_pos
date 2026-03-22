import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

export const useCartStore = defineStore('cart', () => {
    const items = ref([]);

    const total    = computed(() => items.value.reduce((s, i) => s + i.price * i.quantity, 0));
    const count    = computed(() => items.value.reduce((s, i) => s + i.quantity, 0));

    function addItem(item) {
        const existing = items.value.find(i => i.id === item.id);
        if (existing) {
            if (existing.quantity < item.stock) existing.quantity++;
        } else {
            items.value.push({ ...item, quantity: 1 });
        }
    }

    function removeItem(id) {
        items.value = items.value.filter(i => i.id !== id);
    }

    function updateQty(id, qty) {
        const item = items.value.find(i => i.id === id);
        if (item) {
            if (qty <= 0) removeItem(id);
            else item.quantity = Math.min(qty, item.stock);
        }
    }

    function clear() {
        items.value = [];
    }

    return { items, total, count, addItem, removeItem, updateQty, clear };
});
