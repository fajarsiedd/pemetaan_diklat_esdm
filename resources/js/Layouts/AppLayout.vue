<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import { toast } from "vue-sonner";
import { Toaster } from "@/components/ui/sonner";
import { Button } from "@/components/ui/button";
import { Separator } from "@/components/ui/separator";
import { Activity, LayoutDashboard, LogOut, User, BookOpen, Users, Target } from "@lucide/vue";
import {
  attachGlobalFilterInterceptor,
  readGlobalFilters,
  saveGlobalFilters,
  useGlobalFilters,
} from "@/composables/useGlobalFilters";
import 'vue-sonner/style.css' // don't delete this line

const page = usePage();
const appName = page.props.appName;
const user = page.props.auth?.user;

const navItems = computed(() => {
  if (user?.role === "admin") {
    return [
      { label: "Dashboard", href: "/admin/dashboard", icon: LayoutDashboard },
      { label: "Target Pelatihan", href: "/admin/target-pelatihan", icon: Target },
      { label: "Pegawai", href: "/admin/pegawai", icon: Users },
      { label: "Pelatihan", href: "/admin/pelatihan", icon: BookOpen },
    ];
  }
  return [
    { label: "Profil Saya", href: "/pegawai/profile", icon: User },
    { label: "Target Pelatihan", href: "/pegawai/profile#target", icon: Target },
  ];
});

let lastToast = { message: "", error: false, at: 0 };

// Show flash messages reliably on every navigation, not only the first page load.
watch(
  () => page.props.flash,
  (flash) => {
    const now = Date.now();
    if (flash?.success && !(flash.success === lastToast.message && !lastToast.error && now - lastToast.at < 800)) {
      toast.success(flash.success);
      lastToast = { message: flash.success, error: false, at: now };
    }
    if (flash?.error && !(flash.error === lastToast.message && lastToast.error && now - lastToast.at < 800)) {
      toast.error(flash.error);
      lastToast = { message: flash.error, error: true, at: now };
    }
  },
  { immediate: true, deep: true, flush: "sync" },
);

const logout = () => {
  router.post("/logout");
};

const displayName = user?.pegawai?.nama ?? user?.name;

const isAdmin = computed(() => user?.role === "admin");

const { JABATAN_OPTIONS, JENJANG_OPTIONS, allQuery } = useGlobalFilters();

// Append persisted filters to every internal GET navigation (menu links, pagination, redirects…).
attachGlobalFilterInterceptor(page);

let syncing = false;
const initialParams = new URLSearchParams(page.url.split("?")[1] || "");
const stored = readGlobalFilters();
const jabatan = ref(initialParams.get("jabatan") || stored.jabatan);
const jenjang = ref(initialParams.get("jenjang") || stored.jenjang);

const syncFromUrl = () => {
  syncing = true;
  const params = new URLSearchParams(page.url.split("?")[1] || "");
  jabatan.value = params.get("jabatan") || "all";
  jenjang.value = params.get("jenjang") || "all";
  syncing = false;
};

watch(() => page.url, syncFromUrl, { immediate: true });

watch([jabatan, jenjang], () => {
  if (syncing) return;

  saveGlobalFilters({ jabatan: jabatan.value, jenjang: jenjang.value });

  const query = allQuery();
  if (jabatan.value === "all" || jabatan.value === "") delete query.jabatan;
  else query.jabatan = jabatan.value;
  if (jenjang.value === "all" || jenjang.value === "") delete query.jenjang;
  else query.jenjang = jenjang.value;

  const path = page.url.split("?")[0];
  if (path) {
    router.get(path, query, { preserveState: true, preserveScroll: true });
  }
});

