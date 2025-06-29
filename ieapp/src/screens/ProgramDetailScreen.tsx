import React, { useState, useEffect } from 'react';
import { 
  View, 
  Text, 
  StyleSheet, 
  Image, 
  ScrollView, 
  TouchableOpacity,
  ActivityIndicator,
  Alert,
  SafeAreaView,
  Dimensions
} from 'react-native';
import { useNavigation, useRoute, RouteProp } from '@react-navigation/native';
import { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { RootStackParamList } from '../navigation/AppNavigator';
import programService, { Program, Application } from '../services/api/programService';
import apiClient from '../services/api/apiClient';
import { useAuth } from '../contexts/AuthContext';
import { format } from 'date-fns';
import { es } from 'date-fns/locale';
import AsyncStorage from '@react-native-async-storage/async-storage';

type ProgramDetailRouteProp = RouteProp<RootStackParamList, 'ProgramDetail'>;
type ApplicationStatus = 'none' | 'pending' | 'in_progress' | 'approved' | 'rejected';

const { width } = Dimensions.get('window');

const ProgramDetailScreen: React.FC = () => {
  const navigation = useNavigation<NativeStackNavigationProp<RootStackParamList>>();
  const route = useRoute<ProgramDetailRouteProp>();
  const { programId } = route.params;
  const { user } = useAuth();
  
  const [program, setProgram] = useState<Program | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [applicationStatus, setApplicationStatus] = useState<ApplicationStatus>('none');
  const [applicationId, setApplicationId] = useState<number | null>(null);
  const [isApplying, setIsApplying] = useState(false);

  // Estado para monitorear la carga de requisitos
  const [requisitesLoading, setRequisitesLoading] = useState(false);
  const [requisitesError, setRequisitesError] = useState<string | null>(null);

  // Fetch program data and check application status
  useEffect(() => {
    const fetchProgramDetails = async () => {
      try {
        setLoading(true);
        setRequisitesLoading(true);
        setError(null);
        setRequisitesError(null);
        
        // Get program details without requisites primero
        const programData = await programService.getProgramById(programId, false);
        
        // Luego obtenemos los requisitos por separado
        try {
          // Intentamos obtener los requisitos usando el endpoint público
          console.log('Intentando obtener requisitos para programa ID:', programId);
          
          // Usamos el endpoint público que no requiere autenticación y cambiamos localhost por 127.0.0.1
          const url = `/public/programs/${programId}/requisites`;
          console.log('Intentando acceder a la URL:', url);
          
          // Usamos una URL completa que sabemos que funciona
          const fullUrl = `http://127.0.0.1/intercultural-experience/public/api/public/programs/${programId}/requisites`;
          
          // Hacemos un fetch directo en lugar de usar apiClient para evitar problemas de configuración
          const response = await fetch(fullUrl, {
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json'
            }
          });
          
          if (!response.ok) {
            throw new Error(`Error al obtener requisitos: ${response.status}`);
          }
          
          const data = await response.json();
          console.log('Respuesta de requisitos:', JSON.stringify(data));
          
          // Si tenemos datos, los asignamos al programa
          if (data) {
            programData.requisites = data;
          }
        } catch (reqError: any) {
          console.error('Error específico al obtener requisitos:', 
                      reqError.message, 
                      reqError.response?.status, 
                      JSON.stringify(reqError.response?.data));
          setRequisitesError('No se pudieron cargar los requisitos del programa.');
          
          // Si no hay requisitos, inicializamos con array vacío
          programData.requisites = [];
        }
        
        // Ahora establecemos el programa con o sin requisitos
        console.log('Programa con requisitos?', programData.requisites ? `Sí (${programData.requisites.length})` : 'No');
        setProgram(programData);
        
        // Check if user has already applied
        const applications = await programService.getUserApplications();
        if (applications && applications.data && Array.isArray(applications.data.data)) {
          const existingApplication = applications.data.data.find(
            (app: Application) => app.program_id === programId
          );
          
          if (existingApplication) {
            setApplicationId(existingApplication.id);
            setApplicationStatus(existingApplication.status as ApplicationStatus);
          }
        }
      } catch (err) {
        console.error('Error fetching program details:', err);
        setError('No se pudo cargar la información del programa.');
      } finally {
        setRequisitesLoading(false);
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

  const handleApply = () => {
    if (!user) {
      Alert.alert('Acceso Denegado', 'Debes iniciar sesión para postular a este programa.');
      return;
    }
    
    // Redirigir al usuario a la pantalla de confirmación de postulación
    navigation.navigate('ApplicationConfirm', { programId });
  };

  const getApplicationStatusText = () => {
    switch(applicationStatus) {
      case 'pending':
        return 'Postulación en Proceso';
      case 'in_progress':
        return 'Postulación en Proceso';
      case 'approved':
        return 'Postulación Aprobada';
      case 'rejected':
        return 'Postulación Rechazada';
      default:
        return '';
    }
  };

  const renderApplicationButton = () => {
    if (applicationStatus === 'none') {
      return (
        <TouchableOpacity 
          style={styles.applyButton} 
          onPress={handleApply}
          disabled={isApplying}
        >
          {isApplying ? (
            <ActivityIndicator color="#fff" size="small" />
          ) : (
            <Text style={styles.applyButtonText}>Postular</Text>
          )}
        </TouchableOpacity>
      );
    } else {
      return (
        <View style={styles.applicationStatusContainer}>
          <Text style={styles.applicationStatusText}>
            {getApplicationStatusText()}
          </Text>
          <TouchableOpacity 
            style={styles.viewApplicationButton}
            onPress={() => navigation.navigate('ApplicationDetail', { applicationId: applicationId as number })}
          >
            <Text style={styles.viewApplicationButtonText}>Ver mi Postulación</Text>
          </TouchableOpacity>
        </View>
      );
    }
  };

  if (loading) {
    return (
      <SafeAreaView style={styles.loadingContainer}>
        <ActivityIndicator size="large" color="#6C4AA0" />
        <Text style={styles.loadingText}>Cargando detalles del programa...</Text>
      </SafeAreaView>
    );
  }

  if (error || !program) {
    return (
      <SafeAreaView style={styles.errorContainer}>
        <Text style={styles.errorText}>{error || 'No se encontró el programa'}</Text>
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
      <ScrollView contentContainerStyle={styles.scrollContainer}>
        {/* Header Image */}
        <Image 
          source={{ 
            uri: program.image_url || 'https://via.placeholder.com/800x400?text=Programa'
          }} 
          style={styles.headerImage} 
        />
        
        {/* Back Button */}
        <TouchableOpacity 
          style={styles.backButtonOverlay}
          onPress={() => navigation.goBack()}
        >
          <Text style={styles.backButtonText}>← Volver</Text>
        </TouchableOpacity>
        
        {/* Program Details */}
        <View style={styles.detailsContainer}>
          <Text style={styles.programTitle}>{program.name}</Text>
          
          <View style={styles.infoSection}>
            <View style={styles.infoRow}>
              <View style={styles.infoItem}>
                <Text style={styles.infoLabel}>Ubicación</Text>
                <Text style={styles.infoValue}>{program.location}</Text>
              </View>
              <View style={styles.infoItem}>
                <Text style={styles.infoLabel}>Duración</Text>
                <Text style={styles.infoValue}>{program.duration || 'No especificada'}</Text>
              </View>
            </View>
            
            <View style={styles.infoRow}>
              <View style={styles.infoItem}>
                <Text style={styles.infoLabel}>Inicio</Text>
                <Text style={styles.infoValue}>{formatDate(program.start_date)}</Text>
              </View>
              <View style={styles.infoItem}>
                <Text style={styles.infoLabel}>Finalización</Text>
                <Text style={styles.infoValue}>{formatDate(program.end_date)}</Text>
              </View>
            </View>
            
            <View style={styles.infoRow}>
              <View style={styles.infoItem}>
                <Text style={styles.infoLabel}>Costo</Text>
                <Text style={styles.costValue}>A Cotizar</Text>
              </View>
              <View style={styles.infoItem}>
                <Text style={styles.infoLabel}>Créditos</Text>
                <Text style={styles.infoValue}>{program.credits || 'No aplica'}</Text>
              </View>
            </View>
            
            <View style={styles.infoRow}>
              <View style={styles.infoItemFull}>
                <Text style={styles.infoLabel}>Cupos</Text>
                <Text style={styles.infoValue}>
                  {program.available_spots !== undefined 
                    ? `${program.available_spots} de ${program.capacity}` 
                    : `Capacidad: ${program.capacity}`}
                </Text>
              </View>
            </View>
            
            <View style={styles.infoRow}>
              <View style={styles.infoItemFull}>
                <Text style={styles.infoLabel}>Fecha límite de postulación</Text>
                <Text style={styles.infoValue}>
                  {program.application_deadline 
                    ? formatDate(program.application_deadline)
                    : 'Contactar para más información'}
                </Text>
              </View>
            </View>
          </View>
          
          <View style={styles.descriptionSection}>
            <Text style={styles.sectionTitle}>Descripción del Programa</Text>
            <Text style={styles.description}>{program.description}</Text>
          </View>

          {/* Requisites Section */}
          <View style={styles.requisitesSection}>
            <View style={styles.requisitesSectionHeader}>
              <Text style={styles.sectionTitle}>Requisitos del Programa</Text>
              {requisitesLoading && (
                <ActivityIndicator size="small" color="#6C4AA0" style={styles.requisitesLoader} />
              )}
            </View>
            
            {requisitesError && (
              <View style={styles.errorContainer}>
                <Text style={styles.errorTextSmall}>{requisitesError}</Text>
              </View>
            )}
            
            {program.requisites && program.requisites.length > 0 ? (
              <>
                <Text style={styles.requisitesCount}>Total: {program.requisites.length} requisitos</Text>
                {program.requisites.map((requisite) => (
                  <View key={requisite.id} style={styles.requisiteItem}>
                    <View style={[styles.requisiteIconContainer, 
                      requisite.type === 'document' ? styles.documentIcon :
                      requisite.type === 'payment' ? styles.paymentIcon :
                      requisite.type === 'action' ? styles.actionIcon : styles.otherIcon]}>
                      <Text style={styles.requisiteIcon}>
                        {requisite.type === 'document' ? '📄' : 
                         requisite.type === 'payment' ? '💰' : 
                         requisite.type === 'action' ? '✅' : '📋'}
                      </Text>
                    </View>
                    <View style={styles.requisiteContent}>
                      <Text style={styles.requisiteName}>{requisite.name}</Text>
                      {requisite.description && (
                        <Text style={styles.requisiteDescription}>{requisite.description}</Text>
                      )}
                      <Text style={[styles.requisiteType, requisite.required ? styles.requiredLabel : styles.optionalLabel]}>
                        {requisite.type === 'document' ? 'Documento' : 
                         requisite.type === 'payment' ? 'Pago' : 
                         requisite.type === 'action' ? 'Acción' : 'Requisito'}
                        {requisite.required ? ' (Obligatorio)' : ' (Opcional)'}
                      </Text>
                    </View>
                  </View>
                ))}
              </>
            ) : requisitesLoading ? (
              <Text style={styles.loadingRequisitesText}>Cargando requisitos...</Text>
            ) : (
              <Text style={styles.noRequisitesText}>No hay requisitos específicos para este programa.</Text>
            )}
          </View>
          
          {renderApplicationButton()}
        </View>
      </ScrollView>
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#fff',
  },
  scrollContainer: {
    flexGrow: 1,
  },
  headerImage: {
    width: '100%',
    height: 250,
  },
  backButtonOverlay: {
    position: 'absolute',
    top: 10,
    left: 10,
    backgroundColor: 'rgba(0, 0, 0, 0.5)',
    paddingHorizontal: 15,
    paddingVertical: 8,
    borderRadius: 20,
  },
  backButtonText: {
    color: '#fff',
    fontWeight: 'bold',
  },
  detailsContainer: {
    padding: 20,
  },
  programTitle: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 15,
  },
  infoSection: {
    backgroundColor: '#f9f9f9',
    borderRadius: 10,
    padding: 15,
    marginBottom: 20,
  },
  infoRow: {
    flexDirection: 'row',
    marginBottom: 15,
  },
  infoItem: {
    flex: 1,
  },
  infoItemFull: {
    flex: 2,
  },
  infoLabel: {
    fontSize: 14,
    color: '#666',
    marginBottom: 5,
  },
  infoValue: {
    fontSize: 16,
    fontWeight: '500',
    color: '#333',
  },
  costValue: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#E52224',
  },
  descriptionSection: {
    marginBottom: 20,
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#6C4AA0',
    marginBottom: 10,
  },
  description: {
    fontSize: 16,
    lineHeight: 24,
    color: '#444',
  },
  applyButton: {
    backgroundColor: '#6C4AA0',
    paddingVertical: 15,
    borderRadius: 8,
    alignItems: 'center',
    marginTop: 20,
  },
  applyButtonText: {
    color: '#fff',
    fontWeight: 'bold',
    fontSize: 18,
  },
  applicationStatusContainer: {
    marginTop: 20,
    padding: 15,
    backgroundColor: '#F0E6FF',
    borderRadius: 8,
    alignItems: 'center',
  },
  applicationStatusText: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#6C4AA0',
    marginBottom: 10,
  },
  viewApplicationButton: {
    backgroundColor: '#6C4AA0',
    paddingVertical: 10,
    paddingHorizontal: 20,
    borderRadius: 8,
  },
  viewApplicationButtonText: {
    color: '#fff',
    fontWeight: 'bold',
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#fff',
  },
  loadingText: {
    marginTop: 10,
    color: '#6C4AA0',
  },
  requisitesSection: {
    backgroundColor: '#f9f9f9',
    borderRadius: 10,
    padding: 15,
    marginBottom: 20,
  },
  requisitesSectionHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 10,
  },
  requisitesLoader: {
    marginLeft: 10,
  },
  requisitesCount: {
    fontSize: 14,
    color: '#666',
    marginBottom: 12,
    textAlign: 'right',
  },
  requisiteItem: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    marginVertical: 10,
    backgroundColor: '#fff',
    borderRadius: 8,
    padding: 10,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 3,
    elevation: 2,
  },
  requisiteIconContainer: {
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: '#f0f0f0',
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 10,
  },
  documentIcon: {
    backgroundColor: '#E3F2FD',
  },
  paymentIcon: {
    backgroundColor: '#E8F5E9',
  },
  actionIcon: {
    backgroundColor: '#FFF8E1',
  },
  otherIcon: {
    backgroundColor: '#F3E5F5',
  },
  requisiteIcon: {
    fontSize: 20,
  },
  requisiteContent: {
    flex: 1,
  },
  requisiteName: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 4,
  },
  requisiteDescription: {
    fontSize: 14,
    color: '#666',
    marginBottom: 4,
  },
  requisiteType: {
    fontSize: 12,
    fontStyle: 'italic',
  },
  requiredLabel: {
    color: '#D32F2F',
    fontWeight: '500',
  },
  optionalLabel: {
    color: '#388E3C',
  },
  loadingRequisitesText: {
    fontSize: 14,
    color: '#6C4AA0',
    fontStyle: 'italic',
    textAlign: 'center',
    padding: 10,
  },
  noRequisitesText: {
    fontSize: 14,
    color: '#888',
    fontStyle: 'italic',
    textAlign: 'center',
    padding: 10,
  },
  errorTextSmall: {
    color: '#D32F2F',
    fontSize: 14,
    textAlign: 'center',
    marginBottom: 10,
  },
  errorContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: 20,
  },
  errorText: {
    color: '#E52224',
    marginBottom: 20,
    textAlign: 'center',
  },
  backButton: {
    backgroundColor: '#6C4AA0',
    paddingVertical: 10,
    paddingHorizontal: 20,
    borderRadius: 8,
  },
});

export default ProgramDetailScreen;
