/**
 * @typedef {Object} ProductMedia
 * @property {number} id
 * @property {number} product_id
 * @property {string} [type]
 * @property {string|number} [size]
 * @property {any} [metadata]
 * @property {string} [media_url]
 * @property {string} [media_thumbnail_url]
 * @property {string} [mime_type]
 * @property {number} [order]
 */

/**
 * @typedef {Object} ProductImageRow
 * @property {number} id
 * @property {number} product_id
 * @property {string} image_path
 * @property {string} [image_url]
 */

const apiBase = import.meta.env.VITE_API_BASE_URL || '/api';

/**
 * Fetch media rows for a product (new product_media table).
 * @param {number|string} productId
 * @returns {Promise<ProductMedia[]>}
 */
export async function fetchProductMedia(productId) {
  const res = await fetch(`${apiBase}/products/${productId}/media`, { cache: 'no-store' });
  if (!res.ok) throw new Error(`Failed to load product media: ${res.status}`);
  const data = await res.json();
  return Array.isArray(data) ? data : (data.media || []);
}

/**
 * Fetch legacy product images for a product (product_images table).
 * @param {number|string} productId
 * @returns {Promise<ProductImageRow[]>}
 */
export async function fetchProductImages(productId) {
  const res = await fetch(`${apiBase}/products/${productId}/images`, { cache: 'no-store' });
  if (!res.ok) throw new Error(`Failed to load product images: ${res.status}`);
  const data = await res.json();
  return Array.isArray(data) ? data : (data.images || []);
}

/**
 * Fetch user-owned media (e.g., practitioner content gallery).
 * @param {number|string} userId
 * @returns {Promise<ProductMedia[]>}
 */
export async function fetchUserMedia(userId) {
  const res = await fetch(`${apiBase}/users/${userId}/media`, { cache: 'no-store' });
  if (!res.ok) throw new Error(`Failed to load user media: ${res.status}`);
  const data = await res.json();
  return Array.isArray(data) ? data : (data.media || []);
}

/**
 * Pick the best display URL from media rows.
 * @param {ProductMedia[]} media
 * @returns {string|null}
 */
export function pickPrimaryMediaUrl(media) {
  if (!Array.isArray(media) || media.length === 0) return null;
  const sorted = [...media].sort((a, b) => (a.order ?? 9999) - (b.order ?? 9999));
  const first = sorted.find(m => (m.type || '').toLowerCase() !== 'video') || sorted[0];
  return first?.media_thumbnail_url || first?.media_url || null;
}

/**
 * Fetch article media rows (article_media table).
 * @param {number|string} articleId
 * @returns {Promise<ProductMedia[]>}
 */
export async function fetchArticleMedia(articleId) {
  const res = await fetch(`${apiBase}/articles/${articleId}/media`, { cache: 'no-store' });
  if (!res.ok) throw new Error(`Failed to load article media: ${res.status}`);
  const data = await res.json();
  return Array.isArray(data) ? data : (data.media || []);
}

/**
 * Fetch user media tied to an article (user_media table with article_id).
 * @param {number|string} articleId
 * @returns {Promise<ProductMedia[]>}
 */
export async function fetchArticleUserMedia(articleId) {
  const res = await fetch(`${apiBase}/articles/${articleId}/user-media`, { cache: 'no-store' });
  if (!res.ok) throw new Error(`Failed to load article user media: ${res.status}`);
  const data = await res.json();
  return Array.isArray(data) ? data : (data.media || []);
}
