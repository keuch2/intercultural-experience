import { useCallback, useEffect, useRef } from 'react';
import { Linking } from 'react-native';
import * as Notifications from 'expo-notifications';
import { navigationRef } from '../navigation/navigationRef';
import { useOptionalProgram } from '../contexts/ProgramContext';
import { useTabNavigation } from '../contexts/NavigationContext';
import { notificationService } from '../services/api';
import { registerDeviceForPush } from '../services/pushNotifications';
import { refreshesProcess, resolveNotificationDestination } from '../utils/notificationTarget';

// Respuestas ya atendidas (la del arranque en frío también llega por el listener en algunos casos).
const handled = new Set<string>();

const waitForNavigation = async (tries = 20) => {
  for (let i = 0; i < tries && !navigationRef.isReady(); i++) {
    await new Promise(r => setTimeout(r, 150));
  }
  return navigationRef.isReady();
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
    const id = response.notification.request.identifier;
    if (handled.has(id)) return;
    handled.add(id);

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
