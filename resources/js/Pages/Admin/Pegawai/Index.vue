<script setup>
import { ref, watch } from "vue";
import { router } from "@inertiajs/vue3";
import { Link } from "@inertiajs/vue3";
import { Plus, Pencil, Trash2, Search } from "@lucide/vue";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import Badge from "@/components/Badge.vue";
import Pagination from "@/components/Pagination.vue";
import AppLayout from "@/Layouts/AppLayout.vue";

defineOptions({ layout: AppLayout });

const props = defineProps({
  pegawai: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
});

const search = ref(props.filters.search ?? "");

let searchTimeout;
watch(search, (value) => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    router.get(
      "/admin/pegawai",
      { search: value },
      { preserveState: true, replace: true },
    );
  }, 300);
});

const destroy = (p) => {
  if (confirm(`Hapus pegawai ${p.nama}? Data target dan riwayat ikut terhapus.`)) {
    router.delete(`/admin/pegawai/${p.id}`, { preserveScroll: true });
  }
};
</script>

<template>
  <div class="flex flex-col gap-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h1 class="text-2xl font-bold tracking-tight">Data Pegawai</h1>
        <p class="text-muted-foreground">Kelola profil inspektur dan penyelidik bumi</p>
      </div>
      <Link :href="'/admin/pegawai/create'">
        <Button><Plus class="size-4" /> Tambah Pegawai</Button>
      </Link>
    </div>

    <Card>
      <CardHeader class="flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <CardTitle>Daftar Pegawai</CardTitle>
        <div class="relative w-full sm:w-64">
          <Search class="text-muted-foreground pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2" />
          <Input v-model="search" class="pl-9" placeholder="Cari pegawai..." />
        </div>
      </CardHeader>
      <CardContent class="p-0 sm:p-6">
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="border-b text-left text-muted-foreground">
                <th class="px-4 py-3 font-medium">Nama / NIP</th>
                <th class="px-4 py-3 font-medium">Jabatan</th>
                <th class="px-4 py-3 font-medium">Jenjang</th>
                <th class="hidden px-4 py-3 font-medium md:table-cell">Unit Kerja</th>
                <th class="px-4 py-3 text-center font-medium">Target</th>
                <th class="px-4 py-3 text-center font-medium">Riwayat</th>
                <th class="px-4 py-3 text-right font-medium">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="p in pegawai.data" :key="p.id" class="border-b last:border-0 hover:bg-accent/40">
                <td class="px-4 py-3">
                  <p class="font-medium">{{ p.nama }}</p>
                  <p class="text-xs text-muted-foreground">{{ p.nip }}</p>
                </td>
                <td class="px-4 py-3">{{ p.jabatan }}</td>
                <td class="px-4 py-3"><Badge variant="secondary">{{ p.jenjang }}</Badge></td>
                <td class="hidden px-4 py-3 text-muted-foreground md:table-cell">{{ p.unit_kerja }}</td>
                <td class="px-4 py-3 text-center">{{ p.target_count }}</td>
                <td class="px-4 py-3 text-center">{{ p.riwayat_count }}</td>
                <td class="px-4 py-3">
                  <div class="flex items-center justify-end gap-1">
                    <Link :href="`/admin/pegawai/${p.id}/profile`">
                      <Button variant="ghost" size="icon-sm" title="Edit profil">
                        <Pencil class="size-4" />
                      </Button>
                    </Link>
                    <Button variant="ghost" size="icon-sm" class="text-destructive" @click="destroy(p)">
                      <Trash2 class="size-4" />
                    </Button>
                  </div>
                </td>
              </tr>
              <tr v-if="!pegawai.data.length">
                <td colspan="7" class="px-4 py-8 text-center text-muted-foreground">Belum ada data pegawai.</td>
              </tr>
            </tbody>
          </table>
        </div>
        <Pagination :meta="pegawai" />
      </CardContent>
    </Card>
  </div>
</template>