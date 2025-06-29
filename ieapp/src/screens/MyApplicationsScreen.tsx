import React, { useState, useEffect } from 'react';
import { 
  View, 
  Text, 
  StyleSheet, 
  ScrollView, 
  TouchableOpacity,
  ActivityIndicator,
  SafeAreaView,
  RefreshControl,
  Image
} from 'react-native';
import { useNavigation } from '@react-navigation/native';
import { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { RootStackParamList } from '../navigation/AppNavigator';
import programService, { Application } from '../services/api/programService';
import { useAuth } from '../contexts/AuthContext';
import { format } from 'date-fns';
import { es } from 'date-fns/locale';
import Header from '../components/Header';

const MyApplicationsScreen: React.FC = () => {
  const navigation = useNavigation<NativeStackNavigationProp<RootStackParamList>>();
  const { user } = useAuth();
  const [applications, setApplications] = useState<Application[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const fetchApplications = async () => {
    try {
      setLoading(true);
      setError(null);
      
      // Get user's applications
      const response = await programService.getUserApplications();
      if (response && response.data && Array.isArray(response.data.data)) {
        setApplications(response.data.data);
      } else {
        setApplications([]);
      }
    } catch (err) {
      console.error('Error fetching applications:', err);
      setError('No se pudieron cargar tus postulaciones. Por favor intenta nuevamente.');
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  useEffect(() => {
    fetchApplications();
  }, []);

  const onRefresh = () => {
    setRefreshing(true);
    fetchApplications();
  };

  const formatDate = (dateString?: string) => {
    if (!dateString) return 'Fecha no disponible';
    try {
      return format(new Date(dateString), 'dd MMM yyyy', { locale: es });
    } catch (error) {
      return 'Fecha inválida';
    }
  };

  const getStatusLabel = (status: string) => {
    switch(status) {
      case 'pending': return 'Pendiente';
      case 'in_progress': return 'En Proceso';
      case 'approved': return 'Aprobada';
      case 'rejected': return 'Rechazada';
      default: return 'Desconocido';
    }
  };

  const getStatusColor = (status: string) => {
    switch(status) {
      case 'pending': return '#FFC107';
      case 'in_progress': return '#2196F3';
      case 'approved': return '#4CAF50';
      case 'rejected': return '#F44336';
      default: return '#9E9E9E';
    }
  };

  const renderApplication = (application: Application) => {
    return (
      <TouchableOpacity 
        key={application.id} 
        style={styles.applicationCard}
        onPress={() => navigation.navigate('ApplicationDetail', { applicationId: application.id })}
      >
        <View style={styles.applicationHeader}>
          <Text style={styles.programName}>{application.program?.name || 'Programa'}</Text>
          <View style={[styles.statusBadge, { backgroundColor: getStatusColor(application.status) }]}>
            <Text style={styles.statusText}>{getStatusLabel(application.status)}</Text>
          </View>
        </View>
        
        <View style={styles.applicationInfo}>
          <Text style={styles.infoRow}>
            <Text style={styles.infoLabel}>Fecha de aplicación:</Text> {formatDate(application.applied_at)}
          </Text>
          {application.program?.location && (
            <Text style={styles.infoRow}>
              <Text style={styles.infoLabel}>Ubicación:</Text> {application.program.location}
            </Text>
          )}
          {application.program?.start_date && (
            <Text style={styles.infoRow}>
              <Text style={styles.infoLabel}>Fecha de inicio:</Text> {formatDate(application.program.start_date)}
            </Text>
          )}
        </View>
        
        <View style={styles.viewDetailsButton}>
          <Text style={styles.viewDetailsButtonText}>Ver Detalles →</Text>
        </View>
      </TouchableOpacity>
    );
  };

  return (
    <SafeAreaView style={styles.safeArea}>
      <ScrollView 
        contentContainerStyle={styles.container}
        refreshControl={
          <RefreshControl
            refreshing={refreshing}
            onRefresh={onRefresh}
            colors={['#6C4AA0']}
          />
        }
      >
        <Header showBackButton={true} title="Mis Postulaciones" />
        
        {loading && !refreshing ? (
          <View style={styles.loadingContainer}>
            <ActivityIndicator size="large" color="#6C4AA0" />
            <Text style={styles.loadingText}>Cargando tus postulaciones...</Text>
          </View>
        ) : error ? (
          <View style={styles.errorContainer}>
            <Text style={styles.errorText}>{error}</Text>
            <TouchableOpacity style={styles.retryButton} onPress={fetchApplications}>
              <Text style={styles.retryButtonText}>Intentar nuevamente</Text>
            </TouchableOpacity>
          </View>
        ) : applications.length === 0 ? (
          <View style={styles.emptyContainer}>
            <Image
              source={require('../../assets/images/ie-icon.png')}
              style={styles.emptyImage}
              resizeMode="contain"
            />
            <Text style={styles.emptyTitle}>No tienes postulaciones</Text>
            <Text style={styles.emptyText}>
              Aún no has postulado a ningún programa. Explora los programas disponibles para iniciar tu experiencia intercultural.
            </Text>
            <TouchableOpacity 
              style={styles.exploreButton}
              onPress={() => navigation.navigate('Programs')}
            >
              <Text style={styles.exploreButtonText}>Explorar Programas</Text>
            </TouchableOpacity>
          </View>
        ) : (
          <View style={styles.applicationsContainer}>
            {applications.map(renderApplication)}
          </View>
        )}
      </ScrollView>
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  safeArea: {
    flex: 1,
    backgroundColor: '#fff',
  },
  container: {
    flexGrow: 1,
    padding: 20,
  },
  title: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#6C4AA0',
    marginBottom: 20,
  },
  applicationsContainer: {
    marginTop: 10,
  },
  applicationCard: {
    backgroundColor: '#fff',
    borderRadius: 10,
    marginBottom: 20,
    padding: 15,
    elevation: 3,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 3,
    borderWidth: 1,
    borderColor: '#eee',
  },
  applicationHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 10,
  },
  programName: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#333',
    flex: 1,
  },
  statusBadge: {
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 20,
  },
  statusText: {
    color: '#fff',
    fontWeight: 'bold',
    fontSize: 12,
  },
  applicationInfo: {
    marginVertical: 10,
  },
  infoRow: {
    marginVertical: 3,
    color: '#555',
    fontSize: 14,
  },
  infoLabel: {
    fontWeight: 'bold',
    color: '#6C4AA0',
  },
  viewDetailsButton: {
    alignItems: 'flex-end',
    marginTop: 10,
  },
  viewDetailsButtonText: {
    color: '#6C4AA0',
    fontWeight: 'bold',
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    paddingVertical: 50,
  },
  loadingText: {
    marginTop: 10,
    color: '#666',
  },
  errorContainer: {
    padding: 20,
    alignItems: 'center',
  },
  errorText: {
    color: '#F44336',
    textAlign: 'center',
    marginBottom: 15,
  },
  retryButton: {
    backgroundColor: '#6C4AA0',
    paddingVertical: 10,
    paddingHorizontal: 20,
    borderRadius: 5,
  },
  retryButtonText: {
    color: '#fff',
    fontWeight: 'bold',
  },
  emptyContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: 20,
  },
  emptyImage: {
    width: 150,
    height: 150,
    marginBottom: 20,
  },
  emptyTitle: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 10,
  },
  emptyText: {
    textAlign: 'center',
    color: '#666',
    marginBottom: 20,
  },
  exploreButton: {
    backgroundColor: '#E52224',
    paddingVertical: 12,
    paddingHorizontal: 25,
    borderRadius: 25,
  },
  exploreButtonText: {
    color: '#fff',
    fontWeight: 'bold',
  }
});

export default MyApplicationsScreen;
