export type PaymentStatus = 'pending' | 'verified' | 'rejected';

export interface Payment {
  id: number;
  application_id: number;
  concept: string;
  amount: number;
  currency: string | null;
  exchange_rate: number | null;
  converted_amount: number | null;
  payment_method: string | null;
  reference_number: string | null;
  payment_date: string | null;
  status: PaymentStatus;
  status_label: string;
  notes: string | null;
  receipt_url: string | null;
  verified_at: string | null;
  created_at: string | null;
}

export interface InstallmentDetail {
  id: number;
  installment_number: number;
  amount: number;
  due_date: string | null;
  status: string;
}

export interface InstallmentPlan {
  id: number;
  plan_name: string;
  total_installments: number;
  total_amount: number;
  currency: string | null;
  status: string;
  details: InstallmentDetail[];
}
