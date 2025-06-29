import apiClient from './apiClient';
import { Reward, Redemption, PointsBalance, ApiResponse } from '../../types';

export const rewardService = {
  // Obtener todas las recompensas disponibles
  getRewards: async (): Promise<Reward[]> => {
    try {
      const response = await apiClient.get('/rewards');
      return response.data;
    } catch (error) {
      console.error('Error fetching rewards:', error);
      throw error;
    }
  },

  // Obtener una recompensa por ID
  getReward: async (id: number): Promise<Reward> => {
    try {
      const response = await apiClient.get(`/rewards/${id}`);
      return response.data;
    } catch (error) {
      console.error('Error fetching reward:', error);
      throw error;
    }
  },

  // Obtener recompensas por categoría
  getRewardsByCategory: async (category: string): Promise<Reward[]> => {
    try {
      const response = await apiClient.get(`/rewards?category=${encodeURIComponent(category)}`);
      return response.data;
    } catch (error) {
      console.error('Error fetching rewards by category:', error);
      throw error;
    }
  },

  // Obtener categorías de recompensas
  getRewardCategories: async (): Promise<string[]> => {
    try {
      const response = await apiClient.get('/rewards/categories');
      return response.data;
    } catch (error) {
      console.error('Error fetching reward categories:', error);
      // Fallback con categorías por defecto
      return ['Electrónicos', 'Viajes', 'Educación', 'Entretenimiento', 'Otros'];
    }
  },

  // Canjear una recompensa
  redeemReward: async (rewardId: number): Promise<ApiResponse<Redemption>> => {
    try {
      const response = await apiClient.post('/redemptions', {
        reward_id: rewardId
      });
      return response.data;
    } catch (error) {
      console.error('Error redeeming reward:', error);
      throw error;
    }
  },

  // Obtener historial de canjes del usuario
  getMyRedemptions: async (): Promise<Redemption[]> => {
    try {
      const response = await apiClient.get('/redemptions');
      return response.data;
    } catch (error) {
      console.error('Error fetching redemptions:', error);
      throw error;
    }
  },

  // Obtener un canje específico
  getRedemption: async (id: number): Promise<Redemption> => {
    try {
      const response = await apiClient.get(`/redemptions/${id}`);
      return response.data;
    } catch (error) {
      console.error('Error fetching redemption:', error);
      throw error;
    }
  },

  // Obtener balance de puntos del usuario
  getPointsBalance: async (): Promise<PointsBalance> => {
    try {
      const response = await apiClient.get('/points/balance');
      return response.data;
    } catch (error) {
      console.error('Error fetching points balance:', error);
      throw error;
    }
  },

  // Obtener historial de puntos del usuario
  getPointsHistory: async (): Promise<any[]> => {
    try {
      const response = await apiClient.get('/points/history');
      return response.data;
    } catch (error) {
      console.error('Error fetching points history:', error);
      throw error;
    }
  },

  // Verificar si el usuario tiene suficientes puntos para un canje
  canRedeemReward: async (rewardId: number): Promise<{ canRedeem: boolean; reason?: string }> => {
    try {
      const [reward, balance] = await Promise.all([
        rewardService.getReward(rewardId),
        rewardService.getPointsBalance()
      ]);

      if (balance.total < reward.cost) {
        return {
          canRedeem: false,
          reason: `Necesitas ${reward.cost - balance.total} puntos más`
        };
      }

      if (reward.stock !== null && reward.stock <= 0) {
        return {
          canRedeem: false,
          reason: 'No hay stock disponible'
        };
      }

      if (reward.status !== 'active') {
        return {
          canRedeem: false,
          reason: 'Esta recompensa no está disponible'
        };
      }

      return { canRedeem: true };
    } catch (error) {
      console.error('Error checking redemption eligibility:', error);
      return {
        canRedeem: false,
        reason: 'Error verificando elegibilidad'
      };
    }
  }
};

export default rewardService; 