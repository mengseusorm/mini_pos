<script setup lang="ts">
import { cn } from '@/lib/utils'
import { DialogDescription } from 'radix-vue'
interface Props { class?: string }
const props = defineProps<Props>()
</script>
<template>
  <DialogDescription :class="cn('text-sm text-muted-foreground', props.class)">
    <slot />
  </DialogDescription>
</template>
