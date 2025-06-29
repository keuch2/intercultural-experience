import React, { useState } from 'react';
import { 
  View, 
  Text, 
  StyleSheet, 
  TouchableOpacity, 
  Image, 
  ImageBackground,
  Dimensions,
  ActivityIndicator,
  Alert,
  ScrollView
} from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { StatusBar } from 'expo-status-bar';
import { useNavigation } from '@react-navigation/native';
import { useAuth } from '../../contexts/AuthContext';
import InputField from '../../components/InputField';
import { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { RootStackParamList } from '../../navigation/AppNavigator';

const { width, height } = Dimensions.get('window');

const LoginScreen: React.FC = () => {
  const navigation = useNavigation<NativeStackNavigationProp<RootStackParamList>>();
  const { login, isLoading, error: contextError, clearError } = useAuth();
  
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [errors, setErrors] = useState<{email?: string; password?: string}>({});
  const [apiError, setApiError] = useState<string | null>(null);
  const [debugInfo, setDebugInfo] = useState<any>(null);
  
  const validateForm = () => {
    const newErrors: {email?: string; password?: string} = {};
    
    if (!email) {
      newErrors.email = 'El correo electrónico es requerido';
    } else if (!/\S+@\S+\.\S+/.test(email)) {
      newErrors.email = 'Correo electrónico inválido';
    }
    
    if (!password) {
      newErrors.password = 'La contraseña es requerida';
    }
    
    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };
  
  const handleLogin = async () => {
    if (!validateForm()) return;
    
    // Clear previous errors
    setApiError(null);
    setDebugInfo(null);
    clearError();
    
    console.log('Attempting login with:', { email, password });
    const result = await login(email, password);
    
    if (result.success) {
      // Login successful - navigation will happen automatically through AuthContext
      console.log('Login successful, navigation will happen automatically');
    } else {
      // Login failed
      console.error('Login error details:', result);
      
      // Capture detailed error information for debugging
      setDebugInfo(result);
      
      if (result.errors) {
        // Validation errors from Laravel
        const formattedErrors: {email?: string; password?: string} = {};
        
        if (result.errors.email) {
          formattedErrors.email = Array.isArray(result.errors.email) 
            ? result.errors.email[0] 
            : result.errors.email;
        }
        
        if (result.errors.password) {
          formattedErrors.password = Array.isArray(result.errors.password) 
            ? result.errors.password[0] 
            : result.errors.password;
        }
        
        setErrors(formattedErrors);
        setApiError('Por favor verifique los campos del formulario');
      } else {
        // General error
        setApiError(result.message || 'Error al iniciar sesión');
        Alert.alert('Error de autenticación', result.message || 'Credenciales incorrectas');
      }
    }
  };

  const goToApiTest = () => {
    navigation.navigate('ApiTest');
  };
  
  return (
    <View style={styles.container}>
      <StatusBar style="light" />
      <ImageBackground 
        source={require('../../../assets/images/background.jpg')} 
        style={styles.backgroundImage}
      >
        <LinearGradient
          colors={['rgba(0,0,0,0.3)', 'rgba(0,0,0,0.5)']}
          style={styles.gradientOverlay}
        >
          <ScrollView contentContainerStyle={{flexGrow: 1}}>
            <View style={styles.contentContainer}>
              {/* Logo and Headline */}
              <View style={styles.topSection}>
                <Text style={styles.mainHeadline}>¡Conviértete en un ciudadano global!</Text>
                <Text style={styles.subHeadline}>
                  Explora programas de intercambio con la{'\n'}
                  Fundación IE Intercultural Experience.
                </Text>
                <Image 
                  source={require('../../../assets/images/ie-logo.png')} 
                  style={styles.logo} 
                  resizeMode="contain"
                />
              </View>

              {/* Info Box */}
              <View style={styles.infoBox}>
                <Text style={styles.infoBoxTitle}>PARA PARTICIPANTES DE IE:</Text>
                <Text style={styles.infoBoxText}>
                  Accede a tu información personal y financiera.
                </Text>
                <Text style={styles.infoBoxText}>
                  Gana y canjea puntos por beneficios exclusivos.
                </Text>
              </View>

            {/* Login Form */}
            <View style={styles.formContainer}>
              <InputField
                label="Correo Electrónico"
                labelStyle={{color: 'white'}}
                value={email}
                onChangeText={setEmail}
                placeholder="tu@email.com"
                keyboardType="email-address"
                error={errors.email}
                containerStyle={{marginBottom: 15}}
              />
              
              <InputField
                label="Contraseña"
                labelStyle={{color: 'white'}}
                value={password}
                onChangeText={setPassword}
                placeholder="Tu contraseña"
                secureTextEntry
                error={errors.password}
                containerStyle={{marginBottom: 15}}
              />
              
              <TouchableOpacity style={styles.forgotPassword}>
                <Text style={styles.forgotPasswordText}>¿Olvidaste tu contraseña?</Text>
              </TouchableOpacity>
            </View>
            
            {/* Error Messages */}
            {apiError && (
              <View style={styles.errorContainer}>
                <Text style={styles.errorText}>{apiError}</Text>
              </View>
            )}
            
            {/* Debug Information (in development) */}
            {debugInfo && (
              <View style={styles.debugContainer}>
                <Text style={styles.debugTitle}>Debug Information:</Text>
                <ScrollView style={styles.debugScroll}>
                  <Text style={styles.debugText}>
                    {JSON.stringify(debugInfo, null, 2)}
                  </Text>
                </ScrollView>
              </View>
            )}
            
            {/* Action Buttons */}
            <View style={styles.buttonContainer}>
              <TouchableOpacity 
                style={styles.loginButton} 
                onPress={handleLogin}
                disabled={isLoading}
              >
                {isLoading ? (
                  <ActivityIndicator color="#FFFFFF" />
                ) : (
                  <Text style={styles.loginButtonText}>INGRESAR</Text>
                )}
              </TouchableOpacity>
              
              <TouchableOpacity 
                style={styles.registerButton} 
                onPress={() => navigation.navigate('Register')}
                disabled={isLoading}
              >
                <Text style={styles.registerButtonText}>REGISTRO</Text>
              </TouchableOpacity>

              {/* Google Sign In Button */}
              <TouchableOpacity 
                onPress={() => alert('Google login not yet implemented')}
                disabled={isLoading}
                style={styles.googleButtonContainer}
              >
                <Image 
                  source={require('../../../assets/images/google.png')} 
                  style={styles.googleButton}
                  resizeMode="contain"
                />
              </TouchableOpacity>
              
              {/* API Test Button */}
              <TouchableOpacity 
                style={styles.apiTestButton} 
                onPress={goToApiTest}
              >
                <Text style={styles.apiTestButtonText}>API Test Screen</Text>
              </TouchableOpacity>
            </View>
            </View>
          </ScrollView>
        </LinearGradient>
      </ImageBackground>
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
  backgroundImage: {
    width: '100%',
    height: '100%',
  },
  gradientOverlay: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  contentContainer: {
    flex: 1,
    width: '100%',
    paddingHorizontal: 20,
    justifyContent: 'space-between',
    paddingTop: 60,
    paddingBottom: 40,
  },
  topSection: {
    alignItems: 'center',
    marginBottom: 30,
  },
  logo: {
    width: 150,
    height: 150,
    marginTop: 20,
  },
  mainHeadline: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#FFFFFF',
    textAlign: 'center',
    marginBottom: 10,
  },
  subHeadline: {
    fontSize: 16,
    color: '#FFFFFF',
    textAlign: 'center',
    opacity: 0.8,
  },
  infoBox: {
    backgroundColor: 'rgba(255, 255, 255, 0.15)',
    borderRadius: 10,
    padding: 15,
    marginBottom: 30,
  },
  infoBoxTitle: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#FFFFFF',
    marginBottom: 10,
  },
  infoBoxText: {
    fontSize: 14,
    color: '#FFFFFF',
    marginBottom: 5,
  },
  formContainer: {
    marginBottom: 20,
  },
  forgotPassword: {
    alignSelf: 'flex-end',
    marginTop: 10,
  },
  forgotPasswordText: {
    color: '#FFFFFF',
    fontSize: 14,
  },
  buttonContainer: {
    alignItems: 'center',
  },
  loginButton: {
    backgroundColor: '#0066cc',
    borderRadius: 25,
    width: '100%',
    height: 50,
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 15,
  },
  loginButtonText: {
    color: '#FFFFFF',
    fontSize: 16,
    fontWeight: 'bold',
  },
  registerButton: {
    backgroundColor: 'transparent',
    borderWidth: 2,
    borderColor: '#FFFFFF',
    borderRadius: 25,
    width: '100%',
    height: 50,
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 20,
  },
  registerButtonText: {
    color: '#FFFFFF',
    fontSize: 16,
    fontWeight: 'bold',
  },
  googleButtonContainer: {
    alignItems: 'center',
    justifyContent: 'center',
    marginVertical: 15,
  },
  googleButton: {
    width: 220,
    height: 48,
  },
  apiTestButton: {
    backgroundColor: '#333333',
    borderRadius: 25,
    paddingVertical: 10,
    paddingHorizontal: 20,
    marginTop: 10,
  },
  apiTestButtonText: {
    color: '#FFFFFF',
    fontSize: 14,
  },
  errorContainer: {
    backgroundColor: 'rgba(255, 0, 0, 0.1)',
    borderRadius: 8,
    padding: 10,
    marginBottom: 15,
    borderWidth: 1,
    borderColor: '#ff0000',
  },
  errorText: {
    color: '#ff0000',
    fontSize: 14,
    textAlign: 'center',
  },
  debugContainer: {
    backgroundColor: 'rgba(0, 0, 0, 0.7)',
    borderRadius: 8,
    padding: 10,
    marginBottom: 15,
    maxHeight: 200,
  },
  debugTitle: {
    color: '#ffffff',
    fontSize: 14,
    fontWeight: 'bold',
    marginBottom: 5,
  },
  debugScroll: {
    maxHeight: 150,
  },
  debugText: {
    color: '#ffffff',
    fontSize: 12,
    fontFamily: 'monospace',
  }
});

export default LoginScreen;