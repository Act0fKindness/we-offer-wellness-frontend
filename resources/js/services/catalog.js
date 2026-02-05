export async function fetchCatalog(params = {}) {
  const qs = new URLSearchParams(params).toString()
  const url = qs ? `/api/catalog?${qs}` : '/api/catalog'
  try {
    const res = await fetch(url, { cache: 'no-store' })
    if (!res.ok) throw new Error(`Failed to load catalog: ${res.status}`)
    return await res.json()
  } catch (e) {
    console.warn('[catalog] failed', e)
    return []
  }
}
