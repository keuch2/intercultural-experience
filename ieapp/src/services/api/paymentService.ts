import apiClient from './apiClient';
import { Payment, InstallmentPlan } from '../../types/payment';

interface ListResp<T> { status: string; data: T[] }
interface OneResp<T> { status: string; data: T | null }

export interface CreatePaymentPayload {
  application_id: number;
  amount: number;
  concept: string;
  currency_id?: number;
  payment_method?: string;
  reference_number?: string;
  payment_date?: string;
  notes?: string;
  receipt?: { uri: string; name: string; type: string };
}

class PaymentService {
  async getPayments(applicationId?: number): Promise<Payment[]> {
    const res = await apiClient.get<ListResp<Payment>>('/payments', {
      params: applicationId ? { application_id: applicationId } : undefined,
    });
    return res.data?.data ?? [];
  }

  async getPayment(id: number): Promise<Payment | null> {
    try {
      const res = await apiClient.get<OneResp<Payment>>(`/payments/${id}`);
      return res.data?.data ?? null;
    } catch (err: any) {
      if (err?.response?.status === 404) return null;
      throw err;
    }
  }

  async createPayment(payload: CreatePaymentPayload): Promise<Payment | null> {
    const form = new FormData();
    Object.entries(payload).forEach(([key, val]) => {
      if (val === undefined || val === null) return;
      if (key === 'receipt' && val && typeof val === 'object') {
        form.append('receipt', val as any);
      } else {
        form.append(key, String(val));
      }
    });
    const res = await apiClient.post<OneResp<Payment>>('/payments', form, {
      headers: { 'Content-Type': 'multipart/form-data' },
      transformRequest: (d) => d,
    });
    return res.data?.data ?? null;
  }

  async uploadReceipt(id: number, file: { uri: string; name: string; type: string }): Promise<Payment | null> {
    const form = new FormData();
    form.append('receipt', file as any);
    const res = await apiClient.post<OneResp<Payment>>(`/payments/${id}/receipt`, form, {
      headers: { 'Content-Type': 'multipart/form-data' },
      transformRequest: (d) => d,
    });
    return res.data?.data ?? null;
  }

  async getInstallments(applicationId: number): Promise<InstallmentPlan | null> {
    const res = await apiClient.get<OneResp<InstallmentPlan>>('/payments/installments', {
      params: { application_id: applicationId },
    });
    return res.data?.data ?? null;
  }
}

export const paymentService = new PaymentService();
export default paymentService;
