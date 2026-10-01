<script setup>
import { Head, useForm, Link, usePage } from "@inertiajs/vue3";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import InputError from "@/components/InputError.vue";
import GuestLayout from "@/Layouts/GuestLayout.vue";

defineOptions({ layout: GuestLayout });

const appName = usePage().props.appName;

const form = useForm({
  name: "",
  email: "",
  password: "",
  password_confirmation: "",
});

const submit = () => {
  form.post("/register", {
    preserveScroll: true,
    onFinish: () => form.reset("password", "password_confirmation"),
  });
};
</script>

<template>
  <Head>
    <title>Daftar | {{ appName }}</title>
  </Head>
  <div class="w-full max-w-md">
    <Card class="shadow-lg">
      <CardHeader>
        <CardTitle class="text-xl">Buat akun baru</CardTitle>
        <CardDescription>
          Daftar untuk mulai menggunakan aplikasi.
        </CardDescription>
      </CardHeader>
      <CardContent>
        <form @submit.prevent="submit" class="flex flex-col gap-5">
          <div class="flex flex-col gap-2">
            <Label for="name">Nama Lengkap</Label>
            <Input
              id="name"
              v-model="form.name"
              type="text"
              name="name"
              autocomplete="name"
              placeholder="Nama Lengkap"
              :aria-invalid="form.errors.name ? 'true' : undefined"
              autofocus
            />
            <InputError :message="form.errors.name" />
          </div>

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
              autocomplete="new-password"
              placeholder="Min. 8 karakter"
              :aria-invalid="form.errors.password ? 'true' : undefined"
            />
            <InputError :message="form.errors.password" />
          </div>

          <div class="flex flex-col gap-2">
            <Label for="password_confirmation">Konfirmasi Password</Label>
            <Input
              id="password_confirmation"
              v-model="form.password_confirmation"
              type="password"
              name="password_confirmation"
              autocomplete="new-password"
              placeholder="Ulangi password"
              :aria-invalid="form.errors.password_confirmation ? 'true' : undefined"
            />
            <InputError :message="form.errors.password_confirmation" />
          </div>

          <Button type="submit" :disabled="form.processing" class="w-full">
            <span
              v-if="form.processing"
              class="mr-2 size-4 animate-spin rounded-full border-2 border-white/40 border-t-white"
            />
            Daftar
          </Button>
        </form>
      </CardContent>
    </Card>

    <p class="mt-6 text-center text-sm text-muted-foreground">
      Sudah punya akun?
      <Link href="/login" class="font-semibold text-foreground underline underline-offset-4">
        Masuk
      </Link>
    </p>
  </div>
</template>