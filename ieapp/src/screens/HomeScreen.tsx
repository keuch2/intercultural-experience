import React, { useEffect, useState } from 'react';
import { View, Text, StyleSheet, TouchableOpacity, ScrollView, ActivityIndicator, RefreshControl, Image, Dimensions } from 'react-native';
import { useNavigation } from '@react-navigation/native';
import { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { RootStackParamList } from '../navigation/AppNavigator';
import { useAuth } from '../contexts/AuthContext';
import { useTabNavigation } from '../contexts/NavigationContext';
import { programService, rewardService } from '../services/api';
import Header from '../components/Header';
import NetworkStatusBanner from '../components/NetworkStatusBanner';

const HomeScreen: React.FC = () => {
  const navigation = useNavigation<NativeStackNavigationProp<RootStackParamList>>();
  const { user, isLoading } = useAuth();
  const { activeTab, setActiveTab } = useTabNavigation();
  const [refreshing, setRefreshing] = useState(false);
  const [programs, setPrograms] = useState<any[]>([]);
  const [featuredPrograms, setFeaturedPrograms] = useState<any[]>([]);
  const [applications, setApplications] = useState<any[]>([]);
  const [totalPoints, setTotalPoints] = useState(0);
  const [loadingPrograms, setLoadingPrograms] = useState(false);
  
  // Fetch user programs
  const loadUserData = async () => {
    setLoadingPrograms(true);
    try {
      // Get user's applications
      const response = await programService.getUserApplications();
      if (response && response.data && Array.isArray(response.data.data)) {
        setApplications(response.data.data);
      }
      
      // Get available programs (just for display in counter)
      const programsResponse = await programService.getPrograms();
      if (programsResponse && Array.isArray(programsResponse)) {
        setPrograms(programsResponse);
        // Get last 4 programs for featured carousel
        const lastFour = programsResponse.slice(-4).reverse();
        console.log('Featured programs:', lastFour.map(p => ({ 
          id: p.id, 
          name: p.name, 
          image: p.image, 
          image_url: p.image_url 
        })));
        setFeaturedPrograms(lastFour);
      }
      
      // Get user's actual points balance from API
      try {
        const pointsBalance = await rewardService.getPointsBalance();
        setTotalPoints(pointsBalance.total || 0);
      } catch (pointsError) {
        console.error('Error loading points:', pointsError);
        // Fallback: try to get points from user context
        if (user && user.points) {
          const points = user.points.reduce((total: number, point: any) => total + (point.change || 0), 0);
          setTotalPoints(points);
        }
      }
    } catch (error) {
      console.error('Error loading user data:', error);
    } finally {
      setLoadingPrograms(false);
      setRefreshing(false);
    }
  };
  
  useEffect(() => {
    if (user) {
      loadUserData();
    }
  }, [user]);
  
  const onRefresh = () => {
    setRefreshing(true);
    loadUserData();
  };
  
  if (isLoading) {
    return (
      <View style={styles.container}>
        <Header title="Inicio" />
        <NetworkStatusBanner onRetry={loadUserData} />
        <ActivityIndicator size="large" color="#E52224" />
        <Text style={styles.loadingText}>Cargando información...</Text>
      </View>
    );
  }
  
  return (
    <View style={styles.container}>
      <Header title="Inicio" />
      <NetworkStatusBanner onRetry={loadUserData} />
      
      <ScrollView 
        contentContainerStyle={styles.scrollContent}
        refreshControl={
          <RefreshControl refreshing={refreshing} onRefresh={onRefresh} />
        }
      >
        {/* Featured Programs Carousel */}
        <View style={styles.carouselContainer}>
          <Text style={styles.carouselTitle}>Programas Destacados</Text>
          <ScrollView 
            horizontal 
            showsHorizontalScrollIndicator={false}
            contentContainerStyle={styles.carouselContent}
          >
            {loadingPrograms ? (
              <ActivityIndicator size="small" color="#E52224" />
            ) : featuredPrograms.length > 0 ? (
              featuredPrograms.map((program) => {
                const imageUri = program.image_url || program.image || 'https://via.placeholder.com/300x150?text=Programa';
                console.log(`[HomeScreen] Rendering image for program ${program.id}:`, imageUri);
                
                return (
                <TouchableOpacity 
                  key={program.id}
                  style={styles.carouselCard}
                  onPress={() => navigation.navigate('ProgramDetail', { programId: program.id })}
                >
                  <Image 
                    source={{ uri: imageUri }}
                    style={styles.carouselImage}
                    resizeMode="cover"
                    onError={(error) => console.error(`[HomeScreen] Image load error for program ${program.id}:`, error.nativeEvent)}
                    onLoad={() => console.log(`[HomeScreen] Image loaded successfully for program ${program.id}`)}
                  />
                  <View style={styles.carouselCardContent}>
                    <Text style={styles.carouselCardTitle} numberOfLines={2}>
                      {program.name}
                    </Text>
                    <Text style={styles.carouselCardLocation} numberOfLines={1}>
                      📍 {program.location}
                    </Text>
                  </View>
                </TouchableOpacity>
                );
              })
            ) : (
              <Text style={styles.noPrograms}>No hay programas disponibles</Text>
            )}
          </ScrollView>
        </View>
      <TouchableOpacity style={styles.redButton} onPress={() => navigation.navigate('Programs')}>
        <Text style={styles.buttonText}>Ver y postular a Programas de IE</Text>
      </TouchableOpacity>
      <TouchableOpacity 
        style={styles.purpleButton}
        onPress={() => setActiveTab('applications')}
      >
        <Text style={styles.buttonText}>Ver el estado de mi Postulación</Text>
      </TouchableOpacity>
      <View style={styles.infoRow}>
        <View style={styles.infoCard}>
          <Text style={styles.infoTitle}>MIS PROGRAMAS</Text>
          {applications.length > 0 ? (
            <View style={styles.programsList}>
              {applications.map((application) => (
                <TouchableOpacity 
                  key={application.id} 
                  style={styles.programItem}
                  onPress={() => navigation.navigate('ApplicationDetail', { applicationId: application.id })}
                >
                  <View style={styles.programStatusDot}>
                    <View style={[styles.statusDot, {
                      backgroundColor: 
                        application.status === 'approved' ? '#4CAF50' : 
                        application.status === 'pending' ? '#FFC107' : 
                        application.status === 'rejected' ? '#F44336' : 
                        application.status === 'in_review' ? '#2196F3' : '#9E9E9E'
                    }]} />
                  </View>
                  <View style={styles.programItemContent}>
                    <Text style={styles.programName} numberOfLines={1}>
                      {application.program?.name || 'Programa'}
                    </Text>
                    <Text style={styles.programStatus}>
                      {application.status === 'approved' ? 'Aprobado' :
                       application.status === 'pending' ? 'Pendiente' :
                       application.status === 'rejected' ? 'Rechazado' :
                       application.status === 'in_review' ? 'En Revisión' : 'Desconocido'}
                    </Text>
                  </View>
                  <Text style={styles.viewDetails}>→</Text>
                </TouchableOpacity>
              ))}
            </View>
          ) : (
            <Text style={styles.infoContent}>No tienes postulaciones activas</Text>
          )}
        </View>
        <TouchableOpacity 
          style={styles.infoCard}
          onPress={() => navigation.navigate('Rewards')}
        >
          <Text style={styles.infoTitle}>MIS PUNTOS IE</Text>
          <Text style={styles.infoContent}>{totalPoints} puntos listos para canjear</Text>
          <Text style={styles.tapToRedeemText}>Toca para canjear →</Text>
        </TouchableOpacity>
      </View>
      <View style={styles.orangeRow}>
        <TouchableOpacity 
          style={styles.orangeButton} 
          onPress={() => navigation.navigate('MyApplications')}
        >
          <Text style={styles.orangeButtonText}>Postulaciones</Text>
        </TouchableOpacity>
        <TouchableOpacity 
          style={styles.orangeButton}
          onPress={() => navigation.navigate('Rewards')}
        >
          <Text style={styles.orangeButtonText}>Recompensas</Text>
        </TouchableOpacity>
      </View>
      </ScrollView>
    </View>
  );
};

const { width } = Dimensions.get('window');
const CARD_WIDTH = width * 0.7;

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#fff' },
  scrollContent: { padding: 20 },
  centered: { justifyContent: 'center', alignItems: 'center' },
  loadingText: { marginTop: 10, color: '#666' },
  orangeButtonText: { color: '#333', fontWeight: 'bold', textAlign: 'center' },

  // Carousel Styles
  carouselContainer: {
    marginBottom: 20,
  },
  carouselTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 12,
  },
  carouselContent: {
    paddingRight: 20,
  },
  carouselCard: {
    width: CARD_WIDTH,
    marginRight: 15,
    backgroundColor: '#fff',
    borderRadius: 12,
    overflow: 'hidden',
    elevation: 3,
    boxShadow: '0px 2px 8px rgba(0, 0, 0, 0.1)',
  },
  carouselImage: {
    width: '100%',
    height: 150,
  },
  carouselCardContent: {
    padding: 12,
  },
  carouselCardTitle: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 6,
  },
  carouselCardLocation: {
    fontSize: 14,
    color: '#666',
  },
  noPrograms: {
    fontSize: 14,
    color: '#999',
    fontStyle: 'italic',
  },
  redButton: { 
    backgroundColor: '#E52224', 
    borderRadius: 10, 
    padding: 15, 
    marginBottom: 10 
  },
  purpleButton: { 
    backgroundColor: '#6C4AA0', 
    borderRadius: 10, 
    padding: 15, 
    marginBottom: 10 
  },
  buttonText: { 
    color: '#fff', 
    fontWeight: 'bold', 
    textAlign: 'center' 
  },
  infoRow: { 
    flexDirection: 'row', 
    justifyContent: 'space-between', 
    marginBottom: 10 
  },
  infoCard: { 
    backgroundColor: '#4FC3F7', 
    borderRadius: 10, 
    padding: 15, 
    flex: 1, 
    marginHorizontal: 5 
  },
  infoTitle: { 
    fontWeight: 'bold', 
    color: '#fff', 
    marginBottom: 8 
  },
  infoContent: { 
    color: '#fff' 
  },
  tapToRedeemText: { 
    color: '#fff', 
    fontSize: 12, 
    fontStyle: 'italic', 
    marginTop: 5 
  },
  orangeRow: { 
    flexDirection: 'row', 
    justifyContent: 'space-between', 
    marginBottom: 20 
  },
  orangeButton: { 
    backgroundColor: '#F8B400', 
    borderRadius: 10, 
    padding: 15, 
    flex: 1, 
    marginHorizontal: 5, 
    alignItems: 'center' 
  },
  programsList: { 
    marginTop: 5 
  },
  programItem: { 
    flexDirection: 'row', 
    alignItems: 'center', 
    backgroundColor: 'rgba(255, 255, 255, 0.2)',
    borderRadius: 5,
    marginBottom: 5,
    padding: 8
  },
  programStatusDot: {
    width: 15,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 5
  },
  statusDot: {
    width: 8,
    height: 8,
    borderRadius: 4
  },
  programItemContent: {
    flex: 1
  },
  programName: { 
    color: '#fff',
    fontWeight: 'bold',
    fontSize: 12
  },
  programStatus: {
    color: '#E6F7FF',
    fontSize: 10
  },
  viewDetails: {
    color: '#fff',
    fontWeight: 'bold',
    fontSize: 16,
    marginLeft: 3
  }
});

export default HomeScreen; 