<script setup>
import { ref, watch } from "vue";
import { router, useForm, usePage } from "@inertiajs/vue3";
import { Head, Link } from "@inertiajs/vue3";
import { Plus, Pencil, Trash2, Search, Upload } from "@lucide/vue";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import {
  Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription,
  DialogFooter, DialogClose,
} from "@/components/ui/dialog";
import Badge from "@/components/Badge.vue";
import Pagination from "@/components/Pagination.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { useGlobalFilters } from "@/composables/useGlobalFilters";

defineOptions({ layout: AppLayout });

const { activeGlobalQuery } = useGlobalFilters();

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

const search = ref(props.filters.search ?? "");

let searchTimeout;
watch(search, (value) => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    router.get(
      "/admin/pelatihan",
      { search: value, ...activeGlobalQuery() },
      { preserveState: true, replace: true },
    );
  }, 300);
});

const destroy = (p) => {
  if (confirm(`Hapus master pelatihan "${p.judul}"?`)) {
    router.delete(`/admin/pelatihan/${p.id}`, { preserveScroll: true });
  }
};

const importOpen = ref(false);
const importForm = useForm({ file: null });

const submitImport = () => {
  importForm.post("/admin/pelatihan/import", {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => {
      importOpen.value = false;
      importForm.reset();
    },
  });
};
</script>

<template>
  <Head>
    <title>Master Pelatihan | {{ appName }}</title>
  </Head>
  <div class="flex flex-col gap-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h1 class="text-2xl font-bold tracking-tight">Master Pelatihan</h1>
        <p class="text-muted-foreground">Grand design diklat untuk seluruh jenjang</p>
      </div>
      <div class="flex items-center gap-2">
        <Button variant="outline" @click="importOpen = true">
          <Upload class="size-4" /> Import Excel
        </Button>
        <Link :href="'/admin/pelatihan/create'">
          <Button><Plus class="size-4" /> Tambah Pelatihan</Button>
        </Link>
      </div>
    </div>

    <Dialog v-model:open="importOpen">
      <DialogContent class="sm:max-w-md">
        <DialogHeader>
          <DialogTitle>Import Master Pelatihan</DialogTitle>
          <DialogDescription>
            Unggah file Excel (.xlsx, .xls, atau .csv). Kolom yang dibutuhkan:
            kode_diklat, judul, jabatan, jenjang, status, subsektor, kategori.
          </DialogDescription>
        </DialogHeader>
        <div class="flex flex-col gap-4">
          <div class="flex flex-col gap-2">
            <Label for="import-file">File Excel</Label>
            <Input
              id="import-file"
              type="file"
              accept=".xlsx,.xls,.csv"
              @input="importForm.file = $event.target.files[0]"
            />
          </div>
          <p v-if="importForm.errors.file" class="text-sm text-destructive">
            {{ importForm.errors.file }}
          </p>
        </div>
        <DialogFooter>
          <DialogClose as-child>
            <Button type="button" variant="outline">Batal</Button>
          </DialogClose>
          <Button
            type="button"
            :disabled="importForm.processing || !importForm.file"
            @click="submitImport"
          >
            <Upload class="size-4" />
            {{ importForm.processing ? "Mengimpor..." : "Import" }}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <Card>
      <CardHeader class="flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <CardTitle>Daftar Diklat</CardTitle>
        <div class="relative w-full sm:w-64">
          <Search class="text-muted-foreground pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2" />
          <Input v-model="search" class="pl-9" placeholder="Cari pelatihan..." />
        </div>
      </CardHeader>
      <CardContent class="p-0 sm:p-6">
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="border-b text-left text-muted-foreground">
                <th class="px-4 py-3 font-medium">Kode Diklat</th>
                <th class="px-4 py-3 font-medium">Judul</th>
                <th class="px-4 py-3 font-medium">Jenjang</th>
                <th class="hidden px-4 py-3 font-medium md:table-cell">Target Jabatan</th>
                <th class="px-4 py-3 font-medium">Kategori</th>
                <th class="px-4 py-3 font-medium">Status</th>
                <th class="px-4 py-3 text-right font-medium">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="p in pelatihan.data" :key="p.id" class="border-b last:border-0 hover:bg-accent/40">
                <td class="px-4 py-3 font-mono text-xs">{{ p.kode_diklat }}</td>
                <td class="px-4 py-3 font-medium">{{ p.judul }}</td>
                <td class="px-4 py-3"><Badge variant="secondary">{{ p.jenjang || "Semua Jenjang" }}</Badge></td>
                <td class="hidden px-4 py-3 md:table-cell">
                  <div class="flex flex-wrap gap-1">
                    <Badge v-for="jb in p.target_jabatan" :key="jb" variant="outline">{{ jb }}</Badge>
                  </div>
                </td>
                <td class="px-4 py-3">
                  <Badge variant="muted">{{ kategoriLabels[p.kategori] ?? p.kategori }}</Badge>
                </td>
                <td class="px-4 py-3">
                  <Badge :variant="p.status === 'wajib' ? 'default' : 'warning'">
                    {{ p.status === "wajib" ? "Wajib" : "Opsional" }}
                  </Badge>
                </td>
                <td class="px-4 py-3">
                  <div class="flex items-center justify-end gap-1">
                    <Link :href="`/admin/pelatihan/${p.id}/edit`">
                      <Button variant="ghost" size="icon-sm" title="Edit pelatihan">
                        <Pencil class="size-4" />
                      </Button>
                    </Link>
                    <Button variant="ghost" size="icon-sm" class="text-destructive" @click="destroy(p)">
                      <Trash2 class="size-4" />
                    </Button>
                  </div>
                </td>
              </tr>
              <tr v-if="!pelatihan.data.length">
                <td colspan="7" class="px-4 py-8 text-center text-muted-foreground">Belum ada data pelatihan.</td>
              </tr>
            </tbody>
          </table>
        </div>
        <Pagination :meta="pelatihan" />
      </CardContent>
    </Card>
  </div>
</template>