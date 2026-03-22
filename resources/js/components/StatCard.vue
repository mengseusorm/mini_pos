<template>
  <div :class="colorMap[color]" class="rounded-xl p-4 text-white shadow">
    <div class="text-sm opacity-80 mb-1">{{ label }}</div>
    <div class="text-2xl font-bold">{{ value }}</div>
  </div>
</template>

<script setup>
defineProps({
  label: String,
  value: [String, Number],
  color: { type: String, default: 'blue' },
});

const colorMap = {
  blue:   'bg-blue-500',
  green:  'bg-green-500',
  purple: 'bg-purple-500',
  yellow: 'bg-yellow-500',
  red:    'bg-red-500',
  gray:   'bg-gray-500',
};
</script>
