import React, { useState, useEffect } from 'react';
import { 
  View, 
  Text, 
  StyleSheet, 
  TouchableOpacity, 
  ScrollView, 
  ActivityIndicator, 
  Alert,
  Modal,
  TextInput,
  KeyboardAvoidingView,
  Platform,
  TouchableWithoutFeedback,
  Keyboard
} from 'react-native';
import { useNavigation } from '@react-navigation/native';
import { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { RootStackParamList } from '../navigation/AppNavigator';
import { useAuth } from '../contexts/AuthContext';
import { profileService } from '../services/api';
import { format } from 'date-fns';
import { es } from 'date-fns/locale';
import Header from '../components/Header';

const ProfileScreen: React.FC = () => {
  const navigation = useNavigation<NativeStackNavigationProp<RootStackParamList>>();
  const { user, isLoading, logout, refreshUser, updateUserData } = useAuth();
  const [loggingOut, setLoggingOut] = useState(false);
  const [refreshing, setRefreshing] = useState(false);
  
  // Estados para modales de edición
  const [personalInfoVisible, setPersonalInfoVisible] = useState(false);
  const [contactInfoVisible, setContactInfoVisible] = useState(false);
  
  // Estados para los campos de formulario
  const [formData, setFormData] = useState({
    name: '',
    birth_date: '',
    nationality: '',
    phone: '',
    address: ''
  });
  
  // Cargar datos del usuario en el formulario cuando está disponible
  useEffect(() => {
    if (user) {
      setFormData({
        name: user.name || '',
        birth_date: user.birth_date || '',
        nationality: user.nationality || '',
        phone: user.phone || '',
        address: user.address || ''
      });
    }
  }, [user]);
  
  // Format date if available
  const formatDate = (dateString?: string) => {
    if (!dateString) return 'No disponible';
    try {
      return format(new Date(dateString), 'dd/MM/yyyy', { locale: es });
    } catch (error) {
      return 'Fecha inválida';
    }
  };
  
  // Manejar cambios en los campos del formulario
  const handleChange = (name: string, value: string) => {
    setFormData(prevState => ({
      ...prevState,
      [name]: value
    }));
  };
  
  // Actualizar información personal
  const handleUpdatePersonalInfo = async () => {
    try {
      setRefreshing(true);
      
      // Aseguramos que los campos tengan los valores correctos
      const dataToUpdate = {
        name: formData.name,
        birth_date: formData.birth_date,
        nationality: formData.nationality
      };
      
      console.log('Enviando datos de actualización:', dataToUpdate);
      
      // Realizamos la petición de actualización
      const updatedProfile = await profileService.updateProfile(dataToUpdate);
      console.log('Respuesta de actualización:', updatedProfile);
      
      // Actualizar datos del usuario en el contexto de autenticación
      // para que se reflejen inmediatamente en la pantalla
      if (updatedProfile) {
        // Actualizar datos localmente sin hacer petición adicional al servidor
        updateUserData(dataToUpdate);
        
        // Opcionalmente, también podemos hacer un refresh completo
        // pero no es estrictamente necesario
        // await refreshUser();
      }
      
      Alert.alert('Éxito', 'Información personal actualizada correctamente');
      setPersonalInfoVisible(false);
    } catch (error) {
      console.error('Error al actualizar información personal:', error);
      Alert.alert('Error', 'No se pudo actualizar la información. Por favor intente de nuevo.');
    } finally {
      setRefreshing(false);
    }
  };
  
  // Actualizar información de contacto
  const handleUpdateContactInfo = async () => {
    try {
      setRefreshing(true);
      const dataToUpdate = {
        phone: formData.phone,
        address: formData.address
      };
      
      console.log('Enviando datos de actualización de contacto:', dataToUpdate);
      
      // Realizamos la petición de actualización
      const updatedProfile = await profileService.updateProfile(dataToUpdate);
      console.log('Respuesta de actualización de contacto:', updatedProfile);
      
      // Actualizar datos del usuario en el contexto de autenticación
      // para que se reflejen inmediatamente en la pantalla
      if (updatedProfile) {
        // Actualizar datos localmente sin hacer petición adicional al servidor
        updateUserData(dataToUpdate);
      }
      
      Alert.alert('Éxito', 'Información de contacto actualizada correctamente');
      setContactInfoVisible(false);
    } catch (error) {
      console.error('Error al actualizar información de contacto:', error);
      Alert.alert('Error', 'No se pudo actualizar la información. Por favor intente de nuevo.');
    } finally {
      setRefreshing(false);
    }
  };
  
  const handleLogout = async () => {
    try {
      setLoggingOut(true);
      await logout();
      // Navigation will be handled by AuthContext through the app navigator
    } catch (error) {
      Alert.alert('Error', 'No se pudo cerrar la sesión. Por favor intente de nuevo.');
    } finally {
      setLoggingOut(false);
    }
  };
  
  if (isLoading) {
    return (
      <View style={[styles.container, styles.centered]}>
        <ActivityIndicator size="large" color="#E52224" />
        <Text style={styles.loadingText}>Cargando información de perfil...</Text>
      </View>
    );
  }
  
  return (
    <ScrollView contentContainerStyle={styles.container}>
      <Header title="Mi Perfil" showBackButton={false} />
      <View style={styles.section}>
        <Text style={styles.sectionTitle}>Información Personal</Text>
        <Text style={styles.infoLabel}>Nombre:</Text>
        <Text style={styles.infoValue}>{user?.name || 'No disponible'}</Text>
        <Text style={styles.infoLabel}>Fecha de Nacimiento:</Text>
        <Text style={styles.infoValue}>{formatDate(user?.birth_date)}</Text>
        <Text style={styles.infoLabel}>País:</Text>
        <Text style={styles.infoValue}>{user?.nationality || 'No disponible'}</Text>
        <TouchableOpacity 
          style={styles.editButton}
          onPress={() => setPersonalInfoVisible(true)}
        >
          <Text style={styles.editButtonText}>EDITAR</Text>
        </TouchableOpacity>
      </View>
      <View style={styles.section}>
        <Text style={styles.sectionTitle}>Datos de Contacto</Text>
        <Text style={styles.infoLabel}>Email:</Text>
        <Text style={styles.infoValue}>{user?.email || 'No disponible'}</Text>
        <Text style={styles.infoLabel}>Teléfono / Whatsapp:</Text>
        <Text style={styles.infoValue}>{user?.phone || 'No disponible'}</Text>
        <Text style={styles.infoLabel}>Dirección:</Text>
        <Text style={styles.infoValue}>{user?.address || 'No disponible'}</Text>
        <TouchableOpacity 
          style={styles.editButton}
          onPress={() => setContactInfoVisible(true)}
        >
          <Text style={styles.editButtonText}>EDITAR</Text>
        </TouchableOpacity>
      </View>
      <View style={styles.row}>
        <TouchableOpacity style={styles.purpleButton}>
          <Text style={styles.purpleButtonText}>CAMBIAR MI CONTRASEÑA</Text>
        </TouchableOpacity>
        <TouchableOpacity 
          style={[styles.purpleButton, loggingOut && styles.disabledButton]}
          onPress={handleLogout}
          disabled={loggingOut}
        >
          {loggingOut ? (
            <ActivityIndicator size="small" color="#fff" />
          ) : (
            <Text style={styles.purpleButtonText}>CERRAR SESIÓN</Text>
          )}
        </TouchableOpacity>
      </View>
      <TouchableOpacity 
        style={styles.orangeButton} 
        onPress={() => navigation.navigate('MyApplications')}
      >
        <Text style={styles.orangeButtonText}>Ver mis Postulaciones</Text>
      </TouchableOpacity>
      <TouchableOpacity style={styles.orangeButton}>
        <Text style={styles.orangeButtonText}>Ver mis Requisitos Pendientes</Text>
      </TouchableOpacity>
      
      {/* Modal para editar información personal */}
      <Modal
        animationType="slide"
        transparent={true}
        visible={personalInfoVisible}
        onRequestClose={() => setPersonalInfoVisible(false)}
      >
        <KeyboardAvoidingView
          behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
          style={{ flex: 1 }}
        >
          <TouchableOpacity 
            style={styles.modalOverlay} 
            activeOpacity={1} 
            onPress={() => setPersonalInfoVisible(false)}
          />
          <View style={styles.modalContainer}>
            <View style={styles.modalContent}>
              <Text style={styles.modalTitle}>Editar Información Personal</Text>
              
              <Text style={styles.formLabel}>Nombre:</Text>
              <TextInput
                style={styles.input}
                value={formData.name}
                onChangeText={(value) => handleChange('name', value)}
                placeholder="Ingresa tu nombre completo"
                autoCapitalize="words"
              />
              
              <Text style={styles.formLabel}>Fecha de Nacimiento:</Text>
              <TextInput
                style={styles.input}
                value={formData.birth_date}
                onChangeText={(value) => handleChange('birth_date', value)}
                placeholder="YYYY-MM-DD"
                keyboardType="numbers-and-punctuation"
              />
              
              <Text style={styles.formLabel}>País:</Text>
              <TextInput
                style={styles.input}
                value={formData.nationality}
                onChangeText={(value) => handleChange('nationality', value)}
                placeholder="Ingresa tu país"
                autoCapitalize="words"
              />
                
              <View style={styles.modalButtons}>
                <TouchableOpacity 
                  style={[styles.modalButton, styles.cancelButton]}
                  onPress={() => setPersonalInfoVisible(false)}
                >
                  <Text style={styles.modalButtonText}>CANCELAR</Text>
                </TouchableOpacity>
                
                <TouchableOpacity 
                  style={[styles.modalButton, styles.saveButton]}
                  onPress={handleUpdatePersonalInfo}
                  disabled={refreshing}
                >
                  {refreshing ? (
                    <ActivityIndicator size="small" color="#fff" />
                  ) : (
                    <Text style={styles.modalButtonText}>GUARDAR</Text>
                  )}
                </TouchableOpacity>
              </View>
            </View>
          </View>
        </KeyboardAvoidingView>
      </Modal>
      
      {/* Modal para editar información de contacto */}
      <Modal
        animationType="slide"
        transparent={true}
        visible={contactInfoVisible}
        onRequestClose={() => setContactInfoVisible(false)}
      >
        <KeyboardAvoidingView
          behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
          style={{ flex: 1 }}
        >
          <TouchableOpacity 
            style={styles.modalOverlay} 
            activeOpacity={1} 
            onPress={() => setContactInfoVisible(false)}
          />
          <View style={styles.modalContainer}>
            <View style={styles.modalContent}>
                <Text style={styles.modalTitle}>Editar Datos de Contacto</Text>
                
                <Text style={styles.formLabel}>Teléfono / Whatsapp:</Text>
                <TextInput
                  style={styles.input}
                  value={formData.phone}
                  onChangeText={(value) => handleChange('phone', value)}
                  placeholder="Ingresa tu número de teléfono"
                  keyboardType="phone-pad"
                />
                
                <Text style={styles.formLabel}>Dirección:</Text>
                <TextInput
                  style={styles.input}
                  value={formData.address}
                  onChangeText={(value) => handleChange('address', value)}
                  placeholder="Ingresa tu dirección"
                  multiline
                />
                
                <View style={styles.modalButtons}>
                  <TouchableOpacity 
                    style={[styles.modalButton, styles.cancelButton]}
                    onPress={() => setContactInfoVisible(false)}
                  >
                    <Text style={styles.modalButtonText}>CANCELAR</Text>
                  </TouchableOpacity>
                  
                  <TouchableOpacity 
                    style={[styles.modalButton, styles.saveButton]}
                    onPress={handleUpdateContactInfo}
                    disabled={refreshing}
                  >
                    {refreshing ? (
                      <ActivityIndicator size="small" color="#fff" />
                    ) : (
                      <Text style={styles.modalButtonText}>GUARDAR</Text>
                    )}
                  </TouchableOpacity>
                </View>
              </View>
            </View>
        </KeyboardAvoidingView>
      </Modal>
    </ScrollView>
  );
};

const styles = StyleSheet.create({
  container: { flexGrow: 1, backgroundColor: '#fff', padding: 20 },
  centered: { justifyContent: 'center', alignItems: 'center' },
  loadingText: { marginTop: 10, color: '#666' },

  section: { backgroundColor: '#fff', borderRadius: 10, padding: 15, marginBottom: 15, borderWidth: 1, borderColor: '#eee' },
  sectionTitle: { color: '#6C4AA0', fontWeight: 'bold', fontSize: 18, marginBottom: 10 },
  infoLabel: { color: '#666', fontSize: 14, marginTop: 5 },
  infoValue: { color: '#333', fontSize: 16, marginBottom: 5, fontWeight: '500' },
  editButton: { backgroundColor: '#E52224', borderRadius: 8, padding: 8, marginTop: 8, alignItems: 'center' },
  editButtonText: { color: '#fff', fontWeight: 'bold' },
  row: { flexDirection: 'row', justifyContent: 'space-between', marginBottom: 10 },
  purpleButton: { backgroundColor: '#6C4AA0', borderRadius: 8, padding: 10, flex: 1, marginHorizontal: 5, alignItems: 'center' },
  disabledButton: { backgroundColor: '#9980C5', opacity: 0.7 },
  purpleButtonText: { color: '#fff', fontWeight: 'bold', fontSize: 12 },
  orangeButton: { backgroundColor: '#F8B400', borderRadius: 8, padding: 15, marginBottom: 10, alignItems: 'center' },
  orangeButtonText: { color: '#fff', fontWeight: 'bold', fontSize: 16 },
  bottomNav: { flexDirection: 'row', justifyContent: 'space-around', padding: 10, borderTopWidth: 1, borderColor: '#eee' },
  
  // Estilos para modales
  modalOverlay: {
    position: 'absolute',
    top: 0,
    bottom: 0,
    left: 0,
    right: 0,
    backgroundColor: 'rgba(0,0,0,0.5)'
  },
  modalContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: 20
  },
  modalContent: {
    backgroundColor: '#fff',
    borderRadius: 10,
    padding: 20,
    width: '100%',
    maxHeight: '80%'
  },
  modalTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#6C4AA0',
    marginBottom: 15,
    textAlign: 'center'
  },
  formLabel: {
    fontSize: 14,
    color: '#666',
    marginTop: 10,
    marginBottom: 5
  },
  input: {
    borderWidth: 1,
    borderColor: '#ddd',
    borderRadius: 8,
    padding: 10,
    fontSize: 16,
    backgroundColor: '#f9f9f9'
  },
  modalButtons: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginTop: 20
  },
  modalButton: {
    borderRadius: 8,
    padding: 12,
    flex: 1,
    marginHorizontal: 5,
    alignItems: 'center'
  },
  cancelButton: {
    backgroundColor: '#999'
  },
  saveButton: {
    backgroundColor: '#E52224'
  },
  modalButtonText: {
    color: '#fff',
    fontWeight: 'bold'
  }
});

export default ProfileScreen; 