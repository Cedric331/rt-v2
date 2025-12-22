<script setup lang="ts">
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { AlertTriangle } from 'lucide-vue-next';

interface Props {
    open: boolean;
    title: string;
    description: string;
    confirmText?: string;
    cancelText?: string;
    variant?: 'default' | 'destructive';
}

const props = withDefaults(defineProps<Props>(), {
    confirmText: 'Confirmer',
    cancelText: 'Annuler',
    variant: 'destructive',
});

const emit = defineEmits<{
    'update:open': [value: boolean];
    confirm: [];
}>();

const close = () => {
    emit('update:open', false);
};

const confirm = () => {
    emit('confirm');
    close();
};
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/20">
                        <AlertTriangle class="h-5 w-5 text-red-600 dark:text-red-400" />
                    </div>
                    <div>
                        <DialogTitle class="text-lg">{{ title }}</DialogTitle>
                        <DialogDescription class="mt-2">
                            {{ description }}
                        </DialogDescription>
                    </div>
                </div>
            </DialogHeader>
            <DialogFooter class="gap-2 sm:gap-0">
                <Button variant="outline" @click="close">
                    {{ cancelText }}
                </Button>
                <Button
                    :variant="variant"
                    @click="confirm"
                    class="bg-red-600 hover:bg-red-700 text-white"
                >
                    {{ confirmText }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

