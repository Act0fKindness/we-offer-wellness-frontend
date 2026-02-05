export async function fetchPainpoints() {
  const backend = import.meta.env.VITE_BACKEND_URL || ''
  const url = (backend ? backend.replace(/\/$/, '') : '') + '/api/painpoints'
  try {
    const res = await fetch(url, { credentials: 'omit', cache: 'no-store' })
    if (!res.ok) throw new Error('Failed to fetch painpoints')
    return await res.json()
  } catch (e) {
    console.warn('[painpoints] failed, returning empty', e)
    return []
  }
}

