import React, { useState, useEffect } from 'react';
import { 
  View, 
  Text, 
  StyleSheet, 
  Image, 
  ScrollView, 
  TouchableOpacity,
  ActivityIndicator,
  RefreshControl,
  Alert,
  SafeAreaView
} from 'react-native';
import { useNavigation } from '@react-navigation/native';
import { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { RootStackParamList } from '../navigation/AppNavigator';
import programService, { Program } from '../services/api/programService';
import { useAuth } from '../contexts/AuthContext';
import { useTabNavigation } from '../contexts/NavigationContext';
import { format } from 'date-fns';
import { es } from 'date-fns/locale';
import Header from '../components/Header';

const ProgramsScreen: React.FC = () => {
  const navigation = useNavigation<NativeStackNavigationProp<RootStackParamList>>();
  const { user } = useAuth();
  const { activeTab, setActiveTab } = useTabNavigation();
  
  // Actualizar la pestaña activa cuando se carga esta pantalla
  useEffect(() => {
    setActiveTab('programs');
  }, []);
  
  const [programs, setPrograms] = useState<Program[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [error, setError] = useState<string | null>(null);
  
  const fetchPrograms = async () => {
    try {
      setLoading(true);
      setError(null);
      const programsData = await programService.getPrograms();
      setPrograms(programsData);
    } catch (err: any) {
      console.error('Error fetching programs:', err);
      setError('No se pudieron cargar los programas. Por favor intente nuevamente.');
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };
  
  // Load programs on initial render
  useEffect(() => {
    fetchPrograms();
  }, []);
  
  // Handle pull-to-refresh
  const onRefresh = () => {
    setRefreshing(true);
    fetchPrograms();
  };
  
  const formatDate = (dateString?: string) => {
    if (!dateString) return 'Fecha no disponible';
    try {
      return format(new Date(dateString), 'dd MMM yyyy', { locale: es });
    } catch (error) {
      return 'Fecha inválida';
    }
  };
  
  const handleProgramPress = (program: Program) => {
    // Navigate to program detail screen
    navigation.navigate('ProgramDetail', { programId: program.id });
  };
  
  const renderProgram = (program: Program) => {
    return (
      <TouchableOpacity 
        key={program.id} 
        style={styles.programCard}
        onPress={() => handleProgramPress(program)}
      >
        <Image 
          source={{
            uri: program.image_url || program.image || 'https://via.placeholder.com/400x200?text=Programa'
          }} 
          style={styles.programImage}
        />
        <View style={styles.programInfo}>
          <Text style={styles.programTitle}>{program.name}</Text>
          <Text style={styles.programLocation}>
            <Text style={styles.infoLabel}>Ubicación:</Text> {program.location}
          </Text>
          <Text style={styles.programDates}>
            <Text style={styles.infoLabel}>Fechas:</Text> {formatDate(program.start_date)} - {formatDate(program.end_date)}
          </Text>
        </View>
        <View style={styles.applyButton}>
          <Text style={styles.applyButtonText}>Ver detalles</Text>
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
        <Header />
        
        <Text style={styles.title}>Programas Disponibles</Text>
        
        {loading && !refreshing ? (
          <View style={styles.loadingContainer}>
            <ActivityIndicator size="large" color="#6C4AA0" />
            <Text style={styles.loadingText}>Cargando programas...</Text>
          </View>
        ) : error ? (
          <View style={styles.errorContainer}>
            <Text style={styles.errorText}>{error}</Text>
            <TouchableOpacity style={styles.retryButton} onPress={fetchPrograms}>
              <Text style={styles.retryButtonText}>Intentar nuevamente</Text>
            </TouchableOpacity>
          </View>
        ) : programs.length === 0 ? (
          <View style={styles.emptyContainer}>
            <Text style={styles.emptyText}>No hay programas disponibles en este momento.</Text>
          </View>
        ) : (
          <View style={styles.programsContainer}>
            {programs.map(renderProgram)}
          </View>
        )}
        
        {/* El menú de navegación se agrega automáticamente a través del contenedor */}
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
    backgroundColor: '#fff', 
    padding: 20 
  },
  title: { 
    fontSize: 24, 
    color: '#6C4AA0', 
    fontWeight: 'bold', 
    marginBottom: 20 
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: 20,
    height: 200
  },
  loadingText: {
    marginTop: 10,
    color: '#666',
    fontSize: 16
  },
  errorContainer: {
    padding: 20,
    backgroundColor: '#FFF0F0',
    borderRadius: 10,
    marginBottom: 20,
    alignItems: 'center'
  },
  errorText: {
    color: '#E52224',
    marginBottom: 15,
    textAlign: 'center',
    fontSize: 16
  },
  retryButton: {
    backgroundColor: '#E52224',
    paddingHorizontal: 20,
    paddingVertical: 10,
    borderRadius: 5
  },
  retryButtonText: {
    color: '#fff',
    fontWeight: 'bold'
  },
  emptyContainer: {
    padding: 30,
    alignItems: 'center',
    justifyContent: 'center'
  },
  emptyText: {
    fontSize: 16,
    color: '#666',
    textAlign: 'center'
  },
  programsContainer: {
    marginBottom: 20
  },
  programCard: { 
    backgroundColor: '#fff', 
    borderRadius: 10, 
    marginBottom: 20, 
    overflow: 'hidden',
    borderWidth: 1,
    borderColor: '#eee',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3
  },
  programImage: { 
    width: '100%', 
    height: 150,
    resizeMode: 'cover' 
  },
  programInfo: {
    padding: 15
  },
  programTitle: { 
    fontSize: 20, 
    fontWeight: 'bold', 
    color: '#333',
    marginBottom: 8
  },
  infoLabel: {
    fontWeight: 'bold',
    color: '#666'
  },
  programLocation: {
    fontSize: 16,
    marginBottom: 5,
    color: '#444'
  },
  programDates: {
    fontSize: 14,
    marginBottom: 5,
    color: '#666'
  },
  programPrice: {
    fontSize: 16,
    fontWeight: '500',
    color: '#E52224'
  },
  applyButton: {
    backgroundColor: '#6C4AA0',
    padding: 12,
    alignItems: 'center'
  },
  applyButtonText: {
    color: '#fff',
    fontWeight: 'bold',
    fontSize: 16
  },
  bottomNav: { 
    flexDirection: 'row', 
    justifyContent: 'space-around', 
    padding: 15, 
    borderTopWidth: 1, 
    borderColor: '#eee',
    backgroundColor: '#fff'
  },
  navIcon: {
    fontSize: 24
  },
  activeNav: {
    backgroundColor: '#F0E6FF',
    width: 40,
    height: 40,
    borderRadius: 20,
    textAlign: 'center',
    textAlignVertical: 'center',
    overflow: 'hidden'
  }
});

export default ProgramsScreen; 