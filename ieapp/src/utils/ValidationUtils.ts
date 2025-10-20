export interface ValidationResult {
  isValid: boolean;
  errors: string[];
}

export interface PasswordStrength {
  score: number; // 0-4 (weak to strong)
  feedback: string[];
  requirements: {
    minLength: boolean;
    hasUppercase: boolean;
    hasLowercase: boolean;
    hasNumbers: boolean;
    hasSpecialChar: boolean;
  };
}

/**
 * Validates email format with comprehensive regex
 */
export const validateEmail = (email: string): ValidationResult => {
  const errors: string[] = [];
  
  if (!email) {
    errors.push('El correo electrónico es requerido');
  } else {
    // More comprehensive email validation
    const emailRegex = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/;
    
    if (!emailRegex.test(email)) {
      errors.push('Formato de correo electrónico inválido');
    }
    
    if (email.length > 254) {
      errors.push('El correo electrónico es demasiado largo');
    }
  }
  
  return {
    isValid: errors.length === 0,
    errors
  };
};

/**
 * Validates password strength and provides feedback
 */
export const validatePassword = (password: string): PasswordStrength => {
  const requirements = {
    minLength: password.length >= 8,
    hasUppercase: /[A-Z]/.test(password),
    hasLowercase: /[a-z]/.test(password),
    hasNumbers: /\d/.test(password),
    hasSpecialChar: /[!@#$%^&*(),.?":{}|<>]/.test(password)
  };
  
  const feedback: string[] = [];
  let score = 0;
  
  // Check each requirement and provide feedback
  if (!requirements.minLength) {
    feedback.push('Debe tener al menos 8 caracteres');
  } else {
    score++;
  }
  
  if (!requirements.hasUppercase) {
    feedback.push('Debe incluir al menos una letra mayúscula');
  } else {
    score++;
  }
  
  if (!requirements.hasLowercase) {
    feedback.push('Debe incluir al menos una letra minúscula');
  } else {
    score++;
  }
  
  if (!requirements.hasNumbers) {
    feedback.push('Debe incluir al menos un número');
  } else {
    score++;
  }
  
  if (!requirements.hasSpecialChar) {
    feedback.push('Debe incluir al menos un carácter especial (!@#$%^&*etc.)');
  } else {
    score++;
  }
  
  // Additional checks for common weak patterns
  if (password.length > 0) {
    const commonPatterns = [
      /(.)\1{2,}/, // Repeated characters (aaa, 111)
      /123456|654321|abcdef|qwerty/i, // Sequential patterns
      /password|123456|qwerty|admin/i // Common passwords
    ];
    
    commonPatterns.forEach(pattern => {
      if (pattern.test(password)) {
        feedback.push('Evita patrones comunes o secuencias');
        score = Math.max(0, score - 1);
      }
    });
  }
  
  return {
    score: Math.min(4, Math.max(0, score)),
    feedback,
    requirements
  };
};

/**
 * Validates name field
 */
export const validateName = (name: string): ValidationResult => {
  const errors: string[] = [];
  
  if (!name || name.trim().length === 0) {
    errors.push('El nombre es requerido');
  } else {
    const trimmedName = name.trim();
    
    if (trimmedName.length < 2) {
      errors.push('El nombre debe tener al menos 2 caracteres');
    }
    
    if (trimmedName.length > 100) {
      errors.push('El nombre es demasiado largo');
    }
    
    // Check for valid characters (letters, spaces, hyphens, apostrophes)
    const nameRegex = /^[a-zA-ZÀ-ÿĀ-žĀ-Žḁ-ỳ\s\-'\.]+$/;
    if (!nameRegex.test(trimmedName)) {
      errors.push('El nombre contiene caracteres no válidos');
    }
    
    // Check for minimum word count (at least first name)
    const words = trimmedName.split(/\s+/).filter(word => word.length > 0);
    if (words.length < 1) {
      errors.push('Ingresa al menos tu nombre');
    }
  }
  
  return {
    isValid: errors.length === 0,
    errors
  };
};

/**
 * Validates password confirmation
 */
export const validatePasswordConfirmation = (password: string, confirmation: string): ValidationResult => {
  const errors: string[] = [];
  
  if (!confirmation) {
    errors.push('Confirma tu contraseña');
  } else if (password !== confirmation) {
    errors.push('Las contraseñas no coinciden');
  }
  
  return {
    isValid: errors.length === 0,
    errors
  };
};

/**
 * Validates phone number (optional field)
 */
export const validatePhone = (phone: string): ValidationResult => {
  const errors: string[] = [];
  
  // Phone is optional, so empty is valid
  if (phone && phone.trim().length > 0) {
    const cleanPhone = phone.replace(/[\s\-\(\)\+]/g, '');
    
    // Should be between 7-15 digits for international compatibility
    if (cleanPhone.length < 7 || cleanPhone.length > 15) {
      errors.push('Número de teléfono inválido');
    }
    
    // Should contain only digits after cleaning
    if (!/^\d+$/.test(cleanPhone)) {
      errors.push('El teléfono debe contener solo números');
    }
  }
  
  return {
    isValid: errors.length === 0,
    errors
  };
};

/**
 * Gets password strength color for UI
 */
export const getPasswordStrengthColor = (score: number): string => {
  switch (score) {
    case 0:
    case 1:
      return '#E52224'; // Red
    case 2:
      return '#F8B400'; // Orange
    case 3:
      return '#FFC107'; // Yellow
    case 4:
      return '#4CAF50'; // Green
    default:
      return '#CCCCCC'; // Gray
  }
};

/**
 * Gets password strength text for UI
 */
export const getPasswordStrengthText = (score: number): string => {
  switch (score) {
    case 0:
      return 'Muy débil';
    case 1:
      return 'Débil';
    case 2:
      return 'Regular';
    case 3:
      return 'Buena';
    case 4:
      return 'Excelente';
    default:
      return '';
  }
};
