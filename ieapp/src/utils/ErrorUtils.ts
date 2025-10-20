import { Alert } from 'react-native';

export interface AppError {
  code: string;
  message: string;
  details?: any;
  timestamp: Date;
  context?: string;
}

export interface ApiErrorResponse {
  message: string;
  errors?: Record<string, string[]>;
  status?: number;
}

/**
 * Creates a standardized error object
 */
export const createAppError = (
  code: string,
  message: string,
  details?: any,
  context?: string
): AppError => ({
  code,
  message,
  details,
  timestamp: new Date(),
  context
});

/**
 * Handles API errors and converts them to user-friendly messages
 */
export const handleApiError = (error: any, context?: string): AppError => {
  // Network errors
  if (error.code === 'NETWORK_ERROR' || error.message === 'Network Error') {
    return createAppError(
      'NETWORK_ERROR',
      'Sin conexión a internet. Verifica tu conexión y vuelve a intentar.',
      error,
      context
    );
  }

  // Timeout errors
  if (error.code === 'ECONNABORTED' || error.message?.includes('timeout')) {
    return createAppError(
      'TIMEOUT_ERROR',
      'La solicitud tardó demasiado. Por favor intenta nuevamente.',
      error,
      context
    );
  }

  // Server errors (5xx)
  if (error.response?.status >= 500) {
    return createAppError(
      'SERVER_ERROR',
      'Error del servidor. Nuestro equipo ha sido notificado.',
      error.response?.data,
      context
    );
  }

  // Authentication errors (401)
  if (error.response?.status === 401) {
    return createAppError(
      'AUTH_ERROR',
      'Tu sesión ha expirado. Por favor inicia sesión nuevamente.',
      error.response?.data,
      context
    );
  }

  // Authorization errors (403)
  if (error.response?.status === 403) {
    return createAppError(
      'PERMISSION_ERROR',
      'No tienes permisos para realizar esta acción.',
      error.response?.data,
      context
    );
  }

  // Validation errors (422)
  if (error.response?.status === 422) {
    const data = error.response.data;
    return createAppError(
      'VALIDATION_ERROR',
      data?.message || 'Datos inválidos. Por favor revisa la información.',
      data?.errors,
      context
    );
  }

  // Not found errors (404)
  if (error.response?.status === 404) {
    return createAppError(
      'NOT_FOUND_ERROR',
      'El recurso solicitado no existe.',
      error.response?.data,
      context
    );
  }

  // Rate limiting (429)
  if (error.response?.status === 429) {
    return createAppError(
      'RATE_LIMIT_ERROR',
      'Demasiadas solicitudes. Por favor espera un momento e intenta nuevamente.',
      error.response?.data,
      context
    );
  }

  // Generic API error
  if (error.response?.data?.message) {
    return createAppError(
      'API_ERROR',
      error.response.data.message,
      error.response?.data,
      context
    );
  }

  // Unknown error
  return createAppError(
    'UNKNOWN_ERROR',
    'Ha ocurrido un error inesperado. Por favor intenta nuevamente.',
    error,
    context
  );
};

/**
 * Shows user-friendly error alerts
 */
export const showErrorAlert = (error: AppError, options?: {
  title?: string;
  showRetry?: boolean;
  onRetry?: () => void;
  onCancel?: () => void;
}) => {
  const {
    title = 'Error',
    showRetry = false,
    onRetry,
    onCancel
  } = options || {};

  const buttons = [];

  if (showRetry && onRetry) {
    buttons.push({
      text: 'Reintentar',
      onPress: onRetry,
      style: 'default' as const
    });
  }

  buttons.push({
    text: 'OK',
    onPress: onCancel,
    style: 'cancel' as const
  });

  Alert.alert(title, error.message, buttons);
};

/**
 * Logs errors safely (without sensitive information)
 */
export const logError = (error: AppError) => {
  if (__DEV__) {
    console.error('App Error:', {
      code: error.code,
      message: error.message,
      timestamp: error.timestamp,
      context: error.context,
      // Only include details in development and sanitize them
      details: sanitizeErrorDetails(error.details)
    });
  }
  
  // In production, you could send this to your error reporting service
  // Example: analytics.trackError(error);
};

/**
 * Sanitizes error details to remove sensitive information
 */
const sanitizeErrorDetails = (details: any): any => {
  if (!details) return details;
  
  const sensitive_fields = [
    'password',
    'token',
    'authorization',
    'auth',
    'secret',
    'key',
    'bank_info',
    'credit_card',
    'ssn',
    'social_security'
  ];

  if (typeof details === 'object') {
    const sanitized = { ...details };
    
    // Remove sensitive fields
    Object.keys(sanitized).forEach(key => {
      if (sensitive_fields.some(field => 
        key.toLowerCase().includes(field.toLowerCase())
      )) {
        sanitized[key] = '[REDACTED]';
      }
    });
    
    return sanitized;
  }
  
  return details;
};

/**
 * Retry utility with exponential backoff
 */
export const retryWithBackoff = async <T>(
  fn: () => Promise<T>,
  maxAttempts: number = 3,
  baseDelay: number = 1000
): Promise<T> => {
  let lastError: unknown;
  
  for (let attempt = 1; attempt <= maxAttempts; attempt++) {
    try {
      return await fn();
    } catch (error: unknown) {
      lastError = error;
      
      // Don't retry on certain types of errors
      const errorWithResponse = error as any;
      if (errorWithResponse?.response?.status === 401 || 
          errorWithResponse?.response?.status === 403 || 
          errorWithResponse?.response?.status === 422) {
        throw error;
      }
      
      // If this was the last attempt, throw the error
      if (attempt === maxAttempts) {
        throw error;
      }
      
      // Wait before retrying with exponential backoff
      const delay = baseDelay * Math.pow(2, attempt - 1);
      await new Promise(resolve => setTimeout(resolve, delay));
    }
  }
  
  throw lastError;
};

/**
 * Creates a safe async handler that catches and processes errors
 */
export const createSafeAsyncHandler = <T extends any[]>(
  handler: (...args: T) => Promise<void>,
  options?: {
    context?: string;
    showAlert?: boolean;
    onError?: (error: AppError) => void;
  }
) => {
  return async (...args: T) => {
    try {
      await handler(...args);
    } catch (error: unknown) {
      const appError = handleApiError(error, options?.context);
      logError(appError);
      
      if (options?.onError) {
        options.onError(appError);
      } else if (options?.showAlert !== false) {
        showErrorAlert(appError);
      }
    }
  };
};
