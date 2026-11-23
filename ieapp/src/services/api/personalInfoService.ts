import apiClient from './apiClient';
import { PersonalInfoData, PersonalInfoResponse } from '../../types/personalInfo';

/** Formulario de información personal de Admisión. Con `applicationId`, guardar = confirmar para esa postulación. */
export const personalInfoService = {
  async get(applicationId?: number | null): Promise<PersonalInfoResponse> {
    const response = await apiClient.get('/me/personal-info', { params: applicationId ? { application_id: applicationId } : undefined });
    return response.data as PersonalInfoResponse;
  },

  async save(data: Partial<PersonalInfoData>, applicationId?: number | null): Promise<PersonalInfoResponse> {
    const response = await apiClient.put('/me/personal-info', { ...data, application_id: applicationId ?? undefined });
    return response.data as PersonalInfoResponse;
  },
};

export default personalInfoService;
