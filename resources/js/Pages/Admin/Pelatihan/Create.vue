<script setup>
import { useForm } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import PelatihanForm from "./Form.vue";

defineOptions({ layout: AppLayout });

const props = defineProps({
  kategoriOptions: { type: Array, default: () => [] },
  statusOptions: { type: Array, default: () => [] },
  targetJabatanOptions: { type: Array, default: () => [] },
  jenjangOptions: { type: Array, default: () => [] },
});

const form = useForm({
  kode_diklat: "",
  judul: "",
  jenjang: "",
  subsektor: "",
  target_jabatan: [],
  kategori: props.kategoriOptions[0] ?? "technical",
  status: props.statusOptions[0] ?? "opsional",
});

const submit = () => {
  form.post("/admin/pelatihan", {});
};
</script>

<template>
  <PelatihanForm
    :form="form"
    :kategori-options="kategoriOptions"
    :status-options="statusOptions"
    :target-jabatan-options="targetJabatanOptions"
    :jenjang-options="jenjangOptions"
    submit-label="Tambah Pelatihan"
    @submit="submit"
  />
</template>