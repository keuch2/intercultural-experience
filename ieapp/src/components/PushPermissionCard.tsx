import React, { useCallback, useState } from 'react';
import { View, Text, TouchableOpacity, StyleSheet, StyleProp, ViewStyle } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useFocusEffect } from '@react-navigation/native';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { getPushPermission, registerDeviceForPush, requestPushPermission } from '../services/pushNotifications';

const DISMISS_KEY = 'push.permissionCardDismissedAt';
const DISMISS_DAYS = 14;

/**
 * Invitación a activar las notificaciones, antes de que el sistema pida el permiso
 * (el permiso del SO se pide una sola vez: conviene explicar primero para qué sirve).
 */
const PushPermissionCard: React.FC<{ style?: StyleProp<ViewStyle> }> = ({ style }) => {
  const [visible, setVisible] = useState(false);

  useFocusEffect(useCallback(() => {
    let alive = true;
    (async () => {
      if ((await getPushPermission()) !== 'undetermined') { if (alive) setVisible(false); return; }
      let dismissedAt = 0;
      try { dismissedAt = Number(await AsyncStorage.getItem(DISMISS_KEY)) || 0; } catch {}
      if (alive) setVisible(Date.now() - dismissedAt > DISMISS_DAYS * 86400000);
    })();
    return () => { alive = false; };
  }, []));

  if (!visible) return null;

  const activate = async () => {
    setVisible(false);
    if ((await requestPushPermission()) === 'granted') await registerDeviceForPush();
  };
  const dismiss = async () => {
    setVisible(false);
    try { await AsyncStorage.setItem(DISMISS_KEY, String(Date.now())); } catch {}
  };

  return (
    <View style={[styles.card, style]} testID="push-permission-card">
      <View style={styles.row}>
        <View style={styles.icon}><Ionicons name="notifications" size={22} color="#E52224" /></View>
        <View style={{ flex: 1 }}>
          <Text style={styles.title}>Activá las notificaciones</Text>
          <Text style={styles.text}>Te avisamos al instante cuando IE revise tu postulación, tus documentos o tus pagos, y antes de tu cita de visa.</Text>
        </View>
      </View>
      <View style={styles.actions}>
        <TouchableOpacity onPress={dismiss} style={styles.secondary}><Text style={styles.secondaryText}>Ahora no</Text></TouchableOpacity>
        <TouchableOpacity onPress={activate} style={styles.primary} testID="push-permission-activate"><Text style={styles.primaryText}>Activar</Text></TouchableOpacity>
      </View>
    </View>
  );
};

const styles = StyleSheet.create({
  card: { backgroundColor: '#fff', borderRadius: 14, padding: 14, borderWidth: 1, borderColor: '#FBD5D5' },
  row: { flexDirection: 'row', gap: 12 },
  icon: { width: 40, height: 40, borderRadius: 20, backgroundColor: '#FDECEC', alignItems: 'center', justifyContent: 'center' },
  title: { fontSize: 15, fontWeight: '700', color: '#222', marginBottom: 2 },
  text: { fontSize: 13, color: '#555', lineHeight: 18 },
  actions: { flexDirection: 'row', justifyContent: 'flex-end', gap: 10, marginTop: 12 },
  secondary: { paddingVertical: 8, paddingHorizontal: 14 },
  secondaryText: { color: '#666', fontWeight: '600' },
  primary: { backgroundColor: '#E52224', borderRadius: 8, paddingVertical: 8, paddingHorizontal: 18 },
  primaryText: { color: '#fff', fontWeight: '700' },
});

export default PushPermissionCard;
