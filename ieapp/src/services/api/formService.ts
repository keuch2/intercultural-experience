import apiClient from './apiClient';
import { ProgramForm, FormSubmission, FormData, FormSubmissionResponse } from '../../types/forms';

export interface FormStructureResponse {
  form: ProgramForm;
  structure: {
    sections: any[];
    fields: any[];
    settings: any;
  };
}

class FormService {
  /**
   * Obtener formularios disponibles para un programa
   */
  async getProgramForms(programId: number): Promise<ProgramForm[]> {
    try {
      const response = await apiClient.get(`/programs/${programId}/forms`);
      return response.data.data || [];
    } catch (error) {
      console.error('Error fetching program forms:', error);
      throw error;
    }
  }

  /**
   * Obtener estructura de un formulario específico
   */
  async getFormStructure(formId: number): Promise<FormStructureResponse> {
    try {
      const response = await apiClient.get(`/forms/${formId}/structure`);
      return response.data;
    } catch (error) {
      console.error('Error fetching form structure:', error);
      throw error;
    }
  }

  /**
   * Obtener formulario activo para un programa
   */
  async getActiveForm(programId: number): Promise<ProgramForm | null> {
    try {
      const response = await apiClient.get(`/programs/${programId}/active-form`);
      return response.data.data || null;
    } catch (error) {
      console.error('Error fetching active form:', error);
      throw error;
    }
  }

  /**
   * Enviar respuesta de formulario
   */
  async submitForm(formId: number, formData: FormData): Promise<FormSubmissionResponse> {
    try {
      const response = await apiClient.post(`/forms/${formId}/submit`, {
        form_data: formData,
      });
      return response.data;
    } catch (error) {
      console.error('Error submitting form:', error);
      throw error;
    }
  }

  /**
   * Guardar borrador de formulario
   */
  async saveDraft(formId: number, formData: FormData): Promise<FormSubmissionResponse> {
    try {
      const response = await apiClient.post(`/forms/${formId}/draft`, {
        form_data: formData,
      });
      return response.data;
    } catch (error) {
      console.error('Error saving draft:', error);
      throw error;
    }
  }

  /**
   * Obtener borradores del usuario
   */
  async getUserDrafts(): Promise<FormSubmission[]> {
    try {
      const response = await apiClient.get('/forms/drafts');
      return response.data.data || [];
    } catch (error) {
      console.error('Error fetching drafts:', error);
      throw error;
    }
  }

  /**
   * Obtener respuestas enviadas del usuario
   */
  async getUserSubmissions(): Promise<FormSubmission[]> {
    try {
      const response = await apiClient.get('/forms/submissions');
      return response.data.data || [];
    } catch (error) {
      console.error('Error fetching submissions:', error);
      throw error;
    }
  }

  /**
   * Obtener una respuesta específica
   */
  async getSubmission(submissionId: number): Promise<FormSubmission> {
    try {
      const response = await apiClient.get(`/forms/submissions/${submissionId}`);
      return response.data.data;
    } catch (error) {
      console.error('Error fetching submission:', error);
      throw error;
    }
  }

  /**
   * Actualizar borrador existente
   */
  async updateDraft(submissionId: number, formData: FormData): Promise<FormSubmissionResponse> {
    try {
      const response = await apiClient.put(`/forms/submissions/${submissionId}/draft`, {
        form_data: formData,
      });
      return response.data;
    } catch (error) {
      console.error('Error updating draft:', error);
      throw error;
    }
  }

  /**
   * Eliminar borrador
   */
  async deleteDraft(submissionId: number): Promise<void> {
    try {
      await apiClient.delete(`/forms/submissions/${submissionId}/draft`);
    } catch (error) {
      console.error('Error deleting draft:', error);
      throw error;
    }
  }

  /**
   * Subir archivo para un campo de formulario
   */
  async uploadFile(file: any): Promise<{ url: string; filename: string }> {
    try {
      const formData = new FormData();
      formData.append('file', {
        uri: file.uri,
        type: file.type,
        name: file.name,
      } as any);

      const response = await apiClient.post('/forms/upload', formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      });

      return response.data;
    } catch (error) {
      console.error('Error uploading file:', error);
      throw error;
    }
  }

  /**
   * Validar datos del formulario
   */
  async validateFormData(formId: number, formData: FormData): Promise<{
    valid: boolean;
    errors: Array<{ field_key: string; message: string }>;
  }> {
    try {
      const response = await apiClient.post(`/forms/${formId}/validate`, {
        form_data: formData,
      });
      return response.data;
    } catch (error) {
      console.error('Error validating form data:', error);
      throw error;
    }
  }

  /**
   * Obtener países disponibles
   */
  async getCountries(): Promise<Array<{ code: string; name: string }>> {
    try {
      const response = await apiClient.get('/forms/countries');
      return response.data.data || [];
    } catch (error) {
      console.error('Error fetching countries:', error);
      // Fallback a lista básica
      return [
        { code: 'PY', name: 'Paraguay' },
        { code: 'AR', name: 'Argentina' },
        { code: 'BR', name: 'Brasil' },
        { code: 'US', name: 'Estados Unidos' },
        { code: 'ES', name: 'España' },
        { code: 'FR', name: 'Francia' },
        { code: 'DE', name: 'Alemania' },
      ];
    }
  }

  /**
   * Obtener plantillas de formularios disponibles
   */
  async getFormTemplates(category?: string): Promise<any[]> {
    try {
      const params = category ? { category } : {};
      const response = await apiClient.get('/forms/templates', { params });
      return response.data.data || [];
    } catch (error) {
      console.error('Error fetching form templates:', error);
      throw error;
    }
  }
}

export default new FormService(); 