<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from "vue";
import { Head, router, usePage } from "@inertiajs/vue3";
import { toast } from "vue-sonner";
import {
  RefreshCw,
  Users,
  BookOpen,
  Target,
  Gauge,
  Building2,
  Shapes,
  TrendingUp,
  Scale,
} from "@lucide/vue";
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import AppLayout from "@/Layouts/AppLayout.vue";

defineOptions({ layout: AppLayout });

const props = defineProps({
  stats: { type: Object, required: true },
  charts: { type: Object, required: true },
});

const appName = usePage().props.appName;

const statCards = [
  {
    label: "Total Pegawai",
    key: "total_pegawai",
    icon: Users,
    suffix: "",
    description: "Seluruh pegawai terdaftar",
  },
  {
    label: "Master Pelatihan",
    key: "total_pelatihan",
    icon: BookOpen,
    suffix: "",
    description: "Program pada grand design diklat",
  },
  {
    label: "Target Aktif",
    key: "total_target",
    icon: Target,
    suffix: "",
    description: "Target belum diselesaikan pegawai",
  },
  {
    label: "Tingkat Penyelesaian",
    key: "completion_rate",
    icon: Gauge,
    suffix: "%",
    description: "Riwayat selesai vs total target",
  },
];

const recalculating = ref(false);

const recalculate = () => {
  if (recalculating.value) return;
  recalculating.value = true;

  router.post(
    "/admin/dashboard/recalculate",
    {},
    {
      preserveScroll: true,
      onSuccess: (page) => {
        const flash = page.props.flash;
        if (flash?.success) toast.success(flash.success);
        if (flash?.error) toast.error(flash.error);
      },
      onError: (errors) => {
        toast.error(Object.values(errors).join(", ") || "Terjadi kesalahan saat menghitung ulang.");
      },
      onFinish: () => {
        recalculating.value = false;
      },
    },
  );
};

/* ------------------------- Theme detection ------------------------- */
const isDark = ref(false);
let themeObserver = null;

onMounted(() => {
  isDark.value = document.documentElement.classList.contains("dark");
  themeObserver = new MutationObserver(() => {
    isDark.value = document.documentElement.classList.contains("dark");
  });
  themeObserver.observe(document.documentElement, { attributes: true, attributeFilter: ["class"] });
});

onBeforeUnmount(() => {
  themeObserver?.disconnect();
});

const tooltipTheme = computed(() => (isDark.value ? "dark" : "light"));
const labelColor = computed(() => (isDark.value ? "#a1a1aa" : "#71717a"));
const gridColor = computed(() => (isDark.value ? "#27272a" : "#e4e4e7"));

const baseOptions = computed(() => ({
  chart: {
    toolbar: { show: false },
    foreColor: labelColor.value,
    fontFamily: "inherit",
    animations: { enabled: true, speed: 600 },
  },
  grid: { borderColor: gridColor.value },
  tooltip: { theme: tooltipTheme.value },
  dataLabels: { enabled: false },
  legend: {
    labels: { colors: labelColor.value },
    markers: { size: 6 },
  },
}));

const legendBottom = { position: "bottom" };

/* ------------------- Chart 1: Employee distribution ------------------- */
const employeeDistributionOptions = computed(() => ({
  ...baseOptions.value,
  chart: { ...baseOptions.value.chart, type: "bar", stacked: false },
  plotOptions: {
    bar: { horizontal: false, columnWidth: "45%", borderRadius: 4, borderRadiusApplication: "end" },
  },
  colors: ["#ffcc00", "#0ea5e9", "#f59e0b", "#10b981", "#8b5cf6"],
  xaxis: { categories: props.charts.employeeDistribution.categories },
  legend: { ...legendBottom },
}));

const employeeDistributionSeries = computed(() => props.charts.employeeDistribution.series);

/* ------------------- Chart 2: Training categories ------------------- */
const trainingCategoriesOptions = computed(() => ({
  ...baseOptions.value,
  chart: { ...baseOptions.value.chart, type: "donut" },
  labels: props.charts.trainingCategories.labels,
  colors: ["#ffcc00", "#0ea5e9", "#8b5cf6", "#10b981"],
  legend: { ...legendBottom },
  dataLabels: { enabled: true, formatter: (val) => `${Math.round(val)}%` },
  stroke: { width: 2, colors: ["transparent"] },
}));

const trainingCategoriesSeries = computed(() => props.charts.trainingCategories.series);

/* ------------------- Chart 3: Top 5 targeted ------------------- */
const topTargeted = computed(() => [...props.charts.topTargetedTrainings].reverse());

const topTargetedOptions = computed(() => ({
  ...baseOptions.value,
  chart: { ...baseOptions.value.chart, type: "bar" },
  plotOptions: {
    bar: { horizontal: true, barHeight: "45%", borderRadius: 4, borderRadiusApplication: "end" },
  },
  colors: ["#ffcc00"],
  xaxis: { categories: topTargeted.value.map((t) => t.judul) },
  yaxis: {
    labels: {
      maxWidth: 320,
      formatter: (value) => (value.length > 38 ? `${value.slice(0, 38)}…` : value),
    },
  },
  legend: { show: false },
}));

