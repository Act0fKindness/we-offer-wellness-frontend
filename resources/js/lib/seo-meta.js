const SITE_BASE_URL = String(import.meta.env.VITE_PUBLIC_SITE_URL || 'https://www.weofferwellness.co.uk').replace(/\/+$/, '')

function cleanText(value) {
  return String(value ?? '').replace(/\s+/g, ' ').trim()
}

function pushKeyword(target, seen, value) {
  const keyword = cleanText(value)
  if (!keyword) return
  const key = keyword.toLowerCase()
  if (seen.has(key)) return
  seen.add(key)
  target.push(keyword)
}

export function canonicalUrl(path = '') {
  const value = cleanText(path)
  if (!value) return `${SITE_BASE_URL}/`

  if (/^https?:\/\//i.test(value)) {
    try {
      const url = new URL(value)
      return `${SITE_BASE_URL}${url.pathname.replace(/\/+$/, '') || '/'}`
    } catch {
      return `${SITE_BASE_URL}/`
    }
  }

  const normalised = value.startsWith('/') ? value : `/${value}`
  return `${SITE_BASE_URL}${normalised.replace(/\/+$/, '') || '/'}`
}

export function shortOgTitle(title, maxLength = 35) {
  let value = cleanText(title)
  if (!value) return 'WOW®'
  value = value.replace(/\s*[\-|–—]\s*We Offer Wellness®?$/iu, '').trim()
  value = value.replace(/\s*[\-|–—]\s*WOW®$/iu, '').trim()
  if (value.length <= maxLength) return value

  const suffix = ' | WOW®'
  const limit = Math.max(1, maxLength - suffix.length)
  return `${value.slice(0, limit).trim()}${suffix}`
}

export function shortOgDescription(description, maxLength = 62) {
  const value = cleanText(description)
  if (!value) return 'Trusted wellness experiences online and in person.'
  if (value.length <= maxLength) return value
  return value.slice(0, maxLength).trim()
}

export function pageKeywords({
  type = '',
  category = null,
  categories = [],
  location = '',
  city = '',
  county = '',
  town = '',
  products = [],
  offeringResults = [],
  extra = [],
} = {}) {
  const keywords = []
  const seen = new Set()

  ;[
    'We Offer Wellness',
    'WOW',
    'wellness marketplace',
    'holistic wellness',
    'book wellness experiences',
    'online and in person',
  ].forEach((value) => pushKeyword(keywords, seen, value))

  const typeValue = cleanText(type)
  if (typeValue) {
    const typeLabel = typeValue.charAt(0).toUpperCase() + typeValue.slice(1)
    ;[
      typeLabel,
      typeValue,
      `${typeLabel} near me`,
      `book ${typeLabel.toLowerCase()}`,
    ].forEach((value) => pushKeyword(keywords, seen, value))
  }

  const categorySeeds = []
  if (typeof category === 'string') {
    categorySeeds.push(category)
  } else if (category && typeof category === 'object') {
    categorySeeds.push(category.name || category.title || category.slug || '')
  }

  ;(Array.isArray(categories) ? categories : []).forEach((item) => {
    if (!item || typeof item !== 'object') return
    categorySeeds.push(item.name || item.title || '')
    categorySeeds.push(item.slug || '')
  })

  ;(Array.isArray(products) ? products : []).forEach((item) => {
    if (!item || typeof item !== 'object') return
    categorySeeds.push(item?.category?.name || '')
    categorySeeds.push(item?.category?.slug || '')
  })

  const offeringItems = Array.isArray(offeringResults) ? offeringResults : (offeringResults?.items || [])
  offeringItems.forEach((item) => {
    if (!item || typeof item !== 'object') return
    categorySeeds.push(item?.category?.name || '')
    categorySeeds.push(item?.category?.slug || '')
  })

  categorySeeds
    .map((value) => cleanText(value))
    .filter(Boolean)
    .forEach((value) => {
      pushKeyword(keywords, seen, value)
      pushKeyword(keywords, seen, value.replace(/-/g, ' '))
    })

  ;[location, city, county, town]
    .map((value) => cleanText(value))
    .filter(Boolean)
    .forEach((value) => pushKeyword(keywords, seen, value))

  ;[
    'therapies',
    'classes',
    'events',
    'workshops',
    'retreats',
    'reiki',
    'massage',
    'sound healing',
    'breathwork',
    'meditation',
    'yoga',
    'corporate wellness',
    'gift vouchers',
  ].forEach((value) => pushKeyword(keywords, seen, value))

  ;(Array.isArray(extra) ? extra : [extra]).flat().forEach((value) => pushKeyword(keywords, seen, value))

  return keywords
}
