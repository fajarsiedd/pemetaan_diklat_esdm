<script setup>
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Button } from "@/components/ui/button";
import { Checkbox } from "@/components/ui/checkbox";
import { Card, CardContent, CardHeader, CardTitle, CardDescription, CardFooter } from "@/components/ui/card";
import { ArrowLeft } from "@lucide/vue";
import { Link } from "@inertiajs/vue3";

const props = defineProps({
  form: { type: Object, required: true },
  kategoriOptions: { type: Array, default: () => [] },
  statusOptions: { type: Array, default: () => [] },
  targetJabatanOptions: { type: Array, default: () => [] },
  jenjangOptions: { type: Array, default: () => [] },
  submitLabel: { type: String, required: true },
});

defineEmits(["submit"]);

const kategoriLabels = {
  technical: "Technical",
  legal: "Legal",
  commercial: "Commercial",
  soft_skill: "Soft Skill",
};

const statusLabels = {
  wajib: "Wajib",
  opsional: "Opsional",
};

const toggleJabatan = (opt) => {
  const list = Array.isArray(props.form.target_jabatan) ? [...props.form.target_jabatan] : [];
  const idx = list.indexOf(opt);
  if (idx === -1) list.push(opt);
  else list.splice(idx, 1);
  props.form.target_jabatan = list;
};

const selectClass =
  "border-input h-9 w-full min-w-0 rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-3";
</script>

<template>
  <div class="flex flex-col gap-6">
    <div>
      <Link href="/admin/pelatihan" class="text-muted-foreground inline-flex items-center gap-1 text-sm hover:text-foreground">
        <ArrowLeft class="size-4" /> Kembali ke Master Pelatihan
      </Link>
      <h1 class="mt-2 text-2xl font-bold tracking-tight">{{ submitLabel }}</h1>
    </div>

    <Card>
      <CardHeader>
        <CardTitle class="text-lg">{{ submitLabel }}</CardTitle>
        <CardDescription>Lengkapi data pelatihan yang tersedia pada grand design diklat.</CardDescription>
      </CardHeader>
      <CardContent>
        <form id="pelatihan-form" class="flex flex-col gap-4" @submit.prevent="$emit('submit')">
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div class="flex flex-col gap-2">
          <Label for="kode_diklat">Kode Diklat</Label>
          <Input id="kode_diklat" v-model="form.kode_diklat" :aria-invalid="form.errors.kode_diklat ? 'true' : undefined" />
          <p v-if="form.errors.kode_diklat" class="text-xs text-destructive">{{ form.errors.kode_diklat }}</p>
        </div>
        <div class="flex flex-col gap-2">
          <Label for="subsektor">Subsektor</Label>
          <Input id="subsektor" v-model="form.subsektor" :aria-invalid="form.errors.subsektor ? 'true' : undefined" />
          <p v-if="form.errors.subsektor" class="text-xs text-destructive">{{ form.errors.subsektor }}</p>
        </div>
        <div class="flex flex-col gap-2 sm:col-span-2">
          <Label for="judul">Judul</Label>
          <Input id="judul" v-model="form.judul" :aria-invalid="form.errors.judul ? 'true' : undefined" />
          <p v-if="form.errors.judul" class="text-xs text-destructive">{{ form.errors.judul }}</p>
        </div>
        <div class="flex flex-col gap-2">
          <Label for="kategori">Kategori</Label>
          <select id="kategori" v-model="form.kategori" :class="selectClass">
            <option v-for="opt in kategoriOptions" :key="opt" :value="opt">
              {{ kategoriLabels[opt] ?? opt }}
            </option>
          </select>
          <p v-if="form.errors.kategori" class="text-xs text-destructive">{{ form.errors.kategori }}</p>
        </div>
        <div class="flex flex-col gap-2">
          <Label for="status">Status</Label>
          <select id="status" v-model="form.status" :class="selectClass">
            <option v-for="opt in statusOptions" :key="opt" :value="opt">
              {{ statusLabels[opt] ?? opt }}
            </option>
          </select>
          <p v-if="form.errors.status" class="text-xs text-destructive">{{ form.errors.status }}</p>
        </div>
        <div class="flex flex-col gap-2">
          <Label>Target Jabatan</Label>
          <div class="flex flex-col gap-2 rounded-md border p-3">
            <label v-for="opt in targetJabatanOptions" :key="opt" class="flex cursor-pointer items-center gap-2 text-sm">
              <Checkbox
                :model-value="(form.target_jabatan ?? []).includes(opt)"
                @update:model-value="toggleJabatan(opt)"
              />
              {{ opt }}
            </label>
          </div>
          <p v-if="form.errors.target_jabatan" class="text-xs text-destructive">{{ form.errors.target_jabatan }}</p>
        </div>
        <div class="flex flex-col gap-2">
          <Label for="jenjang">Jenjang</Label>
          <select id="jenjang" v-model="form.jenjang" :class="selectClass">
            <option value="">Semua Jenjang (Umum)</option>
            <option v-for="opt in jenjangOptions" :key="opt" :value="opt">{{ opt }}</option>
          </select>
          <p v-if="form.errors.jenjang" class="text-xs text-destructive">{{ form.errors.jenjang }}</p>
          <p class="text-xs text-muted-foreground">Kosongkan jika berlaku untuk seluruh jenjang.</p>
        </div>
      </div>

        </form>
      </CardContent>
      <CardFooter class="flex items-center justify-end gap-2">
        <Button type="submit" form="pelatihan-form" :disabled="form.processing">{{ submitLabel }}</Button>
        <Link :href="'/admin/pelatihan'">
          <Button type="button" variant="outline">Batal</Button>
        </Link>
      </CardFooter>
    </Card>
  </div>
</template>