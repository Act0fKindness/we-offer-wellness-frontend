import { reactive, computed, watch } from 'vue'

const LS_KEY = 'wow_cart_v1'
const COOKIE_KEY = 'wow_cart'

function readCookie(name){
  try {
    const m = document.cookie.split('; ').find(r => r.startsWith(name + '='))
    return m ? decodeURIComponent(m.split('=')[1] || '') : ''
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

watch(state, () => {
  try { localStorage.setItem(LS_KEY, JSON.stringify({ items: state.items })) } catch {}
}, { deep: true })

function normalizeItem(input) {
  const id = input?.id ?? input?.product_id
  return {
    id,
    title: input?.title || '',
    price: Number(input?.price) || 0,
    image: input?.image || input?.image_url || null,
    url: input?.url || (id ? `/products/${id}` : '#'),
    qty: Number(input?.qty) > 0 ? Number(input.qty) : 1,
    meta: input?.meta || {},
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
