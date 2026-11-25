import React, { useCallback, useEffect, useState } from 'react';
import { View, Text, StyleSheet, ScrollView, Switch, TouchableOpacity, ActivityIndicator, Linking, AppState, Alert } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useFocusEffect, useNavigation, useRoute } from '@react-navigation/native';
import { SafeAreaView } from '../components/SafeArea';
import { notificationPreferencesService, NotificationPreferences } from '../services/api/notificationPreferencesService';
import { getPushPermission, PushPermission, registerDeviceForPush, requestPushPermission } from '../services/pushNotifications';

/**
 * Perfil > Notificaciones: qué notificaciones push recibe el participante, por categoría.
 * Silenciar solo corta la push (el aviso sigue en Avisos); los obligatorios llegan igual.
 */
const NotificationSettingsScreen: React.FC = () => {
  const navigation = useNavigation<any>();
  const route = useRoute<any>();
  const [prefs, setPrefs] = useState<NotificationPreferences | null>(null);
  const [permission, setPermission] = useState<PushPermission>('undetermined');
  const [loading, setLoading] = useState(true);

  const loadPermission = useCallback(async () => setPermission(await getPushPermission()), []);

  useFocusEffect(useCallback(() => {
    let alive = true;
    loadPermission();
    notificationPreferencesService.get()
      .then(p => { if (alive) setPrefs(p); })
      .catch(() => { if (alive) Alert.alert('Notificaciones', 'No pudimos cargar tus preferencias. Probá de nuevo.'); })
      .finally(() => { if (alive) setLoading(false); });
    // Al volver de los Ajustes del sistema se actualiza el estado del permiso.
    const sub = AppState.addEventListener('change', s => { if (s === 'active') loadPermission(); });
    return () => { alive = false; sub.remove(); };
  }, [loadPermission]));

  const toggle = async (key: string, value: boolean) => {
    if (!prefs) return;
    const previous = prefs;
    setPrefs({ ...prefs, categories: prefs.categories.map(c => c.key === key ? { ...c, push_enabled: value } : c) });
    try {
      setPrefs(await notificationPreferencesService.save({ [key]: value }));
    } catch {
      setPrefs(previous);
      Alert.alert('Notificaciones', 'No pudimos guardar el cambio. Revisá tu conexión.');
    }
  };

  const activate = async () => {
    const status = await requestPushPermission();
    setPermission(status);
    if (status === 'granted') registerDeviceForPush();
    else if (status === 'denied') Linking.openSettings();
  };

  // Desde la invitación inicial ("¿Activar notificaciones?") se pide el permiso del sistema al entrar.
  useEffect(() => {
    if (!route.params?.prompt) return;
    navigation.setParams({ prompt: undefined });
    (async () => {
      if ((await getPushPermission()) !== 'undetermined') return;
      const status = await requestPushPermission();
      setPermission(status);
      if (status === 'granted') registerDeviceForPush();
    })();
  }, [route.params?.prompt, navigation]);

  const osOff = permission === 'denied' || permission === 'undetermined';

  return (
    <SafeAreaView style={styles.safe}>
      <View style={styles.headerBar}>
        <TouchableOpacity onPress={() => navigation.goBack()} accessibilityLabel="Volver">
          <Ionicons name="arrow-back" size={24} color="#222" />
        </TouchableOpacity>
        <Text style={styles.title}>Notificaciones</Text>
        <View style={{ width: 24 }} />
      </View>

      {loading ? <ActivityIndicator size="large" color="#E52224" style={{ marginTop: 80 }} /> : (
        <ScrollView contentContainerStyle={styles.scroll}>
          {osOff && (
            <View style={styles.osCard} testID="push-os-off">
              <Ionicons name="notifications-off-outline" size={22} color="#B45309" />
              <View style={{ flex: 1, marginLeft: 10 }}>
                <Text style={styles.osTitle}>{permission === 'denied' ? 'Las notificaciones están desactivadas en tu celular' : 'Todavía no activaste las notificaciones'}</Text>
                <Text style={styles.osText}>Sin el permiso no te llegan notificaciones push; los avisos igual quedan en la sección Avisos.</Text>
                <TouchableOpacity onPress={activate} style={styles.osButton}>
                  <Text style={styles.osButtonText}>{permission === 'denied' ? 'Abrir ajustes' : 'Activar notificaciones'}</Text>
                </TouchableOpacity>
              </View>
            </View>
          )}
          {permission === 'unsupported' && (
            <Text style={styles.note}>Las notificaciones push solo llegan a un celular con la app instalada.</Text>
          )}

          {prefs?.categories.filter(c => c.available).map(c => (
            <View key={c.key} style={styles.row} testID={`pref-${c.key}`}>
              <View style={styles.rowIcon}><Ionicons name={(c.icon as any) || 'notifications-outline'} size={20} color="#E52224" /></View>
              <View style={{ flex: 1, marginRight: 8 }}>
                <Text style={styles.rowTitle}>{c.label}</Text>
                <Text style={styles.rowText}>{c.description}</Text>
                {c.mandatory.length > 0 && (
                  <View style={styles.lockRow}>
                    <Ionicons name="lock-closed" size={12} color="#888" />
                    <Text style={styles.lockText}>
                      {c.push_enabled ? 'Siempre activas' : 'Te van a llegar igual'}: {c.mandatory.join(', ').toLowerCase()}.
                    </Text>
                  </View>
                )}
              </View>
              <Switch
                value={c.push_enabled}
                onValueChange={v => toggle(c.key, v)}
                trackColor={{ true: '#E52224', false: '#ccc' }}
                thumbColor="#fff"
                testID={`pref-switch-${c.key}`}
              />
            </View>
          ))}

          {prefs?.note ? <Text style={styles.note}>{prefs.note}</Text> : null}
        </ScrollView>
      )}
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: '#f4f4f5' },
  headerBar: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', padding: 14, backgroundColor: '#fff' },
  title: { fontSize: 17, fontWeight: '700' },
  scroll: { padding: 16, paddingBottom: 40 },
  osCard: { flexDirection: 'row', backgroundColor: '#FFFBEB', borderColor: '#FDE68A', borderWidth: 1, borderRadius: 12, padding: 14, marginBottom: 16 },
  osTitle: { fontWeight: '700', color: '#92400E', marginBottom: 4 },
  osText: { color: '#92400E', fontSize: 13, lineHeight: 18 },
  osButton: { alignSelf: 'flex-start', marginTop: 10, backgroundColor: '#E52224', borderRadius: 8, paddingVertical: 8, paddingHorizontal: 14 },
  osButtonText: { color: '#fff', fontWeight: '700' },
  row: { flexDirection: 'row', alignItems: 'center', backgroundColor: '#fff', borderRadius: 12, padding: 14, marginBottom: 10 },
  rowIcon: { width: 36, height: 36, borderRadius: 18, backgroundColor: '#FDECEC', alignItems: 'center', justifyContent: 'center', marginRight: 12 },
  rowTitle: { fontSize: 15, fontWeight: '700', color: '#222' },
  rowText: { fontSize: 12, color: '#666', marginTop: 2, lineHeight: 16 },
  lockRow: { flexDirection: 'row', alignItems: 'flex-start', gap: 4, marginTop: 6 },
  lockText: { flex: 1, fontSize: 11, color: '#888', lineHeight: 15 },
  note: { fontSize: 12, color: '#777', textAlign: 'center', marginTop: 12, lineHeight: 17 },
});

export default NotificationSettingsScreen;
