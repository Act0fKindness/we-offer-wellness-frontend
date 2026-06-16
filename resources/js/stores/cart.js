import { reactive, computed, watch } from 'vue'
import { trackCommerce } from '@/lib/wow-analytics'

const LS_KEY = 'wow_cart_v1'
const LEGACY_LS_KEY = 'wow_cart'
const COOKIE_KEY = 'wow_cart'

function readCookie(name){
  try {
    const match = document.cookie
      .split(';')
      .map(r => r.trim())
      .find(r => r.startsWith(name + '='))
    return match ? decodeURIComponent(match.slice(name.length + 1)) : ''
  } catch { return '' }
}

function extractStoredItems(obj){
  if (!obj) return []
  if (Array.isArray(obj)) return obj
  if (typeof obj === 'object' && Array.isArray(obj.items)) return obj.items
  if (typeof obj === 'object') return Object.values(obj)
  return []
}

function dedupeItems(items){
  const seen = new Set()
  return (items || []).filter(item => {
    const id = String(item?.id ?? '')
    if (!id || seen.has(id)) return false
    seen.add(id)
    return true
  })
}

function mapToItems(obj){
  return extractStoredItems(obj).map(normalizeItemSafely).filter(Boolean)
}

function normalizeItemSafely(it){
  try { return normalizeItem(it) } catch { return null }
}

function readCookieItems(name){
  try {
    const prefix = `${name}=`
    const matches = document.cookie
      .split(';')
      .map(r => r.trim())
      .filter(r => r.startsWith(prefix))
    if (!matches.length) return []
    const items = []
    matches.forEach((row) => {
      try {
        const raw = decodeURIComponent(row.slice(prefix.length))
        const parsed = JSON.parse(raw)
        items.push(...mapToItems(parsed))
      } catch {}
    })
    return items
  } catch {
    return []
  }
}

function readLocalStorageItems(){
  try {
    const raw = localStorage.getItem(LS_KEY)
    if (raw) {
      const parsed = JSON.parse(raw)
      const items = mapToItems(parsed)
      if (items.length) return items
    }
  } catch {}
  try {
    const raw = localStorage.getItem(LEGACY_LS_KEY)
    if (!raw) return []
    const parsed = JSON.parse(raw)
    return mapToItems(parsed)
  } catch {
    return []
  }
}

function mergeCartItems(primary, fallback){
  const fallbackById = new Map((fallback || []).map(it => [String(it?.id ?? ''), it]).filter(([id]) => id))
  return (primary || []).map(item => {
    const rich = fallbackById.get(String(item?.id ?? ''))
    if (!rich) return item
    const meta = {
      ...(rich.meta && typeof rich.meta === 'object' ? rich.meta : {}),
      ...(item.meta && typeof item.meta === 'object' ? item.meta : {}),
    }
    return {
      ...rich,
      ...item,
      title: item.title || rich.title || '',
      price: Number(item.price ?? rich.price ?? 0) || 0,
      qty: Number(item.qty ?? rich.qty ?? 1) > 0 ? Number(item.qty ?? rich.qty ?? 1) : 1,
      image: item.image || rich.image || null,
      url: item.url || rich.url || '#',
      meta,
    }
  })
}

function compactCookieItem(item){
  if (!item?.id) return null
  const meta = (item.meta && typeof item.meta === 'object') ? item.meta : {}
  return {
    id: String(item.id),
    product_id: item.product_id || meta.product_id || null,
    variant_id: item.variant_id || meta.variant_id || null,
    variant_label: item.variant_label || meta.variant_label || '',
    title: item.title || '',
    price: Number(item.price || 0) || 0,
    qty: Math.max(1, Number(item.qty || 1) || 1),
    image: item.image || null,
    url: item.url || '#',
  }
}

function load() {
  const localItems = readLocalStorageItems()
  const cookieItems = dedupeItems(readCookieItems(COOKIE_KEY))
  if (cookieItems.length) return { items: mergeCartItems(cookieItems, localItems) }
  return { items: localItems }
}

const state = reactive(load())

function currentSubtotal(items = state.items) {
  return items.reduce((sum, it) => sum + (Number(it.price) * (Number(it.qty) || 0)), 0)
}

function persist(){
  try {
    const serialized = JSON.stringify({ items: state.items })
    localStorage.setItem(LS_KEY, serialized)
    localStorage.setItem(LEGACY_LS_KEY, serialized)
  } catch {}
  if (typeof document === 'undefined') return
  try {
    const payload = state.items.map(compactCookieItem).filter(Boolean)
    const maxAge = 30 * 24 * 60 * 60
    if (payload.length) {
      const encoded = encodeURIComponent(JSON.stringify(payload))
      document.cookie = `${COOKIE_KEY}=; Path=/; Max-Age=0; SameSite=Lax`
      document.cookie = `${COOKIE_KEY}=${encoded}; Domain=.weofferwellness.co.uk; Path=/; Max-Age=${maxAge}; SameSite=Lax`
    } else {
      document.cookie = `${COOKIE_KEY}=; Path=/; Max-Age=0; SameSite=Lax`
      document.cookie = `${COOKIE_KEY}=; Domain=.weofferwellness.co.uk; Path=/; Max-Age=0; SameSite=Lax`
    }
  } catch {}
}

