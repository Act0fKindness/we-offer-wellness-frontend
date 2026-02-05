export type ReviewSource =
  | 'google' | 'facebook' | 'trustpilot' | 'yelp' | 'tripadvisor' | 'applemaps'
  | 'treatwell' | 'fresha' | 'booksy' | 'bark' | 'yell'
  | 'doctify' | 'whatclinic' | 'topdoctors' | 'zocdoc' | 'healthgrades'
  | 'nextdoor' | 'yogaalliance' | 'bacp' | 'psychologytoday' | 'wow_manual';

export type ReviewCandidate = {
  source: ReviewSource;
  profile_name?: string;
  profile_url?: string;
  external_id?: string;
  score: number;
  bucket: 'exact' | 'strong' | 'weak' | 'nope';
  evidence?: Record<string, any>;
};

export type ReviewProfile = {
  id: number;
  vendor_id: number;
  source: ReviewSource;
  profile_name?: string;
  profile_url: string;
  write_review_url?: string;
  external_id?: string;
  aggregate_rating?: number;
  review_count?: number;
  confidence?: number;
  meta?: any;
  last_crawled_at?: string;
};

export type CertificationSnapshot = {
  vendor_id: number;
  status: 'not_eligible' | 'eligible' | 'certified' | 'suspended' | 'revoked';
  wow_rating?: number;
  total_verified: number;
  total_unverified: number;
  total_external: number;
  last_evaluated_at?: string;
  reason?: string;
  meta?: any;
};

