import apiClient from './apiClient';
import { Notification, ApiResponse } from '../../types';

export const notificationService = {
  // Obtener todas las notificaciones del usuario
  getNotifications: async (): Promise<Notification[]> => {
    try {
      const response = await apiClient.get('/notifications');
      return response.data;
    } catch (error) {
      console.error('Error fetching notifications:', error);
      throw error;
    }
  },

  // Obtener notificaciones no leídas
  getUnreadNotifications: async (): Promise<Notification[]> => {
    try {
      const response = await apiClient.get('/notifications?unread=true');
      return response.data;
    } catch (error) {
      console.error('Error fetching unread notifications:', error);
      throw error;
    }
  },

  // Obtener el conteo de notificaciones no leídas
  getUnreadCount: async (): Promise<number> => {
    try {
      const response = await apiClient.get('/notifications/unread-count');
      return response.data.count;
    } catch (error) {
      console.error('Error fetching unread count:', error);
      return 0;
    }
  },

  // Marcar una notificación como leída
  markAsRead: async (id: number): Promise<ApiResponse<Notification>> => {
    try {
      const response = await apiClient.patch(`/notifications/${id}/read`);
      return response.data;
    } catch (error) {
      console.error('Error marking notification as read:', error);
      throw error;
    }
  },

  // Marcar todas las notificaciones como leídas
  markAllAsRead: async (): Promise<ApiResponse<{ count: number }>> => {
    try {
      const response = await apiClient.patch('/notifications/mark-all-read');
      return response.data;
    } catch (error) {
      console.error('Error marking all notifications as read:', error);
      throw error;
    }
  },

  // Eliminar una notificación
  deleteNotification: async (id: number): Promise<ApiResponse<void>> => {
    try {
      const response = await apiClient.delete(`/notifications/${id}`);
      return response.data;
    } catch (error) {
      console.error('Error deleting notification:', error);
      throw error;
    }
  },

  // Obtener configuración de notificaciones del usuario
  getNotificationSettings: async (): Promise<any> => {
    try {
      const response = await apiClient.get('/notifications/settings');
      return response.data;
    } catch (error) {
      console.error('Error fetching notification settings:', error);
      throw error;
    }
  },

  // Actualizar configuración de notificaciones
  updateNotificationSettings: async (settings: any): Promise<ApiResponse<any>> => {
    try {
      const response = await apiClient.put('/notifications/settings', settings);
      return response.data;
    } catch (error) {
      console.error('Error updating notification settings:', error);
      throw error;
    }
  }
};

export default notificationService; 