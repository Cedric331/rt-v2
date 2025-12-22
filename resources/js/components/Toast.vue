<script setup lang="ts">
import { Alert, AlertDescription } from '@/components/ui/alert';
import { CheckCircle2, X, AlertCircle } from 'lucide-vue-next';
import { computed, onMounted } from 'vue';

interface Props {
    message: string;
    type?: 'success' | 'error' | 'info';
    duration?: number;
}

const props = withDefaults(defineProps<Props>(), {
    type: 'success',
    duration: 3000,
});

const emit = defineEmits<{
    close: [];
}>();

onMounted(() => {
    if (props.duration > 0) {
        setTimeout(() => {
            emit('close');
        }, props.duration);
    }
});

const alertVariant = computed(() => {
    switch (props.type) {
        case 'error':
            return 'destructive';
        case 'info':
            return 'default';
        default:
            return 'default';
    }
});

const iconClass = computed(() => {
    switch (props.type) {
        case 'error':
            return 'text-red-600 dark:text-red-400';
        case 'info':
            return 'text-blue-600 dark:text-blue-400';
        default:
            return 'text-green-600 dark:text-green-400';
    }
});

const bgClass = computed(() => {
    switch (props.type) {
        case 'error':
            return 'bg-red-50 border-red-200 dark:bg-red-950/20 dark:border-red-900';
        case 'info':
            return 'bg-blue-50 border-blue-200 dark:bg-blue-950/20 dark:border-blue-900';
        default:
            return 'bg-green-50 border-green-200 dark:bg-green-950/20 dark:border-green-900';
    }
});
</script>

<template>
    <div
        :class="['fixed top-4 right-4 z-50 min-w-[300px] max-w-md rounded-lg border p-4 shadow-lg', bgClass]"
        class="animate-in slide-in-from-top-5 fade-in-0"
    >
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-2 flex-1">
                <CheckCircle2 v-if="type === 'success'" :class="['h-5 w-5 flex-shrink-0', iconClass]" />
                <AlertCircle v-else-if="type === 'error'" :class="['h-5 w-5 flex-shrink-0', iconClass]" />
                <CheckCircle2 v-else :class="['h-5 w-5 flex-shrink-0', iconClass]" />
                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ message }}</p>
            </div>
            <button
                @click="emit('close')"
                class="rounded-sm opacity-70 ring-offset-background transition-opacity hover:opacity-100 focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 flex-shrink-0"
            >
                <X class="h-4 w-4" />
                <span class="sr-only">Close</span>
            </button>
        </div>
    </div>
</template>

