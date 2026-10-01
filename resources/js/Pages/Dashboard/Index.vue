<script setup>
import { Head, usePage } from "@inertiajs/vue3";
import { Users, UserPlus, UserCheck } from "@lucide/vue";
import { Card, CardContent, CardHeader, CardTitle, CardDescription, CardFooter } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import AppLayout from "@/Layouts/AppLayout.vue";

defineOptions({ layout: AppLayout });

defineProps({
  stats: {
    type: Object,
    required: true,
  },
});

const page = usePage();
const user = page.props.auth?.user;
const appName = page.props.appName;

const today = new Date().toLocaleDateString("id-ID", {
  weekday: "long",
  year: "numeric",
  month: "long",
  day: "numeric",
});

const statCards = [
  {
    label: "Total Pengguna",
    value: "stats.total_users",
    icon: Users,
    description: "Akun yang terdaftar di aplikasi",
  },
  {
    label: "Pengguna Baru Bulan Ini",
    value: "stats.new_users_this_month",
    icon: UserPlus,
    description: "Registrasi sejak awal bulan",
  },
  {
    label: "Pengguna Terbaru",
    value: "stats.latest_user",
    icon: UserCheck,
    description: "Aplikasi siap digunakan",
  },
];
</script>

<template>
  <Head>
    <title>Dashboard | {{ appName }}</title>
  </Head>
  <div class="flex flex-col gap-6">
    <div>
      <h1 class="text-2xl font-bold tracking-tight">
        Halo, {{ user?.name }}! 👋
      </h1>
      <p class="text-muted-foreground">
        {{ today }}
      </p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
      <Card v-for="card in statCards" :key="card.label">
        <CardHeader class="gap-3">
          <CardTitle class="flex items-center justify-between text-sm font-medium text-muted-foreground">
            {{ card.label }}
            <span class="flex size-8 items-center justify-center rounded-lg bg-primary/10 text-primary">
              <component :is="card.icon" class="size-4" />
            </span>
          </CardTitle>
          <CardDescription>
            <span class="text-2xl font-bold text-foreground">
              {{ stats[card.value.replace('stats.', '')] }}
            </span>
          </CardDescription>
        </CardHeader>
      </Card>
    </div>

    <Card>
      <CardHeader>
        <CardTitle>Selamat datang di dashboard</CardTitle>
        <CardDescription>
          Fitur autentikasi (login & register) beserta dashboard dasar telah siap.
          Mulai kembangkan modul monitoring IKPB daerah Anda di sini.
        </CardDescription>
      </CardHeader>
      <CardFooter>
        <Button variant="outline" as="a" href="https://laravel.com/docs" target="_blank" rel="noopener noreferrer">
          Dokumentasi Laravel
        </Button>
      </CardFooter>
    </Card>
  </div>
</template>