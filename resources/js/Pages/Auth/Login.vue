<script setup>
import { Head, useForm, Link, usePage } from "@inertiajs/vue3";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Checkbox } from "@/components/ui/checkbox";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import InputError from "@/components/InputError.vue";
import GuestLayout from "@/Layouts/GuestLayout.vue";

defineOptions({ layout: GuestLayout });

const appName = usePage().props.appName;

const form = useForm({
  email: "",
  password: "",
  remember: false,
});

const submit = () => {
  form.post("/login", {
    preserveScroll: true,
    onFinish: () => form.reset("password"),
  });
};
</script>

<template>
  <Head>
    <title>Masuk | {{ appName }}</title>
  </Head>
  <div class="w-full max-w-md">
    <Card class="shadow-lg">
      <CardHeader>
        <CardTitle class="text-xl">Masuk ke akun Anda</CardTitle>
        <CardDescription>
          Silakan masuk untuk melanjutkan ke dashboard.
        </CardDescription>
      </CardHeader>
      <CardContent>
        <form @submit.prevent="submit" class="flex flex-col gap-5">
          <div class="flex flex-col gap-2">
            <Label for="email">Email</Label>
            <Input
              id="email"
              v-model="form.email"
              type="email"
              name="email"
              autocomplete="username"
              placeholder="nama@email.com"
              :aria-invalid="form.errors.email ? 'true' : undefined"
              autofocus
            />
            <InputError :message="form.errors.email" />
          </div>

          <div class="flex flex-col gap-2">
            <Label for="password">Password</Label>
            <Input
              id="password"
              v-model="form.password"
              type="password"
              name="password"
              autocomplete="current-password"
              placeholder="••••••••"
              :aria-invalid="form.errors.password ? 'true' : undefined"
            />
            <InputError :message="form.errors.password" />
          </div>

          <label class="flex items-center gap-2 text-sm text-muted-foreground">
            <Checkbox v-model="form.remember" />
            Ingat saya
          </label>

          <Button type="submit" :disabled="form.processing" class="w-full">
            <span
              v-if="form.processing"
              class="mr-2 size-4 animate-spin rounded-full border-2 border-white/40 border-t-white"
            />
            Masuk
          </Button>
        </form>
      </CardContent>
    </Card>

    <p class="mt-6 text-center text-sm text-muted-foreground">
      Belum punya akun?
      <Link href="/register" class="font-semibold text-foreground underline underline-offset-4">
        Daftar sekarang
      </Link>
    </p>
  </div>
</template>