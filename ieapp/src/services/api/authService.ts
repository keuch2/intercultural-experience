import apiClient from './apiClient';
import AsyncStorage from '@react-native-async-storage/async-storage';

export interface LoginCredentials {
  email: string;
  password: string;
}

export interface RegisterData {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
}

export interface AuthResponse {
  status: string;
  message?: string;
  user: any;
  token: string;
  errors?: Record<string, string[]>;
}

const authService = {
  /**
   * Login user with email and password
   */
  login: async (credentials: LoginCredentials) => {
    const response = await apiClient.post<AuthResponse>('/login', credentials);
    
    // Check for successful response based on status field
    if (response.data.status === 'success' && response.data.token) {
      await AsyncStorage.setItem('auth_token', response.data.token);
    } else if (response.data.status === 'error') {
      // Handle error response
      throw new Error(response.data.message || 'Authentication failed');
    }
    
    return response.data;
  },

  /**
   * Register a new user
   */
  register: async (userData: RegisterData) => {
    console.log('Sending registration data:', userData);
    
    try {
      const response = await apiClient.post<AuthResponse>('/register', userData);
      console.log('Registration response:', response.data);
      
      // Check for successful response based on status field
      if (response.data.status === 'success' && response.data.token) {
        await AsyncStorage.setItem('auth_token', response.data.token);
      } else if (response.data.status === 'error') {
        // Handle error response
        const error = new Error(response.data.message || 'Registration failed');
        (error as any).errors = response.data.errors;
        throw error;
      }
      
      return response.data;
    } catch (error: any) {
      console.error('Registration service error:', error);
      // Re-throw the error so it can be handled by the calling code
      throw error;
    }
  },

  /**
   * Logout the current user
   */
  logout: async () => {
    try {
      await apiClient.post('/logout');
    } finally {
      await AsyncStorage.removeItem('auth_token');
    }
  },

  /**
   * Get the current authenticated user
   */
  getCurrentUser: async () => {
    const response = await apiClient.get('/me');
    // Handle new response format - user data is nested under 'user' property
    return response.data.status === 'success' ? response.data.user : null;
  },

  /**
   * Check if user is authenticated
   */
  isAuthenticated: async () => {
    const token = await AsyncStorage.getItem('auth_token');
    return !!token;
  }
};

export default authService;
