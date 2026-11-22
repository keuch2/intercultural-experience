import { Alert, Linking } from 'react-native';

/**
 * Textos legales públicos (siempre los de producción, sin importar el entorno de la API).
 * Se aceptan una sola vez, al registrarse.
 */
export const LEGAL_URLS = {
  terms: 'https://ie.org.py/app/public/terminos',
  privacy: 'https://ie.org.py/app/public/privacidad',
} as const;

export const openLegalUrl = async (key: keyof typeof LEGAL_URLS) => {
  try {
    await Linking.openURL(LEGAL_URLS[key]);
  } catch {
    Alert.alert('No se pudo abrir el enlace', `Podés consultarlo en ${LEGAL_URLS[key]}`);
  }
};
