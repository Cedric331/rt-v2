<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Badge } from '@/components/ui/badge';
import { Copy, Plus, Edit, Trash2, Filter, X, Tag, Settings } from 'lucide-vue-next';
import AlertError from '@/components/AlertError.vue';
import Toast from '@/components/Toast.vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import Pagination from '@/components/Pagination.vue';

interface ResponseTemplate {
    id: number;
    content: string;
    type: string | null;
    usage_count: number;
    categories: Category[];
}

interface Category {
    id: number;
    name: string;
    slug: string;
}

interface PaginationData {
    data: ResponseTemplate[];
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

const props = defineProps<{
    responseTemplates: PaginationData | ResponseTemplate[];
    categories: Category[];
    types: string[];
    filters: {
        type?: string;
        category_id?: number;
        usage_order?: string;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

const showCreateDialog = ref(false);
const showEditDialog = ref(false);
const showCategoryDialog = ref(false);
const showCategoryManageDialog = ref(false);
const showEditCategoryDialog = ref(false);
const showDeleteTemplateDialog = ref(false);
const showDeleteCategoryDialog = ref(false);
const editingTemplate = ref<ResponseTemplate | null>(null);
const editingCategory = ref<Category | null>(null);
const templateToDelete = ref<ResponseTemplate | null>(null);
const categoryToDelete = ref<Category | null>(null);
const selectedType = ref<string>(props.filters.type || '');
const selectedCategory = ref<number | null>(props.filters.category_id || null);
const usageOrder = ref<string>(props.filters.usage_order || 'desc');
const toastMessage = ref<string>('');
const showToast = ref(false);

const createForm = useForm({
    content: '',
    type: '',
    category_ids: [] as number[],
});

const editForm = useForm({
    content: '',
    type: '',
    category_ids: [] as number[],
});

const categoryForm = useForm({
    name: '',
});

const editCategoryForm = useForm({
    name: '',
});

// Check if responseTemplates is paginated or a simple array
const isPaginated = computed(() => {
    return props.responseTemplates && typeof props.responseTemplates === 'object' && 'data' in props.responseTemplates;
});

const displayedTemplates = computed(() => {
    if (isPaginated.value) {
        return (props.responseTemplates as PaginationData).data;
    }
    return props.responseTemplates as ResponseTemplate[];
});

const paginationLinks = computed(() => {
    if (isPaginated.value) {
        return (props.responseTemplates as PaginationData).links;
    }
    return [];
});

const applyFilters = () => {
    const filters: Record<string, any> = {};
    if (selectedType.value) filters.type = selectedType.value;
    if (selectedCategory.value) filters.category_id = selectedCategory.value;
    if (usageOrder.value) filters.usage_order = usageOrder.value;

    router.get(dashboard().url, filters, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    selectedType.value = '';
    selectedCategory.value = null;
    usageOrder.value = 'desc';
    router.get(dashboard().url, {}, {
        preserveState: true,
        preserveScroll: true,
    });
};

const openCreateDialog = () => {
    createForm.reset();
    createForm.clearErrors();
    showCreateDialog.value = true;
};

const openEditDialog = (template: ResponseTemplate) => {
    editingTemplate.value = template;
    editForm.content = template.content;
    editForm.type = template.type || '';
    editForm.category_ids = template.categories.map(c => c.id);
    editForm.clearErrors();
    showEditDialog.value = true;
};

const closeEditDialog = () => {
    editingTemplate.value = null;
    showEditDialog.value = false;
};

const openEditCategoryDialog = (category: Category) => {
    editingCategory.value = category;
    editCategoryForm.name = category.name;
    editCategoryForm.clearErrors();
    showEditCategoryDialog.value = true;
};

const closeEditCategoryDialog = () => {
    editingCategory.value = null;
    showEditCategoryDialog.value = false;
};

const openDeleteTemplateDialog = (template: ResponseTemplate) => {
    templateToDelete.value = template;
    showDeleteTemplateDialog.value = true;
};

const confirmDeleteTemplate = () => {
    if (!templateToDelete.value) return;
    
    router.delete(`/response-templates/${templateToDelete.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            templateToDelete.value = null;
        },
    });
};

const submitCreate = () => {
    createForm.post('/response-templates', {
        onSuccess: () => {
            showCreateDialog.value = false;
            createForm.reset();
        },
    });
};

const submitEdit = () => {
    if (!editingTemplate.value) return;

    editForm.put(`/response-templates/${editingTemplate.value.id}`, {
        onSuccess: () => {
            closeEditDialog();
        },
    });
};

const copyContent = async (template: ResponseTemplate) => {
    try {
        // Copy directly from template content with all formatting
        await navigator.clipboard.writeText(template.content);
        
        // Then increment usage count via API
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const response = await fetch(`/response-templates/${template.id}/copy`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
        });

        if (!response.ok) {
            throw new Error('Failed to increment usage count');
        }

        // Show success toast
        toastMessage.value = 'Contenu copié dans le presse-papiers !';
        showToast.value = true;

        // Refresh the page to update usage count
        router.reload({ only: ['responseTemplates'], preserveScroll: true });
    } catch (error) {
        console.error('Error copying content:', error);
        toastMessage.value = 'Erreur lors de la copie';
        showToast.value = true;
    }
};

const submitCategory = () => {
    categoryForm.post('/categories', {
        onSuccess: () => {
            showCategoryDialog.value = false;
            categoryForm.reset();
        },
    });
};

const submitEditCategory = () => {
    if (!editingCategory.value) return;

    editCategoryForm.put(`/categories/${editingCategory.value.id}`, {
        onSuccess: () => {
            closeEditCategoryDialog();
        },
    });
};

const openDeleteCategoryDialog = (category: Category) => {
    categoryToDelete.value = category;
    showDeleteCategoryDialog.value = true;
};

const confirmDeleteCategory = () => {
    if (!categoryToDelete.value) return;
    
    router.delete(`/categories/${categoryToDelete.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            categoryToDelete.value = null;
        },
    });
};
</script>

<template>
    <Head title="Dashboard - Réponses Types" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">
            <!-- Toast Notification -->
            <Toast
                v-if="showToast"
                :message="toastMessage"
                type="success"
                @close="showToast = false"
            />

            <!-- Header with filters and actions -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent dark:from-blue-400 dark:to-indigo-400">
                        Réponses Types
                    </h1>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        Gérez vos réponses types et catégories
                    </p>
                </div>

                <div class="flex gap-2 flex-wrap">
                    <Dialog v-model:open="showCategoryManageDialog">
                        <DialogTrigger as-child>
                            <Button variant="outline" class="gap-2 bg-gradient-to-r from-purple-50 to-pink-50 hover:from-purple-100 hover:to-pink-100 dark:from-purple-900/20 dark:to-pink-900/20 dark:hover:from-purple-900/30 dark:hover:to-pink-900/30 border-purple-200 dark:border-purple-800">
                                <Settings class="h-4 w-4 text-purple-600 dark:text-purple-400" />
                                Gérer les catégories
                            </Button>
                        </DialogTrigger>
                        <DialogContent class="max-w-2xl">
                            <DialogHeader>
                                <DialogTitle class="text-xl">Gestion des catégories</DialogTitle>
                                <DialogDescription>
                                    Créez, modifiez ou supprimez vos catégories
                                </DialogDescription>
                            </DialogHeader>
                            <div class="space-y-4">
                                <div class="flex justify-end">
                                    <Dialog v-model:open="showCategoryDialog">
                                        <DialogTrigger as-child>
                                            <Button class="gap-2 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700">
                                                <Tag class="h-4 w-4" />
                                                Nouvelle catégorie
                                            </Button>
                                        </DialogTrigger>
                                        <DialogContent>
                                            <DialogHeader>
                                                <DialogTitle>Créer une catégorie</DialogTitle>
                                                <DialogDescription>
                                                    Ajoutez une nouvelle catégorie pour organiser vos réponses types
                                                </DialogDescription>
                                            </DialogHeader>
                                            <form @submit.prevent="submitCategory" class="space-y-4">
                                                <div class="grid gap-2">
                                                    <Label for="category-name">Nom de la catégorie</Label>
                                                    <Input
                                                        id="category-name"
                                                        v-model="categoryForm.name"
                                                        placeholder="Ex: Support client"
                                                        required
                                                    />
                                                    <AlertError v-if="categoryForm.errors.name" :errors="[categoryForm.errors.name]" />
                                                </div>
                                                <DialogFooter>
                                                    <Button type="button" variant="outline" @click="showCategoryDialog = false">
                                                        Annuler
                                                    </Button>
                                                    <Button type="submit" :disabled="categoryForm.processing" class="bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700">
                                                        Créer
                                                    </Button>
                                                </DialogFooter>
                                            </form>
                                        </DialogContent>
                                    </Dialog>
                                </div>

                                <div class="border rounded-lg divide-y">
                                    <div
                                        v-for="category in categories"
                                        :key="category.id"
                                        class="p-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
                                    >
                                        <div class="flex items-center gap-3">
                                            <div class="h-3 w-3 rounded-full bg-gradient-to-r from-purple-500 to-pink-500"></div>
                                            <span class="font-medium">{{ category.name }}</span>
                                        </div>
                                        <div class="flex gap-2">
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                @click="openEditCategoryDialog(category)"
                                                title="Modifier"
                                            >
                                                <Edit class="h-4 w-4" />
                                            </Button>
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                @click="openDeleteCategoryDialog(category)"
                                                title="Supprimer"
                                            >
                                                <Trash2 class="h-4 w-4 text-destructive" />
                                            </Button>
                                        </div>
                                    </div>
                                    <div v-if="categories.length === 0" class="p-8 text-center text-muted-foreground">
                                        Aucune catégorie créée pour le moment
                                    </div>
                                </div>
                            </div>
                        </DialogContent>
                    </Dialog>

                    <Dialog v-model:open="showCategoryDialog">
                        <DialogTrigger as-child>
                            <Button variant="outline" class="gap-2">
                                <Tag class="h-4 w-4" />
                                Nouvelle catégorie
                            </Button>
                        </DialogTrigger>
                        <DialogContent>
                            <DialogHeader>
                                <DialogTitle>Créer une catégorie</DialogTitle>
                                <DialogDescription>
                                    Ajoutez une nouvelle catégorie pour organiser vos réponses types
                                </DialogDescription>
                            </DialogHeader>
                            <form @submit.prevent="submitCategory" class="space-y-4">
                                <div class="grid gap-2">
                                    <Label for="category-name">Nom de la catégorie</Label>
                                    <Input
                                        id="category-name"
                                        v-model="categoryForm.name"
                                        placeholder="Ex: Support client"
                                        required
                                    />
                                    <AlertError v-if="categoryForm.errors.name" :errors="[categoryForm.errors.name]" />
                                </div>
                                <DialogFooter>
                                    <Button type="button" variant="outline" @click="showCategoryDialog = false">
                                        Annuler
                                    </Button>
                                    <Button type="submit" :disabled="categoryForm.processing" class="bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700">
                                        Créer
                                    </Button>
                                </DialogFooter>
                            </form>
                        </DialogContent>
                    </Dialog>

                    <Dialog v-model:open="showCreateDialog">
                        <DialogTrigger as-child>
                            <Button class="gap-2 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 shadow-lg">
                                <Plus class="h-4 w-4" />
                                Nouvelle réponse type
                            </Button>
                        </DialogTrigger>
                        <DialogContent class="max-w-2xl">
                            <DialogHeader>
                                <DialogTitle>Créer une réponse type</DialogTitle>
                                <DialogDescription>
                                    Ajoutez une nouvelle réponse type réutilisable
                                </DialogDescription>
                            </DialogHeader>
                            <form @submit.prevent="submitCreate" class="space-y-4">
                                <div class="grid gap-2">
                                    <Label for="content">Contenu</Label>
                                    <textarea
                                        id="content"
                                        v-model="createForm.content"
                                        class="min-h-[200px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 font-mono whitespace-pre-wrap"
                                        placeholder="Entrez le contenu de la réponse type..."
                                        required
                                    />
                                    <AlertError v-if="createForm.errors.content" :errors="[createForm.errors.content]" />
                                </div>
                                <div class="grid gap-2">
                                    <Label for="type">Tag</Label>
                                    <Input
                                        id="type"
                                        v-model="createForm.type"
                                        placeholder="Ex: Email, SMS, Chat..."
                                    />
                                    <AlertError v-if="createForm.errors.type" :errors="[createForm.errors.type]" />
                                </div>
                                <div class="grid gap-2">
                                    <Label>Catégories</Label>
                                    <div class="flex flex-wrap gap-2">
                                        <label
                                            v-for="category in categories"
                                            :key="category.id"
                                            class="flex cursor-pointer items-center space-x-2 rounded-md border p-2 hover:bg-accent transition-colors"
                                        >
                                            <input
                                                type="checkbox"
                                                :value="category.id"
                                                v-model="createForm.category_ids"
                                                class="rounded"
                                            />
                                            <span class="text-sm">{{ category.name }}</span>
                                        </label>
                                    </div>
                                </div>
                                <DialogFooter>
                                    <Button type="button" variant="outline" @click="showCreateDialog = false">
                                        Annuler
                                    </Button>
                                    <Button type="submit" :disabled="createForm.processing" class="bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700">
                                        Créer
                                    </Button>
                                </DialogFooter>
                            </form>
                        </DialogContent>
                    </Dialog>
                </div>
            </div>

            <!-- Filters -->
            <Card class="border-2 border-blue-100 dark:border-blue-900/30 bg-gradient-to-br from-blue-50/50 to-indigo-50/50 dark:from-blue-950/20 dark:to-indigo-950/20">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2 text-blue-700 dark:text-blue-300">
                        <Filter class="h-5 w-5" />
                        Filtres
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-4 md:grid-cols-3">
                        <div class="grid gap-2">
                            <Label for="filter-type">Tag</Label>
                            <select
                                id="filter-type"
                                v-model="selectedType"
                                @change="applyFilters"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                            >
                                <option value="">Tous les tags</option>
                                <option v-for="type in types" :key="type" :value="type">
                                    {{ type }}
                                </option>
                            </select>
                        </div>
                        <div class="grid gap-2">
                            <Label for="filter-category">Catégorie</Label>
                            <select
                                id="filter-category"
                                v-model="selectedCategory"
                                @change="applyFilters"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                            >
                                <option :value="null">Toutes les catégories</option>
                                <option v-for="category in categories" :key="category.id" :value="category.id">
                                    {{ category.name }}
                                </option>
                            </select>
                        </div>
                        <div class="grid gap-2">
                            <Label for="filter-usage-order">Trier par utilisations</Label>
                            <select
                                id="filter-usage-order"
                                v-model="usageOrder"
                                @change="applyFilters"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                            >
                                <option value="desc">Plus utilisées en premier</option>
                                <option value="asc">Moins utilisées en premier</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-4">
                        <Button variant="outline" @click="clearFilters" class="gap-2">
                            <X class="h-4 w-4" />
                            Réinitialiser les filtres
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <!-- Response Templates List -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <Card
                    v-for="template in displayedTemplates"
                    :key="template.id"
                    class="group hover:shadow-xl transition-all duration-300 border-2 hover:border-blue-300 dark:hover:border-blue-700 bg-gradient-to-br from-white to-gray-50 dark:from-gray-900 dark:to-gray-800 aspect-square flex flex-col"
                >
                    <CardHeader class="flex-1 flex flex-col overflow-hidden pb-2">
                        <div class="flex flex-wrap gap-1.5 mb-3 min-h-[24px]">
                            <Badge v-if="template.type" variant="secondary" class="bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300 text-xs px-2 py-0.5 whitespace-nowrap">
                                {{ template.type }}
                            </Badge>
                            <Badge
                                v-for="category in template.categories"
                                :key="category.id"
                                variant="outline"
                                class="border-purple-300 text-purple-700 dark:border-purple-700 dark:text-purple-300 text-xs px-2 py-0.5 whitespace-nowrap"
                            >
                                {{ category.name }}
                            </Badge>
                        </div>
                        <CardTitle class="text-base mb-0 flex-1 overflow-hidden">
                            <button
                                @click="copyContent(template)"
                                class="text-left w-full h-full hover:text-blue-600 dark:hover:text-blue-400 transition-colors cursor-pointer group-hover:underline whitespace-pre-wrap break-words overflow-auto"
                                title="Cliquer pour copier"
                                style="white-space: pre-wrap; word-break: break-word;"
                            >
                                {{ template.content }}
                            </button>
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="pt-0">
                        <div class="flex items-center justify-between">
                            <div class="text-xs text-muted-foreground flex items-center gap-1">
                                <Copy class="h-3 w-3" />
                                <span class="font-medium">{{ template.usage_count }}</span> utilisation{{ template.usage_count > 1 ? 's' : '' }}
                            </div>
                            <div class="flex gap-1">
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    @click.stop="openEditDialog(template)"
                                    title="Modifier"
                                    class="h-7 w-7 p-0 hover:bg-blue-50 dark:hover:bg-blue-900/20"
                                >
                                    <Edit class="h-3 w-3" />
                                </Button>
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    @click.stop="openDeleteTemplateDialog(template)"
                                    title="Supprimer"
                                    class="h-7 w-7 p-0 hover:bg-red-50 dark:hover:bg-red-900/20"
                                >
                                    <Trash2 class="h-3 w-3 text-destructive" />
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div v-if="displayedTemplates.length === 0" class="text-center py-12">
                <p class="text-muted-foreground">Aucune réponse type trouvée.</p>
            </div>

            <!-- Pagination -->
            <Pagination v-if="isPaginated && paginationLinks.length > 3" :links="paginationLinks" />

            <!-- Edit Dialog -->
            <Dialog v-model:open="showEditDialog">
                <DialogContent class="max-w-2xl" v-if="editingTemplate">
                    <DialogHeader>
                        <DialogTitle>Modifier la réponse type</DialogTitle>
                        <DialogDescription>
                            Modifiez le contenu et les propriétés de cette réponse type
                        </DialogDescription>
                    </DialogHeader>
                    <form @submit.prevent="submitEdit" class="space-y-4">
                        <div class="grid gap-2">
                            <Label for="edit-content">Contenu</Label>
                            <textarea
                                id="edit-content"
                                v-model="editForm.content"
                                class="min-h-[200px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 font-mono whitespace-pre-wrap"
                                placeholder="Entrez le contenu de la réponse type..."
                                required
                            />
                            <AlertError v-if="editForm.errors.content" :errors="[editForm.errors.content]" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="edit-type">Tag</Label>
                            <Input
                                id="edit-type"
                                v-model="editForm.type"
                                placeholder="Ex: Email, SMS, Chat..."
                            />
                            <AlertError v-if="editForm.errors.type" :errors="[editForm.errors.type]" />
                        </div>
                        <div class="grid gap-2">
                            <Label>Catégories</Label>
                            <div class="flex flex-wrap gap-2">
                                <label
                                    v-for="category in categories"
                                    :key="category.id"
                                    class="flex cursor-pointer items-center space-x-2 rounded-md border p-2 hover:bg-accent transition-colors"
                                >
                                    <input
                                        type="checkbox"
                                        :value="category.id"
                                        v-model="editForm.category_ids"
                                        class="rounded"
                                    />
                                    <span class="text-sm">{{ category.name }}</span>
                                </label>
                            </div>
                        </div>
                        <DialogFooter>
                            <Button type="button" variant="outline" @click="closeEditDialog">
                                Annuler
                            </Button>
                            <Button type="submit" :disabled="editForm.processing" class="bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700">
                                Enregistrer
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            <!-- Edit Category Dialog -->
            <Dialog v-model:open="showEditCategoryDialog">
                <DialogContent v-if="editingCategory">
                    <DialogHeader>
                        <DialogTitle>Modifier la catégorie</DialogTitle>
                        <DialogDescription>
                            Modifiez le nom de cette catégorie
                        </DialogDescription>
                    </DialogHeader>
                    <form @submit.prevent="submitEditCategory" class="space-y-4">
                        <div class="grid gap-2">
                            <Label for="edit-category-name">Nom de la catégorie</Label>
                            <Input
                                id="edit-category-name"
                                v-model="editCategoryForm.name"
                                placeholder="Ex: Support client"
                                required
                            />
                            <AlertError v-if="editCategoryForm.errors.name" :errors="[editCategoryForm.errors.name]" />
                        </div>
                        <DialogFooter>
                            <Button type="button" variant="outline" @click="closeEditCategoryDialog">
                                Annuler
                            </Button>
                            <Button type="submit" :disabled="editCategoryForm.processing" class="bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700">
                                Enregistrer
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            <!-- Delete Template Confirmation Dialog -->
            <ConfirmDialog
                v-model:open="showDeleteTemplateDialog"
                title="Supprimer la réponse type"
                description="Êtes-vous sûr de vouloir supprimer cette réponse type ? Cette action est irréversible."
                confirm-text="Supprimer"
                cancel-text="Annuler"
                @confirm="confirmDeleteTemplate"
            />

            <!-- Delete Category Confirmation Dialog -->
            <ConfirmDialog
                v-model:open="showDeleteCategoryDialog"
                title="Supprimer la catégorie"
                description="Êtes-vous sûr de vouloir supprimer cette catégorie ? Cette action est irréversible."
                confirm-text="Supprimer"
                cancel-text="Annuler"
                @confirm="confirmDeleteCategory"
            />
        </div>
    </AppLayout>
</template>
