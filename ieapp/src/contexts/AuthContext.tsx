import React, { createContext, useState, useEffect, useContext, ReactNode } from 'react';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { authService } from '../services/api';
import { handleApiError, logError, retryWithBackoff, AppError } from '../utils/ErrorUtils';

interface AuthResult {
  success: boolean;
  message?: string;
  errors?: any;
  appError?: AppError;
}

interface AuthContextType {
  user: any | null;
  isLoading: boolean;
  isAuthenticated: boolean;
  error: AppError | null;
  login: (email: string, password: string) => Promise<AuthResult>;
  register: (name: string, email: string, password: string, passwordConfirmation: string) => Promise<AuthResult>;
  logout: () => Promise<void>;
  clearError: () => void;
  refreshUser: () => Promise<void>;
  updateUserData: (userData: any) => void;
  retryAuth: () => Promise<void>;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export const AuthProvider: React.FC<{ children: ReactNode }> = ({ children }) => {
  const [user, setUser] = useState<any | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [isAuthenticated, setIsAuthenticated] = useState(false);
  const [error, setError] = useState<AppError | null>(null);
  const [retryCount, setRetryCount] = useState(0);

  // Check if user is authenticated on app startup
  useEffect(() => {
    const checkAuthStatus = async () => {
      try {
        setIsLoading(true);
        const isAuth = await retryWithBackoff(() => authService.isAuthenticated(), 2, 1000);
        
        if (isAuth) {
          // Token is valid, get user data
          const userData = await retryWithBackoff(() => authService.getCurrentUser(), 2, 1000);
          setUser(userData);
          setIsAuthenticated(true);
          setError(null);
        } else {
          // Token is invalid or missing
          setUser(null);
          setIsAuthenticated(false);
        }
      } catch (err) {
        const appError = handleApiError(err, 'AUTH_CHECK');
        logError(appError);
        
        // Only set error for non-auth related issues
        if (appError.code !== 'AUTH_ERROR') {
          setError(appError);
        }
        
        // Clear any stored tokens and reset state
        await authService.clearStoredToken();
        setUser(null);
        setIsAuthenticated(false);
      } finally {
        setIsLoading(false);
      }
    };

    checkAuthStatus();
  }, [retryCount]);

  const login = async (email: string, password: string): Promise<AuthResult> => {
    setIsLoading(true);
    setError(null);
    
    try {
      const response = await retryWithBackoff(
        () => authService.login({ email, password }),
        1, // No retry for auth attempts to prevent account lockout
        0
      );
      
      setUser(response.user);
      setIsAuthenticated(true);
      return { success: true };
    } catch (err: any) {
      const appError = handleApiError(err, 'LOGIN');
      logError(appError);
      setError(appError);
      
      return { 
        success: false, 
        message: appError.message,
        errors: appError.details?.errors,
        appError
      };
    } finally {
      setIsLoading(false);
    }
  };

  const register = async (
    name: string, 
    email: string, 
    password: string, 
    passwordConfirmation: string
  ): Promise<AuthResult> => {
    setIsLoading(true);
    setError(null);
    
    try {
      const response = await retryWithBackoff(
        () => authService.register({ 
          name, 
          email, 
          password, 
          password_confirmation: passwordConfirmation 
        }),
        1, // No retry for registration to prevent duplicate accounts
        0
      );
      
      setUser(response.user);
      setIsAuthenticated(true);
      return { success: true };
    } catch (err: any) {
      const appError = handleApiError(err, 'REGISTRATION');
      logError(appError);
      setError(appError);
      
      return { 
        success: false, 
        message: appError.message,
        errors: appError.details?.errors,
        appError
      };
    } finally {
      setIsLoading(false);
    }
  };

  const logout = async () => {
    setIsLoading(true);
    setError(null);
    
    try {
      // Try to logout from server, but don't fail if it doesn't work
      await authService.logout();
    } catch (err: any) {
      // Log error but don't prevent logout
      const appError = handleApiError(err, 'LOGOUT');
      logError(appError);
    } finally {
      // Always clear local state regardless of server response
      setUser(null);
      setIsAuthenticated(false);
      setIsLoading(false);
    }
  };
  
  // Clear any error message
  const clearError = () => {
    setError(null);
  };

  // Retry authentication (useful for network errors)
  const retryAuth = async () => {
    setRetryCount(prev => prev + 1);
  };

  // Función para refrescar los datos del usuario desde el servidor
  const refreshUser = async () => {
    try {
      setIsLoading(true);
      const userData = await retryWithBackoff(() => authService.getCurrentUser(), 3, 1000);
      console.log('Datos del usuario actualizados:', userData);
      setUser(userData);
      setError(null);
      return userData;
    } catch (err) {
      const appError = handleApiError(err, 'USER_REFRESH');
      logError(appError);
      
      // If it's an auth error, clear the session
      if (appError.code === 'AUTH_ERROR') {
        await authService.clearStoredToken();
        setUser(null);
        setIsAuthenticated(false);
      } else {
        setError(appError);
      }
    } finally {
      setIsLoading(false);
    }
  };
  
  // Función para actualizar localmente los datos del usuario sin hacer petición al servidor
  const updateUserData = (userData: any) => {
    console.log('Actualizando datos del usuario localmente:', userData);
    setUser((prevUser: any) => ({
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
        updateUserData,
        retryAuth
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
