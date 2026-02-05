export async function fetchLocations(limit = 12, search = '') {
  try {
    const qs = new URLSearchParams({ limit: String(limit), ...(search ? { search } : {}) }).toString()
    const res = await fetch(`/api/locations?${qs}`, { cache: 'no-store' })
    if (!res.ok) throw new Error(`locations ${res.status}`)
    const data = await res.json()
    const items = Array.isArray(data) ? data : (data.items || [])
    // Ensure Online is first and unique
    const seen = new Set()
    const out = []
    items.forEach(it => {
      const v = (it?.value || it?.label || '').trim()
      if (!v || seen.has(v.toLowerCase())) return
      seen.add(v.toLowerCase())
      out.push({ label: it.label || v, subtitle: it.subtitle || '', value: v, icon: it.icon || 'geo' })
    })
    // Guarantee Online first
    const idx = out.findIndex(x => x.value.toLowerCase() === 'online')
    if (idx > 0) { const [o] = out.splice(idx,1); out.unshift(o) }
    if (idx === -1) out.unshift({ label:'Online', subtitle:'Virtual', value:'Online', icon:'wifi' })
    return out
  } catch (e) {
    console.warn('[locations] failed', e)
    return [
      { label:'Online', subtitle:'Virtual', value:'Online', icon:'wifi' },
      { label:'London', subtitle:'United Kingdom', value:'London', icon:'geo' },
      { label:'Manchester', subtitle:'United Kingdom', value:'Manchester', icon:'geo' },
      { label:'Brighton & Hove', subtitle:'United Kingdom', value:'Brighton & Hove', icon:'geo' },
      { label:'Kent', subtitle:'United Kingdom', value:'Kent', icon:'geo' },
    ]
  }
}
