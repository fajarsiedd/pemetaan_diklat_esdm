<script setup>
import { computed, ref, watch } from "vue";
import { Head, router, usePage } from "@inertiajs/vue3";
import { Eye, Search, Target, Users, X } from "@lucide/vue";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import {
  Table, TableHeader, TableBody, TableRow, TableHead, TableCell,
} from "@/components/ui/table";
import {
  Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription,
} from "@/components/ui/dialog";
import Badge from "@/components/Badge.vue";
import Pagination from "@/components/Pagination.vue";
import AppLayout from "@/Layouts/AppLayout.vue";

defineOptions({ layout: AppLayout });

const props = defineProps({
  pelatihan: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
});

const appName = usePage().props.appName;

const kategoriLabels = {
  technical: "Technical",
  legal: "Legal",
  commercial: "Commercial",
  soft_skill: "Soft Skill",
};

const kategoriVariant = {
  technical: "default",
  legal: "success",
  commercial: "warning",
  soft_skill: "muted",
};

const statusVariant = {
  ditargetkan: "warning",
  sedang_proses: "default",
  selesai: "success",
};

const kategoriOptions = Object.keys(kategoriLabels);
const statusOptions = ["wajib", "opsional"];
const jabatanOptions = ["Inspektur Ketenagalistrikan", "Penyelidik Bumi"];

const filters = ref({
  search: props.filters.search ?? "",
  kategori: props.filters.kategori ?? "",
  status: props.filters.status ?? "",
  jabatan: props.filters.jabatan ?? "",
});

const hasActiveFilters = computed(() =>
  Object.values(filters.value).some((v) => v !== ""),
);

let searchTimeout;

const applyFilters = () => {
  clearTimeout(searchTimeout);
  router.get("/admin/target-pelatihan", { ...filters.value }, { preserveScroll: true, replace: true });
};

watch(
  filters,
  () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 300);
  },
  { deep: true },
);

watch(
  () => props.filters,
  (f) => {
    filters.value.search = f.search ?? "";
    filters.value.kategori = f.kategori ?? "";
    filters.value.status = f.status ?? "";
    filters.value.jabatan = f.jabatan ?? "";
  },
  { deep: true },
);

const resetFilters = () => {
  filters.value = { search: "", kategori: "", status: "", jabatan: "" };
};

const selected = ref(null);

const openDetail = (p) => {
  selected.value = p;
};

const dialogOpen = computed({
  get: () => selected.value !== null,
  set: (value) => {
    if (!value) selected.value = null;
  },
});

const employees = computed(() => {
  if (!selected.value) return [];

  return (selected.value.target_pelatihan ?? [])
    .map((t) => ({
      ...(t.pegawai ?? {}),
      prioritas: t.prioritas,
      status: t.status,
    }))
    .filter((e) => e.id);
});

const selectClass =
  "border-input h-9 w-full min-w-0 rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-3";
</script>

