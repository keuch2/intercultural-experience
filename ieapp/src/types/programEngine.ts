/**
 * Tipos del motor de programas (Work & Travel y programas configurables).
 * Corresponden a app/Services/ProgramEngine/EnvelopeBuilder y a los controladores
 * API/ProgramEngine/*. Las claves de etapa/grupo son strings abiertos: las define
 * el admin, no la app.
 */
export type StageState = 'locked' | 'in_progress' | 'complete';

export interface EngineProgram {
  id: number;
  slug: string;
  name: string;
  modules: string[];
  rules: Record<string, any>;
  onboarding: ProgramOnboarding | null;
}

export interface ProgramOnboarding {
  title?: string | null;
  intro?: string | null;
  steps?: Array<{ title: string; body: string }>;
  terms_text?: string | null;
  requires_adult?: boolean;
}

export interface EngineStage {
  key: string;
  label: string;
  state: StageState;
  status: string;
  is_terminal: boolean;
  mobile_screen: string | null;
}

export interface EngineNextAction {
  key: string;
  label: string;
  screen: string | null;
  params?: Record<string, any> | null;
}

export interface EngineChecklistItem {
  key: string;
  label: string;
  stage_key: string | null;
  item_type: 'boolean' | 'file';
  done: boolean;
  done_at: string | null;
  has_file: boolean;
}

export interface EngineGate {
  key: string;
  label: string;
  verified: boolean;
  verified_at: string | null;
  amount: number | null;
  currency: string | null;
}

export interface EngineDocumentGroup {
  key: string;
  label: string;
  stage_key: string;
  unlock_gate_key: string | null;
  unlocked: boolean;
  lock_reason: string | null;
  counts: { required: number; approved: number; pending: number; missing: number };
}

export interface EngineModuleInfo {
  enabled: boolean;
  label: string;
  icon: string;
  screen: string;
  implemented: boolean;
  // english_test
  best_level?: string | null;
  min_level?: string;
  remaining_attempts?: number;
  meets_minimum?: boolean;
  // visa
  progress?: number;
  // job_pool
  access?: boolean;
  has_active_assignment?: boolean;
  // placement
  complete?: boolean;
}

export interface ProgramEnvelope {
  id: number;
  application_id: number;
  program: EngineProgram;
  current_stage: string;
  status: 'active' | 'completed' | 'cancelled';
  application_approved: boolean;
  application_review_status: string | null;
  enrollment_date: string | null;
  season: string | null;
  stages: EngineStage[];
  statuses: Record<string, string>;
  flags: Record<string, boolean>;
  checklist: EngineChecklistItem[];
  gates: EngineGate[];
  document_groups: EngineDocumentGroup[];
  modules: Record<string, EngineModuleInfo>;
  progress_pct: number;
  next_action: EngineNextAction;
  blocking_reasons: string[];
  finalization: { result: string; reason: string | null; date: string | null } | null;
}

export type DocStatus = 'missing' | 'pending' | 'approved' | 'rejected';

export interface ProgramDocumentFile {
  id: number;
  document_type: string;
  stage: string;
  status: 'pending' | 'approved' | 'rejected';
  status_label: string;
  original_filename: string | null;
  file_size: number | null;
  file_size_formatted: string | null;
  rejection_reason: string | null;
  reviewed_at: string | null;
  uploaded_at: string | null;
  uploaded_by_type: 'participant' | 'staff';
  download_url: string | null;
}

/** Superset estructural de AuPairDocumentEntry: `stage` = grupo (tab). */
export interface ProgramDocumentEntry {
  document_type: string;
  label: string;
  description?: string | null;
  stage: string;
  stage_key?: string;
  group?: string;
  required: boolean;
  min_count: number | null;
  allow_multiple?: boolean;
  uploaded_by: 'participant' | 'staff';
  section?: string | null;
  unlocked?: boolean;
  lock_reason?: string | null;
  count: number;
  approved_count?: number;
  status: DocStatus;
  files: ProgramDocumentFile[];
}

export interface EngineEnglishTestsResp {
  max_attempts: number;
  used_attempts: number;
  remaining_attempts: number;
  min_level?: string;
  best_level?: string | null;
  meets_minimum?: boolean;
  tests: Array<{
    id: number;
    exam_name: string | null;
    attempt_number: number;
    oral_score: string | null;
    listening_score: number | null;
    reading_score: number | null;
    final_score: number | null;
    cefr_level: string | null;
    meets_minimum: boolean;
    observations: string | null;
    pdf_url: string | null;
    created_at: string | null;
  }>;
}

// ── Pool de ofertas / placement ─────────────────────────────────────────
export interface JobPoolOffer {
  id: number;
  job_title: string | null;
  employer_name: string;
  state: string;
  city: string;
  positions_available: number;
  positions_total: number;
  pdf_url: string | null;
  published_at: string | null;
  /** Fecha límite para postular (YYYY-MM-DD); se muestra mientras la oferta siga abierta */
  application_deadline: string | null;
  deadline_passed?: boolean;
}

export interface JobPoolAssignment {
  id: number;
  status: 'active' | 'released' | 'reassigned';
  selected_at: string | null;
  offer: JobPoolOffer;
}

export interface JobPoolOffersResp {
  access: boolean;
  allow_reselect?: boolean;
  my_assignment: JobPoolAssignment | null;
  data: JobPoolOffer[];
}

export interface PlacementData {
  has_assignment: boolean;
  offer: {
    id: number;
    job_title?: string | null;
    employer_name: string;
    state: string;
    city: string;
    pdf_url: string | null;
    selected_at: string | null;
  } | null;
  placement: {
    status: string;
    status_label: string;
    sponsor: string | null;
    acceptance_date: string | null;
    program_start_date: string | null;
    program_end_date: string | null;
    terms_accepted_at: string | null;
    sevis_number: string | null;
    ds2019_number: string | null;
    ds_tracking_carrier: string | null;
    ds_tracking_number: string | null;
    ds_received_at: string | null;
    documents_complete: boolean;
    is_complete: boolean;
  } | null;
}
