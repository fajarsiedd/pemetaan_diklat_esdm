import { router, usePage } from "@inertiajs/vue3";

export const JABATAN_OPTIONS = ["Inspektur Ketenagalistrikan", "Penyelidik Bumi"];

export const JENJANG_OPTIONS = ["Ahli Pertama", "Ahli Muda", "Ahli Madya", "Ahli Madya-Pusaka"];

const STORAGE_KEY = "monitoring-ikpb-global-filters";

const normalize = (value) => (value === undefined || value === null || value === "" ? "all" : value);

/** Read the persisted Jabatan/Jenjang filter values from localStorage. */
export function readGlobalFilters() {
  try {
    const raw = window.localStorage.getItem(STORAGE_KEY);
    const parsed = raw ? JSON.parse(raw) : {};
    return { jabatan: normalize(parsed.jabatan), jenjang: normalize(parsed.jenjang) };
  } catch {
    return { jabatan: "all", jenjang: "all" };
  }
}

/** Persist the current Jabatan/Jenjang filter values to localStorage. */
export function saveGlobalFilters({ jabatan, jenjang }) {
  try {
    window.localStorage.setItem(STORAGE_KEY, JSON.stringify({ jabatan, jenjang }));
  } catch {
    // storage unavailable (private mode/quota) — filters just won't persist across reloads.
  }
}

/** Query-string helpers bound to the current page URL. */
export function useGlobalFilters() {
  const page = usePage();

  const parseUrl = () => new URLSearchParams(page.url.split("?")[1] || "");

  /** All query params currently present in the page URL. */
  const allQuery = () => {
    const params = parseUrl();
    const query = {};
    for (const [key, value] of params.entries()) query[key] = value;
    return query;
  };

  /** Just the global jabatan/jenjang params currently active in the URL. */
  const activeGlobalQuery = () => {
    const params = parseUrl();
    const query = {};
    ["jabatan", "jenjang"].forEach((key) => {
      const value = params.get(key);
      if (value) query[key] = value;
    });
    return query;
  };

  return { JABATAN_OPTIONS, JENJANG_OPTIONS, allQuery, activeGlobalQuery };
}

/**
 * Registers a global Inertia `before` interceptor (once per app session) that
 * appends the persisted Jabatan/Jenjang filters to every internal GET
 * navigation. Menu changes, sidebar links, pagination, form redirects and
 * back/forward history all keep the active filter state automatically.
 * Only applies for admin users. Pass the reactive `usePage()` object here so
 * the role check stays current without calling usePage() outside setup().
 */
let interceptorAttached = false;

export function attachGlobalFilterInterceptor(page) {
  if (interceptorAttached) return;
  interceptorAttached = true;

  router.on("before", (event) => {
    const visit = event.detail?.visit;
    if (!visit || visit.method !== "get") return;
    if (page.props?.auth?.user?.role !== "admin") return;

    const filters = readGlobalFilters();
    ["jabatan", "jenjang"].forEach((key) => {
      const value = filters[key];
      if (value && value !== "all") visit.url.searchParams.set(key, value);
      else visit.url.searchParams.delete(key);
    });
  });
}