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
import { PointsBalance } from '../types';

interface PointTransaction {
  id: number;
  change: number;
  reason: string;
  created_at: string;
  related_id?: number;
}

export default function PointsHistoryScreen() {
  const navigation = useNavigation();
  const [transactions, setTransactions] = useState<PointTransaction[]>([]);
  const [pointsBalance, setPointsBalance] = useState<PointsBalance | null>(null);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const loadData = useCallback(async () => {
    try {
      const [historyData, balanceData] = await Promise.all([
        rewardService.getPointsHistory(),
        rewardService.getPointsBalance()
      ]);

      setTransactions(historyData.data || historyData);
      setPointsBalance(balanceData);
    } catch (error) {
      console.error('Error loading points data:', error);
      Alert.alert('Error', 'No se pudo cargar el historial de puntos');
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, []);

  useEffect(() => {
    loadData();
  }, [loadData]);

  const handleRefresh = () => {
    setRefreshing(true);
    loadData();
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

  const getTransactionIcon = (change: number, reason: string) => {
    if (change > 0) {
      if (reason.toLowerCase().includes('programa')) return 'school';
      if (reason.toLowerCase().includes('documento')) return 'document';
      if (reason.toLowerCase().includes('registro')) return 'person-add';
      if (reason.toLowerCase().includes('referir')) return 'people';
      if (reason.toLowerCase().includes('webinar')) return 'videocam';
      return 'arrow-up-circle';
    } else {
      return 'arrow-down-circle';
    }
  };

  const getTransactionColor = (change: number) => {
    return change > 0 ? '#4CAF50' : '#F44336';
  };

  const renderTransaction = (transaction: PointTransaction) => {
    const isPositive = transaction.change > 0;
    const color = getTransactionColor(transaction.change);
    const icon = getTransactionIcon(transaction.change, transaction.reason);

    return (
      <View key={transaction.id} style={styles.transactionCard}>
        <View style={styles.transactionIcon}>
          <View style={[styles.iconContainer, { backgroundColor: color + '20' }]}>
            <Ionicons name={icon} size={20} color={color} />
          </View>
        </View>

        <View style={styles.transactionContent}>
          <Text style={styles.transactionReason} numberOfLines={2}>
            {transaction.reason}
          </Text>
          <Text style={styles.transactionDate}>
            {formatDate(transaction.created_at)}
          </Text>
        </View>

        <View style={styles.transactionAmount}>
          <Text style={[styles.amountText, { color }]}>
            {isPositive ? '+' : ''}{transaction.change}
          </Text>
          <Text style={styles.pointsLabel}>puntos</Text>
        </View>
      </View>
    );
  };

  const renderBalanceCard = () => (
    <View style={styles.balanceCard}>
      <View style={styles.balanceHeader}>
        <Text style={styles.balanceTitle}>Balance Actual</Text>
        <TouchableOpacity 
          style={styles.backButton}
          onPress={() => navigation.goBack()}
        >
          <Ionicons name="close" size={24} color="#666" />
        </TouchableOpacity>
      </View>

      <View style={styles.balanceMain}>
        <View style={styles.totalBalance}>
          <Ionicons name="diamond" size={32} color="#FFD700" />
          <Text style={styles.totalPoints}>
            {pointsBalance?.total || 0}
          </Text>
          <Text style={styles.totalLabel}>Puntos Totales</Text>
        </View>
      </View>

      <View style={styles.balanceBreakdown}>
        <View style={styles.breakdownItem}>
          <Text style={styles.breakdownValue}>
            +{pointsBalance?.earned || 0}
          </Text>
          <Text style={styles.breakdownLabel}>Ganados</Text>
        </View>
        
        <View style={styles.breakdownItem}>
          <Text style={[styles.breakdownValue, { color: '#F44336' }]}>
            -{pointsBalance?.spent || 0}
          </Text>
          <Text style={styles.breakdownLabel}>Gastados</Text>
        </View>
        
        <View style={styles.breakdownItem}>
          <Text style={[styles.breakdownValue, { color: '#FF9800' }]}>
            {pointsBalance?.pending || 0}
          </Text>
          <Text style={styles.breakdownLabel}>Pendientes</Text>
        </View>
      </View>
    </View>
  );

  if (loading) {
    return (
      <View style={styles.loadingContainer}>
        <ActivityIndicator size="large" color="#007AFF" />
        <Text style={styles.loadingText}>Cargando historial...</Text>
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
      >
        {renderBalanceCard()}
        
        <View style={styles.historySection}>
          <Text style={styles.sectionTitle}>Historial de Transacciones</Text>
          
          {transactions.length > 0 ? (
            <View style={styles.transactionsList}>
              {transactions.map(renderTransaction)}
            </View>
          ) : (
            <View style={styles.emptyState}>
              <Ionicons name="time-outline" size={64} color="#ccc" />
              <Text style={styles.emptyTitle}>No hay transacciones</Text>
              <Text style={styles.emptySubtitle}>
                Cuando realices actividades que generen puntos, aparecerán aquí
              </Text>
            </View>
          )}
        </View>
      </ScrollView>

      {/* Botón flotante para ir a recompensas */}
      <TouchableOpacity 
        style={styles.floatingButton}
        onPress={() => navigation.navigate('Rewards' as never)}
      >
        <Ionicons name="gift" size={24} color="white" />
      </TouchableOpacity>
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
  balanceCard: {
    backgroundColor: 'white',
    margin: 16,
    borderRadius: 16,
    padding: 20,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.1,
    shadowRadius: 8,
    elevation: 6,
  },
  balanceHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 20,
  },
  balanceTitle: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#333',
  },
  backButton: {
    padding: 4,
  },
  balanceMain: {
    alignItems: 'center',
    marginBottom: 24,
  },
  totalBalance: {
    alignItems: 'center',
  },
  totalPoints: {
    fontSize: 36,
    fontWeight: 'bold',
    color: '#333',
    marginTop: 8,
    marginBottom: 4,
  },
  totalLabel: {
    fontSize: 16,
    color: '#666',
  },
  balanceBreakdown: {
    flexDirection: 'row',
    justifyContent: 'space-around',
    paddingTop: 16,
    borderTopWidth: 1,
    borderTopColor: '#f0f0f0',
  },
  breakdownItem: {
    alignItems: 'center',
  },
  breakdownValue: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#4CAF50',
    marginBottom: 4,
  },
  breakdownLabel: {
    fontSize: 12,
    color: '#666',
  },
  historySection: {
    flex: 1,
    paddingHorizontal: 16,
    paddingBottom: 100,
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 16,
  },
  transactionsList: {
    backgroundColor: 'white',
    borderRadius: 12,
    overflow: 'hidden',
  },
  transactionCard: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 16,
    borderBottomWidth: 1,
    borderBottomColor: '#f0f0f0',
  },
  transactionIcon: {
    marginRight: 12,
  },
  iconContainer: {
    width: 40,
    height: 40,
    borderRadius: 20,
    justifyContent: 'center',
    alignItems: 'center',
  },
  transactionContent: {
    flex: 1,
    marginRight: 12,
  },
  transactionReason: {
    fontSize: 16,
    fontWeight: '500',
    color: '#333',
    marginBottom: 4,
  },
  transactionDate: {
    fontSize: 12,
    color: '#666',
  },
  transactionAmount: {
    alignItems: 'flex-end',
  },
  amountText: {
    fontSize: 18,
    fontWeight: 'bold',
    marginBottom: 2,
  },
  pointsLabel: {
    fontSize: 12,
    color: '#666',
  },
  emptyState: {
    alignItems: 'center',
    paddingVertical: 48,
    paddingHorizontal: 32,
  },
  emptyTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#333',
    marginTop: 16,
    marginBottom: 8,
  },
  emptySubtitle: {
    fontSize: 14,
    color: '#666',
    textAlign: 'center',
    lineHeight: 20,
  },
  floatingButton: {
    position: 'absolute',
    bottom: 24,
    right: 24,
    width: 56,
    height: 56,
    borderRadius: 28,
    backgroundColor: '#007AFF',
    justifyContent: 'center',
    alignItems: 'center',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.3,
    shadowRadius: 6,
    elevation: 8,
  },
}); 