<template>
  <Head>
    <title>Target Pelatihan | {{ appName }}</title>
  </Head>
  <div class="flex flex-col gap-6">
    <div>
      <h1 class="text-2xl font-bold tracking-tight">Pemetaan Target Pelatihan</h1>
      <p class="text-muted-foreground">
        Ringkasan target pelatihan.
      </p>
    </div>

    <Card>
      <CardHeader>
        <CardTitle class="flex items-center gap-2 text-lg">
          <Target class="size-4" /> Daftar Diklat Berdasarkan Target Aktif
        </CardTitle>
        <CardDescription>
          Menampilkan seluruh pelatihan pada master grand design yang saat ini memiliki pegawai yang ditargetkan.
        </CardDescription>
      </CardHeader>
      <CardContent class="p-0 sm:p-6">
        <div class="mb-4 grid grid-cols-1 gap-3 border-b px-4 pb-4 sm:grid-cols-2 lg:grid-cols-4">
          <div class="flex flex-col gap-1.5 lg:col-span-1">
            <Label for="filter-search">Cari Pelatihan</Label>
            <div class="relative">
              <Search class="text-muted-foreground pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2" />
              <Input
                id="filter-search"
                v-model="filters.search"
                class="pl-9"
                placeholder="Cari judul / kode diklat..."
              />
            </div>
          </div>
          <div class="flex flex-col gap-1.5">
            <Label for="filter-kategori">Kategori</Label>
            <select id="filter-kategori" v-model="filters.kategori" :class="selectClass">
              <option value="">Semua Kategori</option>
              <option v-for="opt in kategoriOptions" :key="opt" :value="opt">
                {{ kategoriLabels[opt] }}
              </option>
            </select>
          </div>
          <div class="flex flex-col gap-1.5">
            <Label for="filter-status">Status</Label>
            <select id="filter-status" v-model="filters.status" :class="selectClass">
              <option value="">Semua Status</option>
              <option value="wajib">Wajib</option>
              <option value="opsional">Opsional</option>
            </select>
          </div>
          <div class="flex flex-col gap-1.5">
            <Label for="filter-jabatan">Target Jabatan</Label>
            <div class="flex gap-2">
              <select id="filter-jabatan" v-model="filters.jabatan" :class="selectClass">
                <option value="">Semua Jabatan</option>
                <option v-for="opt in jabatanOptions" :key="opt" :value="opt">
                  {{ opt }}
                </option>
              </select>
              <Button
                v-if="hasActiveFilters"
                variant="outline"
                size="icon"
                class="h-9 w-9 shrink-0"
                title="Reset filter"
                @click="resetFilters"
              >
                <X class="size-4" />
              </Button>
            </div>
          </div>
        </div>

        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>Judul Pelatihan</TableHead>
              <TableHead>Kategori</TableHead>
              <TableHead>Status</TableHead>
              <TableHead class="hidden md:table-cell">Target Jabatan</TableHead>
              <TableHead>Jumlah Pegawai Ditargetkan</TableHead>
              <TableHead class="text-right">Aksi</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="p in pelatihan.data" :key="p.id">
              <TableCell>
                <p class="font-medium">{{ p.judul }}</p>
                <p class="text-muted-foreground font-mono text-xs">{{ p.kode_diklat }}</p>
              </TableCell>
              <TableCell>
                <Badge :variant="kategoriVariant[p.kategori]">
                  {{ kategoriLabels[p.kategori] ?? p.kategori }}
                </Badge>
              </TableCell>
              <TableCell>
                <Badge :variant="p.status === 'wajib' ? 'default' : 'warning'">
                  {{ p.status === "wajib" ? "Wajib" : "Opsional" }}
                </Badge>
              </TableCell>
              <TableCell class="hidden md:table-cell">
                <div class="flex flex-wrap gap-1">
                  <Badge v-for="jb in p.target_jabatan" :key="jb" variant="outline">{{ jb }}</Badge>
                </div>
              </TableCell>
              <TableCell>
                <span class="inline-flex items-center gap-1.5 font-medium">
                  <Users class="text-muted-foreground size-4" />
                  {{ p.target_pelatihan_count }} Pegawai
                </span>
              </TableCell>
              <TableCell class="text-right">
                <Button variant="outline" size="sm" @click="openDetail(p)">
                  <Eye class="size-4" /> Lihat Detail
                </Button>
              </TableCell>
            </TableRow>
            <TableRow v-if="hasActiveFilters && !pelatihan.data.length">
              <TableCell colspan="6" class="h-24 text-center text-muted-foreground">
                Tidak ada pelatihan yang cocok dengan filter yang dipilih.
              </TableCell>
            </TableRow>
            <TableRow v-if="!hasActiveFilters && !pelatihan.data.length">
              <TableCell colspan="6" class="h-24 text-center text-muted-foreground">
                Belum ada pelatihan yang ditargetkan. Jalankan penghitungan target terlebih dahulu.
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>

        <Pagination :meta="pelatihan" />
      </CardContent>
    </Card>

    <Dialog v-model:open="dialogOpen">
      <DialogContent class="flex max-h-[85vh] flex-col gap-0 overflow-hidden p-0 sm:max-w-5xl">
        <DialogHeader class="border-b px-6 pt-6 pb-4">
          <DialogTitle>{{ selected?.judul }}</DialogTitle>
          <DialogDescription>
            {{ selected?.kode_diklat }} • {{ selected?.jenjang || "Semua Jenjang" }} • {{ selected?.subsektor }}
            — Daftar pegawai yang ditargetkan untuk pelatihan ini.
          </DialogDescription>
        </DialogHeader>

        <div class="w-full min-w-0 flex-1 min-h-0 px-6 pb-6">
          <div
            class="max-h-[70vh] min-h-0 w-full overflow-x-auto overflow-y-auto rounded-md border"
          >
            <table class="w-full min-w-[700px] caption-bottom text-sm">
              <TableHeader class="sticky top-0 z-10 bg-background">
                <TableRow>
                  <TableHead>Nama &amp; NIP</TableHead>
                  <TableHead>Jabatan &amp; Jenjang</TableHead>
                  <TableHead>Unit Kerja</TableHead>
                  <TableHead>Status</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-for="e in employees" :key="e.id">
                  <TableCell>
                    <p class="font-medium">{{ e.nama }}</p>
                    <p class="text-muted-foreground font-mono text-xs">{{ e.nip }}</p>
                  </TableCell>
                  <TableCell>
                    <p>{{ e.jabatan }}</p>
                    <p class="text-muted-foreground text-xs">Jenjang {{ e.jenjang }}</p>
                  </TableCell>
                  <TableCell>{{ e.unit_kerja }}</TableCell>
                  <TableCell>
                    <Badge :variant="statusVariant[e.status] ?? 'muted'">
                      {{ e.status === "ditargetkan" ? "Ditargetkan" : e.status === "sedang_proses" ? "Sedang Proses" : "Selesai" }}
                    </Badge>
                  </TableCell>
                </TableRow>
                <TableRow v-if="!employees.length">
                  <TableCell colspan="4" class="h-24 text-center text-muted-foreground">
                    Tidak ada pegawai yang ditargetkan pada pelatihan ini.
                  </TableCell>
                </TableRow>
              </TableBody>
            </table>
          </div>
        </div>
      </DialogContent>
    </Dialog>
  </div>
</template>