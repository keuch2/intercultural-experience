import apiClient from './apiClient';

export interface UserProfile {
  id: number;
  name: string;
  email: string;
  bio?: string;
  avatar?: string;
  phone?: string;
  address?: string;
  created_at: string;
  updated_at: string;
}

export interface ProfileUpdateData {
  name?: string;
  bio?: string;
  phone?: string;
  address?: string;
  avatar?: File;
  birth_date?: string;
  nationality?: string;
  city?: string;
  country?: string;
  academic_level?: string;
  english_level?: string;
}

const profileService = {
  /**
   * Get the user's profile
   */
  getProfile: async () => {
    const response = await apiClient.get<UserProfile>('/profile');
    return response.data;
  },

  /**
   * Update the user's profile
   */
  updateProfile: async (profileData: ProfileUpdateData) => {
    try {
      console.log('Intentando actualizar perfil con:', profileData);
      
      // Si tenemos un archivo para subir, usamos FormData
      if (profileData.avatar) {
        const formData = new FormData();
        
        Object.entries(profileData).forEach(([key, value]) => {
          if (value !== undefined) {
            formData.append(key, value);
          }
        });
        
        // Usar la ruta correcta según la implementación del API
        const response = await apiClient.post('/profile/avatar', formData, {
          headers: {
            'Content-Type': 'multipart/form-data',
          },
        });
        
        return response.data;
      }
      
      // Para actualización regular sin archivos, usamos PUT pero con la ruta correcta
      // Usa la ruta específica que se implementó en el API de Laravel
      const response = await apiClient.put('/profile', profileData);
      console.log('Respuesta de la API:', response.data);
      return response.data;
    } catch (error) {
      console.error('Error detallado en actualización de perfil:', error);
      throw error;
    }
  },

  /**
   * Update user avatar/profile photo
   */
  updateAvatar: async (formData: FormData) => {
    try {
      const response = await apiClient.post('/profile/avatar', formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      });
      return response.data;
    } catch (error) {
      console.error('Error al actualizar avatar:', error);
      throw error;
    }
  },

  /**
   * Change user password
   */
  changePassword: async (passwordData: { current_password: string; password: string; password_confirmation: string }) => {
    const response = await apiClient.put('/password', passwordData);
    return response.data;
  }
};

export default profileService;
