import { reactive, computed, watch } from 'vue'

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

function mapToItems(obj){
  if (!obj) return []
  if (Array.isArray(obj)) return obj.map(normalizeItemSafely)
  if (typeof obj === 'object') return Object.values(obj).map(normalizeItemSafely)
  return []
}

function normalizeItemSafely(it){
  try { return normalizeItem(it) } catch { return null }
}

function load() {
  // Prefer server-synced cookie (wow_cart) if present
  try {
    const cookieRaw = readCookie(COOKIE_KEY)
    if (cookieRaw) {
      const parsed = JSON.parse(cookieRaw)
      const items = mapToItems(parsed).filter(Boolean)
      if (items.length) return { items }
    }
  } catch {}
  // Fallback to legacy localStorage format
  try {
    const raw = localStorage.getItem(LS_KEY)
    if (!raw) return { items: [] }
    const obj = JSON.parse(raw)
    if (!Array.isArray(obj.items)) return { items: [] }
    return { items: obj.items }
  } catch { return { items: [] } }
}

const state = reactive(load())

function persist(){
  try {
    const serialized = JSON.stringify({ items: state.items })
    localStorage.setItem(LS_KEY, serialized)
    localStorage.setItem(LEGACY_LS_KEY, serialized)
  } catch {}
  if (typeof document === 'undefined') return
  try {
    const payload = {}
    state.items.forEach(it => {
      if (!it?.id) return
      const meta = (it?.meta && typeof it.meta === 'object') ? it.meta : {}
      payload[String(it.id)] = {
        id: it.id,
        product_id: it.product_id || meta.product_id || null,
        variant_id: it.variant_id || meta.variant_id || null,
        variant_label: it.variant_label || meta.variant_label || '',
        title: it.title || '',
        price: Number(it.price) || 0,
        qty: Number(it.qty) || 1,
        image: it.image || null,
        url: it.url || '#',
        meta,
        booking: meta.booking || {},
        selected: Array.isArray(meta.selected) ? meta.selected : [],
        groupCount: meta.groupCount ?? meta.group_count ?? null,
        reservationId: meta.reservationId ?? meta.reservation_id ?? null,
        holdExpiresAt: meta.holdExpiresAt ?? meta.hold_expires_at ?? null,
        location: meta.location || null,
        options: Array.isArray(meta.variant_options) ? meta.variant_options : [],
      }
    })
    const maxAge = 30 * 24 * 60 * 60
    document.cookie = `${COOKIE_KEY}=${encodeURIComponent(JSON.stringify(payload))}; Path=/; Max-Age=${maxAge}; SameSite=Lax`
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
    if (existing) existing.qty += item.qty
    else state.items.push(item)
  }
  function remove(id) {
    state.items = state.items.filter(it => it.id !== id)
  }
  function updateQty(id, qty) {
    const it = state.items.find(x => x.id === id)
    if (!it) return
    it.qty = Math.max(1, Number(qty) || 1)
  }
  function clear() { state.items = [] }

  return { items, count, subtotal, add, remove, updateQty, clear }
}
