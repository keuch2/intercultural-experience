import React, { useState, useEffect, useCallback } from 'react';
import {
  View,
  Text,
  ScrollView,
  StyleSheet,
  TouchableOpacity,
  RefreshControl,
  Alert,
  ActivityIndicator,
} from 'react-native';
import { useNavigation } from '@react-navigation/native';
import { Ionicons } from '@expo/vector-icons';
import { rewardService } from '../services/api';
import { Redemption } from '../types';

export default function MyRedemptionsScreen() {
  const navigation = useNavigation();
  const [redemptions, setRedemptions] = useState<Redemption[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const loadRedemptions = useCallback(async () => {
    try {
      const data = await rewardService.getMyRedemptions();
      setRedemptions(data);
    } catch (error) {
      console.error('Error loading redemptions:', error);
      Alert.alert('Error', 'No se pudieron cargar tus canjes');
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, []);

  useEffect(() => {
    loadRedemptions();
  }, [loadRedemptions]);

  const handleRefresh = () => {
    setRefreshing(true);
    loadRedemptions();
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'pending':
        return '#FF9800';
      case 'approved':
        return '#4CAF50';
      case 'rejected':
        return '#F44336';
      case 'delivered':
        return '#2196F3';
      default:
        return '#666';
    }
  };

  const getStatusText = (status: string) => {
    switch (status) {
      case 'pending':
        return 'Pendiente';
      case 'approved':
        return 'Aprobado';
      case 'rejected':
        return 'Rechazado';
      case 'delivered':
        return 'Entregado';
      default:
        return status;
    }
  };

  const getStatusIcon = (status: string) => {
    switch (status) {
      case 'pending':
        return 'hourglass';
      case 'approved':
        return 'checkmark-circle';
      case 'rejected':
        return 'close-circle';
      case 'delivered':
        return 'cube';
      default:
        return 'help-circle';
    }
  };

  const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('es-ES', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  };

  const handleRedemptionPress = (redemption: Redemption) => {
    navigation.navigate('RedemptionDetail' as never, { redemption } as never);
  };

  const renderRedemptionCard = (redemption: Redemption) => {
    const statusColor = getStatusColor(redemption.status);
    
    return (
      <TouchableOpacity
        key={redemption.id}
        style={styles.redemptionCard}
        onPress={() => handleRedemptionPress(redemption)}
      >
        <View style={styles.cardHeader}>
          <View style={styles.rewardInfo}>
            <Text style={styles.rewardName} numberOfLines={1}>
              {redemption.reward.name}
            </Text>
            <Text style={styles.requestDate}>
              Solicitado: {formatDate(redemption.requested_at)}
            </Text>
          </View>
          
          <View style={[styles.statusBadge, { backgroundColor: statusColor }]}>
            <Ionicons 
              name={getStatusIcon(redemption.status)} 
              size={16} 
              color="white" 
            />
            <Text style={styles.statusText}>
              {getStatusText(redemption.status)}
            </Text>
          </View>
        </View>

        <View style={styles.cardBody}>
          <Text style={styles.rewardDescription} numberOfLines={2}>
            {redemption.reward.description}
          </Text>
          
          <View style={styles.pointsContainer}>
            <Ionicons name="diamond" size={16} color="#FFD700" />
            <Text style={styles.pointsCost}>
              {redemption.points_cost} puntos
            </Text>
          </View>
        </View>

        {redemption.status === 'approved' && (
          <View style={styles.cardFooter}>
            <Text style={styles.approvedText}>
              Aprobado: {redemption.resolved_at ? formatDate(redemption.resolved_at) : 'N/A'}
            </Text>
          </View>
        )}

        {redemption.status === 'delivered' && redemption.tracking_number && (
          <View style={styles.cardFooter}>
            <Text style={styles.trackingText}>
              Seguimiento: {redemption.tracking_number}
            </Text>
          </View>
        )}

        {redemption.status === 'rejected' && (
          <View style={styles.cardFooter}>
            <Text style={styles.rejectedText}>
              Rechazado: {redemption.resolved_at ? formatDate(redemption.resolved_at) : 'N/A'}
            </Text>
            {redemption.admin_notes && (
              <Text style={styles.adminNotes} numberOfLines={2}>
                Motivo: {redemption.admin_notes}
              </Text>
            )}
          </View>
        )}

        <View style={styles.cardArrow}>
          <Ionicons name="chevron-forward" size={20} color="#ccc" />
        </View>
      </TouchableOpacity>
    );
  };

  if (loading) {
    return (
      <View style={styles.loadingContainer}>
        <ActivityIndicator size="large" color="#007AFF" />
        <Text style={styles.loadingText}>Cargando canjes...</Text>
      </View>
    );
  }

  return (
    <View style={styles.container}>
      <ScrollView 
        refreshControl={
          <RefreshControl refreshing={refreshing} onRefresh={handleRefresh} />
        }
        showsVerticalScrollIndicator={false}
        contentContainerStyle={styles.scrollContent}
      >
        {redemptions.length > 0 ? (
          <>
            <View style={styles.summary}>
              <Text style={styles.summaryTitle}>Resumen de Canjes</Text>
              <View style={styles.summaryStats}>
                <View style={styles.statItem}>
                  <Text style={styles.statNumber}>
                    {redemptions.filter(r => r.status === 'pending').length}
                  </Text>
                  <Text style={styles.statLabel}>Pendientes</Text>
                </View>
                <View style={styles.statItem}>
                  <Text style={styles.statNumber}>
                    {redemptions.filter(r => r.status === 'approved').length}
                  </Text>
                  <Text style={styles.statLabel}>Aprobados</Text>
                </View>
                <View style={styles.statItem}>
                  <Text style={styles.statNumber}>
                    {redemptions.filter(r => r.status === 'delivered').length}
                  </Text>
                  <Text style={styles.statLabel}>Entregados</Text>
                </View>
              </View>
            </View>
            
            <View style={styles.redemptionsList}>
              {redemptions.map(renderRedemptionCard)}
            </View>
          </>
        ) : (
          <View style={styles.emptyState}>
            <Ionicons name="bag-outline" size={64} color="#ccc" />
            <Text style={styles.emptyTitle}>No tienes canjes</Text>
            <Text style={styles.emptySubtitle}>
              Ve a la sección de recompensas para empezar a canjear tus puntos
            </Text>
            <TouchableOpacity 
              style={styles.browseButton}
              onPress={() => navigation.navigate('Rewards' as never)}
            >
              <Text style={styles.browseButtonText}>Ver Recompensas</Text>
            </TouchableOpacity>
          </View>
        )}
      </ScrollView>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f5f5f5',
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#f5f5f5',
  },
  loadingText: {
    marginTop: 16,
    fontSize: 16,
    color: '#666',
  },
  scrollContent: {
    flexGrow: 1,
  },
  summary: {
    backgroundColor: 'white',
    padding: 20,
    marginBottom: 16,
  },
  summaryTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 16,
  },
  summaryStats: {
    flexDirection: 'row',
    justifyContent: 'space-around',
  },
  statItem: {
    alignItems: 'center',
  },
  statNumber: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#007AFF',
    marginBottom: 4,
  },
  statLabel: {
    fontSize: 12,
    color: '#666',
  },
  redemptionsList: {
    paddingHorizontal: 16,
  },
  redemptionCard: {
    backgroundColor: 'white',
    borderRadius: 12,
    padding: 16,
    marginBottom: 12,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
    position: 'relative',
  },
  cardHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
    marginBottom: 12,
  },
  rewardInfo: {
    flex: 1,
    marginRight: 12,
  },
  rewardName: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 4,
  },
  requestDate: {
    fontSize: 12,
    color: '#666',
  },
  statusBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 12,
  },
  statusText: {
    color: 'white',
    fontSize: 12,
    fontWeight: 'bold',
    marginLeft: 4,
  },
  cardBody: {
    marginBottom: 12,
  },
  rewardDescription: {
    fontSize: 14,
    color: '#666',
    marginBottom: 8,
    lineHeight: 20,
  },
  pointsContainer: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  pointsCost: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#333',
    marginLeft: 4,
  },
  cardFooter: {
    paddingTop: 12,
    borderTopWidth: 1,
    borderTopColor: '#f0f0f0',
  },
  approvedText: {
    fontSize: 12,
    color: '#4CAF50',
    fontWeight: '500',
  },
  trackingText: {
    fontSize: 12,
    color: '#2196F3',
    fontWeight: '500',
  },
  rejectedText: {
    fontSize: 12,
    color: '#F44336',
    fontWeight: '500',
    marginBottom: 4,
  },
  adminNotes: {
    fontSize: 12,
    color: '#666',
    fontStyle: 'italic',
  },
  cardArrow: {
    position: 'absolute',
    right: 16,
    top: '50%',
    transform: [{ translateY: -10 }],
  },
  emptyState: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    paddingHorizontal: 32,
  },
  emptyTitle: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#333',
    marginTop: 16,
    marginBottom: 8,
  },
  emptySubtitle: {
    fontSize: 16,
    color: '#666',
    textAlign: 'center',
    lineHeight: 24,
    marginBottom: 24,
  },
  browseButton: {
    backgroundColor: '#007AFF',
    paddingHorizontal: 24,
    paddingVertical: 12,
    borderRadius: 25,
  },
  browseButtonText: {
    color: 'white',
    fontSize: 16,
    fontWeight: '600',
  },
}); 