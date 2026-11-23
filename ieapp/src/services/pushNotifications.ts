import { Platform } from 'react-native';
import * as Notifications from 'expo-notifications';
import * as Device from 'expo-device';
import Constants from 'expo-constants';
import AsyncStorage from '@react-native-async-storage/async-storage';
import apiClient from './api/apiClient';

/**
 * Notificaciones push (Expo). El aviso siempre queda en Avisos; la push es un canal adicional.
 * - Canales de Android = categorías de preferencias del backend (NotificationCatalog::CATEGORIES).
 * - El token se registra en /me/device-tokens al iniciar sesión / abrir la app (si hay permiso)
 *   y se da de baja al cerrar sesión.
 */

const TOKEN_KEY = 'push.expoToken';

export const ANDROID_CHANNELS: { id: string; name: string }[] = [
  { id: 'process', name: 'Mi proceso' },
  { id: 'documents', name: 'Documentos' },
  { id: 'payments', name: 'Pagos' },
  { id: 'visa', name: 'Visa' },
  { id: 'job_offers', name: 'Ofertas laborales' },
  { id: 'support', name: 'Soporte' },
  { id: 'news', name: 'Novedades y promociones' },
];

// Con la app abierta la push también se muestra (banner + lista), además de actualizar la campana.
Notifications.setNotificationHandler({
  handleNotification: async () => ({
    shouldShowBanner: true,
    shouldShowList: true,
    shouldPlaySound: true,
    shouldSetBadge: false,
  }),
});

let channelsReady = false;
const ensureAndroidChannels = async () => {
  if (Platform.OS !== 'android' || channelsReady) return;
  await Promise.all(ANDROID_CHANNELS.map(c => Notifications.setNotificationChannelAsync(c.id, {
    name: c.name,
    importance: Notifications.AndroidImportance.HIGH,
    vibrationPattern: [0, 250, 250, 250],
    lightColor: '#E52224',
  })));
  channelsReady = true;
};

export type PushPermission = 'granted' | 'denied' | 'undetermined' | 'unsupported';

export const getPushPermission = async (): Promise<PushPermission> => {
  try {
    const { status } = await Notifications.getPermissionsAsync();
    return status as PushPermission;
  } catch {
    return 'unsupported';
  }
};

/** Pide el permiso al sistema (solo después de explicarle al participante para qué sirve). */
export const requestPushPermission = async (): Promise<PushPermission> => {
  await ensureAndroidChannels();
  const { status } = await Notifications.requestPermissionsAsync();
  return status as PushPermission;
};

const projectId = (): string | undefined =>
  (Constants as any)?.expoConfig?.extra?.eas?.projectId ?? (Constants as any)?.easConfig?.projectId;

/** Registra el dispositivo en el backend si ya hay permiso. No pide permiso. */
export const registerDeviceForPush = async (): Promise<string | null> => {
  try {
    // El token de Expo solo existe en un celular real (no en simuladores/emuladores).
    if (!Device.isDevice || (await getPushPermission()) !== 'granted') return null;
    await ensureAndroidChannels();
    const { data: token } = await Notifications.getExpoPushTokenAsync({ projectId: projectId() });
    await apiClient.post('/me/device-tokens', {
      token,
      platform: Platform.OS === 'ios' ? 'ios' : 'android',
      app_version: (Constants as any)?.expoConfig?.version ?? null,
      device_name: Device.modelName ?? null,
    });
    await AsyncStorage.setItem(TOKEN_KEY, token);
    return token;
  } catch (e) {
    // Expo Go en Android, simulador sin APNs, sin red…: la app funciona igual, solo sin push.
    if (__DEV__) console.log('[push] registro no disponible:', (e as any)?.message ?? e);
    return null;
  }
};

/** Token registrado en este dispositivo (para darlo de baja al cerrar sesión). */
export const storedPushToken = async (): Promise<string | null> => {
  try { return await AsyncStorage.getItem(TOKEN_KEY); } catch { return null; }
};

/** Baja del dispositivo: debe llamarse con la sesión todavía activa. */
export const unregisterDeviceForPush = async (): Promise<void> => {
  const token = await storedPushToken();
  if (!token) return;
  try { await apiClient.delete('/me/device-tokens', { data: { token } }); } catch {}
  try { await AsyncStorage.removeItem(TOKEN_KEY); } catch {}
};
