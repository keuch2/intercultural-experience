import React, { useState, useEffect } from 'react';
import {
  TouchableOpacity,
  View,
  StyleSheet,
  Linking,
  Alert,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useAuth } from '../contexts/AuthContext';
import { settingsService, WhatsAppSettings } from '../services/api/settingsService';

// Cache para evitar múltiples llamadas API
let settingsCache: WhatsAppSettings | null = null;
let isLoadingSettings = false;

const WhatsAppButton: React.FC = () => {
  const [settings, setSettings] = useState<WhatsAppSettings | null>(settingsCache);
  const [isVisible, setIsVisible] = useState(false);
  const { user } = useAuth();

  useEffect(() => {
    if (!settingsCache && !isLoadingSettings) {
      fetchWhatsAppSettings();
    } else if (settingsCache) {
      setSettings(settingsCache);
    }
  }, []);

  useEffect(() => {
    // Verificar si WhatsApp está habilitado y el usuario está autenticado
    const isEnabled = settings?.data?.whatsapp_support_enabled;
    if (isEnabled && user) {
      setIsVisible(true);
    } else {
      setIsVisible(false);
    }
  }, [settings, user]);

  const fetchWhatsAppSettings = async () => {
    if (isLoadingSettings) return;
    
    try {
      isLoadingSettings = true;
      const fetchedSettings = await settingsService.getWhatsAppSettings();
      settingsCache = fetchedSettings;
      setSettings(fetchedSettings);
    } catch (error) {
      console.error('Error fetching WhatsApp settings:', error);
      // En caso de error, usar configuración por defecto
      const defaultSettings = {
        success: true,
        data: {
          whatsapp_support_number: '+595981234567',
          whatsapp_support_enabled: false
        }
      };
      settingsCache = defaultSettings;
      setSettings(defaultSettings);
    } finally {
      isLoadingSettings = false;
    }
  };

  const openWhatsApp = async () => {
    const whatsappNumber = settings?.data?.whatsapp_support_number;
    if (!whatsappNumber) {
      Alert.alert('Error', 'Número de WhatsApp no configurado');
      return;
    }

    try {
      // Generar mensaje personalizado
      const message = settingsService.generateWhatsAppMessage(user?.name, user?.email);
      
      // Crear URLs de WhatsApp
      const { appUrl, webUrl } = settingsService.createWhatsAppUrl(
        whatsappNumber,
        message
      );

      // Intentar abrir la app de WhatsApp primero
      const canOpenWhatsApp = await Linking.canOpenURL(appUrl);
      
      if (canOpenWhatsApp) {
        await Linking.openURL(appUrl);
      } else {
        // Si no puede abrir la app, abrir en el navegador
        await Linking.openURL(webUrl);
      }
    } catch (error) {
      console.error('Error opening WhatsApp:', error);
      Alert.alert(
        'Error',
        'No se pudo abrir WhatsApp. Asegúrate de tener la aplicación instalada.'
      );
    }
  };

  if (!isVisible) {
    return null;
  }

  return (
    <View style={styles.container}>
      <TouchableOpacity
        style={styles.button}
        onPress={openWhatsApp}
        activeOpacity={0.8}
      >
        <Ionicons name="logo-whatsapp" size={28} color="#fff" />
      </TouchableOpacity>
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    position: 'absolute',
    bottom: 90,
    right: 20,
    zIndex: 99999,
    pointerEvents: 'box-none',
  },
  button: {
    width: 60,
    height: 60,
    borderRadius: 30,
    backgroundColor: '#25D366',
    justifyContent: 'center',
    alignItems: 'center',
    pointerEvents: 'auto',
  },
});

export default WhatsAppButton; 