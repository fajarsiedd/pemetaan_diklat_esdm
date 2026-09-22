<script setup>
import { computed, onMounted, ref } from "vue";
import { useForm, router } from "@inertiajs/vue3";
import {
  Target, History, BookOpen, Plus, Trash2, Download, CheckCircle2,
  GraduationCap,
} from "@lucide/vue";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Card, CardContent, CardHeader, CardTitle, CardDescription, CardFooter } from "@/components/ui/card";
import { Tabs, TabsList, TabsTrigger, TabsContent } from "@/components/ui/tabs";
import {
  Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription,
  DialogFooter, DialogClose,
} from "@/components/ui/dialog";
import Badge from "@/components/Badge.vue";

const props = defineProps({
  targets: { type: Array, required: true },
  riwayat: { type: Array, required: true },
  grandDesign: { type: Array, required: true },
  completeUrl: { type: Function, required: true },
  riwayatStoreUrl: { type: String, required: true },
  riwayatDeleteUrl: { type: Function, required: true },
  riwayatDownloadUrl: { type: Function, required: true },
});

const completeTarget = (target) => {
  if (confirm(`Tandai "${target.pelatihan?.judul ?? target.judul_custom}" selesai? Item dipindahkan ke riwayat.`)) {
    router.post(props.completeUrl(target), { status: "selesai" }, { preserveScroll: true });
  }
};

const riwayatDialogOpen = ref(false);
const riwayatForm = useForm({
  pelatihan_id: "",
  judul_custom: "",
  tahun: String(new Date().getFullYear()),
  penyelenggara: "",
  file_sertifikat: null,
});

const selectedPelatihan = computed(() =>
  props.grandDesign.find((p) => String(p.id) === String(riwayatForm.pelatihan_id)),
);

const submitRiwayat = () => {
  riwayatForm.post(props.riwayatStoreUrl, {
    preserveScroll: true,
    forceFormData: true,
    onSuccess: () => {
      riwayatDialogOpen.value = false;
      riwayatForm.reset();
    },
  });
};

const deleteRiwayat = (item) => {
  if (confirm(`Hapus riwayat "${item.pelatihan?.judul ?? item.judul_custom}"?`)) {
    router.delete(props.riwayatDeleteUrl(item), { preserveScroll: true });
  }
};

const wajibList = computed(() => props.grandDesign.filter((p) => p.status === "wajib"));
const opsionalList = computed(() => props.grandDesign.filter((p) => p.status === "opsional"));

