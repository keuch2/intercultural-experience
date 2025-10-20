import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  TextInput,
  TouchableOpacity,
  StyleSheet,
  KeyboardAvoidingView,
  Platform,
  ScrollView,
  Alert,
  ActivityIndicator,
} from 'react-native';
import { useNavigation, useRoute } from '@react-navigation/native';
import authService from '../../services/api/authService';
import PasswordStrengthIndicator from '../../components/PasswordStrengthIndicator';

const ResetPasswordScreen = () => {
  const navigation = useNavigation();
  const route = useRoute();
  const { token } = route.params as { token: string };

  const [validatingToken, setValidatingToken] = useState(true);
  const [tokenValid, setTokenValid] = useState(false);
  const [email, setEmail] = useState('');
  
  const [password, setPassword] = useState('');
  const [passwordConfirmation, setPasswordConfirmation] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [showPasswordConfirmation, setShowPasswordConfirmation] = useState(false);
  
  const [loading, setLoading] = useState(false);
  const [resetSuccess, setResetSuccess] = useState(false);

  // Validar token al cargar
  useEffect(() => {
    validateToken();
  }, []);

  const validateToken = async () => {
    try {
      const response = await authService.validateResetToken(token);
      
      if (response.status === 'success') {
        setTokenValid(true);
        setEmail(response.email);
      } else {
        setTokenValid(false);
        Alert.alert(
          'Token Inválido',
          'El link de recuperación es inválido o ha expirado. Por favor, solicita uno nuevo.',
          [
            {
              text: 'OK',
              onPress: () => navigation.navigate('ForgotPassword' as never)
            }
          ]
        );
      }
    } catch (error) {
      console.error('Token validation error:', error);
      setTokenValid(false);
      Alert.alert(
        'Error',
        'No se pudo validar el link de recuperación. Por favor, intenta nuevamente.',
        [
          {
            text: 'OK',
            onPress: () => navigation.navigate('ForgotPassword' as never)
          }
        ]
      );
    } finally {
      setValidatingToken(false);
    }
  };

  const validatePassword = () => {
    if (password.length < 8) {
      Alert.alert('Error', 'La contraseña debe tener al menos 8 caracteres');
      return false;
    }

    if (!/[A-Z]/.test(password)) {
      Alert.alert('Error', 'La contraseña debe contener al menos una mayúscula');
      return false;
    }

    if (!/[a-z]/.test(password)) {
      Alert.alert('Error', 'La contraseña debe contener al menos una minúscula');
      return false;
    }

    if (!/[0-9]/.test(password)) {
      Alert.alert('Error', 'La contraseña debe contener al menos un número');
      return false;
    }

    if (!/[@$!%*#?&]/.test(password)) {
      Alert.alert('Error', 'La contraseña debe contener al menos un carácter especial (@$!%*#?&)');
      return false;
    }

    if (password !== passwordConfirmation) {
      Alert.alert('Error', 'Las contraseñas no coinciden');
      return false;
    }

    return true;
  };

  const handleResetPassword = async () => {
    if (!validatePassword()) {
      return;
    }

    setLoading(true);

    try {
      const response = await authService.resetPassword(token, password, passwordConfirmation);
      
      if (response.status === 'success') {
        setResetSuccess(true);
      }
    } catch (error: any) {
      console.error('Reset password error:', error);
      
      const errorMessage = error.response?.data?.message || 
        'Ocurrió un error al restablecer la contraseña. Por favor, intenta nuevamente.';
      
      Alert.alert('Error', errorMessage);
    } finally {
      setLoading(false);
    }
  };

  // Pantalla de carga mientras valida token
  if (validatingToken) {
    return (
      <View style={styles.loadingContainer}>
        <ActivityIndicator size="large" color="#2196F3" />
        <Text style={styles.loadingText}>Validando link de recuperación...</Text>
      </View>
    );
  }

  // Token inválido
  if (!tokenValid) {
    return (
      <View style={styles.errorContainer}>
        <Text style={styles.errorIcon}>⚠️</Text>
        <Text style={styles.errorTitle}>Link Inválido</Text>
        <Text style={styles.errorMessage}>
          El link de recuperación es inválido o ha expirado.
        </Text>
        <TouchableOpacity
          style={styles.errorButton}
          onPress={() => navigation.navigate('ForgotPassword' as never)}
        >
          <Text style={styles.errorButtonText}>Solicitar Nuevo Link</Text>
        </TouchableOpacity>
      </View>
    );
  }

  // Éxito
  if (resetSuccess) {
    return (
      <View style={styles.successContainer}>
        <Text style={styles.successIcon}>✓</Text>
        <Text style={styles.successTitle}>¡Contraseña Actualizada!</Text>
        <Text style={styles.successMessage}>
          Tu contraseña ha sido actualizada exitosamente.
        </Text>
        <Text style={styles.successNote}>
          Por seguridad, se cerraron todas tus sesiones activas.
        </Text>
        <TouchableOpacity
          style={styles.loginButton}
          onPress={() => navigation.navigate('Login' as never)}
        >
          <Text style={styles.loginButtonText}>Iniciar Sesión</Text>
        </TouchableOpacity>
      </View>
    );
  }

  // Formulario de reset
  return (
    <KeyboardAvoidingView
      style={styles.container}
      behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
    >
      <ScrollView
        contentContainerStyle={styles.scrollContent}
        keyboardShouldPersistTaps="handled"
      >
        <View style={styles.header}>
          <Text style={styles.title}>Nueva Contraseña</Text>
          <Text style={styles.subtitle}>
            Ingresa tu nueva contraseña para {email}
          </Text>
        </View>

        <View style={styles.form}>
          <View style={styles.inputContainer}>
            <Text style={styles.label}>Nueva Contraseña</Text>
            <View style={styles.passwordInputContainer}>
              <TextInput
                style={styles.passwordInput}
                placeholder="Ingresa tu nueva contraseña"
                value={password}
                onChangeText={setPassword}
                secureTextEntry={!showPassword}
                autoCapitalize="none"
                autoCorrect={false}
              />
              <TouchableOpacity
                style={styles.eyeButton}
                onPress={() => setShowPassword(!showPassword)}
              >
                <Text style={styles.eyeIcon}>{showPassword ? '👁️' : '👁️‍🗨️'}</Text>
              </TouchableOpacity>
            </View>
          </View>

          <PasswordStrengthIndicator password={password} />

          <View style={styles.inputContainer}>
            <Text style={styles.label}>Confirmar Contraseña</Text>
            <View style={styles.passwordInputContainer}>
              <TextInput
                style={styles.passwordInput}
                placeholder="Confirma tu nueva contraseña"
                value={passwordConfirmation}
                onChangeText={setPasswordConfirmation}
                secureTextEntry={!showPasswordConfirmation}
                autoCapitalize="none"
                autoCorrect={false}
              />
              <TouchableOpacity
                style={styles.eyeButton}
                onPress={() => setShowPasswordConfirmation(!showPasswordConfirmation)}
              >
                <Text style={styles.eyeIcon}>
                  {showPasswordConfirmation ? '👁️' : '👁️‍🗨️'}
                </Text>
              </TouchableOpacity>
            </View>
          </View>

          {password && passwordConfirmation && password !== passwordConfirmation && (
            <Text style={styles.errorText}>Las contraseñas no coinciden</Text>
          )}

          <TouchableOpacity
            style={[styles.resetButton, loading && styles.resetButtonDisabled]}
            onPress={handleResetPassword}
            disabled={loading}
          >
            {loading ? (
              <ActivityIndicator color="#FFF" />
            ) : (
              <Text style={styles.resetButtonText}>Restablecer Contraseña</Text>
            )}
          </TouchableOpacity>

          <TouchableOpacity
            style={styles.cancelButton}
            onPress={() => navigation.navigate('Login' as never)}
            disabled={loading}
          >
            <Text style={styles.cancelButtonText}>Cancelar</Text>
          </TouchableOpacity>
        </View>
      </ScrollView>
    </KeyboardAvoidingView>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#F5F5F5',
  },
  scrollContent: {
    flexGrow: 1,
    padding: 24,
    justifyContent: 'center',
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#F5F5F5',
  },
  loadingText: {
    marginTop: 16,
    fontSize: 16,
    color: '#666',
  },
  errorContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: 24,
    backgroundColor: '#F5F5F5',
  },
  errorIcon: {
    fontSize: 64,
    marginBottom: 24,
  },
  errorTitle: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 16,
  },
  errorMessage: {
    fontSize: 16,
    color: '#666',
    textAlign: 'center',
    marginBottom: 32,
    lineHeight: 24,
  },
  errorButton: {
    backgroundColor: '#2196F3',
    borderRadius: 8,
    padding: 16,
    width: '100%',
    alignItems: 'center',
  },
  errorButtonText: {
    color: '#FFF',
    fontSize: 16,
    fontWeight: '600',
  },
  successContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: 24,
    backgroundColor: '#F5F5F5',
  },
  successIcon: {
    fontSize: 64,
    color: '#4CAF50',
    marginBottom: 24,
  },
  successTitle: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 16,
  },
  successMessage: {
    fontSize: 16,
    color: '#666',
    textAlign: 'center',
    marginBottom: 12,
    lineHeight: 24,
  },
  successNote: {
    fontSize: 14,
    color: '#999',
    textAlign: 'center',
    marginBottom: 32,
  },
  loginButton: {
    backgroundColor: '#4CAF50',
    borderRadius: 8,
    padding: 16,
    width: '100%',
    alignItems: 'center',
  },
  loginButtonText: {
    color: '#FFF',
    fontSize: 16,
    fontWeight: '600',
  },
  header: {
    marginBottom: 32,
  },
  title: {
    fontSize: 28,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 12,
  },
  subtitle: {
    fontSize: 16,
    color: '#666',
    lineHeight: 24,
  },
  form: {
    marginBottom: 24,
  },
  inputContainer: {
    marginBottom: 20,
  },
  label: {
    fontSize: 14,
    fontWeight: '600',
    color: '#333',
    marginBottom: 8,
  },
  passwordInputContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#FFF',
    borderWidth: 1,
    borderColor: '#E0E0E0',
    borderRadius: 8,
  },
  passwordInput: {
    flex: 1,
    padding: 16,
    fontSize: 16,
    color: '#333',
  },
  eyeButton: {
    padding: 16,
  },
  eyeIcon: {
    fontSize: 20,
  },
  errorText: {
    color: '#F44336',
    fontSize: 13,
    marginTop: -12,
    marginBottom: 12,
  },
  resetButton: {
    backgroundColor: '#4CAF50',
    borderRadius: 8,
    padding: 16,
    alignItems: 'center',
    marginBottom: 12,
  },
  resetButtonDisabled: {
    backgroundColor: '#B0BEC5',
  },
  resetButtonText: {
    color: '#FFF',
    fontSize: 16,
    fontWeight: '600',
  },
  cancelButton: {
    padding: 16,
    alignItems: 'center',
  },
  cancelButtonText: {
    color: '#666',
    fontSize: 16,
  },
});

export default ResetPasswordScreen;
