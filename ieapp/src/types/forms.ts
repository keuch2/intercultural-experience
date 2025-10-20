export interface FormField {
  id: number;
  field_key: string;
  label: string;
  field_type: 'text' | 'email' | 'phone' | 'number' | 'textarea' | 'select' | 'multi_select' | 'checkbox' | 'radio' | 'date' | 'file' | 'section_header';
  is_required: boolean;
  placeholder?: string;
  help_text?: string;
  validation_rules?: {
    min_length?: number;
    max_length?: number;
    min_value?: number;
    max_value?: number;
    pattern?: string;
    allowed_extensions?: string[];
    max_file_size?: number;
  };
  options?: Array<{
    value: string;
    label: string;
  }>;
  conditional_logic?: {
    show_if_field?: string;
    show_if_value?: any;
  };
  order: number;
  section_id?: number;
}

export interface FormSection {
  id: number;
  title: string;
  description?: string;
  order: number;
  fields: FormField[];
}

export interface ProgramForm {
  id: number;
  program_id: number;
  title: string;
  description?: string;
  is_active: boolean;
  submission_deadline?: string;
  max_submissions?: number;
  allow_drafts: boolean;
  sections: FormSection[];
  fields: FormField[];
  created_at: string;
  updated_at: string;
}

export interface FormData {
  [fieldKey: string]: any;
}

export interface FormSubmission {
  id: number;
  program_form_id: number;
  user_id: number;
  form_data: FormData;
  is_draft: boolean;
  submitted_at?: string;
  created_at: string;
  updated_at: string;
  status: 'draft' | 'submitted' | 'approved' | 'rejected' | 'pending_review';
  reviewer_notes?: string;
}

export interface FormSubmissionResponse {
  success: boolean;
  message?: string;
  submission_id?: number;
  errors?: Array<{
    field_key: string;
    message: string;
  }>;
  data?: FormSubmission;
}

export interface FormValidationError {
  field_key: string;
  message: string;
}

export interface FileUploadResponse {
  url: string;
  filename: string;
  size: number;
  mime_type: string;
}

export interface CountryOption {
  code: string;
  name: string;
}

export interface FormTemplate {
  id: number;
  name: string;
  description?: string;
  category: string;
  structure: {
    sections: FormSection[];
    fields: FormField[];
    settings: any;
  };
}