// On initial load, re-apply persisted filters when the URL doesn't carry them yet
// (e.g. after a full page refresh or a direct deep link into an admin page).
onMounted(() => {
  const params = new URLSearchParams(page.url.split("?")[1] || "");
  const urlJabatan = params.get("jabatan") || "all";
  const urlJenjang = params.get("jenjang") || "all";

  if (urlJabatan !== jabatan.value || urlJenjang !== jenjang.value) {
    const query = {};
    if (jabatan.value !== "all") query.jabatan = jabatan.value;
    if (jenjang.value !== "all") query.jenjang = jenjang.value;
    const path = page.url.split("?")[0];
    if (path) router.get(path, query, { preserveState: true, preserveScroll: true });
  }
});

const selectClass = (active) =>
  "h-9 w-full min-w-[9.5rem] max-w-[12rem] cursor-pointer rounded-md border bg-background px-3 text-sm shadow-xs outline-none transition-colors focus-visible:border-amber-500 focus-visible:ring-3 focus-visible:ring-amber-400/40 " +
  (active ? "border-amber-300 font-medium text-foreground" : "border-input text-muted-foreground");
</script>

<template>
  <div class="min-h-screen bg-muted/40">
    <header class="sticky top-0 z-40 h-16 border-b bg-white">
      <div class="flex h-full items-center justify-between px-4 md:px-6">
        <Link :href="user?.role === 'admin' ? '/admin/dashboard' : '/pegawai/profile'" class="flex items-center gap-2 text-lg font-bold tracking-tight">
          <img src="/images/logo-esdm.png" class="w-8 h-8" alt="" srcset="">
          <span class="hidden sm:inline">{{ appName }}</span>
        </Link>

        <div class="flex items-center gap-3">
          <div v-if="isAdmin" class="hidden items-center gap-2 lg:flex">
            <select
              id="global-jabatan"
              v-model="jabatan"
              :class="selectClass(jabatan !== 'all')"
              title="Saring menurut Jabatan"
              aria-label="Saring menurut Jabatan"
            >
              <option value="all">Semua Jabatan</option>
              <option v-for="opt in JABATAN_OPTIONS" :key="opt" :value="opt">{{ opt }}</option>
            </select>
            <select
              id="global-jenjang"
              v-model="jenjang"
              :class="selectClass(jenjang !== 'all')"
              title="Saring menurut Jenjang"
              aria-label="Saring menurut Jenjang"
            >
              <option value="all">Semua Jenjang</option>
              <option v-for="opt in JENJANG_OPTIONS" :key="opt" :value="opt">{{ opt }}</option>
            </select>
          </div>
          <Button variant="ghost" size="sm" @click="logout">
            <LogOut class="size-4" />
            <span class="hidden sm:inline">Keluar</span>
          </Button>
        </div>
      </div>
    </header>

    <div class="mx-auto flex max-w-7xl">
      <aside class="hidden h-fit w-64 shrink-0 flex-col gap-1 self-start rounded-xl borde px-3 py-6 md:flex">
        <template v-for="item in navItems" :key="item.href">
          <Link
            :href="item.href"
            :class="
              'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors',
              $page.url.split('#')[0] === item.href.split('#')[0]
                ? 'bg-amber-100 font-semibold text-amber-900'
                : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground'
            "
          >
            <component :is="item.icon" class="size-4" />
            {{ item.label }}
          </Link>
        </template>
        <Separator class="my-4" />
        <div class="flex items-center gap-3 rounded-lg border bg-background p-3">
          <div class="flex size-9 items-center justify-center rounded-full bg-amber-100 text-sm font-semibold text-amber-700">
            {{ displayName?.charAt(0)?.toUpperCase() }}
          </div>
          <div class="min-w-0 leading-tight">
            <p class="truncate text-sm font-medium">{{ displayName }}</p>
            <p class="truncate text-xs text-muted-foreground">{{ user?.email }}</p>
          </div>
        </div>
      </aside>

      <main class="min-w-0 flex-1 px-4 py-6 md:px-8">
        <slot />
      </main>
    </div>

    <Toaster rich-colors position="top-right" />
  </div>
</template>