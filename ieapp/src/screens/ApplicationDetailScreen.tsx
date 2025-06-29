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
  Image,
  Platform
} from 'react-native';
import { useNavigation, useRoute, RouteProp } from '@react-navigation/native';
import { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { RootStackParamList } from '../navigation/AppNavigator';
import programService, { Application } from '../services/api/programService';
import { useAuth } from '../contexts/AuthContext';
import { format } from 'date-fns';
import { es } from 'date-fns/locale';
import * as DocumentPicker from 'expo-document-picker';
import Header from '../components/Header';
// import * as FileSystem from 'expo-file-system';

type ApplicationDetailRouteProp = RouteProp<RootStackParamList, 'ApplicationDetail'>;

interface Requisite {
  id: number;
  name: string;
  description: string;
  required: boolean;
  type: 'document' | 'form' | 'payment' | 'other';
  status: 'pending' | 'submitted' | 'approved' | 'rejected';
  completed_at?: string;
  comments?: string;
}

const ApplicationDetailScreen: React.FC = () => {
  const navigation = useNavigation<NativeStackNavigationProp<RootStackParamList>>();
  const route = useRoute<ApplicationDetailRouteProp>();
  const { applicationId } = route.params;
  const { user } = useAuth();
  
  const [application, setApplication] = useState<Application | null>(null);
  const [requisites, setRequisites] = useState<Requisite[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [progressPercentage, setProgressPercentage] = useState(0);
  const [uploading, setUploading] = useState(false);
  const [processingRequisite, setProcessingRequisite] = useState<number | null>(null);

  // Fetch application data and requisites
  useEffect(() => {
    const fetchApplicationDetails = async () => {
      try {
        setLoading(true);
        
        // Get application details
        const applications = await programService.getUserApplications();
        if (applications && applications.data && Array.isArray(applications.data.data)) {
          const foundApplication = applications.data.data.find(
            (app: Application) => app.id === applicationId
          );
          
          if (foundApplication) {
            setApplication(foundApplication);
            
            // Fetch application requisites using the public URL that doesn't require authentication
            try {
              console.log('Obteniendo requisitos para la aplicación ID:', applicationId);
              
              // Usar URL completa que sabemos que funciona
              const fullUrl = `http://127.0.0.1/intercultural-experience/public/api/public/applications/${applicationId}/requisites`;
              
              // Hacer un fetch directo en lugar de usar el servicio para evitar problemas de configuración
              const response = await fetch(fullUrl, {
                headers: {
                  'Content-Type': 'application/json',
                  'Accept': 'application/json'
                }
              });
              
              if (!response.ok) {
                throw new Error(`Error al obtener requisitos: ${response.status}`);
              }
              
              const requisitesData = await response.json();
              console.log('Requisitos obtenidos:', JSON.stringify(requisitesData));
              
              if (requisitesData && Array.isArray(requisitesData)) {
                setRequisites(requisitesData);
                
                // Calculate progress percentage
                const completedRequisites = requisitesData.filter(req => 
                  req.status === 'approved' || req.status === 'submitted'
                ).length;
                
                const totalRequisites = requisitesData.length;
                const percentage = totalRequisites > 0 
                  ? Math.round((completedRequisites / totalRequisites) * 100) 
                  : 0;
                  
                setProgressPercentage(percentage);
              }
            } catch (reqError) {
              console.error('Error al obtener requisitos para la aplicación:', reqError);
              setRequisites([]);
            }
          } else {
            setError('No se encontró la aplicación especificada.');
          }
        }
      } catch (err) {
        console.error('Error fetching application details:', err);
        setError('No se pudo cargar la información de la aplicación.');
      } finally {
        setLoading(false);
      }
    };
    
    fetchApplicationDetails();
  }, [applicationId]);

  const formatDate = (dateString?: string) => {
    if (!dateString) return 'No disponible';
    try {
      return format(new Date(dateString), 'dd/MM/yyyy', { locale: es });
    } catch (error) {
      return 'Fecha inválida';
    }
  };

  const handleUploadDocument = async (requisiteId: number) => {
    try {
      setProcessingRequisite(requisiteId);
      setUploading(true);
      
      // Pick a document
      const result = await DocumentPicker.getDocumentAsync({
        type: ['application/pdf', 'image/*'],
        copyToCacheDirectory: true,
      });
      
      if (result.canceled) {
        setUploading(false);
        setProcessingRequisite(null);
        return;
      }
      
      const fileUri = result.assets[0].uri;
      const fileType = result.assets[0].mimeType;
      const fileName = result.assets[0].name;
      
      // For implementation, we'd need to upload the file to a server
      // But for now, let's simulate a successful document upload
      
      // TODO: When integrating with an actual server, use FormData to upload the file
      // const formData = new FormData();
      // formData.append('document', {
      //   uri: fileUri,
      //   name: fileName,
      //   type: fileType,
      // });
      
      // Call the API to mark the requisite as completed
      await programService.completeRequisite(requisiteId, {
        application_id: applicationId,
        name: fileName,
        document_type: fileType,
        // In a real implementation, we'd pass the file or its URL after upload
      });
      
      // Update the local state to reflect the change
      setRequisites(prevRequisites => 
        prevRequisites.map(req => 
          req.id === requisiteId 
            ? { ...req, status: 'submitted', completed_at: new Date().toISOString() } 
            : req
        )
      );
      
      // Update progress percentage
      const updatedRequisites = requisites.map(req => 
        req.id === requisiteId 
          ? { ...req, status: 'submitted', completed_at: new Date().toISOString() } 
          : req
      );
      
      const completedRequisites = updatedRequisites.filter(req => 
        req.status === 'approved' || req.status === 'submitted'
      ).length;
      
      const totalRequisites = updatedRequisites.length;
      const percentage = totalRequisites > 0 
        ? Math.round((completedRequisites / totalRequisites) * 100) 
        : 0;
        
      setProgressPercentage(percentage);
      
      Alert.alert(
        'Documento Subido', 
        'Tu documento ha sido enviado y está pendiente de revisión.'
      );
    } catch (err) {
      console.error('Error uploading document:', err);
      Alert.alert(
        'Error', 
        'No se pudo subir el documento. Por favor intenta nuevamente.'
      );
    } finally {
      setUploading(false);
      setProcessingRequisite(null);
    }
  };

  const completeFormRequisite = async (requisiteId: number) => {
    // In a real implementation, this would navigate to a form screen
    // For now, we'll just mark it as completed
    
    Alert.alert(
      'Completar Requisito',
      '¿Estás seguro de que quieres marcar este requisito como completado?',
      [
        {
          text: 'Cancelar',
          style: 'cancel',
        },
        {
          text: 'Completar',
          onPress: async () => {
            try {
              setProcessingRequisite(requisiteId);
              
              // Call the API to mark the requisite as completed
              await programService.completeRequisite(requisiteId, {
                application_id: applicationId,
              });
              
              // Update the local state to reflect the change
              setRequisites(prevRequisites => 
                prevRequisites.map(req => 
                  req.id === requisiteId 
                    ? { ...req, status: 'submitted', completed_at: new Date().toISOString() } 
                    : req
                )
              );
              
              // Update progress percentage
              const updatedRequisites = requisites.map(req => 
                req.id === requisiteId 
                  ? { ...req, status: 'submitted', completed_at: new Date().toISOString() } 
                  : req
              );
              
              const completedRequisites = updatedRequisites.filter(req => 
                req.status === 'approved' || req.status === 'submitted'
              ).length;
              
              const totalRequisites = updatedRequisites.length;
              const percentage = totalRequisites > 0 
                ? Math.round((completedRequisites / totalRequisites) * 100) 
                : 0;
                
              setProgressPercentage(percentage);
              
              Alert.alert(
                'Requisito Completado', 
                'El requisito ha sido marcado como completado y está pendiente de revisión.'
              );
            } catch (err) {
              console.error('Error completing requisite:', err);
              Alert.alert(
                'Error', 
                'No se pudo completar el requisito. Por favor intenta nuevamente.'
              );
            } finally {
              setProcessingRequisite(null);
            }
          },
        },
      ],
      { cancelable: true }
    );
  };

  const getStatusColor = (status: string) => {
    switch(status) {
      case 'approved':
        return '#4CAF50';
      case 'submitted':
        return '#2196F3';
      case 'rejected':
        return '#F44336';
      default:
        return '#9E9E9E';
    }
  };

  const getStatusText = (status: string) => {
    switch(status) {
      case 'approved':
        return 'Aprobado';
      case 'submitted':
        return 'Enviado';
      case 'rejected':
        return 'Rechazado';
      default:
        return 'Pendiente';
    }
  };

  const renderRequisite = (requisite: Requisite) => {
    const isProcessing = processingRequisite === requisite.id;
    const isPending = requisite.status === 'pending';
    const isRejected = requisite.status === 'rejected';
    
    return (
      <View key={requisite.id} style={styles.requisiteCard}>
        <View style={styles.requisiteHeader}>
          <Text style={styles.requisiteName}>{requisite.name}</Text>
          <View style={[styles.statusBadge, { backgroundColor: getStatusColor(requisite.status) }]}>
            <Text style={styles.statusText}>{getStatusText(requisite.status)}</Text>
          </View>
        </View>
        
        <Text style={styles.requisiteDescription}>{requisite.description}</Text>
        
        {requisite.completed_at && (
          <Text style={styles.completedDate}>
            Enviado el: {formatDate(requisite.completed_at)}
          </Text>
        )}
        
        {(isPending || isRejected) && (
          <View style={styles.actionContainer}>
            {requisite.type === 'document' ? (
              <TouchableOpacity
                style={[styles.uploadButton, isProcessing && styles.processingButton]}
                onPress={() => handleUploadDocument(requisite.id)}
                disabled={isProcessing}
              >
                {isProcessing ? (
                  <ActivityIndicator size="small" color="#fff" />
                ) : (
                  <Text style={styles.buttonText}>
                    {isRejected ? 'Volver a Subir Documento' : 'Subir Documento'}
                  </Text>
                )}
              </TouchableOpacity>
            ) : (
              <TouchableOpacity
                style={[styles.completeButton, isProcessing && styles.processingButton]}
                onPress={() => completeFormRequisite(requisite.id)}
                disabled={isProcessing}
              >
                {isProcessing ? (
                  <ActivityIndicator size="small" color="#fff" />
                ) : (
                  <Text style={styles.buttonText}>
                    {isRejected ? 'Volver a Completar' : 'Completar'}
                  </Text>
                )}
              </TouchableOpacity>
            )}
          </View>
        )}
        
        {isRejected && requisite.comments && (
          <View style={styles.commentsContainer}>
            <Text style={styles.commentsLabel}>Comentarios:</Text>
            <Text style={styles.commentsText}>{requisite.comments}</Text>
          </View>
        )}
      </View>
    );
  };

  if (loading) {
    return (
      <SafeAreaView style={styles.loadingContainer}>
        <ActivityIndicator size="large" color="#6C4AA0" />
        <Text style={styles.loadingText}>Cargando detalles de la aplicación...</Text>
      </SafeAreaView>
    );
  }

  if (error || !application) {
    return (
      <SafeAreaView style={styles.errorContainer}>
        <Text style={styles.errorText}>{error || 'No se encontró la aplicación'}</Text>
        <TouchableOpacity 
          style={styles.errorButton}
          onPress={() => navigation.goBack()}
        >
          <Text style={styles.errorButtonText}>Volver</Text>
        </TouchableOpacity>
      </SafeAreaView>
    );
  }

  return (
    <SafeAreaView style={styles.container}>
      <ScrollView contentContainerStyle={styles.scrollContainer}>
        <Header
          showBackButton={true}
          showProfileButton={false}
          title="Mi Postulación"
        />
        
        {/* Program Info */}
        <View style={styles.programInfo}>
          <Text style={styles.programName}>
            {application.program?.name || 'Programa'}
          </Text>
          
          <View style={styles.applicationStatus}>
            <Text style={styles.statusLabel}>Estado:</Text>
            <View style={[
              styles.statusBadge, 
              { backgroundColor: getStatusColor(application.status) }
            ]}>
              <Text style={styles.statusText}>{getStatusText(application.status)}</Text>
            </View>
          </View>
          
          <View style={styles.applicationDates}>
            <Text style={styles.dateLabel}>Fecha de postulación:</Text>
            <Text style={styles.dateValue}>{formatDate(application.applied_at)}</Text>
          </View>
        </View>
        
        {/* Progress */}
        <View style={styles.progressContainer}>
          <View style={styles.progressHeader}>
            <Text style={styles.progressTitle}>Progreso de Requisitos</Text>
            <Text style={styles.progressPercentage}>{progressPercentage}%</Text>
          </View>
          
          <View style={styles.progressBarContainer}>
            <View 
              style={[
                styles.progressBar,
                { width: `${progressPercentage}%` }
              ]} 
            />
          </View>
        </View>
        
        {/* Requisites */}
        <View style={styles.requisitesContainer}>
          <Text style={styles.sectionTitle}>Requisitos del Programa</Text>
          
          {requisites.length === 0 ? (
            <View style={styles.emptyContainer}>
              <Text style={styles.emptyText}>
                No hay requisitos disponibles para este programa.
              </Text>
            </View>
          ) : (
            requisites.map(renderRequisite)
          )}
        </View>
        
        {/* Help Section */}
        <View style={styles.helpSection}>
          <Text style={styles.helpTitle}>¿Necesitas ayuda?</Text>
          <Text style={styles.helpText}>
            Si tienes dudas sobre los requisitos o el proceso de postulación, 
            comunícate con nosotros.
          </Text>
          <TouchableOpacity style={styles.contactButton}>
            <Text style={styles.contactButtonText}>Contactar Soporte</Text>
          </TouchableOpacity>
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
    padding: 20,
  },
  programInfo: {
    backgroundColor: '#f9f9f9',
    borderRadius: 10,
    padding: 15,
    marginBottom: 20,
  },
  programName: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 10,
  },
  applicationStatus: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 10,
  },
  statusLabel: {
    fontSize: 16,
    fontWeight: '500',
    color: '#666',
    marginRight: 10,
  },
  statusBadge: {
    paddingHorizontal: 10,
    paddingVertical: 5,
    borderRadius: 15,
  },
  statusText: {
    color: '#fff',
    fontWeight: 'bold',
    fontSize: 14,
  },
  applicationDates: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  dateLabel: {
    fontSize: 14,
    color: '#666',
    marginRight: 5,
  },
  dateValue: {
    fontSize: 14,
    fontWeight: '500',
    color: '#333',
  },
  progressContainer: {
    marginBottom: 20,
  },
  progressHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 10,
  },
  progressTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#333',
  },
  progressPercentage: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#6C4AA0',
  },
  progressBarContainer: {
    height: 12,
    backgroundColor: '#E0E0E0',
    borderRadius: 6,
    overflow: 'hidden',
  },
  progressBar: {
    height: '100%',
    backgroundColor: '#6C4AA0',
  },
  requisitesContainer: {
    marginBottom: 20,
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 15,
  },
  requisiteCard: {
    backgroundColor: '#fff',
    borderRadius: 10,
    padding: 15,
    marginBottom: 15,
    borderWidth: 1,
    borderColor: '#E0E0E0',
  },
  requisiteHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 10,
  },
  requisiteName: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#333',
    flex: 1,
  },
  requisiteDescription: {
    fontSize: 14,
    color: '#666',
    marginBottom: 10,
  },
  completedDate: {
    fontSize: 12,
    color: '#666',
    fontStyle: 'italic',
    marginBottom: 10,
  },
  actionContainer: {
    marginTop: 10,
  },
  uploadButton: {
    backgroundColor: '#2196F3',
    paddingVertical: 10,
    borderRadius: 5,
    alignItems: 'center',
  },
  completeButton: {
    backgroundColor: '#4CAF50',
    paddingVertical: 10,
    borderRadius: 5,
    alignItems: 'center',
  },
  processingButton: {
    opacity: 0.7,
  },
  buttonText: {
    color: '#fff',
    fontWeight: 'bold',
  },
  commentsContainer: {
    marginTop: 15,
    padding: 10,
    backgroundColor: '#FFF9C4',
    borderRadius: 5,
  },
  commentsLabel: {
    fontSize: 14,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 5,
  },
  commentsText: {
    fontSize: 14,
    color: '#666',
  },
  helpSection: {
    backgroundColor: '#F0E6FF',
    borderRadius: 10,
    padding: 15,
    marginBottom: 20,
  },
  helpTitle: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#6C4AA0',
    marginBottom: 10,
  },
  helpText: {
    fontSize: 14,
    color: '#333',
    marginBottom: 15,
  },
  contactButton: {
    backgroundColor: '#6C4AA0',
    paddingVertical: 10,
    borderRadius: 5,
    alignItems: 'center',
  },
  contactButtonText: {
    color: '#fff',
    fontWeight: 'bold',
  },
  emptyContainer: {
    padding: 30,
    alignItems: 'center',
    backgroundColor: '#f9f9f9',
    borderRadius: 10,
  },
  emptyText: {
    fontSize: 16,
    color: '#666',
    textAlign: 'center',
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  loadingText: {
    marginTop: 10,
    color: '#666',
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
  errorButton: {
    backgroundColor: '#6C4AA0',
    paddingHorizontal: 15,
    paddingVertical: 8,
    borderRadius: 20,
  },
  errorButtonText: {
    color: '#FFFFFF',
    fontWeight: '500',
  },
});

export default ApplicationDetailScreen;
