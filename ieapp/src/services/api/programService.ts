import apiClient from './apiClient';
import AsyncStorage from '@react-native-async-storage/async-storage';

export interface ProgramRequisite {
  id: number;
  program_id: number;
  name: string;
  description?: string;
  type: string; // 'document', 'action', 'payment', etc.
  required: boolean;
  order?: number;
  created_at: string;
  updated_at: string;
}

export interface Program {
  id: number;
  name: string; // Changed from title to name to match API
  description: string;
  location: string;
  start_date: string;
  end_date: string;
  cost: number; // Changed from price to cost to match API
  image?: string; // Campo original de la BD
  image_url?: string; // Accessor del backend
  capacity: number;
  available_spots?: number;
  duration?: string;
  credits?: number;
  application_deadline?: string;
  status?: string;
  created_at: string;
  updated_at: string;
  requisites?: ProgramRequisite[];
}

export interface Application {
  id: number;
  user_id: number;
  program_id: number;
  status: string;
  applied_at: string;
  completed_at?: string;
  progress_percentage?: number;
  program?: Program;
  documents?: any[];
  requisites?: any[];
}

const programService = {
  /**
   * Get all available programs
   */
  getAllPrograms: async () => {
    const response = await apiClient.get<Program[]>('/programs');
    return response.data;
  },
  
  /**
   * Alias for getAllPrograms to match HomeScreen usage
   */
  getPrograms: async () => {
    const response = await apiClient.get<Program[]>('/programs');
    return response.data;
  },

  /**
   * Get a specific program by ID
   * @param id Program ID
   * @param includeRequisites Whether to include program requisites
   */
  getProgramById: async (id: number, includeRequisites: boolean = false) => {
    const response = await apiClient.get<Program>(`/programs/${id}`);
    const program = response.data;
    
    // If includeRequisites is true, get program requisites
    if (includeRequisites) {
      try {
        // Obtener los requisitos directamente usando la misma lógica que getProgramRequisites
        const token = await AsyncStorage.getItem('auth_token');
        if (token) {
          const requisitesResponse = await apiClient.get(`/programs/${id}/requisites`, {
            headers: {
              'Authorization': `Bearer ${token}`
            }
          });
          program.requisites = requisitesResponse.data;
        }
      } catch (error) {
        console.error('Error fetching program requisites:', error);
      }
    }
    
    return program;
  },

  /**
   * Apply for a program (create a new application)
   */
  applyForProgram: async (programId: number, applicationData: any) => {
    const response = await apiClient.post('/applications', {
      program_id: programId,
      ...applicationData
    });
    return response.data;
  },

  /**
   * Get all user's applications
   */
  getUserApplications: async () => {
    const response = await apiClient.get('/applications');
    return response;
  },
  
  /**
   * Get user's enrolled programs
   */
  getUserPrograms: async () => {
    const response = await apiClient.get('/applications');
    return response.data;
  },
  
  /**
   * Get program requisites
   * @param programId ID del programa
   * @param retryAuth Si debe reintentar con nuevo token en caso de error de autenticación
   */
  getProgramRequisites: async (programId: number, retryAuth: boolean = false) => {
    try {
      // Asegurar que tenemos un token válido
      const token = await AsyncStorage.getItem('auth_token');
      
      // Si no hay token y se requiere autenticación, manejamos el error
      if (!token) {
        console.warn('No authentication token available for fetching program requisites');
        return [];
      }
      
      const response = await apiClient.get(`/programs/${programId}/requisites`, {
        headers: {
          'Authorization': `Bearer ${token}`
        }
      });
      
      return response.data;
    } catch (error: any) {
      // Si es error de autenticación y podemos reintentar
      if (error?.response?.status === 401 && retryAuth) {
        // Aquí podríamos implementar un refresh del token si fuera necesario
        console.warn('Authentication failed when fetching requisites');
      }
      console.error('Error fetching program requisites:', error);
      return [];
    }
  },
  
  /**
   * Get application requisites for a user
   */
  getApplicationRequisites: async (applicationId: number) => {
    const response = await apiClient.get(`/applications/${applicationId}/requisites`);
    return response.data;
  },
  
  /**
   * Complete a requisite
   */
  completeRequisite: async (requisiteId: number, data: any) => {
    const response = await apiClient.post(`/requisites/${requisiteId}/complete`, data);
    return response.data;
  },
  
  /**
   * Get application progress
   */
  getApplicationProgress: async (applicationId: number) => {
    const response = await apiClient.get(`/applications/${applicationId}/progress`);
    return response.data;
  },
  
  /**
   * Upload a document for an application
   */
  uploadDocument: async (applicationId: number, documentData: any) => {
    const response = await apiClient.post('/application-documents', {
      application_id: applicationId,
      ...documentData
    });
    return response.data;
  }
};

export default programService;
