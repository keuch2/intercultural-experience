import React, { createContext, useState, useEffect, useContext, ReactNode } from 'react';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { authService } from '../services/api';

interface AuthContextType {
  user: any | null;
  isLoading: boolean;
  isAuthenticated: boolean;
  error: string | null;
  login: (email: string, password: string) => Promise<{success: boolean; message?: string; errors?: any}>;
  register: (name: string, email: string, password: string, passwordConfirmation: string) => Promise<{success: boolean; message?: string; errors?: any}>;
  logout: () => Promise<void>;
  clearError: () => void;
  refreshUser: () => Promise<void>;
  updateUserData: (userData: any) => void;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export const AuthProvider: React.FC<{ children: ReactNode }> = ({ children }) => {
  const [user, setUser] = useState<any | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [isAuthenticated, setIsAuthenticated] = useState(false);
  const [error, setError] = useState<string | null>(null);

  // Check if user is authenticated on app startup
  useEffect(() => {
    const checkAuthStatus = async () => {
      try {
        const token = await AsyncStorage.getItem('auth_token');
        
        if (token) {
          // Token exists, try to get user data
          const userData = await authService.getCurrentUser();
          setUser(userData);
          setIsAuthenticated(true);
        }
      } catch (error) {
        // If there's an error (like invalid token), clear auth state
        await AsyncStorage.removeItem('auth_token');
        setUser(null);
        setIsAuthenticated(false);
      } finally {
        setIsLoading(false);
      }
    };

    checkAuthStatus();
  }, []);

  const login = async (email: string, password: string) => {
    setIsLoading(true);
    setError(null);
    try {
      const response = await authService.login({ email, password });
      setUser(response.user);
      setIsAuthenticated(true);
      return { success: true };
    } catch (err: any) {
      const errorMessage = err.message || 'Error al iniciar sesión';
      setError(errorMessage);
      return { 
        success: false, 
        message: errorMessage,
        errors: err.errors
      };
    } finally {
      setIsLoading(false);
    }
  };

  const register = async (name: string, email: string, password: string, passwordConfirmation: string) => {
    setIsLoading(true);
    setError(null);
    try {
      const response = await authService.register({ 
        name, 
        email, 
        password, 
        password_confirmation: passwordConfirmation 
      });
      setUser(response.user);
      setIsAuthenticated(true);
      return { success: true };
    } catch (err: any) {
      console.error('Auth context registration error:', err);
      
      const errorMessage = err.message || 'Error al registrarse';
      setError(errorMessage);
      
      // Check if it's a validation error (422)
      let errors = null;
      if (err.response && err.response.status === 422 && err.response.data && err.response.data.errors) {
        errors = err.response.data.errors;
      } else if (err.errors) {
        errors = err.errors;
      }
      
      return { 
        success: false, 
        message: errorMessage,
        errors: errors
      };
    } finally {
      setIsLoading(false);
    }
  };

  const logout = async () => {
    setIsLoading(true);
    setError(null);
    try {
      await authService.logout();
      setUser(null);
      setIsAuthenticated(false);
    } catch (err: any) {
      setError(err.message || 'Error al cerrar sesión');
    } finally {
      setIsLoading(false);
    }
  };
  
  // Clear any error message
  const clearError = () => {
    setError(null);
  };

  // Función para refrescar los datos del usuario desde el servidor
  const refreshUser = async () => {
    try {
      setIsLoading(true);
      const userData = await authService.getCurrentUser();
      console.log('Datos del usuario actualizados:', userData);
      setUser(userData);
      return userData;
    } catch (error) {
      console.error('Error al refrescar datos del usuario:', error);
      // No cambiamos el estado del usuario en caso de error
    } finally {
      setIsLoading(false);
    }
  };
  
  // Función para actualizar localmente los datos del usuario sin hacer petición al servidor
  const updateUserData = (userData: any) => {
    console.log('Actualizando datos del usuario localmente:', userData);
    setUser(prevUser => ({
      ...prevUser,
      ...userData
    }));
  };

  return (
    <AuthContext.Provider
      value={{
        user,
        isLoading,
        isAuthenticated,
        error,
        login,
        register,
        logout,
        clearError,
        refreshUser,
        updateUserData
      }}
    >
      {children}
    </AuthContext.Provider>
  );
};

export const useAuth = (): AuthContextType => {
  const context = useContext(AuthContext);
  if (context === undefined) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
};
