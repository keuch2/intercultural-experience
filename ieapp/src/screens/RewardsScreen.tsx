import React, { useState, useEffect, useCallback } from 'react';
import {
  View,
  Text,
  ScrollView,
  StyleSheet,
  TouchableOpacity,
  Image,
  RefreshControl,
  Alert,
  Dimensions,
  ActivityIndicator,
} from 'react-native';
import { useNavigation } from '@react-navigation/native';
import { Ionicons } from '@expo/vector-icons';
import { rewardService } from '../services/api';
import { Reward, PointsBalance } from '../types';

const { width } = Dimensions.get('window');
const ITEM_WIDTH = (width - 48) / 2; // 2 columnas con padding

export default function RewardsScreen() {
  const navigation = useNavigation();
  const [rewards, setRewards] = useState<Reward[]>([]);
  const [categories, setCategories] = useState<string[]>([]);
  const [selectedCategory, setSelectedCategory] = useState<string>('');
  const [pointsBalance, setPointsBalance] = useState<PointsBalance | null>(null);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const loadData = useCallback(async () => {
    try {
      const [rewardsData, categoriesData, balanceData] = await Promise.all([
        rewardService.getRewards(),
        rewardService.getRewardCategories(),
        rewardService.getPointsBalance()
      ]);

      setRewards(rewardsData);
      setCategories(categoriesData);
      setPointsBalance(balanceData);
    } catch (error) {
      console.error('Error loading rewards data:', error);
      Alert.alert('Error', 'No se pudieron cargar las recompensas');
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

  const handleCategorySelect = async (category: string) => {
    setSelectedCategory(category);
    setLoading(true);
    
    try {
      const filteredRewards = category 
        ? await rewardService.getRewardsByCategory(category)
        : await rewardService.getRewards();
      setRewards(filteredRewards);
    } catch (error) {
      console.error('Error filtering rewards:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleRewardPress = (reward: Reward) => {
    navigation.navigate('RewardDetail' as never, { reward, pointsBalance } as never);
  };

  const canAfford = (cost: number) => {
    return pointsBalance ? pointsBalance.total >= cost : false;
  };

  const renderCategoryTabs = () => (
    <ScrollView 
      horizontal 
      showsHorizontalScrollIndicator={false}
      style={styles.categoryContainer}
      contentContainerStyle={styles.categoryContent}
    >
      <TouchableOpacity
        style={[
          styles.categoryTab,
          selectedCategory === '' && styles.selectedCategoryTab
        ]}
        onPress={() => handleCategorySelect('')}
      >
        <Text style={[
          styles.categoryText,
          selectedCategory === '' && styles.selectedCategoryText
        ]}>
          Todas
        </Text>
      </TouchableOpacity>
      
      {categories.map((category) => (
        <TouchableOpacity
          key={category}
          style={[
            styles.categoryTab,
            selectedCategory === category && styles.selectedCategoryTab
          ]}
          onPress={() => handleCategorySelect(category)}
        >
          <Text style={[
            styles.categoryText,
            selectedCategory === category && styles.selectedCategoryText
          ]}>
            {category}
          </Text>
        </TouchableOpacity>
      ))}
    </ScrollView>
  );

  const renderRewardCard = (reward: Reward) => {
    const affordable = canAfford(reward.cost);
    const hasStock = reward.stock === null || reward.stock > 0;
    const isAvailable = reward.status === 'active' && hasStock;

    return (
      <TouchableOpacity
        key={reward.id}
        style={[
          styles.rewardCard,
          !isAvailable && styles.unavailableCard
        ]}
        onPress={() => handleRewardPress(reward)}
        disabled={!isAvailable}
      >
        <View style={styles.rewardImageContainer}>
          {reward.image ? (
            <Image 
              source={{ uri: reward.image }} 
              style={styles.rewardImage}
              resizeMode="cover"
            />
          ) : (
            <View style={styles.placeholderImage}>
              <Ionicons name="gift" size={40} color="#ccc" />
            </View>
          )}
          
          {!hasStock && (
            <View style={styles.stockBadge}>
              <Text style={styles.stockBadgeText}>Sin Stock</Text>
            </View>
          )}
        </View>

        <View style={styles.rewardInfo}>
          <Text style={styles.rewardName} numberOfLines={2}>
            {reward.name}
          </Text>
          
          <Text style={styles.rewardDescription} numberOfLines={2}>
            {reward.description}
          </Text>

          <View style={styles.rewardFooter}>
            <View style={styles.costContainer}>
              <Ionicons 
                name="diamond" 
                size={16} 
                color={affordable ? "#4CAF50" : "#F44336"} 
              />
              <Text style={[
                styles.rewardCost,
                { color: affordable ? "#4CAF50" : "#F44336" }
              ]}>
                {reward.cost}
              </Text>
            </View>

            {reward.stock !== null && (
              <Text style={styles.stockText}>
                Stock: {reward.stock}
              </Text>
            )}
          </View>
        </View>
      </TouchableOpacity>
    );
  };

  const renderPointsHeader = () => (
    <View style={styles.pointsHeader}>
      <View style={styles.pointsInfo}>
        <Text style={styles.pointsLabel}>Mis Puntos</Text>
        <View style={styles.pointsValue}>
          <Ionicons name="diamond" size={24} color="#FFD700" />
          <Text style={styles.pointsNumber}>
            {pointsBalance?.total || 0}
          </Text>
        </View>
      </View>
      
      <TouchableOpacity 
        style={styles.historyButton}
        onPress={() => navigation.navigate('PointsHistory' as never)}
      >
        <Ionicons name="time" size={20} color="#666" />
        <Text style={styles.historyText}>Historial</Text>
      </TouchableOpacity>
    </View>
  );

  if (loading && !refreshing) {
    return (
      <View style={styles.loadingContainer}>
        <ActivityIndicator size="large" color="#007AFF" />
        <Text style={styles.loadingText}>Cargando recompensas...</Text>
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
        {renderPointsHeader()}
        {renderCategoryTabs()}
        
        <View style={styles.rewardsGrid}>
          {rewards.length > 0 ? (
            rewards.map(renderRewardCard)
          ) : (
            <View style={styles.emptyState}>
              <Ionicons name="gift-outline" size={64} color="#ccc" />
              <Text style={styles.emptyTitle}>No hay recompensas disponibles</Text>
              <Text style={styles.emptySubtitle}>
                {selectedCategory 
                  ? `No se encontraron recompensas en la categoría "${selectedCategory}"`
                  : 'Pronto habrá nuevas recompensas disponibles'
                }
              </Text>
            </View>
          )}
        </View>
      </ScrollView>

      {/* Botón flotante para ver mis canjes */}
      <TouchableOpacity 
        style={styles.floatingButton}
        onPress={() => navigation.navigate('MyRedemptions' as never)}
      >
        <Ionicons name="bag" size={24} color="white" />
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
  pointsHeader: {
    backgroundColor: 'white',
    padding: 20,
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  pointsInfo: {
    flex: 1,
  },
  pointsLabel: {
    fontSize: 14,
    color: '#666',
    marginBottom: 4,
  },
  pointsValue: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  pointsNumber: {
    fontSize: 28,
    fontWeight: 'bold',
    color: '#333',
    marginLeft: 8,
  },
  historyButton: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 8,
    paddingHorizontal: 12,
    backgroundColor: '#f0f0f0',
    borderRadius: 20,
  },
  historyText: {
    marginLeft: 4,
    fontSize: 14,
    color: '#666',
  },
  categoryContainer: {
    backgroundColor: 'white',
    paddingVertical: 16,
  },
  categoryContent: {
    paddingHorizontal: 16,
  },
  categoryTab: {
    paddingHorizontal: 20,
    paddingVertical: 8,
    marginRight: 12,
    backgroundColor: '#f0f0f0',
    borderRadius: 20,
  },
  selectedCategoryTab: {
    backgroundColor: '#007AFF',
  },
  categoryText: {
    fontSize: 14,
    color: '#666',
    fontWeight: '500',
  },
  selectedCategoryText: {
    color: 'white',
  },
  rewardsGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    padding: 16,
    justifyContent: 'space-between',
  },
  rewardCard: {
    width: ITEM_WIDTH,
    backgroundColor: 'white',
    borderRadius: 12,
    marginBottom: 16,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  unavailableCard: {
    opacity: 0.6,
  },
  rewardImageContainer: {
    position: 'relative',
    height: 120,
    borderTopLeftRadius: 12,
    borderTopRightRadius: 12,
    overflow: 'hidden',
  },
  rewardImage: {
    width: '100%',
    height: '100%',
  },
  placeholderImage: {
    width: '100%',
    height: '100%',
    backgroundColor: '#f0f0f0',
    justifyContent: 'center',
    alignItems: 'center',
  },
  stockBadge: {
    position: 'absolute',
    top: 8,
    right: 8,
    backgroundColor: 'rgba(244, 67, 54, 0.9)',
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 12,
  },
  stockBadgeText: {
    color: 'white',
    fontSize: 10,
    fontWeight: 'bold',
  },
  rewardInfo: {
    padding: 12,
  },
  rewardName: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 4,
  },
  rewardDescription: {
    fontSize: 12,
    color: '#666',
    marginBottom: 8,
  },
  rewardFooter: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  costContainer: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  rewardCost: {
    fontSize: 16,
    fontWeight: 'bold',
    marginLeft: 4,
  },
  stockText: {
    fontSize: 12,
    color: '#666',
  },
  emptyState: {
    width: '100%',
    alignItems: 'center',
    paddingVertical: 64,
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