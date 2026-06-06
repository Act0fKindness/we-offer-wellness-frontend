export async function fetchWhatCategories() {
  try {
    const res = await fetch('/cache/what-categories.json', { cache: 'no-store' })
    if (!res.ok) throw new Error(`Failed to load what categories: ${res.status}`)

    const payload = await res.json()
    const categories = Array.isArray(payload?.categories) ? payload.categories : []

    return categories
      .map((item) => {
        const title = String(item?.title || item?.label || item?.value || '').trim()
        const value = String(item?.value || title).trim()
        const counts = item?.counts || {}
        const products = Number(counts?.products || 0)
        const offerings = Number(counts?.offerings || 0)
        const total = Number(counts?.total || products + offerings || 0)

        return {
          cat: String(item?.cat || 'Modalities').trim() || 'Modalities',
          title,
          label: title,
          value,
          type: String(item?.type || 'Modality').trim() || 'Modality',
          subtitle: String(item?.subtitle || '').trim().replace(/\bproducts?\b/gi, 'offerings'),
          slug: String(item?.slug || '').trim(),
          search: String(item?.search || `${title} ${item?.slug || ''}`).trim(),
          counts: { products, offerings, total },
        }
      })
      .filter((item) => item.title)
      .sort((a, b) => {
        const at = Number(a?.counts?.total || 0)
        const bt = Number(b?.counts?.total || 0)
        if (at !== bt) return bt - at
        return String(a.title).localeCompare(String(b.title))
      })
  } catch (error) {
    console.warn('[what-categories] failed', error)
    return []
  }
}
