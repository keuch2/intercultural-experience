/** Formulario de información personal de Admisión (GET/PUT /me/personal-info). */
export interface EmergencyContactInput {
  id?: number | null;
  name: string;
  relationship: string;
  phone: string;
  alternative_phone?: string | null;
  email?: string | null;
}

export interface WorkExperienceInput {
  id?: number | null;
  company: string;
  position: string;
  start_date: string; // AAAA-MM-DD
  end_date?: string | null;
  is_current: boolean;
  description?: string | null;
  reference_name?: string | null;
  reference_phone?: string | null;
  reference_email?: string | null;
}

export interface PersonalInfoData {
  name: string | null;
  email?: string;
  phone: string | null;
  birth_date: string | null;
  gender: string | null;
  nationality: string | null;
  ci_number: string | null;
  marital_status: string | null;
  country: string | null;
  city: string | null;
  city_id: number | null;
  address: string | null;
  academic_level: string | null;
  university: string | null;
  career: string | null;
  academic_year: string | null;
  english_level: string | null;
  medical_conditions: string | null;
  allergies: string | null;
  medications: string | null;
  emergency_contacts: EmergencyContactInput[];
  work_experiences: WorkExperienceInput[];
}

/** Estado de confirmación (también viene en el proceso: envelope.personal_info / auPairProcess.personal_info). */
export interface PersonalInfoStatus {
  required: boolean;
  confirmed: boolean;
  confirmed_at: string | null;
  complete: boolean;
  missing_sections: string[];
  /** La app debe pedirlo antes de seguir (requerido y sin confirmar para esta postulación). */
  pending: boolean;
}

export interface Option { value: string; label: string }

export interface PersonalInfoResponse {
  data: PersonalInfoData;
  personal_info: PersonalInfoStatus;
  sections: { key: string; label: string }[];
  options: { gender: Option[]; marital_status: Option[]; academic_level: Option[]; english_level: Option[] };
  message?: string;
}
