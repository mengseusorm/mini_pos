<script setup lang="ts">
import { cn } from '@/lib/utils'
import { ChevronsUpDown } from 'lucide-vue-next'
import { SelectTrigger, SelectValue, useForwardProps } from 'radix-vue'
import type { SelectTriggerProps } from 'radix-vue'

interface Props extends /* @vue-ignore */ SelectTriggerProps {
  class?: string
  placeholder?: string
}

const props = defineProps<Props>()
const forwarded = useForwardProps(props)
</script>

<template>
  <SelectTrigger
    v-bind="forwarded"
    :class="cn(
      'flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 [&>span]:line-clamp-1',
      props.class
    )"
  >
    <slot>
      <SelectValue :placeholder="props.placeholder" />
    </slot>
    <ChevronsUpDown class="h-4 w-4 shrink-0 opacity-50" />
  </SelectTrigger>
</template>