const topTargetedSeries = computed(() => [
  { name: "Pegawai Ditargetkan", data: topTargeted.value.map((t) => t.count) },
]);

/* ------------------- Chart 4: Training status ratio ------------------- */
const statusRatioOptions = computed(() => ({
  ...baseOptions.value,
  chart: { ...baseOptions.value.chart, type: "bar" },
  plotOptions: {
    bar: { horizontal: false, columnWidth: "35%", borderRadius: 4, borderRadiusApplication: "end" },
  },
  colors: ["#ffcc00", "#0ea5e9"],
  xaxis: { categories: props.charts.trainingStatusRatio.labels },
  legend: { show: false },
  dataLabels: { enabled: true, formatter: (val) => val },
}));

const statusRatioSeries = computed(() => [
  { name: "Jumlah Diklat", data: props.charts.trainingStatusRatio.series },
]);
</script>

<template>
  <Head>
    <title>Dashboard | {{ appName }}</title>
  </Head>
  <div class="flex flex-col gap-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h1 class="text-2xl font-bold tracking-tight">Dashboard Admin</h1>
        <p class="text-muted-foreground">Monitoring Inspektur Ketenagalistrikan & Penyelidik Bumi</p>
      </div>
      <Button @click="recalculate" :disabled="recalculating">
        <RefreshCw class="size-4" :class="{ 'animate-spin': recalculating }" />
        {{ recalculating ? "Menghitung ulang..." : "Recalculate Target Pelatihan" }}
      </Button>
    </div>

    <!-- Row 1: Stat cards -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
      <Card v-for="card in statCards" :key="card.key">
        <CardHeader class="gap-3">
          <CardTitle class="flex items-center justify-between text-sm font-medium text-muted-foreground">
            {{ card.label }}
            <span class="flex size-8 items-center justify-center rounded-lg bg-primary/10 text-primary">
              <component :is="card.icon" class="size-4" />
            </span>
          </CardTitle>
          <CardDescription>
            <span class="text-2xl font-bold text-foreground">{{ stats[card.key] }}{{ card.suffix }}</span>
          </CardDescription>
        </CardHeader>
      </Card>
    </div>

    <!-- Row 2: Distribution & Categories -->
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
      <Card>
        <CardHeader class="gap-2">
          <CardTitle class="flex items-center gap-2 text-lg">
            <Building2 class="size-4" /> Distribusi Pegawai
          </CardTitle>
          <CardDescription>Jumlah pegawai per jabatan, dikelompokkan per jenjang.</CardDescription>
        </CardHeader>
        <CardContent>
          <apexchart
            type="bar"
            height="320"
            width="100%"
            :options="employeeDistributionOptions"
            :series="employeeDistributionSeries"
          ></apexchart>
        </CardContent>
      </Card>

      <Card>
        <CardHeader class="gap-2">
          <CardTitle class="flex items-center gap-2 text-lg">
            <Shapes class="size-4" /> Proporsi Kategori Diklat
          </CardTitle>
          <CardDescription>Sebaran master diklat: Technical, Legal, Commercial, Soft Skill.</CardDescription>
        </CardHeader>
        <CardContent>
          <apexchart
            type="donut"
            height="320"
            width="100%"
            :options="trainingCategoriesOptions"
            :series="trainingCategoriesSeries"
          ></apexchart>
        </CardContent>
      </Card>
    </div>

    <!-- Row 3: Performance & Priorities -->
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
      <Card>
        <CardHeader class="gap-2">
          <CardTitle class="flex items-center gap-2 text-lg">
            <TrendingUp class="size-4" /> 5 Diklat Paling Banyak Ditargetkan
          </CardTitle>
          <CardDescription>Program dengan jumlah pegawai ditargetkan terbanyak.</CardDescription>
        </CardHeader>
        <CardContent>
          <apexchart
            type="bar"
            height="320"
            width="100%"
            :options="topTargetedOptions"
            :series="topTargetedSeries"
          ></apexchart>
        </CardContent>
      </Card>

      <Card>
        <CardHeader class="gap-2">
          <CardTitle class="flex items-center gap-2 text-lg">
            <Scale class="size-4" /> Rasio Status Diklat
          </CardTitle>
          <CardDescription>Perbandingan beban diklat Wajib vs Opsional.</CardDescription>
        </CardHeader>
        <CardContent class="flex items-center justify-center">
          <apexchart
            type="bar"
            height="320"
            width="100%"
            :options="statusRatioOptions"
            :series="statusRatioSeries"
          ></apexchart>
        </CardContent>
      </Card>
    </div>
  </div>
</template>