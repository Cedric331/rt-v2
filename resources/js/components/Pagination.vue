<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Props {
    links: PaginationLink[];
}

defineProps<Props>();
</script>

<template>
    <div v-if="links && links.length > 3" class="flex items-center justify-center gap-2 mt-6">
        <template v-for="(link, index) in links" :key="index">
            <Link
                v-if="link.url"
                :href="link.url"
                :class="[
                    'px-3 py-2 rounded-md text-sm font-medium transition-colors',
                    link.active
                        ? 'bg-blue-600 text-white hover:bg-blue-700'
                        : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-300 dark:border-gray-600'
                ]"
            >
                <span v-if="index === 0" class="flex items-center gap-1">
                    <ChevronLeft class="h-4 w-4" />
                    <span class="hidden sm:inline">Précédent</span>
                </span>
                <span v-else-if="index === links.length - 1" class="flex items-center gap-1">
                    <span class="hidden sm:inline">Suivant</span>
                    <ChevronRight class="h-4 w-4" />
                </span>
                <span v-else>{{ link.label }}</span>
            </Link>
            <span
                v-else
                :class="[
                    'px-3 py-2 rounded-md text-sm font-medium',
                    'bg-gray-100 dark:bg-gray-700 text-gray-400 dark:text-gray-500 cursor-not-allowed'
                ]"
            >
                <span v-if="index === 0" class="flex items-center gap-1">
                    <ChevronLeft class="h-4 w-4" />
                    <span class="hidden sm:inline">Précédent</span>
                </span>
                <span v-else-if="index === links.length - 1" class="flex items-center gap-1">
                    <span class="hidden sm:inline">Suivant</span>
                    <ChevronRight class="h-4 w-4" />
                </span>
                <span v-else>{{ link.label }}</span>
            </span>
        </template>
    </div>
</template>

