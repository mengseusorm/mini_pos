<template>
  <div class="min-h-screen flex items-center justify-center bg-muted/30">
    <div class="w-full max-w-sm">
      <Card class="shadow-lg">
        <CardHeader class="space-y-1 pb-4">
          <div class="flex justify-center mb-2">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary text-primary-foreground font-bold text-lg">
              P
            </div>
          </div>
          <CardTitle class="text-2xl text-center">Mini POS</CardTitle>
          <CardDescription class="text-center">Sign in to your account</CardDescription>
        </CardHeader>

        <CardContent>
          <form @submit.prevent="handleLogin" class="space-y-4">
            <div class="space-y-2">
              <Label for="email">Email</Label>
              <Input
                id="email"
                v-model="form.email"
                type="email"
                placeholder="admin@pos.com"
                required
                autocomplete="email"
              />
            </div>

            <div class="space-y-2">
              <Label for="password">Password</Label>
              <Input
                id="password"
                v-model="form.password"
                type="password"
                placeholder="••••••••"
                required
                autocomplete="current-password"
              />
            </div>

            <p v-if="error" class="text-sm text-destructive">{{ error }}</p>

            <Button type="submit" class="w-full" :disabled="loading">
              <Loader2 v-if="loading" class="mr-2 h-4 w-4 animate-spin" />
              {{ loading ? 'Signing in…' : 'Sign in' }}
            </Button>
          </form>
        </CardContent>
      </Card>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { Loader2 } from 'lucide-vue-next';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useAuthStore } from '@/stores/auth';

const auth   = useAuthStore();
const router = useRouter();

const form    = ref({ email: 'admin@pos.com', password: 'password' });
const loading = ref(false);
const error   = ref('');

async function handleLogin() {
  error.value   = '';
  loading.value = true;
  try {
    await auth.login(form.value.email, form.value.password);
    router.push('/dashboard');
  } catch (e) {
    error.value = e.response?.data?.message || 'Login failed.';
  } finally {
    loading.value = false;
  }
}
</script>
