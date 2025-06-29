import apiClient from './apiClient';

export interface AppSettings {
  app_name: string;
  app_version: string;
  support_email: string;
  company_phone: string;
  company_address: string;
  whatsapp_support_number: string;
  whatsapp_support_enabled: boolean;
}

export interface WhatsAppSettings {
  success: boolean;
  data: {
    whatsapp_support_number: string;
    whatsapp_support_enabled: boolean;
  };
}

export interface ContactInfo {
  app_name: string;
  support_email: string;
  company_phone: string;
  company_address: string;
  whatsapp_number: string;
  whatsapp_enabled: boolean;
}

class SettingsService {
  /**
   * Obtiene todas las configuraciones públicas
   */
  async getAllSettings(): Promise<AppSettings> {
    try {
      const response = await apiClient.get('/settings');
      return response.data;
    } catch (error) {
      console.error('Error fetching all settings:', error);
      throw error;
    }
  }

  /**
   * Obtiene configuraciones específicas de WhatsApp
   */
  async getWhatsAppSettings(): Promise<WhatsAppSettings> {
    try {
      const response = await apiClient.get('/settings/whatsapp');
      return response.data;
    } catch (error) {
      console.error('Error fetching WhatsApp settings:', error);
      // Devolver configuración por defecto en caso de error
      return {
        success: true,
        data: {
          whatsapp_support_number: '+595981234567',
          whatsapp_support_enabled: false
        }
      };
    }
  }

  /**
   * Obtiene información de contacto
   */
  async getContactInfo(): Promise<ContactInfo> {
    try {
      const response = await apiClient.get('/settings/contact');
      return response.data;
    } catch (error) {
      console.error('Error fetching contact info:', error);
      throw error;
    }
  }

  /**
   * Obtiene información de la aplicación
   */
  async getAppInfo(): Promise<{ app_name: string; app_version: string; support_email: string; whatsapp_support_enabled: boolean }> {
    try {
      const response = await apiClient.get('/settings/app-info');
      return response.data;
    } catch (error) {
      console.error('Error fetching app info:', error);
      throw error;
    }
  }

  /**
   * Formatea un número de WhatsApp para abrir la aplicación
   */
  formatWhatsAppNumber(number: string): string {
    return number.replace(/[^\d]/g, '');
  }

  /**
   * Crea una URL de WhatsApp con mensaje
   */
  createWhatsAppUrl(number: string, message: string): { appUrl: string; webUrl: string } {
    const cleanNumber = this.formatWhatsAppNumber(number);
    const encodedMessage = encodeURIComponent(message);
    
    return {
      appUrl: `whatsapp://send?phone=${cleanNumber}&text=${encodedMessage}`,
      webUrl: `https://wa.me/${cleanNumber}?text=${encodedMessage}`
    };
  }

  /**
   * Genera un mensaje de WhatsApp personalizado
   */
  generateWhatsAppMessage(userName?: string, userEmail?: string): string {
    const name = userName || 'Usuario';
    const email = userEmail || '';
    
    return `Hola! Soy ${name}${email ? ` (${email})` : ''} de la app Intercultural Experience. Necesito ayuda con mi cuenta.`;
  }
}

export const settingsService = new SettingsService(); 