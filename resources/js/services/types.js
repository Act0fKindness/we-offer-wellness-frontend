export async function fetchProductTypes() {
  try {
    const res = await fetch('/api/product-types', { cache: 'no-store' })
    if (!res.ok) throw new Error(`types ${res.status}`)
    const data = await res.json()
    // Normalise to array of strings
    return (Array.isArray(data) ? data : []).map(x => (typeof x === 'string' ? x : (x?.name || ''))).filter(Boolean)
  } catch (e) {
    console.warn('[types] failed', e)
    return []
  }
}

