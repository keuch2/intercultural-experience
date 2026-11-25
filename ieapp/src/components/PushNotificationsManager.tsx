import { useCallback, useEffect, useRef } from 'react';
import { Alert, Linking } from 'react-native';
import * as Notifications from 'expo-notifications';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { navigationRef } from '../navigation/navigationRef';
import { useOptionalProgram } from '../contexts/ProgramContext';
import { useTabNavigation } from '../contexts/NavigationContext';
import { notificationService } from '../services/api';
import { getPushPermission, registerDeviceForPush } from '../services/pushNotifications';
import { refreshesProcess, resolveNotificationDestination } from '../utils/notificationTarget';

// Pushes ya atendidas, persistidas entre aperturas. Android vuelve a entregar el toque original
// (intent de la notificación) cada vez que se reabre la app desde recientes, y getLastNotificationResponse
// conserva la última respuesta: sin esto la app volvía a abrir Avisos en cada arranque.
const HANDLED_KEY = 'push.handledResponses';
const MAX_HANDLED = 50;
let handledPromise: Promise<string[]> | null = null;

const loadHandled = () => {
  handledPromise ??= AsyncStorage.getItem(HANDLED_KEY)
    .then(v => (v ? (JSON.parse(v) as string[]) : []))
    .catch(() => []);
  return handledPromise;
};

/** Un aviso del servidor se abre una sola vez; sin notification_id se usa el id de la push. */
const responseKey = (response: Notifications.NotificationResponse) => {
  const data: any = response.notification.request.content.data ?? {};
  return data.notification_id ? `n:${data.notification_id}` : `i:${response.notification.request.identifier}`;
};

const clearLastResponse = () => {
  try { Notifications.clearLastNotificationResponse(); } catch {}
};

const waitForNavigation = async (tries = 20) => {
  for (let i = 0; i < tries && !navigationRef.isReady(); i++) {
    await new Promise(r => setTimeout(r, 150));
  }
  return navigationRef.isReady();
};

// Invitación inicial: se ofrece una sola vez por dispositivo, la primera vez que se entra con sesión.
const INVITE_KEY = 'push.firstRunInviteShownAt';

const inviteToEnablePush = async () => {
  try {
    if (await AsyncStorage.getItem(INVITE_KEY)) return;
    if ((await getPushPermission()) === 'granted') return;
    await AsyncStorage.setItem(INVITE_KEY, String(Date.now()));
  } catch {
    return;
  }
  // Dejar que se pinte la pantalla inicial antes de la alerta.
  await new Promise(r => setTimeout(r, 1200));
  if (!(await waitForNavigation())) return;
  Alert.alert(
    '¿Activar notificaciones?',
    'Te avisamos al instante cuando IE revise tu postulación, tus documentos o tus pagos, y antes de tu cita de visa.',
    [
      { text: 'Ahora no', style: 'cancel' },
      { text: 'Activar', onPress: () => navigationRef.navigate('NotificationSettings', { prompt: true }) },
    ],
  );
};

/**
 * Montado con la sesión iniciada: registra el dispositivo (si ya hay permiso), actualiza la campana
 * cuando llega una push con la app abierta y, al tocar una push, marca el aviso como leído y abre su pantalla.
 */
const PushNotificationsManager: React.FC = () => {
  const program = useOptionalProgram();
  const { refreshUnread } = useTabNavigation();
  const programRef = useRef(program);
  programRef.current = program;

  const open = useCallback(async (response: Notifications.NotificationResponse) => {
    const key = responseKey(response);
    const handled = await loadHandled();
    clearLastResponse();
    if (handled.includes(key)) return;
    handled.push(key);
    handled.splice(0, Math.max(0, handled.length - MAX_HANDLED));
    AsyncStorage.setItem(HANDLED_KEY, JSON.stringify(handled)).catch(() => {});

    const data: any = response.notification.request.content.data ?? {};
    const notificationId = Number(data.notification_id);
    if (notificationId) notificationService.markAsRead(notificationId).catch(() => {});

    const ctx = programRef.current;
    if (ctx && refreshesProcess(data.category)) {
      try { await ctx.refresh(); } catch {}
    }
    const destination = resolveNotificationDestination(programRef.current?.flow ?? 'none', data.category, data);
    refreshUnread(true);

    if (destination?.kind === 'url') {
      Linking.openURL(destination.url).catch(() => {});
      return;
    }
    if (!(await waitForNavigation())) return;
    if (destination?.kind === 'screen') navigationRef.navigate(destination.name, destination.params);
    else navigationRef.navigate('Notifications');
  }, [refreshUnread]);

  useEffect(() => {
    registerDeviceForPush();
    inviteToEnablePush();

    Notifications.getLastNotificationResponseAsync().then(r => { if (r) open(r); }).catch(() => {});
    const tapSub = Notifications.addNotificationResponseReceivedListener(open);
    const receivedSub = Notifications.addNotificationReceivedListener(() => refreshUnread(true));
    return () => {
      tapSub.remove();
      receivedSub.remove();
    };
  }, [open, refreshUnread]);

  return null;
};

export default PushNotificationsManager;
