/**
 * Tipos del dominio Au Pair (mobile V1).
 * Corresponden 1:1 con la serialización del backend en:
 *   - app/Http/Controllers/API/AuPairProcessController.php
 *   - app/Http/Controllers/API/AuPairDocumentController.php
 */

export type AuPairStage = 'admission' | 'application' | 'match_visa' | 'support' | 'completed';
export type DocStage = 'admission' | 'application_payment1' | 'application_payment2' | 'visa';
export type StageState = 'locked' | 'in_progress' | 'complete';

export interface AuPairStageSummary {
  key: AuPairStage;
  label: string;
  state: StageState;
}

export interface AuPairFlags {
  welcome_email_sent: boolean;
  interview_process_email_sent: boolean;
  all_docs_and_payments_complete: boolean;
  itep_completed: boolean;
  contract_signed: boolean;
  payment_1_verified: boolean;
  payment_2_verified: boolean;
}

export interface AuPairNextAction {
  key: string;
  label: string;
  screen: string | null;
  params?: Record<string, any>;
}

export interface AuPairProcess {
  id: number;
  application_id: number;
  current_stage: AuPairStage;
  enrollment_date: string | null;
  statuses: {
    admission: string;
    application: string;
    match_visa: string;
    support: string;
  };
  flags: AuPairFlags;
  stages: AuPairStageSummary[];
  progress_pct: number;
  next_action: AuPairNextAction;
  finalization: { result: string; reason: string | null; date: string | null } | null;
}

export type DocStatus = 'missing' | 'pending' | 'approved' | 'rejected';

export interface AuPairDocumentFile {
  id: number;
  document_type: string;
  stage: DocStage;
  status: 'pending' | 'approved' | 'rejected';
  status_label: string;
  original_filename: string | null;
  file_size: number | null;
  file_size_formatted: string | null;
  rejection_reason: string | null;
  reviewed_at: string | null;
  uploaded_at: string | null;
  uploaded_by_type: 'participant' | 'staff';
  download_url: string;
}

export interface AuPairDocumentEntry {
  document_type: string;
  label: string;
  stage: DocStage;
  required: boolean;
  min_count: number | null;
  allow_multiple?: boolean;
  uploaded_by: 'participant' | 'staff';
  count: number;
  status: DocStatus;
  files: AuPairDocumentFile[];
}

// English Tests
export interface AuPairEnglishTest {
  id: number;
  exam_name: string;
  attempt_number: number;
  oral_score: string | null;
  listening_score: number | null;
  reading_score: number | null;
  final_score: number;
  cefr_level: string | null;
  meets_minimum: boolean;
  observations: string | null;
  pdf_url: string | null;
  created_at: string | null;
}

export interface AuPairEnglishTestsResp {
  max_attempts: number;
  used_attempts: number;
  remaining_attempts: number;
  tests: AuPairEnglishTest[];
}

// Visa
export interface VisaTimelineItem {
  key: string;
  label: string;
  completed: boolean;
  meta?: string | null;
}

export interface AuPairVisaProcessData {
  has_visa_process: boolean;
  message?: string;
  progress_pct?: number;
  interview?: {
    result: string | null;
    result_label: string | null;
    notes: string | null;
    date: string | null;
    time: string | null;
    embassy: string | null;
  };
  travel?: {
    departure: string | null;
    arrival_usa: string | null;
    flight_info: any;
  };
  pre_departure?: {
    date: string | null;
    completed: boolean;
  };
  timeline?: VisaTimelineItem[];
}

// Matches
export interface AuPairMatch {
  id: number;
  match_type: 'initial' | 'rematch' | 'extension';
  match_type_label: string;
  match_date: string | null;
  host_state: string | null;
  host_city: string | null;
  is_active: boolean;
  // Solo en detalle
  host_address?: string | null;
  host_mom_name?: string | null;
  host_dad_name?: string | null;
  host_email?: string | null;
  host_phone?: string | null;
  ended_at?: string | null;
  end_reason?: string | null;
}

// Support log
export interface AuPairSupportLog {
  id: number;
  log_type: string;
  log_type_label: string;
  title: string | null;
  description: string | null;
  log_date: string | null;
  follow_up_number: number | null;
  severity: string | null;
  severity_label: string | null;
  resolution: string | null;
  resolved_at: string | null;
}

// Resources
export interface AuPairResource {
  id: number;
  title: string;
  description: string | null;
  icon: string | null;
  file_type: string | null;
  file_size_formatted: string | null;
  external_url: string | null;
  download_url: string | null;
}
