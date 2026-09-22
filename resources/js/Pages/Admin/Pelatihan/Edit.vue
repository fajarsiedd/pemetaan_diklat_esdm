<script setup>
import { useForm } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import PelatihanForm from "./Form.vue";

defineOptions({ layout: AppLayout });

const props = defineProps({
  pelatihan: { type: Object, required: true },
  kategoriOptions: { type: Array, default: () => [] },
  statusOptions: { type: Array, default: () => [] },
  targetJabatanOptions: { type: Array, default: () => [] },
  jenjangOptions: { type: Array, default: () => [] },
});

const form = useForm({
  kode_diklat: props.pelatihan.kode_diklat ?? "",
  judul: props.pelatihan.judul ?? "",
  jenjang: props.pelatihan.jenjang ?? "",
  subsektor: props.pelatihan.subsektor ?? "",
  target_jabatan: props.pelatihan.target_jabatan ?? [],
  kategori: props.pelatihan.kategori ?? props.kategoriOptions[0] ?? "technical",
  status: props.pelatihan.status ?? props.statusOptions[0] ?? "opsional",
});

const submit = () => {
  form.put(`/admin/pelatihan/${props.pelatihan.id}`, {});
};
</script>

<template>
  <PelatihanForm
    :form="form"
    :kategori-options="kategoriOptions"
    :status-options="statusOptions"
    :target-jabatan-options="targetJabatanOptions"
    :jenjang-options="jenjangOptions"
    submit-label="Edit Pelatihan"
    @submit="submit"
  />
</template>