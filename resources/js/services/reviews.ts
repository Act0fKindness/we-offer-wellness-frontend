import axios from 'axios'
import type { ReviewCandidate, CertificationSnapshot } from '@/types/reviews'

export async function discoverReviews(sources?: string[]): Promise<ReviewCandidate[]> {
  const { data } = await axios.post('/api/reviews/discover', { sources })
  return data?.candidates || []
}

export async function refreshReviewAggregates(sources?: string[]): Promise<number> {
  const { data } = await axios.post('/api/reviews/refresh', { sources })
  return data?.updated || 0
}

export async function evaluateCertification(): Promise<CertificationSnapshot> {
  const { data } = await axios.post('/api/reviews/evaluate')
  return data?.snapshot
}

export async function suppressCandidate(payload: {
  source: string
  external_id?: string
  profile_name?: string
  postcode?: string
  city?: string
  reason?: string
}): Promise<boolean> {
  const { data } = await axios.post('/api/reviews/suppress', payload)
  return !!data?.ok
}

export async function linkProfile(payload: {
  source: string
  profile_url: string
  profile_name?: string
  external_id?: string
}) {
  const { data } = await axios.post('/api/reviews/link', payload)
  return data?.profile
}
