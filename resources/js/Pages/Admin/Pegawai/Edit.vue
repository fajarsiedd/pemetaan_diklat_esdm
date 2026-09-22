<script setup>
import { useForm } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import PegawaiForm from "./Form.vue";
import PegawaiTraining from "@/components/PegawaiTraining.vue";

defineOptions({ layout: AppLayout });

const props = defineProps({
  pegawai: { type: Object, required: true },
  jabatanOptions: { type: Array, default: () => [] },
  jenjangOptions: { type: Array, default: () => [] },
  targets: { type: Array, default: () => [] },
  riwayat: { type: Array, default: () => [] },
  grandDesign: { type: Array, default: () => [] },
});

const form = useForm({
  nama: props.pegawai.nama ?? "",
  email: props.pegawai.email ?? "",
  nip: props.pegawai.nip ?? "",
  jabatan: props.pegawai.jabatan ?? props.jabatanOptions[0] ?? "Inspektur Ketenagalistrikan",
  jenjang: props.pegawai.jenjang ?? "",
  unit_kerja: props.pegawai.unit_kerja ?? "",
  provinsi: props.pegawai.provinsi ?? "",
});

const submit = () => {
  form.put(`/admin/pegawai/${props.pegawai.id}`, {});
};

const completeUrl = (target) => `/admin/pegawai/${props.pegawai.id}/targets/${target.id}/complete`;
const riwayatStoreUrl = `/admin/pegawai/${props.pegawai.id}/riwayat`;
const riwayatDeleteUrl = (item) => `/admin/pegawai/${props.pegawai.id}/riwayat/${item.id}`;
const riwayatDownloadUrl = (item) => `/admin/pegawai/${props.pegawai.id}/riwayat/${item.id}/sertifikat`;
</script>

<template>
  <div class="flex flex-col gap-6">
    <PegawaiForm
      :form="form"
      :jabatan-options="jabatanOptions"
      :jenjang-options="jenjangOptions"
      submit-label="Edit Pegawai"
      @submit="submit"
    />

    <PegawaiTraining
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