watch(state, () => { persist() }, { deep: true })

function normalizeItem(input) {
  const meta = (input?.meta && typeof input.meta === 'object') ? { ...input.meta } : {}
  if (input?.booking && typeof input.booking === 'object' && !meta.booking) meta.booking = input.booking
  if (Array.isArray(input?.selected) && !meta.selected) meta.selected = input.selected
  if ((input?.groupCount ?? input?.group_count) != null && meta.group_count == null && meta.groupCount == null) meta.group_count = input.groupCount ?? input.group_count
  if ((input?.reservationId ?? input?.reservation_id) != null && meta.reservation_id == null && meta.reservationId == null) meta.reservation_id = input.reservationId ?? input.reservation_id
  if ((input?.holdExpiresAt ?? input?.hold_expires_at) != null && meta.hold_expires_at == null && meta.holdExpiresAt == null) meta.hold_expires_at = input.holdExpiresAt ?? input.hold_expires_at
  if (input?.location && !meta.location) meta.location = input.location
  if ((input?.variant_id ?? input?.variantId) != null && meta.variant_id == null) meta.variant_id = input.variant_id ?? input.variantId
  if ((input?.variant_label ?? input?.variantLabel) && !meta.variant_label) meta.variant_label = input.variant_label ?? input.variantLabel
  if ((input?.product_id ?? null) != null && meta.product_id == null) meta.product_id = input.product_id
  if ((input?.source_version ?? null) != null && meta.source_version == null) meta.source_version = input.source_version
  const id = input?.id ?? input?.product_id
  return {
    id,
    product_id: input?.product_id || meta.product_id || null,
    variant_id: input?.variant_id || input?.variantId || meta.variant_id || null,
    variant_label: input?.variant_label || input?.variantLabel || meta.variant_label || '',
    title: input?.title || '',
    price: Number(input?.price) || 0,
    image: input?.image || input?.image_url || null,
    url: input?.url || (id ? `/products/${id}` : '#'),
    qty: Number(input?.qty) > 0 ? Number(input.qty) : 1,
    meta,
  }
}

export function useCart() {
  const items = computed(() => state.items)
  const count = computed(() => state.items.reduce((sum, it) => sum + (Number(it.qty) || 0), 0))
  const subtotal = computed(() => state.items.reduce((sum, it) => sum + (Number(it.price) * (Number(it.qty) || 0)), 0))

  function add(input) {
    const item = normalizeItem(input)
    if (!item.id) return
    const existing = state.items.find(it => it.id === item.id)
    const addedQty = Math.max(1, Number(item.qty) || 1)
    if (existing) existing.qty += item.qty
    else state.items.push(item)
    try {
      trackCommerce('wow_v3_add_to_cart', {
        items: [{ ...item, qty: addedQty }],
        currency: 'GBP',
        value: Number(item.price || 0) * addedQty,
        item_count: addedQty,
        cart_value: currentSubtotal(),
        source: 'vue-store',
        source_version: item.meta?.source_version || null,
      })
    } catch {}
  }
  function remove(id) {
    const existing = state.items.find(it => it.id === id)
    if (!existing) return
    state.items = state.items.filter(it => it.id !== id)
    try {
      trackCommerce('wow_v3_remove_from_cart', {
        items: [existing],
        currency: 'GBP',
        value: Number(existing.price || 0) * Math.max(1, Number(existing.qty) || 1),
        item_count: Math.max(1, Number(existing.qty) || 1),
        cart_value: currentSubtotal(),
        source: 'vue-store',
        source_version: existing.meta?.source_version || null,
      })
    } catch {}
  }
  function updateQty(id, qty) {
    const it = state.items.find(x => x.id === id)
    if (!it) return
    const previousQty = Math.max(1, Number(it.qty) || 1)
    const nextQty = Math.max(1, Number(qty) || 1)
    if (nextQty === previousQty) return
    it.qty = nextQty
    try {
      trackCommerce('wow_v3_update_cart_quantity', {
        items: [{ ...it, qty: nextQty }],
        currency: 'GBP',
        value: Number(it.price || 0) * nextQty,
        item_count: nextQty,
        previous_qty: previousQty,
        quantity_delta: nextQty - previousQty,
        cart_value: currentSubtotal(),
        source: 'vue-store',
        source_version: it.meta?.source_version || null,
      })
    } catch {}
  }
  function clear() {
    const snapshot = state.items.map(it => ({ ...it }))
    if (!snapshot.length) {
      state.items = []
      return
    }
    try {
      trackCommerce('wow_v3_clear_cart', {
        items: snapshot,
        currency: 'GBP',
        value: currentSubtotal(snapshot),
        item_count: snapshot.reduce((sum, it) => sum + (Number(it.qty) || 0), 0),
        source: 'vue-store',
        source_version: snapshot.find(it => it?.meta?.source_version)?.meta?.source_version || null,
      })
    } catch {}
    state.items = []
  }

  return { items, count, subtotal, add, remove, updateQty, clear }
}
