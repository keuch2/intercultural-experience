import apiClient from './apiClient';

export interface NotificationCategoryPreference {
  key: string;
  label: string;
  description: string;
  icon: string;
  push_enabled: boolean;
  /** Avisos que llegan igual aunque la categoría esté silenciada. */
  mandatory: string[];
  available: boolean;
}

export interface NotificationPreferences {
  categories: NotificationCategoryPreference[];
  note: string;
}

export const notificationPreferencesService = {
  get: async (): Promise<NotificationPreferences> => (await apiClient.get('/me/notification-preferences')).data.data,
  save: async (preferences: Record<string, boolean>): Promise<NotificationPreferences> =>
    (await apiClient.put('/me/notification-preferences', { preferences })).data.data,
};
