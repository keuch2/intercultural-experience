import React, { useEffect, useState } from 'react';
import { TouchableOpacity, Text, StyleSheet, Linking, Alert } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { settingsService, WhatsAppSettings } from '../../services/api/settingsService';
import { useAuth } from '../../contexts/AuthContext';

/** "Escribir a IE por WhatsApp": número y mensaje configurados desde el admin (Configuración > WhatsApp). */
const SupportWhatsAppButton: React.FC<{ programName?: string }> = ({ programName }) => {
  const { user } = useAuth();
  const [settings, setSettings] = useState<WhatsAppSettings['data'] | null>(null);

  useEffect(() => { settingsService.getWhatsAppSettings().then(s => setSettings(s.data)).catch(() => setSettings(null)); }, []);

  if (!settings?.whatsapp_support_enabled || !settings.whatsapp_support_number) return null;

  const open = async () => {
    const message = settingsService.buildSupportMessage(settings.whatsapp_support_message, { name: user?.name, program: programName, email: user?.email });
    const { appUrl, webUrl } = settingsService.createWhatsAppUrl(settings.whatsapp_support_number, message);
    try {
      if (await Linking.canOpenURL(appUrl)) await Linking.openURL(appUrl);
      else await Linking.openURL(webUrl);
    } catch {
      Alert.alert('WhatsApp', 'No pudimos abrir WhatsApp en este dispositivo.');
    }
  };

  return (
    <TouchableOpacity style={styles.btn} onPress={open} accessibilityLabel="Escribir a IE por WhatsApp">
      <Ionicons name="logo-whatsapp" size={20} color="#fff" />
      <Text style={styles.text}>Escribir a IE por WhatsApp</Text>
    </TouchableOpacity>
  );
};

const styles = StyleSheet.create({
  btn: { flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 8, backgroundColor: '#25D366', borderRadius: 12, paddingVertical: 12, marginBottom: 12 },
  text: { color: '#fff', fontWeight: '800', fontSize: 14 },
});

export default SupportWhatsAppButton;
