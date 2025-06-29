import React, { useState } from 'react';
import { View, Text, TextInput, TouchableOpacity, StyleSheet, ScrollView, ActivityIndicator, Alert, Image } from 'react-native';
import { useAuth } from '../../contexts/AuthContext';
import InputField from '../../components/InputField';
import { useNavigation } from '@react-navigation/native';
import { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { RootStackParamList } from '../../navigation/AppNavigator';

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
  
  // Error state
  const [errors, setErrors] = useState<{[key: string]: string}>({});
  
  const validateForm = () => {
    const newErrors: {[key: string]: string} = {};
    
    if (!name) newErrors.name = 'El nombre es requerido';
    
    if (!email) {
      newErrors.email = 'El correo electrónico es requerido';
    } else if (!/\S+@\S+\.\S+/.test(email)) {
      newErrors.email = 'Correo electrónico inválido';
    }
    
    if (!password) {
      newErrors.password = 'La contraseña es requerida';
    } else if (password.length < 8) {
      newErrors.password = 'La contraseña debe tener al menos 8 caracteres';
    }
    
    if (!passwordConfirmation) {
      newErrors.passwordConfirmation = 'Confirma tu contraseña';
    } else if (password !== passwordConfirmation) {
      newErrors.passwordConfirmation = 'Las contraseñas no coinciden';
    }
    
    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };
  
  const handleRegister = async () => {
    if (!validateForm()) return;
    
    console.log('Attempting registration with:', { name, email, password: '***', passwordConfirmation: '***' });
    
    const result = await register(name, email, password, passwordConfirmation);
    
    if (result.success) {
      Alert.alert('Registro exitoso', 'Tu cuenta ha sido creada correctamente');
      // Navigation will happen automatically through AuthContext
    } else {
      console.error('Registration failed:', result);
      
      if (result.errors) {
        // Validation errors
        const formattedErrors: {[key: string]: string} = {};
        
        Object.keys(result.errors).forEach(key => {
          const errorArray = result.errors[key];
          formattedErrors[key] = Array.isArray(errorArray) ? errorArray[0] : errorArray;
        });
        
        setErrors(formattedErrors);
        Alert.alert('Error de validación', 'Por favor corrige los errores en el formulario.');
      } else {
        // General error
        Alert.alert('Error', result.message || 'Ha ocurrido un error al registrarte. Inténtalo de nuevo.');
      }
    }
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
        onChangeText={setName}
        placeholder="Ingresa tu nombre completo"
        error={errors.name}
      />
      
      <InputField
        label="Correo electrónico"
        value={email}
        onChangeText={setEmail}
        placeholder="ejemplo@correo.com"
        keyboardType="email-address"
        error={errors.email}
      />
      
      <InputField
        label="Contraseña"
        value={password}
        onChangeText={setPassword}
        placeholder="Mínimo 8 caracteres"
        secureTextEntry
        error={errors.password}
      />
      
      <InputField
        label="Confirmar contraseña"
        value={passwordConfirmation}
        onChangeText={setPasswordConfirmation}
        placeholder="Repite tu contraseña"
        secureTextEntry
        error={errors.passwordConfirmation}
      />
      
      <InputField
        label="Ciudad"
        value={city}
        onChangeText={setCity}
        placeholder="Tu ciudad"
        error={errors.city}
      />
      
      <InputField
        label="País"
        value={country}
        onChangeText={setCountry}
        placeholder="Tu país"
        error={errors.country}
      />
      
      <InputField
        label="Nivel Académico"
        value={academicLevel}
        onChangeText={setAcademicLevel}
        placeholder="Ej: Licenciatura, Maestría"
        error={errors.academicLevel}
      />
      
      <InputField
        label="Nivel de Inglés"
        value={englishLevel}
        onChangeText={setEnglishLevel}
        placeholder="Ej: Básico, Intermedio, Avanzado"
        error={errors.englishLevel}
      />
      
      <TouchableOpacity 
        style={styles.button} 
        onPress={handleRegister}
        disabled={isLoading}
      >
        {isLoading ? (
          <ActivityIndicator color="#FFFFFF" />
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
});

export default RegisterScreen; 