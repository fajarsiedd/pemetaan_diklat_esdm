<script setup>
import { Head, useForm, usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import PegawaiForm from "./Form.vue";

defineOptions({ layout: AppLayout });

const appName = usePage().props.appName;

const props = defineProps({
  jabatanOptions: { type: Array, default: () => [] },
  jenjangOptions: { type: Array, default: () => [] },
});

const form = useForm({
  nama: "",
  email: "",
  nip: "",
  jabatan: props.jabatanOptions[0] ?? "Inspektur Ketenagalistrikan",
  jenjang: "",
  unit_kerja: "",
  provinsi: "",
});

const submit = () => {
  form.post("/admin/pegawai", {});
};
</script>

<template>
  <Head>
    <title>Tambah Pegawai | {{ appName }}</title>
  </Head>
  <PegawaiForm
    :form="form"
    :jabatan-options="jabatanOptions"
    :jenjang-options="jenjangOptions"
    submit-label="Tambah Pegawai"
    @submit="submit"
  />
</template>