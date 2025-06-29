import React, { useState } from 'react';
import {
  View,
  Text,
  ScrollView,
  StyleSheet,
  TouchableOpacity,
  Image,
  Alert,
  Dimensions,
  ActivityIndicator,
} from 'react-native';
import { useNavigation, useRoute } from '@react-navigation/native';
import { Ionicons } from '@expo/vector-icons';
import { rewardService } from '../services/api';
import { Reward, PointsBalance } from '../types';

const { width } = Dimensions.get('window');

interface RouteParams {
  reward: Reward;
  pointsBalance: PointsBalance;
}

export default function RewardDetailScreen() {
  const navigation = useNavigation();
  const route = useRoute();
  const { reward, pointsBalance } = route.params as RouteParams;
  
  const [loading, setLoading] = useState(false);

  const canAfford = pointsBalance.total >= reward.cost;
  const hasStock = reward.stock === null || reward.stock > 0;
  const isAvailable = reward.status === 'active' && hasStock;
  
  const handleRedeem = async () => {
    if (!canAfford) {
      Alert.alert(
        'Puntos Insuficientes',
        `Necesitas ${reward.cost - pointsBalance.total} puntos más para canjear esta recompensa.`,
        [{ text: 'Entendido' }]
      );
      return;
    }

    if (!hasStock) {
      Alert.alert(
        'Sin Stock',
        'Esta recompensa no tiene stock disponible.',
        [{ text: 'Entendido' }]
      );
      return;
    }

    Alert.alert(
      'Confirmar Canje',
      `¿Estás seguro de que quieres canjear "${reward.name}" por ${reward.cost} puntos?`,
      [
        { text: 'Cancelar', style: 'cancel' },
        { text: 'Confirmar', onPress: confirmRedeem }
      ]
    );
  };

  const confirmRedeem = async () => {
    setLoading(true);
    
    try {
      const response = await rewardService.redeemReward(reward.id);
      
      Alert.alert(
        'Canje Exitoso',
        'Tu canje ha sido procesado exitosamente. Recibirás una notificación cuando sea aprobado.',
        [
          {
            text: 'Ver Mis Canjes',
            onPress: () => navigation.navigate('MyRedemptions' as never)
          },
          {
            text: 'Continuar',
            onPress: () => navigation.goBack()
          }
        ]
      );
    } catch (error: any) {
      console.error('Error redeeming reward:', error);
      
      let errorMessage = 'Ocurrió un error al procesar el canje.';
      if (error.response?.data?.message) {
        errorMessage = error.response.data.message;
      }
      
      Alert.alert('Error', errorMessage);
    } finally {
      setLoading(false);
    }
  };

  const renderAvailabilityBadge = () => {
    if (!isAvailable) {
      return (
        <View style={[styles.badge, styles.unavailableBadge]}>
          <Text style={styles.badgeText}>No Disponible</Text>
        </View>
      );
    }
    
    if (!canAfford) {
      return (
        <View style={[styles.badge, styles.warningBadge]}>
          <Text style={styles.badgeText}>Puntos Insuficientes</Text>
        </View>
      );
    }
    
    return (
      <View style={[styles.badge, styles.availableBadge]}>
        <Text style={styles.badgeText}>Disponible</Text>
      </View>
    );
  };

  return (
    <View style={styles.container}>
      <ScrollView showsVerticalScrollIndicator={false}>
        {/* Header Image */}
        <View style={styles.imageContainer}>
          {reward.image ? (
            <Image 
              source={{ uri: reward.image }} 
              style={styles.rewardImage}
              resizeMode="cover"
            />
          ) : (
            <View style={styles.placeholderImage}>
              <Ionicons name="gift" size={80} color="#ccc" />
            </View>
          )}
          
          {/* Back Button */}
          <TouchableOpacity 
            style={styles.backButton}
            onPress={() => navigation.goBack()}
          >
            <Ionicons name="arrow-back" size={24} color="white" />
          </TouchableOpacity>
          
          {/* Availability Badge */}
          <View style={styles.badgeContainer}>
            {renderAvailabilityBadge()}
          </View>
        </View>

        {/* Content */}
        <View style={styles.content}>
          {/* Title and Category */}
          <View style={styles.header}>
            <Text style={styles.title}>{reward.name}</Text>
            <Text style={styles.category}>{reward.category}</Text>
          </View>

          {/* Points Cost */}
          <View style={styles.costSection}>
            <View style={styles.costContainer}>
              <Ionicons 
                name="diamond" 
                size={24} 
                color={canAfford ? "#4CAF50" : "#F44336"} 
              />
              <Text style={[
                styles.cost,
                { color: canAfford ? "#4CAF50" : "#F44336" }
              ]}>
                {reward.cost} puntos
              </Text>
            </View>
            
            <View style={styles.balanceInfo}>
              <Text style={styles.balanceLabel}>Tus puntos:</Text>
              <Text style={styles.balanceValue}>{pointsBalance.total}</Text>
            </View>
          </View>

          {/* Stock Information */}
          {reward.stock !== null && (
            <View style={styles.stockSection}>
              <Ionicons 
                name="cube" 
                size={16} 
                color={hasStock ? "#4CAF50" : "#F44336"} 
              />
              <Text style={styles.stockText}>
                Stock disponible: {reward.stock}
              </Text>
            </View>
          )}

          {/* Description */}
          <View style={styles.descriptionSection}>
            <Text style={styles.sectionTitle}>Descripción</Text>
            <Text style={styles.description}>{reward.description}</Text>
          </View>

          {/* Terms and Conditions */}
          <View style={styles.termsSection}>
            <Text style={styles.sectionTitle}>Términos y Condiciones</Text>
            <Text style={styles.termsText}>
              • Este canje no es reembolsable una vez procesado{'\n'}
              • La entrega puede tomar entre 5-10 días hábiles{'\n'}
              • Stock sujeto a disponibilidad{'\n'}
              • Aplican términos y condiciones generales del programa
            </Text>
          </View>

          {/* Action Button */}
          <TouchableOpacity
            style={[
              styles.redeemButton,
              (!isAvailable || !canAfford) && styles.disabledButton
            ]}
            onPress={handleRedeem}
            disabled={loading || !isAvailable || !canAfford}
          >
            {loading ? (
              <ActivityIndicator color="white" />
            ) : (
              <>
                <Ionicons name="gift" size={20} color="white" />
                <Text style={styles.redeemButtonText}>
                  {!isAvailable 
                    ? 'No Disponible'
                    : !canAfford 
                      ? 'Puntos Insuficientes'
                      : 'Canjear Ahora'
                  }
                </Text>
              </>
            )}
          </TouchableOpacity>

          {/* Points Needed */}
          {!canAfford && isAvailable && (
            <View style={styles.pointsNeededContainer}>
              <Text style={styles.pointsNeededText}>
                Necesitas {reward.cost - pointsBalance.total} puntos más
              </Text>
              <TouchableOpacity 
                style={styles.earnPointsButton}
                onPress={() => navigation.navigate('Programs' as never)}
              >
                <Text style={styles.earnPointsText}>Ver Programas</Text>
              </TouchableOpacity>
            </View>
          )}
        </View>
      </ScrollView>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f5f5f5',
  },
  imageContainer: {
    position: 'relative',
    height: 250,
  },
  rewardImage: {
    width: '100%',
    height: '100%',
  },
  placeholderImage: {
    width: '100%',
    height: '100%',
    backgroundColor: '#e0e0e0',
    justifyContent: 'center',
    alignItems: 'center',
  },
  backButton: {
    position: 'absolute',
    top: 50,
    left: 16,
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: 'rgba(0, 0, 0, 0.5)',
    justifyContent: 'center',
    alignItems: 'center',
  },
  badgeContainer: {
    position: 'absolute',
    top: 50,
    right: 16,
  },
  badge: {
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 15,
  },
  availableBadge: {
    backgroundColor: '#4CAF50',
  },
  unavailableBadge: {
    backgroundColor: '#F44336',
  },
  warningBadge: {
    backgroundColor: '#FF9800',
  },
  badgeText: {
    color: 'white',
    fontSize: 12,
    fontWeight: 'bold',
  },
  content: {
    flex: 1,
    backgroundColor: 'white',
    borderTopLeftRadius: 20,
    borderTopRightRadius: 20,
    marginTop: -20,
    paddingTop: 24,
    paddingHorizontal: 20,
  },
  header: {
    marginBottom: 20,
  },
  title: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 4,
  },
  category: {
    fontSize: 16,
    color: '#666',
    textTransform: 'capitalize',
  },
  costSection: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 20,
    padding: 16,
    backgroundColor: '#f8f9fa',
    borderRadius: 12,
  },
  costContainer: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  cost: {
    fontSize: 20,
    fontWeight: 'bold',
    marginLeft: 8,
  },
  balanceInfo: {
    alignItems: 'flex-end',
  },
  balanceLabel: {
    fontSize: 12,
    color: '#666',
    marginBottom: 2,
  },
  balanceValue: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#333',
  },
  stockSection: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 20,
    padding: 12,
    backgroundColor: '#f0f8ff',
    borderRadius: 8,
  },
  stockText: {
    marginLeft: 8,
    fontSize: 14,
    color: '#333',
  },
  descriptionSection: {
    marginBottom: 24,
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 12,
  },
  description: {
    fontSize: 16,
    color: '#666',
    lineHeight: 24,
  },
  termsSection: {
    marginBottom: 32,
  },
  termsText: {
    fontSize: 14,
    color: '#666',
    lineHeight: 20,
  },
  redeemButton: {
    flexDirection: 'row',
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#007AFF',
    paddingVertical: 16,
    borderRadius: 12,
    marginBottom: 16,
  },
  disabledButton: {
    backgroundColor: '#ccc',
  },
  redeemButtonText: {
    color: 'white',
    fontSize: 16,
    fontWeight: 'bold',
    marginLeft: 8,
  },
  pointsNeededContainer: {
    alignItems: 'center',
    padding: 16,
    backgroundColor: '#fff3cd',
    borderRadius: 12,
    marginBottom: 20,
  },
  pointsNeededText: {
    fontSize: 14,
    color: '#856404',
    marginBottom: 8,
  },
  earnPointsButton: {
    paddingHorizontal: 16,
    paddingVertical: 8,
    backgroundColor: '#007AFF',
    borderRadius: 20,
  },
  earnPointsText: {
    color: 'white',
    fontSize: 14,
    fontWeight: '500',
  },
}); 