const kategoriLabel = {
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

const activeTab = ref("target");

onMounted(() => {
  const hash = window.location.hash.replace("#", "");
  if (["target", "riwayat", "grand"].includes(hash)) {
    activeTab.value = hash;
  }
});

const selectClass =
  "border-input h-9 w-full min-w-0 rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-3";
</script>

<template>
  <Tabs v-model="activeTab" class="flex-col gap-4">
    <TabsList class="w-full sm:w-fit">
      <TabsTrigger value="target"><Target class="size-4" /> Target Pelatihan</TabsTrigger>
      <TabsTrigger value="riwayat"><History class="size-4" /> Riwayat Pelatihan</TabsTrigger>
      <TabsTrigger value="grand"><BookOpen class="size-4" /> Grand Design</TabsTrigger>
    </TabsList>

    <TabsContent value="target" class="flex-col gap-4">
      <Card>
        <CardHeader>
          <CardTitle class="flex items-center justify-between text-lg">
            Target Pelatihan
            <Badge :variant="targets.length >= 4 ? 'destructive' : 'muted'">
              {{ targets.length }}/4 slot
            </Badge>
          </CardTitle>
          <CardDescription>
            Maksimal 4 target aktif. Saat satu ditandai selesai, slot otomatis diisi
            pelatihan prioritas berikutnya.
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div v-if="targets.length" class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <Card v-for="t in targets" :key="t.id" class="gap-0 overflow-hidden">
              <CardHeader class="flex-row items-center justify-between gap-2">
                <Badge variant="warning">Prioritas {{ t.prioritas }}</Badge>
                <Badge :variant="statusVariant[t.status]">
                  {{ t.status === "ditargetkan" ? "Ditargetkan" : t.status === "sedang_proses" ? "Sedang Proses" : "Selesai" }}
                </Badge>
              </CardHeader>
              <CardContent class="pb-4">
                <p class="font-medium">{{ t.pelatihan?.judul }}</p>
                <p class="mt-1 text-xs text-muted-foreground">
                  {{ t.pelatihan?.kode_diklat }} • {{ t.pelatihan?.jenjang || "Semua Jenjang" }} • {{ t.pelatihan?.subsektor }}
                </p>
              </CardContent>
              <CardFooter class="flex flex-wrap gap-2 border-t pt-4">
                <Button size="sm" @click="completeTarget(t)">
                  <CheckCircle2 class="size-4" /> Update ke Selesai
                </Button>
              </CardFooter>
            </Card>
          </div>
          <div v-else class="rounded-lg border border-dashed p-8 text-center text-sm text-muted-foreground">
            Belum ada target pelatihan. Hubungi admin untuk menghitung ulang target.
          </div>
        </CardContent>
      </Card>
    </TabsContent>

    <TabsContent value="riwayat" class="flex-col gap-4">
      <Card>
        <CardHeader class="flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <CardTitle class="text-lg">Riwayat Pelatihan</CardTitle>
            <CardDescription>Pelatihan yang sudah diselesaikan.</CardDescription>
          </div>
          <Button size="sm" @click="riwayatDialogOpen = true">
            <Plus class="size-4" /> Tambah Riwayat Manual
          </Button>
        </CardHeader>
        <CardContent>
          <div v-if="riwayat.length" class="flex flex-col">
            <div v-for="r in riwayat" :key="r.id" class="flex flex-col gap-2 border-b py-4 last:border-0 sm:flex-row sm:items-center sm:justify-between">
              <div class="flex items-start gap-3">
                <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary">
                  <GraduationCap class="size-4" />
                </span>
                <div>
                  <p class="font-medium">{{ r.pelatihan?.judul ?? r.judul_custom }}</p>
                  <p class="text-xs text-muted-foreground">
                    Tahun {{ r.tahun }}
                    <template v-if="r.penyelenggara"> • {{ r.penyelenggara }}</template>
                    <template v-if="r.pelatihan"> • {{ r.pelatihan.kode_diklat }}</template>
                  </p>
                </div>
              </div>
              <div class="flex items-center gap-2">
                <Button v-if="r.file_sertifikat" variant="outline" size="sm" as="a" :href="riwayatDownloadUrl(r)">
                  <Download class="size-4" /> Sertifikat
                </Button>
                <Button variant="ghost" size="icon-sm" class="text-destructive" @click="deleteRiwayat(r)">
                  <Trash2 class="size-4" />
                </Button>
              </div>
            </div>
          </div>
          <div v-else class="rounded-lg border border-dashed p-8 text-center text-sm text-muted-foreground">
            Belum ada riwayat pelatihan.
          </div>
        </CardContent>
      </Card>
    </TabsContent>

    <TabsContent value="grand" class="flex-col gap-4">
      <Card>
        <CardHeader>
          <CardTitle class="text-lg">Grand Design Diklat</CardTitle>
          <CardDescription>Seluruh pelatihan yang tersedia di kurikulum.</CardDescription>
        </CardHeader>
        <CardContent class="flex flex-col gap-6">
          <div>
            <h3 class="mb-2 text-sm font-semibold text-muted-foreground">Landasan Wajib</h3>
            <div class="flex flex-col gap-2">
              <div v-for="p in wajibList" :key="p.id" class="flex items-start justify-between gap-3 rounded-lg border px-4 py-3">
                <div>
                  <p class="text-sm font-medium">{{ p.judul }}</p>
<p class="text-xs text-muted-foreground">{{ p.kode_diklat }} • {{ p.jenjang || "Semua Jenjang" }} • {{ p.subsektor }}</p>
                  </div>
                  <div class="flex shrink-0 items-center gap-2">
                    <Badge :variant="kategoriVariant[p.kategori]">{{ kategoriLabel[p.kategori] ?? p.kategori }}</Badge>
                    <Badge>Wajib</Badge>
                  </div>
              </div>
              <p v-if="!wajibList.length" class="text-sm text-muted-foreground">Tidak ada.</p>
            </div>
          </div>
          <div>
            <h3 class="mb-2 text-sm font-semibold text-muted-foreground">Opsional</h3>
            <div class="flex flex-col gap-2">
              <div v-for="p in opsionalList" :key="p.id" class="flex items-start justify-between gap-3 rounded-lg border px-4 py-3">
                <div>
                  <p class="text-sm font-medium">{{ p.judul }}</p>
<p class="text-xs text-muted-foreground">{{ p.kode_diklat }} • {{ p.jenjang || "Semua Jenjang" }} • {{ p.subsektor }}</p>
                  </div>
                  <div class="flex shrink-0 items-center gap-2">
                    <Badge :variant="kategoriVariant[p.kategori]">{{ kategoriLabel[p.kategori] ?? p.kategori }}</Badge>
                    <Badge variant="warning">Opsional</Badge>
                  </div>
              </div>
              <p v-if="!opsionalList.length" class="text-sm text-muted-foreground">Tidak ada.</p>
            </div>
          </div>
        </CardContent>
      </Card>
    </TabsContent>
  </Tabs>

  <Dialog v-model:open="riwayatDialogOpen">
    <DialogContent>
      <DialogHeader>
        <DialogTitle>Tambah Riwayat Pelatihan</DialogTitle>
        <DialogDescription>Pilih diklat dari grand design atau isi manual untuk pelatihan eksternal.</DialogDescription>
      </DialogHeader>

      <form id="riwayat-form" class="flex flex-col gap-4" @submit.prevent="submitRiwayat">
        <div class="flex flex-col gap-2">
          <Label for="pelatihan_id">Diklat (Grand Design)</Label>
          <select id="pelatihan_id" v-model="riwayatForm.pelatihan_id" :class="selectClass">
            <option value="">— Pilih diklat / isi manual di bawah —</option>
            <option v-for="p in grandDesign" :key="p.id" :value="String(p.id)">
              {{ p.kode_diklat }} — {{ p.judul }}
            </option>
          </select>
          <p v-if="riwayatForm.errors.pelatihan_id" class="text-xs text-destructive">{{ riwayatForm.errors.pelatihan_id }}</p>
        </div>

        <div class="flex flex-col gap-2">
          <Label for="judul_custom">Judul Manual</Label>
          <Input id="judul_custom" v-model="riwayatForm.judul_custom" placeholder="Kosongkan jika memilih diklat di atas" :aria-invalid="riwayatForm.errors.judul_custom ? 'true' : undefined" />
          <p v-if="riwayatForm.errors.judul_custom" class="text-xs text-destructive">{{ riwayatForm.errors.judul_custom }}</p>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <div class="flex flex-col gap-2">
            <Label for="tahun">Tahun</Label>
            <Input id="tahun" v-model="riwayatForm.tahun" :aria-invalid="riwayatForm.errors.tahun ? 'true' : undefined" />
            <p v-if="riwayatForm.errors.tahun" class="text-xs text-destructive">{{ riwayatForm.errors.tahun }}</p>
          </div>
          <div class="flex flex-col gap-2">
            <Label for="penyelenggara">Penyelenggara</Label>
            <Input id="penyelenggara" v-model="riwayatForm.penyelenggara" :aria-invalid="riwayatForm.errors.penyelenggara ? 'true' : undefined" />
            <p v-if="riwayatForm.errors.penyelenggara" class="text-xs text-destructive">{{ riwayatForm.errors.penyelenggara }}</p>
          </div>
        </div>

        <div class="flex flex-col gap-2">
          <Label for="file_sertifikat">File Sertifikat</Label>
          <Input
            id="file_sertifikat"
            type="file"
            accept=".pdf,.jpg,.jpeg,.png"
            @input="riwayatForm.file_sertifikat = $event.target.files[0] || null"
            :aria-invalid="riwayatForm.errors.file_sertifikat ? 'true' : undefined"
          />
          <p v-if="riwayatForm.errors.file_sertifikat" class="text-xs text-destructive">{{ riwayatForm.errors.file_sertifikat }}</p>
          <p v-if="selectedPelatihan" class="text-xs text-muted-foreground">
            Terpilih: {{ selectedPelatihan.judul }}
          </p>
        </div>
      </form>

      <DialogFooter>
        <DialogClose as-child>
          <Button type="button" variant="outline">Batal</Button>
        </DialogClose>
        <Button type="submit" form="riwayat-form" :disabled="riwayatForm.processing">Simpan Riwayat</Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>