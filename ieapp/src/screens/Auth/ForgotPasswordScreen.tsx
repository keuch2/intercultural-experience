import React, { useState } from 'react';
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
import { useNavigation } from '@react-navigation/native';
import authService from '../../services/api/authService';

const ForgotPasswordScreen = () => {
  const navigation = useNavigation();
  const [email, setEmail] = useState('');
  const [loading, setLoading] = useState(false);
  const [emailSent, setEmailSent] = useState(false);
  const [rateLimited, setRateLimited] = useState(false);

  const validateEmail = (email: string) => {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
  };

  const handleSendResetLink = async () => {
    // Validaciones
    if (!email.trim()) {
      Alert.alert('Error', 'Por favor ingresa tu email');
      return;
    }

    if (!validateEmail(email)) {
      Alert.alert('Error', 'Por favor ingresa un email válido');
      return;
    }

    setLoading(true);
    setRateLimited(false);

    try {
      const response = await authService.forgotPassword(email);
      
      if (response.status === 'success') {
        setEmailSent(true);
      }
    } catch (error: any) {
      console.error('Forgot password error:', error);
      
      // Manejar rate limiting
      if (error.response?.status === 429) {
        setRateLimited(true);
        Alert.alert(
          'Demasiados Intentos',
          'Has excedido el límite de intentos. Por favor, espera 1 hora antes de intentar nuevamente.',
          [{ text: 'OK' }]
        );
      } else {
        Alert.alert(
          'Error',
          error.response?.data?.message || 'Ocurrió un error al enviar el email. Por favor, intenta nuevamente.',
          [{ text: 'OK' }]
        );
      }
    } finally {
      setLoading(false);
    }
  };

  if (emailSent) {
    return (
      <View style={styles.container}>
        <View style={styles.successContainer}>
          <Text style={styles.successIcon}>✓</Text>
          <Text style={styles.successTitle}>Email Enviado</Text>
          <Text style={styles.successMessage}>
            Si el email existe en nuestro sistema, recibirás un link de recuperación.
          </Text>
          <Text style={styles.successNote}>
            Por favor, revisa tu bandeja de entrada y carpeta de spam.
          </Text>
          <Text style={styles.successNote}>
            El link expirará en 60 minutos.
          </Text>

          <TouchableOpacity
            style={styles.backButton}
            onPress={() => navigation.goBack()}
          >
            <Text style={styles.backButtonText}>Volver al Login</Text>
          </TouchableOpacity>

          <TouchableOpacity
            style={styles.resendButton}
            onPress={() => {
              setEmailSent(false);
              setEmail('');
            }}
          >
            <Text style={styles.resendButtonText}>Enviar a otro email</Text>
          </TouchableOpacity>
        </View>
      </View>
    );
  }

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
          <Text style={styles.title}>¿Olvidaste tu Contraseña?</Text>
          <Text style={styles.subtitle}>
            Ingresa tu email y te enviaremos un link para recuperar tu contraseña.
          </Text>
        </View>

        <View style={styles.form}>
          <View style={styles.inputContainer}>
            <Text style={styles.label}>Email</Text>
            <TextInput
              style={styles.input}
              placeholder="tu@email.com"
              value={email}
              onChangeText={setEmail}
              keyboardType="email-address"
              autoCapitalize="none"
              autoCorrect={false}
              editable={!loading && !rateLimited}
            />
          </View>

          {rateLimited && (
            <View style={styles.warningContainer}>
              <Text style={styles.warningText}>
                ⚠️ Has excedido el límite de intentos. Espera 1 hora.
              </Text>
            </View>
          )}

          <TouchableOpacity
            style={[
              styles.sendButton,
              (loading || rateLimited) && styles.sendButtonDisabled
            ]}
            onPress={handleSendResetLink}
            disabled={loading || rateLimited}
          >
            {loading ? (
              <ActivityIndicator color="#FFF" />
            ) : (
              <Text style={styles.sendButtonText}>Enviar Link de Recuperación</Text>
            )}
          </TouchableOpacity>

          <TouchableOpacity
            style={styles.cancelButton}
            onPress={() => navigation.goBack()}
            disabled={loading}
          >
            <Text style={styles.cancelButtonText}>Cancelar</Text>
          </TouchableOpacity>
        </View>

        <View style={styles.infoContainer}>
          <Text style={styles.infoText}>
            💡 El link de recuperación expirará en 60 minutos.
          </Text>
          <Text style={styles.infoText}>
            🔒 Por seguridad, solo puedes solicitar 3 links por hora.
          </Text>
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
  input: {
    backgroundColor: '#FFF',
    borderWidth: 1,
    borderColor: '#E0E0E0',
    borderRadius: 8,
    padding: 16,
    fontSize: 16,
    color: '#333',
  },
  warningContainer: {
    backgroundColor: '#FFF3CD',
    borderWidth: 1,
    borderColor: '#FFC107',
    borderRadius: 8,
    padding: 12,
    marginBottom: 16,
  },
  warningText: {
    color: '#856404',
    fontSize: 14,
    textAlign: 'center',
  },
  sendButton: {
    backgroundColor: '#2196F3',
    borderRadius: 8,
    padding: 16,
    alignItems: 'center',
    marginBottom: 12,
  },
  sendButtonDisabled: {
    backgroundColor: '#B0BEC5',
  },
  sendButtonText: {
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
  infoContainer: {
    backgroundColor: '#E3F2FD',
    borderRadius: 8,
    padding: 16,
    gap: 8,
  },
  infoText: {
    fontSize: 13,
    color: '#1976D2',
    lineHeight: 20,
  },
  successContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: 24,
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
    marginBottom: 8,
  },
  backButton: {
    backgroundColor: '#2196F3',
    borderRadius: 8,
    padding: 16,
    width: '100%',
    alignItems: 'center',
    marginTop: 32,
  },
  backButtonText: {
    color: '#FFF',
    fontSize: 16,
    fontWeight: '600',
  },
  resendButton: {
    padding: 16,
    marginTop: 12,
  },
  resendButtonText: {
    color: '#2196F3',
    fontSize: 14,
  },
});

export default ForgotPasswordScreen;
