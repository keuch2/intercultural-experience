import apiClient from './apiClient';
import {
  ProgramEnvelope,
  ProgramDocumentEntry,
  EngineEnglishTestsResp,
  JobPoolOffersResp,
  JobPoolAssignment,
  PlacementData,
} from '../../types/programEngine';
import { AuPairVisaProcessData, AuPairSupportLog, AuPairResource } from '../../types/aupair';

interface SingleResp<T> { status: string; data: T }
interface ListResp<T> { status: string; data: T[]; locked?: boolean; reason?: string }

export interface EngineUploadPayload {
  document_type: string;
  files: Array<{ uri: string; name: string; type: string }>;
}

/**
 * API genérica del motor de programas: /api/programs/{slug}/… y /api/me/process.
 * Espejo de auPairService para programas configurables (Work & Travel, etc.).
 */
class ProgramEngineService {
  /** Última postulación del usuario en un programa del motor (arranque de la app). */
  async getMyProcess(): Promise<ProgramEnvelope | null> {
    try {
      const res = await apiClient.get<SingleResp<ProgramEnvelope>>('/me/process');
      return res.data?.data ?? null;
    } catch (err: any) {
      if (err?.response?.status === 404) return null;
      throw err;
    }
  }

  async getProcess(slug: string): Promise<ProgramEnvelope | null> {
    try {
      const res = await apiClient.get<SingleResp<ProgramEnvelope>>(`/programs/${slug}/process`);
      return res.data?.data ?? null;
    } catch (err: any) {
      if (err?.response?.status === 404) return null;
      throw err;
    }
  }

  async getDocuments(slug: string, group?: string): Promise<{ entries: ProgramDocumentEntry[]; locked: boolean }> {
    const res = await apiClient.get<ListResp<ProgramDocumentEntry>>(`/programs/${slug}/documents`, {
      params: group ? { group } : undefined,
    });
    return { entries: res.data?.data ?? [], locked: !!res.data?.locked };
  }

  async uploadDocument(slug: string, payload: EngineUploadPayload): Promise<any[]> {
    const form = new FormData();
    form.append('document_type', payload.document_type);
    payload.files.forEach((f, i) => {
      form.append(`files[${i}]`, { uri: f.uri, name: f.name, type: f.type } as any);
    });
    const res = await apiClient.post<ListResp<any>>(`/programs/${slug}/documents`, form, {
      headers: { 'Content-Type': 'multipart/form-data' },
      transformRequest: (d) => d,
    });
    return res.data?.data ?? [];
  }

  async deleteDocument(slug: string, id: number): Promise<void> {
    await apiClient.delete(`/programs/${slug}/documents/${id}`);
  }

  async getEnglishTests(slug: string): Promise<EngineEnglishTestsResp> {
    const res = await apiClient.get<SingleResp<EngineEnglishTestsResp>>(`/programs/${slug}/english-tests`);
    return res.data?.data || { max_attempts: 3, used_attempts: 0, remaining_attempts: 3, tests: [] };
  }

  async getVisaProcess(slug: string): Promise<AuPairVisaProcessData> {
    const res = await apiClient.get<SingleResp<AuPairVisaProcessData>>(`/programs/${slug}/visa-process`);
    return res.data?.data || { has_visa_process: false };
  }

  async getSupportLogs(slug: string): Promise<AuPairSupportLog[]> {
    const res = await apiClient.get<ListResp<AuPairSupportLog>>(`/programs/${slug}/support-logs`);
    return res.data?.data ?? [];
  }

  async getResources(slug: string): Promise<AuPairResource[]> {
    const res = await apiClient.get<ListResp<AuPairResource>>(`/programs/${slug}/resources`);
    return res.data?.data ?? [];
  }

  // ── Pool de ofertas / placement ──────────────────────────────────────
  async getJobPoolOffers(slug: string): Promise<JobPoolOffersResp> {
    try {
      const res = await apiClient.get<JobPoolOffersResp>(`/programs/${slug}/job-pool/offers`);
      return { ...res.data, access: res.data?.access ?? true, my_assignment: res.data?.my_assignment ?? null, data: res.data?.data ?? [] };
    } catch (err: any) {
      if (err?.response?.status === 403) return { access: false, my_assignment: null, data: [] };
      throw err;
    }
  }

  async selectOffer(slug: string, offerId: number): Promise<JobPoolAssignment> {
    const res = await apiClient.post<SingleResp<JobPoolAssignment>>(`/programs/${slug}/job-pool/offers/${offerId}/select`, { confirm: true });
    return res.data.data;
  }

  async getAssignment(slug: string): Promise<JobPoolAssignment | null> {
    const res = await apiClient.get<SingleResp<JobPoolAssignment | null>>(`/programs/${slug}/job-pool/assignment`);
    return res.data?.data ?? null;
  }

  async getPlacement(slug: string): Promise<PlacementData> {
    const res = await apiClient.get<SingleResp<PlacementData>>(`/programs/${slug}/placement`);
    return res.data?.data ?? { has_assignment: false, offer: null, placement: null };
  }
}

export const programEngineService = new ProgramEngineService();
export default programEngineService;
