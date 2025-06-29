import React, { useState, useEffect } from 'react';
import { 
  View, 
  Text, 
  StyleSheet, 
  ScrollView, 
  TouchableOpacity,
  ActivityIndicator,
  Alert,
  SafeAreaView,
  Switch
} from 'react-native';
import { useNavigation, useRoute, RouteProp } from '@react-navigation/native';
import { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { RootStackParamList } from '../navigation/AppNavigator';
import programService, { Program } from '../services/api/programService';
import { useAuth } from '../contexts/AuthContext';
import { format } from 'date-fns';
import { es } from 'date-fns/locale';
import formService from '../services/api/formService';

type ApplicationConfirmRouteProp = RouteProp<RootStackParamList, 'ApplicationConfirm'>;

// Requisito simplificado para mostrar en la pantalla de confirmación
interface ProgramRequisite {
  id: number;
  name: string;
  description: string;
  type: string;
  required: boolean;
}

const ApplicationConfirmScreen: React.FC = () => {
  const navigation = useNavigation<NativeStackNavigationProp<RootStackParamList>>();
  const route = useRoute<ApplicationConfirmRouteProp>();
  const { programId } = route.params;
  const { user } = useAuth();
  
  const [program, setProgram] = useState<Program | null>(null);
  const [requisites, setRequisites] = useState<ProgramRequisite[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [isApplying, setIsApplying] = useState(false);
  const [acceptTerms, setAcceptTerms] = useState(false);
  const [hasActiveForm, setHasActiveForm] = useState(false);
  const [activeForm, setActiveForm] = useState<any>(null);
  
  // Fetch program data and requisites
  useEffect(() => {
    const fetchProgramDetails = async () => {
      try {
        setLoading(true);
        
        // Get program details
        const programData = await programService.getProgramById(programId);
        setProgram(programData);
        
        // Get program requisites
        const requisitesData = await programService.getProgramRequisites(programId);
        if (requisitesData && Array.isArray(requisitesData)) {
          setRequisites(requisitesData);
        }
        
        // Check for active form
        try {
          const activeFormData = await formService.getActiveForm(programId);
          if (activeFormData) {
            setHasActiveForm(true);
            setActiveForm(activeFormData);
          }
        } catch (formError) {
          console.log('No active form found for this program:', formError);
          setHasActiveForm(false);
        }
      } catch (err) {
        console.error('Error fetching program details:', err);
        setError('No se pudo cargar la información del programa.');
      } finally {
        setLoading(false);
      }
    };
    
    fetchProgramDetails();
  }, [programId]);

  const formatDate = (dateString?: string) => {
    if (!dateString) return 'Fecha no disponible';
    try {
      return format(new Date(dateString), 'dd MMMM yyyy', { locale: es });
    } catch (error) {
      return 'Fecha inválida';
    }
  };
  
  const handleConfirmApply = async () => {
    if (!user) {
      Alert.alert('Acceso Denegado', 'Debes iniciar sesión para postular a este programa.');
      return;
    }
    
    if (!acceptTerms) {
      Alert.alert('Términos y Condiciones', 'Debes aceptar los términos y condiciones para continuar.');
      return;
    }
    
    // Si hay un formulario activo, navegar al formulario primero
    if (hasActiveForm && activeForm) {
      navigation.navigate('FormScreen', {
        programId: programId,
        formId: activeForm.id,
      });
      return;
    }
    
    try {
      setIsApplying(true);
      const result = await programService.applyForProgram(programId, {});
      
      if (result && result.id) {
        Alert.alert(
          'Postulación Exitosa', 
          'Tu postulación ha sido recibida. Ahora debes completar los requisitos para avanzar en el proceso.',
          [
            { 
              text: 'Ver Requisitos', 
              onPress: () => navigation.navigate('ApplicationDetail', { applicationId: result.id }) 
            }
          ]
        );
      }
    } catch (err: any) {
      console.error('Error applying to program:', err);
      Alert.alert(
        'Error', 
        'No se pudo procesar tu postulación. Por favor intenta nuevamente.'
      );
    } finally {
      setIsApplying(false);
    }
  };

  if (loading) {
    return (
      <SafeAreaView style={styles.loadingContainer}>
        <ActivityIndicator size="large" color="#0066cc" />
        <Text style={styles.loadingText}>Cargando información...</Text>
      </SafeAreaView>
    );
  }

  if (error || !program) {
    return (
      <SafeAreaView style={styles.errorContainer}>
        <Text style={styles.errorText}>{error || 'Ha ocurrido un error inesperado.'}</Text>
        <TouchableOpacity 
          style={styles.backButton}
          onPress={() => navigation.goBack()}
        >
          <Text style={styles.backButtonText}>Volver</Text>
        </TouchableOpacity>
      </SafeAreaView>
    );
  }

  return (
    <SafeAreaView style={styles.container}>
      <ScrollView contentContainerStyle={styles.scrollContent}>
        <View style={styles.header}>
          <Text style={styles.title}>Confirmar Postulación</Text>
          <Text style={styles.programName}>{program.name}</Text>
        </View>
        
        <View style={styles.infoContainer}>
          <Text style={styles.sectionTitle}>Detalles del Programa</Text>
          
          <View style={styles.infoRow}>
            <Text style={styles.infoLabel}>Fecha de inicio:</Text>
            <Text style={styles.infoValue}>{formatDate(program.start_date)}</Text>
          </View>
          
          <View style={styles.infoRow}>
            <Text style={styles.infoLabel}>Fecha de finalización:</Text>
            <Text style={styles.infoValue}>{formatDate(program.end_date)}</Text>
          </View>
          
          <View style={styles.infoRow}>
            <Text style={styles.infoLabel}>Lugar:</Text>
            <Text style={styles.infoValue}>{program.location}</Text>
          </View>
        </View>
        
        <View style={styles.requisitesContainer}>
          <Text style={styles.sectionTitle}>Requisitos del Programa</Text>
          <Text style={styles.requisitesDescription}>
            Para completar tu postulación, deberás proporcionar la siguiente documentación y cumplir con estos requisitos:
          </Text>
          
          {requisites.length > 0 ? (
            requisites.map((requisite) => (
              <View key={requisite.id} style={styles.requisiteItem}>
                <Text style={styles.requisiteName}>{requisite.name}</Text>
                <Text style={styles.requisiteDescription}>{requisite.description}</Text>
                <View style={styles.requisiteTypeContainer}>
                  <Text style={styles.requisiteType}>
                    Tipo: {requisite.type === 'document' ? 'Documento' : 
                          requisite.type === 'form' ? 'Formulario' : 
                          requisite.type === 'payment' ? 'Pago' : 'Otro'}
                  </Text>
                  {requisite.required && (
                    <Text style={styles.requisiteRequired}>Obligatorio</Text>
                  )}
                </View>
              </View>
            ))
          ) : (
            <Text style={styles.noRequisitesText}>Este programa no tiene requisitos específicos.</Text>
          )}
        </View>
        
        {hasActiveForm && activeForm && (
          <View style={styles.formContainer}>
            <Text style={styles.sectionTitle}>Formulario de Inscripción</Text>
            <Text style={styles.formDescription}>
              Este programa requiere completar un formulario de inscripción con información detallada.
            </Text>
            <View style={styles.formInfoContainer}>
              <Text style={styles.formName}>{activeForm.name}</Text>
              {activeForm.description && (
                <Text style={styles.formDescriptionText}>{activeForm.description}</Text>
              )}
              <Text style={styles.formNote}>
                Al confirmar tu postulación, serás dirigido al formulario de inscripción que debes completar.
              </Text>
            </View>
          </View>
        )}
        
        <View style={styles.termsContainer}>
          <Text style={styles.termsTitle}>Términos y Condiciones</Text>
          <Text style={styles.termsText}>
            Al postular a este programa, acepto que:
          </Text>
          
          <View style={styles.termsList}>
            <Text style={styles.termsItem}>• He leído y comprendido toda la información del programa.</Text>
            <Text style={styles.termsItem}>• Cumplo con los requisitos básicos para participar.</Text>
            <Text style={styles.termsItem}>• Proporcionaré información verídica en mi solicitud.</Text>
            <Text style={styles.termsItem}>• Acepto completar todos los documentos requeridos dentro del plazo.</Text>
          </View>
          
          <View style={styles.acceptTermsContainer}>
            <Switch
              value={acceptTerms}
              onValueChange={setAcceptTerms}
              trackColor={{ false: '#cccccc', true: '#0066cc80' }}
              thumbColor={acceptTerms ? '#0066cc' : '#f4f4f4'}
            />
            <Text style={styles.acceptTermsText}>
              Acepto los términos y condiciones del programa
            </Text>
          </View>
        </View>
      </ScrollView>
      
      <View style={styles.buttonContainer}>
        <TouchableOpacity 
          style={styles.cancelButton}
          onPress={() => navigation.goBack()}
          disabled={isApplying}
        >
          <Text style={styles.cancelButtonText}>Cancelar</Text>
        </TouchableOpacity>
        
        <TouchableOpacity 
          style={[
            styles.confirmButton, 
            (!acceptTerms || isApplying) && styles.disabledButton
          ]}
          onPress={handleConfirmApply}
          disabled={!acceptTerms || isApplying}
        >
          {isApplying ? (
            <ActivityIndicator color="#FFFFFF" size="small" />
          ) : (
            <Text style={styles.confirmButtonText}>
              {hasActiveForm ? 'Completar Formulario' : 'Confirmar Postulación'}
            </Text>
          )}
        </TouchableOpacity>
      </View>
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#ffffff',
  },
  scrollContent: {
    padding: 16,
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#ffffff',
  },
  loadingText: {
    marginTop: 12,
    fontSize: 16,
    color: '#333333',
  },
  errorContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: 20,
    backgroundColor: '#ffffff',
  },
  errorText: {
    fontSize: 16,
    color: '#e74c3c',
    textAlign: 'center',
    marginBottom: 20,
  },
  backButton: {
    backgroundColor: '#0066cc',
    paddingHorizontal: 20,
    paddingVertical: 10,
    borderRadius: 8,
  },
  backButtonText: {
    color: '#ffffff',
    fontSize: 16,
    fontWeight: '500',
  },
  header: {
    marginBottom: 24,
  },
  title: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#333333',
    marginBottom: 8,
  },
  programName: {
    fontSize: 18,
    color: '#0066cc',
    fontWeight: '500',
  },
  infoContainer: {
    backgroundColor: '#f5f5f5',
    borderRadius: 10,
    padding: 16,
    marginBottom: 24,
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#333333',
    marginBottom: 16,
  },
  infoRow: {
    flexDirection: 'row',
    marginBottom: 8,
  },
  infoLabel: {
    flex: 1,
    fontSize: 15,
    color: '#666666',
  },
  infoValue: {
    flex: 2,
    fontSize: 15,
    color: '#333333',
    fontWeight: '500',
  },
  requisitesContainer: {
    marginBottom: 24,
  },
  requisitesDescription: {
    fontSize: 15,
    color: '#666666',
    marginBottom: 16,
  },
  requisiteItem: {
    backgroundColor: '#f5f5f5',
    borderRadius: 8,
    padding: 16,
    marginBottom: 12,
  },
  requisiteName: {
    fontSize: 16,
    fontWeight: '600',
    color: '#333333',
    marginBottom: 4,
  },
  requisiteDescription: {
    fontSize: 14,
    color: '#666666',
    marginBottom: 8,
  },
  requisiteTypeContainer: {
    flexDirection: 'row',
    justifyContent: 'space-between',
  },
  requisiteType: {
    fontSize: 14,
    color: '#888888',
  },
  requisiteRequired: {
    fontSize: 14,
    color: '#e74c3c',
    fontWeight: '500',
  },
  noRequisitesText: {
    fontSize: 15,
    color: '#666666',
    fontStyle: 'italic',
  },
  termsContainer: {
    marginBottom: 32,
  },
  termsTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#333333',
    marginBottom: 12,
  },
  termsText: {
    fontSize: 15,
    color: '#666666',
    marginBottom: 12,
  },
  termsList: {
    marginBottom: 16,
  },
  termsItem: {
    fontSize: 14,
    color: '#333333',
    marginBottom: 8,
    paddingLeft: 4,
  },
  acceptTermsContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    marginTop: 8,
  },
  acceptTermsText: {
    fontSize: 15,
    color: '#333333',
    marginLeft: 12,
    flex: 1,
  },
  buttonContainer: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    padding: 16,
    borderTopWidth: 1,
    borderTopColor: '#eeeeee',
  },
  cancelButton: {
    backgroundColor: '#ffffff',
    borderWidth: 1,
    borderColor: '#0066cc',
    paddingVertical: 12,
    paddingHorizontal: 16,
    borderRadius: 8,
    marginRight: 12,
    flex: 1,
    alignItems: 'center',
  },
  cancelButtonText: {
    color: '#0066cc',
    fontSize: 16,
    fontWeight: '500',
  },
  confirmButton: {
    backgroundColor: '#0066cc',
    paddingVertical: 12,
    paddingHorizontal: 16,
    borderRadius: 8,
    flex: 2,
    alignItems: 'center',
  },
  confirmButtonText: {
    color: '#ffffff',
    fontSize: 16,
    fontWeight: '500',
  },
  disabledButton: {
    backgroundColor: '#cccccc',
  },
  formContainer: {
    backgroundColor: '#e6f3ff',
    borderRadius: 10,
    padding: 16,
    marginBottom: 24,
  },
  formDescription: {
    fontSize: 15,
    color: '#666666',
    marginBottom: 16,
  },
  formInfoContainer: {
    backgroundColor: '#ffffff',
    borderRadius: 8,
    padding: 12,
  },
  formName: {
    fontSize: 16,
    fontWeight: '600',
    color: '#0066cc',
    marginBottom: 8,
  },
  formDescriptionText: {
    fontSize: 14,
    color: '#666666',
    marginBottom: 8,
  },
  formNote: {
    fontSize: 13,
    color: '#888888',
    fontStyle: 'italic',
  },
});

export default ApplicationConfirmScreen;
