import React, { useCallback, useState } from 'react';
import {
  View, Text, StyleSheet, ScrollView, ActivityIndicator,
  TouchableOpacity, RefreshControl, Linking
} from 'react-native';
import { SafeAreaView } from '../components/SafeArea';
import { Ionicons } from '@expo/vector-icons';
import { useNavigation, useFocusEffect } from '@react-navigation/native';
import { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { notificationService } from '../services/api';
import { Notification } from '../types';
import EmptyState from '../components/EmptyState';
import { RootStackParamList } from '../navigation/AppNavigator';
import { useTabNavigation } from '../contexts/NavigationContext';
import { useOptionalProgram } from '../contexts/ProgramContext';
import { refreshesProcess, resolveNotificationDestination } from '../utils/notificationTarget';

type Nav = NativeStackNavigationProp<RootStackParamList>;

const ICON_BY_TYPE: Record<Notification['type'], { icon: keyof typeof import('@expo/vector-icons').Ionicons.glyphMap; color: string }> = {
  info: { icon: 'information-circle', color: '#3B82F6' },
  success: { icon: 'checkmark-circle', color: '#10B981' },
  warning: { icon: 'warning', color: '#F59E0B' },
  error: { icon: 'alert-circle', color: '#EF4444' },
};

const NotificationsScreen: React.FC = () => {
  const navigation = useNavigation<Nav>();
  const [notifications, setNotifications] = useState<Notification[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const load = useCallback(async () => {
    try {
      const data = await notificationService.getNotifications();
      const list = Array.isArray(data) ? data : ((data as any)?.data ?? []);
      setNotifications(list);
    } catch {
      setNotifications([]);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, []);

  useFocusEffect(useCallback(() => { load(); }, [load]));

  const { refreshUnread, setUnreadCount } = useTabNavigation();
  const program = useOptionalProgram();
  const flow = program?.flow ?? 'none';

  const markRead = async (n: Notification) => {
    if (!n.is_read) {
      setNotifications(prev => prev.map(x => x.id === n.id ? { ...x, is_read: true } : x));
      setUnreadCount(Math.max(0, notifications.filter(x => !x.is_read).length - 1));
      try { await notificationService.markAsRead(n.id); } catch {}
      refreshUnread(true);
    }
    // Avisos que cambian el estado del proceso (aprobación, etapa, fechas): se recarga antes de abrirlo.
    if (program && refreshesProcess(n.category)) {
      await program.refresh();
    }
    const destination = resolveNotificationDestination(flow, n.category, n.data);
    if (destination?.kind === 'url') Linking.openURL(destination.url).catch(() => {});
    else if (destination?.kind === 'screen') (navigation as any).navigate(destination.name, destination.params);
  };

  const markAll = async () => {
    try {
      await notificationService.markAllAsRead();
      setNotifications(prev => prev.map(x => ({ ...x, is_read: true })));
      setUnreadCount(0);
      refreshUnread(true);
    } catch {}
  };

  const unread = notifications.filter(n => !n.is_read).length;

  if (loading) {
    return <SafeAreaView style={styles.safe}><ActivityIndicator size="large" color="#E52224" style={{ marginTop: 80 }} /></SafeAreaView>;
  }

  return (
    <SafeAreaView style={styles.safe}>
      <View style={styles.headerBar}>
        <TouchableOpacity onPress={() => navigation.goBack()}>
          <Ionicons name="arrow-back" size={24} color="#222" />
        </TouchableOpacity>
        <Text style={styles.title}>Avisos</Text>
        <View style={styles.headerActions}>
          {unread > 0 && (
            <TouchableOpacity onPress={markAll}>
              <Text style={styles.markAll}>Leer todo</Text>
            </TouchableOpacity>
          )}
          <TouchableOpacity onPress={() => (navigation as any).navigate('NotificationSettings')} accessibilityLabel="Configurar notificaciones" testID="notifications-settings">
            <Ionicons name="settings-outline" size={22} color="#222" />
          </TouchableOpacity>
        </View>
      </View>

      <ScrollView
        contentContainerStyle={styles.scroll}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={() => { setRefreshing(true); load(); }} />}
      >
        {notifications.length === 0 ? (
          <EmptyState
            icon="notifications-off-outline"
            title="Sin avisos"
            message="Las novedades de tu proceso aparecerán acá."
          />
        ) : (
          notifications.map(n => {
            const cfg = ICON_BY_TYPE[n.type] || ICON_BY_TYPE.info;
            return (
              <TouchableOpacity
                key={n.id}
                style={[styles.card, !n.is_read && styles.cardUnread]}
                onPress={() => markRead(n)}
              >
                <Ionicons name={cfg.icon} size={22} color={cfg.color} />
                <View style={{ flex: 1, marginLeft: 10 }}>
                  <Text style={[styles.cardTitle, !n.is_read && styles.bold]}>{n.title}</Text>
                  <Text style={styles.message} numberOfLines={3}>{n.message}</Text>
                  <Text style={styles.date}>{new Date(n.created_at).toLocaleDateString('es-PY')}</Text>
                </View>
                {!n.is_read && <View style={styles.unreadDot} />}
              </TouchableOpacity>
            );
          })
        )}
      </ScrollView>
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: '#f4f4f5' },
  headerBar: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', padding: 14, backgroundColor: '#fff' },
  title: { fontSize: 17, fontWeight: '700' },
  headerActions: { flexDirection: 'row', alignItems: 'center', gap: 14, minWidth: 60, justifyContent: 'flex-end' },
  markAll: { color: '#E52224', fontWeight: '700', fontSize: 13 },
  scroll: { padding: 16, paddingBottom: 40 },
  card: { flexDirection: 'row', alignItems: 'flex-start', backgroundColor: '#fff', padding: 14, borderRadius: 10, marginBottom: 8 },
  cardUnread: { borderLeftWidth: 3, borderLeftColor: '#E52224' },
  cardTitle: { color: '#222', fontSize: 14, marginBottom: 2 },
  bold: { fontWeight: '700' },
  message: { color: '#555', fontSize: 13, lineHeight: 18 },
  date: { color: '#999', fontSize: 11, marginTop: 4 },
  unreadDot: { width: 8, height: 8, borderRadius: 4, backgroundColor: '#E52224', marginLeft: 6, marginTop: 4 },
});

export default NotificationsScreen;
