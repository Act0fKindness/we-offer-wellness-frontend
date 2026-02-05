export async function fetchArticles(limit = 3) {
  const url = `/api/articles?limit=${encodeURIComponent(limit)}`
  try {
    const res = await fetch(url, { cache: 'no-store' })
    if (!res.ok) throw new Error(`Failed to load articles: ${res.status}`)
    const data = await res.json()
    return Array.isArray(data) ? data : data.articles || []
  } catch (e) {
    console.warn('[articles] fallback to empty list', e)
    return []
  }
}
