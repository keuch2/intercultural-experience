import { Alert, Linking, Platform } from 'react-native';
import * as FileSystem from 'expo-file-system/legacy';
import * as Sharing from 'expo-sharing';
import authService from '../services/api/authService';

/**
 * Descarga un archivo de un endpoint protegido (Bearer token) y lo abre con el
 * visor nativo / hoja de compartir. Los enlaces externos (sin token) se abren
 * directamente con Linking.
 */
const MIME_BY_EXT: Record<string, string> = {
  pdf: 'application/pdf', jpg: 'image/jpeg', jpeg: 'image/jpeg', png: 'image/png',
  doc: 'application/msword', docx: 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
  mp4: 'video/mp4', mov: 'video/quicktime', xlsx: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
};

const sanitize = (name: string) => name.replace(/[^A-Za-z0-9._-]+/g, '_').slice(0, 120) || `archivo-${Date.now()}`;

export const mimeFor = (filename: string): string | undefined => MIME_BY_EXT[(filename.split('.').pop() || '').toLowerCase()];

export async function downloadAndOpen(url: string, filename?: string, mimeType?: string): Promise<boolean> {
  try {
    const token = await authService.getStoredToken();
    const name = sanitize(filename || url.split('/').pop() || `archivo-${Date.now()}`);
    const dir = FileSystem.cacheDirectory ?? FileSystem.documentDirectory;
    if (!dir) throw new Error('Almacenamiento no disponible');
    const target = `${dir}${name}`;

    const res = await FileSystem.downloadAsync(url, target, {
      headers: { Accept: 'application/octet-stream, */*', ...(token ? { Authorization: `Bearer ${token}` } : {}) },
    });
    if (res.status < 200 || res.status >= 300) {
      throw new Error(res.status === 401 ? 'Tu sesión expiró. Volvé a iniciar sesión.' : res.status === 404 ? 'El archivo no está disponible.' : `No pudimos descargar el archivo (HTTP ${res.status}).`);
    }

    if (await Sharing.isAvailableAsync()) {
      await Sharing.shareAsync(res.uri, {
        mimeType: mimeType || mimeFor(name),
        dialogTitle: name,
        UTI: Platform.OS === 'ios' ? undefined : undefined,
      });
    } else {
      await Linking.openURL(res.uri);
    }
    return true;
  } catch (e: any) {
    Alert.alert('Descarga', e?.message || 'No pudimos descargar el archivo.');
    return false;
  }
}

/** Abre un enlace público (sin token) o descarga con token si es un endpoint de la API. */
export async function openResource(opts: { external_url?: string | null; download_url?: string | null; filename?: string | null }): Promise<void> {
  if (opts.external_url) {
    Linking.openURL(opts.external_url).catch(() => Alert.alert('Enlace', 'No pudimos abrir el enlace.'));
    return;
  }
  if (opts.download_url) {
    await downloadAndOpen(opts.download_url, opts.filename || undefined);
    return;
  }
  Alert.alert('Sin archivo', 'Este recurso todavía no tiene archivo disponible.');
}
