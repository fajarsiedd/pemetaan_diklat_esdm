<script setup>
import { ref } from "vue";
import { router } from "@inertiajs/vue3";
import { toast } from "vue-sonner";
import { RefreshCw, Users, BookOpen, Target, History, ClipboardList } from "@lucide/vue";
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import AppLayout from "@/Layouts/AppLayout.vue";

defineOptions({ layout: AppLayout });

defineProps({
  stats: {
    type: Object,
    required: true,
  },
});

const statCards = [
  { label: "Total Pegawai", key: "total_pegawai", icon: Users, description: "Seluruh pegawai terdaftar" },
  { label: "Master Pelatihan", key: "total_pelatihan", icon: BookOpen, description: "Grand design diklat" },
  { label: "Target Aktif", key: "total_target", icon: Target, description: "Target belum diselesaikan" },
  { label: "Riwayat Pelatihan", key: "total_riwayat", icon: History, description: "Pelatihan sudah ditempuh" },
];

const recalculating = ref(false);

const recalculate = () => {
  if (recalculating.value) return;
  recalculating.value = true;

  router.post(
    "/admin/dashboard/recalculate",
    {},
    {
      preserveScroll: true,
      onSuccess: (page) => {
        const flash = page.props.flash;
        if (flash?.success) toast.success(flash.success);
        if (flash?.error) toast.error(flash.error);
      },
      onError: (errors) => {
        toast.error(Object.values(errors).join(", ") || "Terjadi kesalahan saat menghitung ulang.");
      },
      onFinish: () => {
        recalculating.value = false;
      },
    },
  );
};
</script>

<template>
  <div class="flex flex-col gap-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h1 class="text-2xl font-bold tracking-tight">Dashboard Admin</h1>
        <p class="text-muted-foreground">Monitoring Inspektur Ketenagalistrikan & Penyelidik Bumi</p>
      </div>
      <Button @click="recalculate" :disabled="recalculating">
        <RefreshCw class="size-4" :class="{ 'animate-spin': recalculating }" />
        {{ recalculating ? "Menghitung ulang..." : "Recalculate Target Pelatihan" }}
      </Button>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
      <Card v-for="card in statCards" :key="card.key">
        <CardHeader class="gap-3">
          <CardTitle class="flex items-center justify-between text-sm font-medium text-muted-foreground">
            {{ card.label }}
            <span class="flex size-8 items-center justify-center rounded-lg bg-primary/10 text-primary">
              <component :is="card.icon" class="size-4" />
            </span>
          </CardTitle>
          <CardDescription>
            <span class="text-2xl font-bold text-foreground">{{ stats[card.key] }}</span>
          </CardDescription>
        </CardHeader>
      </Card>
    </div>

    <Card>
      <CardHeader class="gap-2">
        <CardTitle class="text-lg">Alur Kerja</CardTitle>
        <CardDescription>
          Target pelatihan dihitung otomatis berdasarkan kesenjangan penyelesaian antar kelompok
          pegawai (jabatan + jenjang). Hanya diklat dengan target jabatan yang cocok yang
          ditugaskan; pelatihan dengan jumlah peserta belum menyelesaikan terbanyak diprioritaskan
          pertama.
        </CardDescription>
      </CardHeader>
      <CardContent class="flex flex-wrap items-center gap-3 text-sm text-muted-foreground">
        <span class="flex items-center gap-2">
          <Users class="size-4" /> Data pegawai
        </span>
        <span>→</span>
        <span class="flex items-center gap-2">
          <ClipboardList class="size-4" /> Master diklat
        </span>
        <span>→</span>
        <span class="flex items-center gap-2">
          <Target class="size-4" /> Target maks 4/pegawai
        </span>
        <span>→</span>
        <span class="flex items-center gap-2">
          <History class="size-4" /> Riwayat
        </span>
      </CardContent>
    </Card>
  </div>
</template>