/** Postulación del usuario tal como la devuelve GET /api/applications (con `program`). */
export interface UserApplicationProgram {
  id: number;
  name: string;
  slug?: string | null;
  engine_enabled?: boolean;
  is_available_in_app?: boolean;
  subcategory?: string | null;
  main_category?: string;
  image_url?: string | null;
  country?: string | null;
  duration?: string | null;
}

export interface UserApplication {
  id: number;
  user_id: number;
  program_id: number;
  status: string; // pending | in_review | approved | rejected | cancelled | ...
  current_stage?: string | null;
  progress_percentage?: number | null;
  applied_at?: string | null;
  started_at?: string | null;
  completed_at?: string | null;
  created_at?: string;
  program: UserApplicationProgram;
}

export const APPLICATION_STATUS_LABELS: Record<string, string> = {
  pending: 'En revisión',
  in_review: 'En revisión',
  approved: 'Aprobada',
  rejected: 'Rechazada',
  cancelled: 'Cancelada',
  withdrawn: 'Retirada',
  completed: 'Finalizada',
};
