import React, { useState, useCallback, useMemo } from 'react';
import { View, Text, TextInput, TouchableOpacity, StyleSheet, ScrollView, ActivityIndicator, Alert, Image } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useAuth } from '../../contexts/AuthContext';
import { openLegalUrl } from '../../utils/legalLinks';
import InputField from '../../components/InputField';
import DropdownField from '../../components/DropdownField';
import { allCountries } from '../../data/countries';
import { useNavigation } from '@react-navigation/native';
import { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { RootStackParamList } from '../../navigation/AppNavigator';
import {
  validateEmail,
  validatePassword,
  validateName,
  validatePasswordConfirmation,
  validatePhone,
  getPasswordStrengthColor,
  getPasswordStrengthText,
  PasswordStrength
} from '../../utils/ValidationUtils';

const RegisterScreen: React.FC = () => {
  const navigation = useNavigation<NativeStackNavigationProp<RootStackParamList>>();
  const { register, isLoading } = useAuth();
  
  // Form state
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [passwordConfirmation, setPasswordConfirmation] = useState('');
  const [city, setCity] = useState('');
  const [country, setCountry] = useState('');
  const [academicLevel, setAcademicLevel] = useState('');
  const [englishLevel, setEnglishLevel] = useState('');
  // Términos y Política de Privacidad: se aceptan una sola vez, acá (no en cada postulación).
  const [acceptTerms, setAcceptTerms] = useState(false);
  
  // Error state
  const [errors, setErrors] = useState<{[key: string]: string[]}>({});
  const [passwordStrength, setPasswordStrength] = useState<PasswordStrength | null>(null);
  const [isValidating, setIsValidating] = useState(false);
  
  // Dropdown options
  const academicLevelOptions = [
    { label: 'Bachiller', value: 'bachiller' },
    { label: 'Licenciatura', value: 'licenciatura' },
    { label: 'Maestría', value: 'maestria' },
    { label: 'Posgrado', value: 'posgrado' },
    { label: 'Doctorado', value: 'doctorado' }
  ];
  
  const englishLevelOptions = [
    { label: 'Básico', value: 'basico' },
    { label: 'Intermedio', value: 'intermedio' },
    { label: 'Avanzado', value: 'avanzado' },
    { label: 'Nativo', value: 'nativo' }
  ];
  
  const validateForm = useCallback(() => {
    setIsValidating(true);
    const newErrors: {[key: string]: string[]} = {};
    
    // Validate name
    const nameValidation = validateName(name);
    if (!nameValidation.isValid) {
      newErrors.name = nameValidation.errors;
    }
    
    // Validate email
    const emailValidation = validateEmail(email);
    if (!emailValidation.isValid) {
      newErrors.email = emailValidation.errors;
    }
    
    // Validate password
    const passwordValidation = validatePassword(password);
    if (passwordValidation.score < 2) { // Require at least 'Regular' strength
      newErrors.password = passwordValidation.feedback;
    }
    
    // Validate password confirmation
    const confirmationValidation = validatePasswordConfirmation(password, passwordConfirmation);
    if (!confirmationValidation.isValid) {
      newErrors.passwordConfirmation = confirmationValidation.errors;
    }
    
    // Validate optional fields if filled
    if (city && city.trim().length > 0 && city.trim().length < 2) {
      newErrors.city = ['La ciudad debe tener al menos 2 caracteres'];
    }
    
    if (!acceptTerms) {
      newErrors.accept_terms = ['Debés aceptar los Términos y Condiciones y la Política de Privacidad.'];
    }
    
    setErrors(newErrors);
    setIsValidating(false);
    return Object.keys(newErrors).length === 0;
  }, [name, email, password, passwordConfirmation, city, acceptTerms]);
  
  const handleRegister = async () => {
    if (!validateForm()) {
      if (!acceptTerms) {
        Alert.alert('Términos y Condiciones', 'Para crear tu cuenta debés aceptar los Términos y Condiciones y la Política de Privacidad.');
      }
      return;
    }
    
    console.log('Attempting registration with:', { name, email, password: '***', passwordConfirmation: '***' });
    
    // Solo se envían los campos opcionales que el usuario completó.
    const profile = Object.fromEntries(
      Object.entries({
        city: city.trim(),
        // Se guarda el nombre del país ("Paraguay"), igual que en Mi Perfil y el panel.
        country: allCountries.find((c) => c.value === country)?.label ?? country,
        academic_level: academicLevel,
        english_level: englishLevel,
      }).filter(([, value]) => value !== '')
    );
    const payload = { ...profile, accept_terms: true };

    const result = await register(name, email, password, passwordConfirmation, payload);
    
    if (result.success) {
      Alert.alert('Registro exitoso', 'Tu cuenta ha sido creada correctamente');
      // Navigation will happen automatically through AuthContext
    } else {
      console.error('Registration failed:', result);
      
      if (result.errors) {
        // Validation errors
        const formattedErrors: {[key: string]: string[]} = {};
        
        Object.keys(result.errors).forEach(key => {
          const errorArray = result.errors[key];
          formattedErrors[key] = Array.isArray(errorArray) ? errorArray : [errorArray];
        });
        
        setErrors(formattedErrors);
        const firstError = Object.values(formattedErrors)[0];
        Alert.alert('Error de validación', Array.isArray(firstError) ? firstError[0] : firstError);
      } else {
        // General error
        Alert.alert('Error', result.message || 'Ha ocurrido un error al registrarte. Inténtalo de nuevo.');
      }
    }
  };

  // Real-time password validation
  const handlePasswordChange = useCallback((newPassword: string) => {
    setPassword(newPassword);
    const strength = validatePassword(newPassword);
    setPasswordStrength(strength);
    
    // Clear password errors when user starts typing
    if (errors.password) {
      setErrors(prev => {
        const newErrors = { ...prev };
        delete newErrors.password;
        return newErrors;
      });
    }
  }, [errors.password]);

  // Real-time field validation
  const handleFieldChange = useCallback((field: string, value: string) => {
    // Clear errors when user starts typing
    if (errors[field]) {
      setErrors(prev => {
        const newErrors = { ...prev };
        delete newErrors[field];
        return newErrors;
      });
    }
    
    switch (field) {
      case 'name':
        setName(value);
        break;
      case 'email':
        setEmail(value);
        break;
      case 'passwordConfirmation':
        setPasswordConfirmation(value);
        break;
      case 'city':
        setCity(value);
        break;
    }
  }, [errors]);

  // Format error messages for display
  const getErrorMessage = (field: string): string | undefined => {
    const fieldErrors = errors[field];
    return fieldErrors && fieldErrors.length > 0 ? fieldErrors[0] : undefined;
  };
  return (
    <ScrollView contentContainerStyle={styles.container}>
      <View style={styles.headerContainer}>
        <Image 
          source={require('../../../assets/images/ie-logo-blk.png')} 
          style={styles.logo} 
          resizeMode="contain"
        />
        <Text style={styles.title}>Crea tu cuenta IE</Text>
        <Text style={styles.subtitle}>Completa tus datos para comenzar tu experiencia intercultural</Text>
      </View>
      
      <InputField
        label="Nombre completo"
        value={name}
        onChangeText={(value) => handleFieldChange('name', value)}
        placeholder="Ingresa tu nombre completo"
        error={getErrorMessage('name')}
        autoCapitalize="words"
      />
      
      <InputField
        label="Correo electrónico"
        value={email}
        onChangeText={(value) => handleFieldChange('email', value)}
        placeholder="ejemplo@correo.com"
        keyboardType="email-address"
        autoCapitalize="none"
        error={getErrorMessage('email')}
      />
      
      <View>
        <InputField
          label="Contraseña"
          value={password}
          onChangeText={handlePasswordChange}
          placeholder="Mínimo 8 caracteres"
          secureTextEntry
          error={getErrorMessage('password')}
        />
        {passwordStrength && password.length > 0 && (
          <View style={styles.passwordStrengthContainer}>
            <View style={styles.strengthBar}>
              <View 
                style={[
                  styles.strengthFill, 
                  { 
                    width: `${(passwordStrength.score / 4) * 100}%`,
                    backgroundColor: getPasswordStrengthColor(passwordStrength.score)
                  }
                ]} 
              />
            </View>
            <Text style={[
              styles.strengthText,
              { color: getPasswordStrengthColor(passwordStrength.score) }
            ]}>
              {getPasswordStrengthText(passwordStrength.score)}
            </Text>
          </View>
        )}
      </View>
      
      <InputField
        label="Confirmar contraseña"
        value={passwordConfirmation}
        onChangeText={(value) => handleFieldChange('passwordConfirmation', value)}
        placeholder="Repite tu contraseña"
        secureTextEntry
        error={getErrorMessage('passwordConfirmation')}
      />
      
      <InputField
        label="Ciudad (opcional)"
        value={city}
        onChangeText={(value) => handleFieldChange('city', value)}
        placeholder="Tu ciudad"
        error={getErrorMessage('city')}
        autoCapitalize="words"
      />
      
      <DropdownField
        label="País (opcional)"
        options={allCountries}
        value={country}
        onSelect={setCountry}
        placeholder="Selecciona tu país"
        error={getErrorMessage('country')}
        searchable={true}
        maxHeight={250}
      />
      
      <DropdownField
        label="Nivel Académico (opcional)"
        options={academicLevelOptions}
        value={academicLevel}
        onSelect={setAcademicLevel}
        placeholder="Selecciona tu nivel académico"
        error={getErrorMessage('academic_level')}
      />
      
      <DropdownField
        label="Nivel de Inglés (opcional)"
        options={englishLevelOptions}
        value={englishLevel}
        onSelect={setEnglishLevel}
        placeholder="Selecciona tu nivel de inglés"
        error={getErrorMessage('english_level')}
      />
      
      <View style={styles.termsRow}>
        <TouchableOpacity
          onPress={() => setAcceptTerms((v) => !v)}
          accessibilityRole="checkbox"
          accessibilityState={{ checked: acceptTerms }}
          hitSlop={8}
        >
          <Ionicons
            name={acceptTerms ? 'checkbox' : 'square-outline'}
            size={24}
            color={getErrorMessage('accept_terms') && !acceptTerms ? '#E52224' : '#6C4AA0'}
          />
        </TouchableOpacity>
        <Text style={styles.termsText}>
          <Text onPress={() => setAcceptTerms((v) => !v)}>Leí y acepto los </Text>
          <Text style={styles.termsLink} onPress={() => openLegalUrl('terms')}>Términos y Condiciones</Text>
          <Text onPress={() => setAcceptTerms((v) => !v)}> y la </Text>
          <Text style={styles.termsLink} onPress={() => openLegalUrl('privacy')}>Política de Privacidad</Text>
          <Text onPress={() => setAcceptTerms((v) => !v)}> de IE.</Text>
        </Text>
      </View>
      {getErrorMessage('accept_terms') && !acceptTerms ? (
        <Text style={styles.termsError}>{getErrorMessage('accept_terms')}</Text>
      ) : null}

      <TouchableOpacity 
        style={styles.button} 
        onPress={handleRegister}
        disabled={isLoading || isValidating}
      >
        {isLoading || isValidating ? (
          <View style={styles.loadingContainer}>
            <ActivityIndicator color="#FFFFFF" />
            <Text style={styles.loadingText}>
              {isValidating ? 'Validando...' : 'Registrando...'}
            </Text>
          </View>
        ) : (
          <Text style={styles.buttonText}>REGISTRARSE</Text>
        )}
      </TouchableOpacity>
      
      <TouchableOpacity onPress={() => navigation.navigate('Login')}>
        <Text style={styles.link}>¿Ya tienes cuenta? Inicia sesión</Text>
      </TouchableOpacity>
    </ScrollView>
  );
};

const styles = StyleSheet.create({
  container: { 
    flexGrow: 1,
    padding: 20, 
    backgroundColor: '#fff',
    width: '100%'
  },
  headerContainer: {
    alignItems: 'center',
    marginBottom: 30,
    width: '100%'
  },
  logo: { 
    width: 120,
    height: 120,
    marginBottom: 20
  },
  title: { 
    fontSize: 28, 
    fontWeight: 'bold', 
    marginBottom: 10,
    color: '#333333'
  },
  subtitle: {
    fontSize: 16,
    color: '#666666',
    textAlign: 'center',
    marginBottom: 20
  },
  button: { 
    backgroundColor: '#E52224', 
    borderRadius: 10, 
    padding: 15, 
    width: '100%', 
    alignItems: 'center', 
    marginVertical: 20 
  },
  buttonText: { 
    color: '#fff', 
    fontWeight: 'bold', 
    fontSize: 16 
  },
  link: { 
    color: '#6C4AA0', 
    textDecorationLine: 'underline', 
    marginTop: 15, 
    marginBottom: 30,
    fontWeight: 'bold' 
  },
  passwordStrengthContainer: {
    marginTop: 8,
    marginBottom: 8,
  },
  strengthBar: {
    height: 4,
    backgroundColor: '#E0E0E0',
    borderRadius: 2,
    overflow: 'hidden',
    marginBottom: 4,
  },
  strengthFill: {
    height: '100%',
    borderRadius: 2,
  },
  strengthText: {
    fontSize: 12,
    fontWeight: '500',
  },
  termsRow: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    gap: 10,
    marginTop: 16,
    width: '100%',
  },
  termsText: {
    flex: 1,
    color: '#444',
    fontSize: 14,
    lineHeight: 21,
  },
  termsLink: {
    color: '#6C4AA0',
    fontWeight: 'bold',
    textDecorationLine: 'underline',
  },
  termsError: {
    color: '#E52224',
    fontSize: 12,
    marginTop: 6,
    width: '100%',
  },
  loadingContainer: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  loadingText: {
    color: '#FFFFFF',
    marginLeft: 8,
    fontSize: 16,
    fontWeight: 'bold',
  },
});

export default RegisterScreen; 