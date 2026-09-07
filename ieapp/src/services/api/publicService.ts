import apiClient from './apiClient';

/**
 * Programa promocional para vista pública (sin auth).
 * Au Pair y los programas del motor (engine_enabled) tienen `is_available_in_app=true`.
 */
export interface PublicProgram {
  id: number;
  name: string;
  description: string;
  country: string | null;
  location: string | null;
  main_category: string;
  subcategory: string | null;
  image_url: string | null;
  duration: string | null;
  is_available_in_app: boolean;
  slug?: string | null;
  engine_enabled?: boolean;
  onboarding?: import('../../types/programEngine').ProgramOnboarding | null;
  // Solo en detalle
  start_date?: string | null;
  end_date?: string | null;
  application_deadline?: string | null;
  capacity?: number | null;
  credits?: number | null;
  cost?: number | string | null;
}

interface ListResponse {
  status: 'success';
  data: PublicProgram[];
}

interface ShowResponse {
  status: 'success';
  data: PublicProgram;
}

export const publicService = {
  async getPublicPrograms(): Promise<PublicProgram[]> {
    const res = await apiClient.get<ListResponse>('/public/programs');
    return res.data?.data ?? [];
  },

  async getPublicProgram(id: number | string): Promise<PublicProgram | null> {
    try {
      const res = await apiClient.get<ShowResponse>(`/public/programs/${id}`);
      return res.data?.data ?? null;
    } catch (err: any) {
      if (err?.response?.status === 404) return null;
      throw err;
    }
  },
};

export default publicService;
