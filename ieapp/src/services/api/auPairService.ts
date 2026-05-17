import apiClient from './apiClient';
import {
  AuPairProcess,
  AuPairDocumentEntry,
  DocStage,
  AuPairEnglishTestsResp,
  AuPairVisaProcessData,
  AuPairMatch,
  AuPairSupportLog,
  AuPairResource,
} from '../../types/aupair';

interface SingleResp<T> { status: string; data: T }
interface ListResp<T> { status: string; data: T[] }

export interface UploadDocumentPayload {
  document_type: string;
  stage: DocStage;
  files: Array<{
    uri: string;
    name: string;
    type: string; // mime
  }>;
}

class AuPairService {
  async getProcess(): Promise<AuPairProcess | null> {
    try {
      const res = await apiClient.get<SingleResp<AuPairProcess>>('/au-pair/process');
      return res.data?.data ?? null;
    } catch (err: any) {
      if (err?.response?.status === 404) return null;
      throw err;
    }
  }

  async getDocuments(stage?: DocStage): Promise<AuPairDocumentEntry[]> {
    const res = await apiClient.get<ListResp<AuPairDocumentEntry>>('/au-pair/documents', {
      params: stage ? { stage } : undefined,
    });
    return res.data?.data ?? [];
  }

  async uploadDocument(payload: UploadDocumentPayload): Promise<AuPairDocumentEntry[]> {
    const form = new FormData();
    form.append('document_type', payload.document_type);
    form.append('stage', payload.stage);
    payload.files.forEach((f, i) => {
      // RN FormData espera { uri, name, type }
      form.append(`files[${i}]`, {
        uri: f.uri,
        name: f.name,
        type: f.type,
      } as any);
    });

    const res = await apiClient.post<ListResp<AuPairDocumentEntry>>('/au-pair/documents', form, {
      headers: { 'Content-Type': 'multipart/form-data' },
      transformRequest: (d) => d, // axios no debe serializar el FormData en RN
    });
    return res.data?.data ?? [];
  }

  async deleteDocument(id: number): Promise<void> {
    await apiClient.delete(`/au-pair/documents/${id}`);
  }

  // English tests
  async getEnglishTests(): Promise<AuPairEnglishTestsResp> {
    const res = await apiClient.get<SingleResp<AuPairEnglishTestsResp>>('/au-pair/english-tests');
    return res.data?.data || { max_attempts: 3, used_attempts: 0, remaining_attempts: 3, tests: [] };
  }

  async submitEnglishTest(payload: {
    exam_name: string;
    final_score: number;
    oral_score?: 'Good' | 'Great' | 'Excellent';
    listening_score?: number;
    reading_score?: number;
    observations?: string;
    pdf?: { uri: string; name: string; type: string };
  }): Promise<any> {
    const form = new FormData();
    Object.entries(payload).forEach(([k, v]) => {
      if (v === undefined || v === null) return;
      if (k === 'pdf' && v) form.append('pdf', v as any);
      else form.append(k, String(v));
    });
    const res = await apiClient.post('/au-pair/english-tests', form, {
      headers: { 'Content-Type': 'multipart/form-data' },
      transformRequest: (d) => d,
    });
    return res.data?.data;
  }

  // Visa
  async getVisaProcess(): Promise<AuPairVisaProcessData> {
    const res = await apiClient.get<SingleResp<AuPairVisaProcessData>>('/au-pair/visa-process');
    return res.data?.data || { has_visa_process: false };
  }

  // Matches
  async getMatches(): Promise<AuPairMatch[]> {
    const res = await apiClient.get<ListResp<AuPairMatch>>('/au-pair/matches');
    return res.data?.data ?? [];
  }

  async getMatch(id: number): Promise<AuPairMatch | null> {
    try {
      const res = await apiClient.get<SingleResp<AuPairMatch>>(`/au-pair/matches/${id}`);
      return res.data?.data ?? null;
    } catch (err: any) {
      if (err?.response?.status === 404) return null;
      throw err;
    }
  }

  // Support logs
  async getSupportLogs(): Promise<AuPairSupportLog[]> {
    const res = await apiClient.get<ListResp<AuPairSupportLog>>('/au-pair/support-logs');
    return res.data?.data ?? [];
  }

  // Resources
  async getResources(): Promise<AuPairResource[]> {
    const res = await apiClient.get<ListResp<AuPairResource>>('/au-pair/resources');
    return res.data?.data ?? [];
  }
}

export const auPairService = new AuPairService();
export default auPairService;
