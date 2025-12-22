<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthBase from '@/layouts/AuthLayout.vue';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';
import { Form, Head } from '@inertiajs/vue3';
import { Lock, Mail } from 'lucide-vue-next';

defineProps<{
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
}>();
</script>

<template>
    <Head title="Connexion" />
    
    <div class="flex min-h-screen items-center justify-center bg-gradient-to-br from-blue-50 via-white to-indigo-50 p-4 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900">
        <div class="w-full max-w-md">
            <div class="rounded-2xl bg-white/80 backdrop-blur-sm shadow-2xl dark:bg-gray-800/80">
                <div class="p-8">
                    <div class="mb-8 text-center">
                        <div class="mb-4 flex justify-center">
                            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 shadow-lg">
                                <Lock class="h-8 w-8 text-white" />
                            </div>
                        </div>
                        <h1 class="mb-2 text-2xl font-bold text-gray-900 dark:text-white">
                            Connexion
                        </h1>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Connectez-vous à votre compte
                        </p>
                    </div>

                    <div
                        v-if="status"
                        class="mb-4 rounded-lg bg-green-50 p-3 text-center text-sm font-medium text-green-700 dark:bg-green-900/20 dark:text-green-400"
                    >
                        {{ status }}
                    </div>

                    <Form
                        v-bind="store.form()"
                        :reset-on-success="['password']"
                        v-slot="{ errors, processing }"
                        class="flex flex-col gap-5"
                    >
                        <div class="grid gap-5">
                            <div class="grid gap-2">
                                <Label for="email" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Adresse email
                                </Label>
                                <div class="relative">
                                    <Mail class="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" />
                                    <Input
                                        id="email"
                                        type="email"
                                        name="email"
                                        required
                                        autofocus
                                        :tabindex="1"
                                        autocomplete="email"
                                        placeholder="votre@email.com"
                                        class="pl-10"
                                    />
                                </div>
                                <InputError :message="errors.email" />
                            </div>

                            <div class="grid gap-2">
                                <div class="flex items-center justify-between">
                                    <Label for="password" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Mot de passe
                                    </Label>
                                    <TextLink
                                        v-if="canResetPassword"
                                        :href="request()"
                                        class="text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
                                        :tabindex="5"
                                    >
                                        Mot de passe oublié ?
                                    </TextLink>
                                </div>
                                <div class="relative">
                                    <Lock class="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" />
                                    <Input
                                        id="password"
                                        type="password"
                                        name="password"
                                        required
                                        :tabindex="2"
                                        autocomplete="current-password"
                                        placeholder="••••••••"
                                        class="pl-10"
                                    />
                                </div>
                                <InputError :message="errors.password" />
                            </div>

                            <div class="flex items-center">
                                <Label for="remember" class="flex cursor-pointer items-center space-x-2">
                                    <Checkbox id="remember" name="remember" :tabindex="3" />
                                    <span class="text-sm text-gray-700 dark:text-gray-300">Se souvenir de moi</span>
                                </Label>
                            </div>

                            <Button
                                type="submit"
                                class="mt-2 w-full bg-gradient-to-r from-blue-500 to-indigo-600 text-white shadow-lg transition-all hover:from-blue-600 hover:to-indigo-700 hover:shadow-xl"
                                :tabindex="4"
                                :disabled="processing"
                                data-test="login-button"
                            >
                                <Spinner v-if="processing" class="mr-2" />
                                <span v-if="!processing">Se connecter</span>
                                <span v-else>Connexion en cours...</span>
                            </Button>
                        </div>

                        <div
                            class="mt-4 text-center text-sm text-gray-600 dark:text-gray-400"
                            v-if="canRegister"
                        >
                            Vous n'avez pas de compte ?
                            <TextLink 
                                :href="register()" 
                                :tabindex="5"
                                class="font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
                            >
                                S'inscrire
                            </TextLink>
                        </div>
                    </Form>
                </div>
            </div>
        </div>
    </div>
</template>
