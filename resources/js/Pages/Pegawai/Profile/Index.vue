<script setup>
import { useForm } from "@inertiajs/vue3";
import { AlertTriangle } from "@lucide/vue";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Card, CardContent, CardHeader, CardTitle, CardDescription, CardFooter } from "@/components/ui/card";
import AppLayout from "@/Layouts/AppLayout.vue";
import PegawaiTraining from "@/components/PegawaiTraining.vue";

defineOptions({ layout: AppLayout });

const props = defineProps({
  pegawai: { type: Object, default: null },
  targets: { type: Array, default: () => [] },
  riwayat: { type: Array, default: () => [] },
  grandDesign: { type: Array, default: () => [] },
});

const JABATAN_OPTIONS = ["Inspektur Ketenagalistrikan", "Penyelidik Bumi"];
const JENJANG_OPTIONS = ["Ahli Pertama", "Ahli Muda", "Ahli Madya", "Ahli Madya-Pusaka"];
const initial = props.pegawai ?? {};

const profileForm = useForm({
  nama: initial.nama ?? "",
  nip: initial.nip ?? "",
  jabatan: initial.jabatan ?? "Inspektur Ketenagalistrikan",
  jenjang: initial.jenjang ?? "",
  unit_kerja: initial.unit_kerja ?? "",
  provinsi: initial.provinsi ?? "",
});

const saveProfile = () => {
  profileForm.put("/pegawai/profile", { preserveScroll: true });
};

const completeUrl = (target) => `/pegawai/targets/${target.id}/complete`;
const riwayatStoreUrl = "/pegawai/riwayat";
const riwayatDeleteUrl = (item) => `/pegawai/riwayat/${item.id}`;
const riwayatDownloadUrl = (item) => `/pegawai/riwayat/${item.id}/sertifikat`;

const selectClass =
  "border-input h-9 w-full min-w-0 rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-3";
</script>

<template>
  <div class="flex flex-col gap-6">
    <div>
      <h1 class="text-2xl font-bold tracking-tight">Profil Saya</h1>
      <p class="text-muted-foreground">
        {{ pegawai?.jabatan }} — Jenjang {{ pegawai?.jenjang }}
      </p>
    </div>

    <div v-if="!pegawai" class="flex items-start gap-3 rounded-lg border border-amber-300 bg-amber-50 p-4 text-sm text-amber-800 dark:border-amber-500/40 dark:bg-amber-500/10 dark:text-amber-300">
      <AlertTriangle class="mt-0.5 size-4 shrink-0" />
      <p>Profil pegawai belum lengkap. Lengkapi data di bawah agar fitur target pelatihan &amp; riwayat dapat diakses.</p>
    </div>

    <Card>
      <CardHeader>
        <CardTitle class="text-lg">Data Pribadi</CardTitle>
        <CardDescription>Informasi yang tampil pada dashboard monitoring.</CardDescription>
      </CardHeader>
      <CardContent>
        <form id="profile-form" class="flex flex-col gap-4" @submit.prevent="saveProfile">
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="flex flex-col gap-2">
              <Label for="nama">Nama Lengkap</Label>
              <Input id="nama" v-model="profileForm.nama" :aria-invalid="profileForm.errors.nama ? 'true' : undefined" />
              <p v-if="profileForm.errors.nama" class="text-xs text-destructive">{{ profileForm.errors.nama }}</p>
            </div>
            <div class="flex flex-col gap-2">
              <Label for="nip">NIP</Label>
              <Input id="nip" v-model="profileForm.nip" :aria-invalid="profileForm.errors.nip ? 'true' : undefined" />
              <p v-if="profileForm.errors.nip" class="text-xs text-destructive">{{ profileForm.errors.nip }}</p>
            </div>
            <div class="flex flex-col gap-2">
              <Label for="jabatan">Jabatan</Label>
              <select id="jabatan" v-model="profileForm.jabatan" :class="selectClass">
                <option v-for="opt in JABATAN_OPTIONS" :key="opt" :value="opt">{{ opt }}</option>
              </select>
              <p v-if="profileForm.errors.jabatan" class="text-xs text-destructive">{{ profileForm.errors.jabatan }}</p>
            </div>
            <div class="flex flex-col gap-2">
              <Label for="jenjang">Jenjang</Label>
              <select id="jenjang" v-model="profileForm.jenjang" :class="selectClass" :aria-invalid="profileForm.errors.jenjang ? 'true' : undefined">
                <option value="">— Pilih Jenjang —</option>
                <option v-for="opt in JENJANG_OPTIONS" :key="opt" :value="opt">{{ opt }}</option>
              </select>
              <p v-if="profileForm.errors.jenjang" class="text-xs text-destructive">{{ profileForm.errors.jenjang }}</p>
            </div>
            <div class="flex flex-col gap-2">
              <Label for="unit_kerja">Unit Kerja</Label>
              <Input id="unit_kerja" v-model="profileForm.unit_kerja" :aria-invalid="profileForm.errors.unit_kerja ? 'true' : undefined" />
              <p v-if="profileForm.errors.unit_kerja" class="text-xs text-destructive">{{ profileForm.errors.unit_kerja }}</p>
            </div>
            <div class="flex flex-col gap-2">
              <Label for="provinsi">Provinsi</Label>
              <Input id="provinsi" v-model="profileForm.provinsi" :aria-invalid="profileForm.errors.provinsi ? 'true' : undefined" />
              <p v-if="profileForm.errors.provinsi" class="text-xs text-destructive">{{ profileForm.errors.provinsi }}</p>
            </div>
          </div>
        </form>
      </CardContent>
      <CardFooter class="justify-end">
        <Button type="submit" form="profile-form" :disabled="profileForm.processing">Simpan Profil</Button>
      </CardFooter>
    </Card>

    <PegawaiTraining
      v-if="pegawai"
      :targets="targets"
      :riwayat="riwayat"
      :grand-design="grandDesign"
      :complete-url="completeUrl"
      :riwayat-store-url="riwayatStoreUrl"
      :riwayat-delete-url="riwayatDeleteUrl"
      :riwayat-download-url="riwayatDownloadUrl"
    />
  </div>
</template>