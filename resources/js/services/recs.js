// Lightweight client-side recommendation profile
const KEY = 'wow_rec_profile_v1'

function load(){
  try { return JSON.parse(localStorage.getItem(KEY) || '{}') || {} } catch { return {} }
}
function save(p){ try { localStorage.setItem(KEY, JSON.stringify(p)) } catch {}
}
function bump(map, key){ if (!key) return; map[key] = (map[key]||0) + 1 }

export function getProfile(){ const p = load(); p.categories = p.categories||{}; p.types=p.types||{}; p.vendors=p.vendors||{}; p.formats=p.formats||{}; p.locations=p.locations||{}; return p }

export function recordProductView(prod){
  const p = getProfile()
  const catId = prod?.category?.id || prod?.category_id
  const typ = String(prod?.type || prod?.product_type || '').toLowerCase()
  const ven = prod?.vendor_id || prod?.vendor?.id
  if (catId) bump(p.categories, String(catId))
  if (typ) bump(p.types, typ)
  if (ven) bump(p.vendors, String(ven))
  const price = Number(prod?.price_min || prod?.price || 0)
  if (Number.isFinite(price) && price>0){
    p.priceMin = Math.min(p.priceMin ?? price, price)
    p.priceMax = Math.max(p.priceMax ?? price, price)
  }
  save(p)
}

export function recordFormat(format){ const p=getProfile(); const f=String(format||''); if (f) bump(p.formats, f); save(p) }
export function recordLocation(label){ const p=getProfile(); const s=String(label||''); if (s){ bump(p.locations, s); p.lastLocation=s } save(p) }
export function recordVendor(vendorId){ const p=getProfile(); if (vendorId) bump(p.vendors, String(vendorId)); save(p) }

export function topKeys(obj, n=3){ return Object.entries(obj||{}).sort((a,b)=>b[1]-a[1]).slice(0,n).map(([k])=>k) }

export function profileSignals(){
  const p=getProfile()
  return {
    topCategories: topKeys(p.categories, 3).map(x=>Number(x)),
    topTypes: topKeys(p.types, 3),
    topVendors: topKeys(p.vendors, 3).map(x=>Number(x)),
    prefersOnline: (p.formats?.Online||0) > (p.formats?.['In-person']||0),
    lastLocation: p.lastLocation || null,
    priceMin: p.priceMin || null,
    priceMax: p.priceMax || null,
  }
}

