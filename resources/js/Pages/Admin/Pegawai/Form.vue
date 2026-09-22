<script setup>
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle, CardDescription, CardFooter } from "@/components/ui/card";
import { ArrowLeft } from "@lucide/vue";
import { Link } from "@inertiajs/vue3";

defineProps({
  form: { type: Object, required: true },
  jabatanOptions: { type: Array, default: () => [] },
  jenjangOptions: { type: Array, default: () => [] },
  submitLabel: { type: String, required: true },
  backHref: { type: String, default: "/admin/pegawai" },
});

defineEmits(["submit"]);

const selectClass =
  "border-input h-9 w-full min-w-0 rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-3";
</script>

<template>
  <div class="flex flex-col gap-6">
    <div>
      <Link href="/admin/pegawai" class="text-muted-foreground inline-flex items-center gap-1 text-sm hover:text-foreground">
        <ArrowLeft class="size-4" /> Kembali ke Daftar Pegawai
      </Link>
      <h1 class="mt-2 text-2xl font-bold tracking-tight">{{ submitLabel }}</h1>
    </div>

    <Card>
      <CardHeader>
        <CardTitle class="text-lg">{{ submitLabel }}</CardTitle>
        <CardDescription>Lengkapi data pegawai yang terdaftar pada sistem.</CardDescription>
      </CardHeader>
      <CardContent>
        <form id="pegawai-form" class="flex flex-col gap-4" @submit.prevent="$emit('submit')">
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div class="flex flex-col gap-2">
          <Label for="nama">Nama Lengkap</Label>
          <Input id="nama" v-model="form.nama" :aria-invalid="form.errors.nama ? 'true' : undefined" />
          <p v-if="form.errors.nama" class="text-xs text-destructive">{{ form.errors.nama }}</p>
        </div>
        <div class="flex flex-col gap-2">
          <Label for="email">Email</Label>
          <Input id="email" v-model="form.email" type="email" :aria-invalid="form.errors.email ? 'true' : undefined" />
          <p v-if="form.errors.email" class="text-xs text-destructive">{{ form.errors.email }}</p>
        </div>
        <div class="flex flex-col gap-2">
          <Label for="nip">NIP</Label>
          <Input id="nip" v-model="form.nip" :aria-invalid="form.errors.nip ? 'true' : undefined" />
          <p v-if="form.errors.nip" class="text-xs text-destructive">{{ form.errors.nip }}</p>
        </div>
        <div class="flex flex-col gap-2">
          <Label for="jabatan">Jabatan</Label>
          <select id="jabatan" v-model="form.jabatan" :class="selectClass">
            <option v-for="opt in jabatanOptions" :key="opt" :value="opt">{{ opt }}</option>
          </select>
          <p v-if="form.errors.jabatan" class="text-xs text-destructive">{{ form.errors.jabatan }}</p>
        </div>
        <div class="flex flex-col gap-2">
          <Label for="jenjang">Jenjang</Label>
          <select id="jenjang" v-model="form.jenjang" :class="selectClass" :aria-invalid="form.errors.jenjang ? 'true' : undefined">
            <option value="">— Pilih Jenjang —</option>
            <option v-for="opt in jenjangOptions" :key="opt" :value="opt">{{ opt }}</option>
          </select>
          <p v-if="form.errors.jenjang" class="text-xs text-destructive">{{ form.errors.jenjang }}</p>
        </div>
        <div class="flex flex-col gap-2">
          <Label for="unit_kerja">Unit Kerja</Label>
          <Input id="unit_kerja" v-model="form.unit_kerja" :aria-invalid="form.errors.unit_kerja ? 'true' : undefined" />
          <p v-if="form.errors.unit_kerja" class="text-xs text-destructive">{{ form.errors.unit_kerja }}</p>
        </div>
        <div class="flex flex-col gap-2 sm:col-span-2">
          <Label for="provinsi">Provinsi</Label>
          <Input id="provinsi" v-model="form.provinsi" :aria-invalid="form.errors.provinsi ? 'true' : undefined" />
          <p v-if="form.errors.provinsi" class="text-xs text-destructive">{{ form.errors.provinsi }}</p>
        </div>
      </div>

        </form>
      </CardContent>
      <CardFooter class="flex items-center justify-end gap-2">
        <Button type="submit" form="pegawai-form" :disabled="form.processing">{{ submitLabel }}</Button>
        <Link :href="backHref">
          <Button type="button" variant="outline">Batal</Button>
        </Link>
      </CardFooter>
    </Card>
  </div>
</template>