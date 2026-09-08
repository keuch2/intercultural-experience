import React, { useState } from 'react';
import {
  View, Text, StyleSheet, ScrollView, ActivityIndicator, TouchableOpacity, Alert
} from 'react-native';
import { SafeAreaView, FULL_EDGES } from '../../components/SafeArea';
import { Ionicons } from '@expo/vector-icons';
import { useNavigation } from '@react-navigation/native';
import * as DocumentPicker from 'expo-document-picker';

export type PickedFile = { uri: string; name: string; type: string };

export interface UploadEntryLike {
  document_type: string;
  label: string;
  required: boolean;
  min_count: number | null;
  allow_multiple?: boolean;
  description?: string | null;
}

interface Props {
  entry: UploadEntryLike;
  onUpload: (files: PickedFile[]) => Promise<void>;
  hint?: string;
}

type Selected = { uri: string; name: string; mimeType: string; size: number };

/**
 * Formulario de subida de documentos compartido por Au Pair y el motor genérico.
 * La pantalla dueña decide a qué endpoint enviar (prop `onUpload`).
 */
const DocumentUploadForm: React.FC<Props> = ({ entry, onUpload, hint }) => {
  const navigation = useNavigation<any>();
  const [files, setFiles] = useState<Selected[]>([]);
  const [uploading, setUploading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const allowMultiple = entry.allow_multiple === true || (!!entry.min_count && entry.min_count > 1);

  const pick = async () => {
    try {
      setError(null);
      const res = await DocumentPicker.getDocumentAsync({ multiple: allowMultiple, copyToCacheDirectory: true, type: ['image/*', 'application/pdf', 'video/*'] });
      if (res.canceled) return;
      const newOnes: Selected[] = res.assets.map(a => ({ uri: a.uri, name: a.name || `archivo-${Date.now()}`, mimeType: a.mimeType || 'application/octet-stream', size: a.size || 0 }));
      setFiles(prev => (allowMultiple ? [...prev, ...newOnes] : newOnes));
    } catch (e: any) {
      setError(e?.message || 'No pudimos abrir el selector de archivos.');
    }
  };

  const submit = async () => {
    if (files.length === 0) { Alert.alert('Sin archivos', 'Seleccioná al menos un archivo.'); return; }
    try {
      setUploading(true); setError(null);
      await onUpload(files.map(f => ({ uri: f.uri, name: f.name, type: f.mimeType })));
      Alert.alert('Listo', 'Tus archivos fueron enviados a revisión.', [{ text: 'OK', onPress: () => navigation.goBack() }]);
    } catch (e: any) {
      setError(e?.response?.data?.message || e?.message || 'Error al subir.');
    } finally {
      setUploading(false);
    }
  };

  const formatSize = (b: number) => (b > 1048576 ? `${(b / 1048576).toFixed(1)} MB` : b > 1024 ? `${(b / 1024).toFixed(0)} KB` : `${b} B`);

  return (
    <SafeAreaView style={styles.safe} edges={FULL_EDGES}>
      <View style={styles.headerBar}>
        <TouchableOpacity onPress={() => navigation.goBack()}><Ionicons name="close" size={26} color="#222" /></TouchableOpacity>
        <Text style={styles.title} numberOfLines={1}>Subir documento</Text>
        <View style={{ width: 26 }} />
      </View>
      <ScrollView contentContainerStyle={styles.scroll}>
        <View style={styles.head}>
          <Text style={styles.docLabel}>{entry.label}</Text>
          {entry.required && <Text style={styles.required}>Requerido</Text>}
          {entry.description ? <Text style={styles.minCount}>{entry.description}</Text> : null}
          {entry.min_count && entry.min_count > 1 ? <Text style={styles.minCount}>Necesitás subir al menos {entry.min_count} archivos.</Text> : null}
        </View>
        <TouchableOpacity style={styles.pickBtn} onPress={pick}>
          <Ionicons name="cloud-upload-outline" size={20} color="#3B82F6" />
          <Text style={styles.pickBtnText}>{allowMultiple ? 'Agregar archivos' : 'Seleccionar archivo'}</Text>
        </TouchableOpacity>
        <Text style={styles.hint}>{hint ?? 'Formatos: JPG, PNG, PDF.'}</Text>
        {files.length > 0 && (
          <View style={styles.list}>
            {files.map((f, idx) => (
              <View key={idx} style={styles.fileRow}>
                <Ionicons name="document-outline" size={20} color="#444" />
                <View style={{ flex: 1, marginLeft: 8 }}>
                  <Text style={styles.fileName} numberOfLines={1}>{f.name}</Text>
                  <Text style={styles.fileSize}>{formatSize(f.size)}</Text>
                </View>
                <TouchableOpacity onPress={() => setFiles(prev => prev.filter((_, i) => i !== idx))}><Ionicons name="trash-outline" size={18} color="#E52224" /></TouchableOpacity>
              </View>
            ))}
          </View>
        )}
        {error && <Text style={styles.error}>{error}</Text>}
      </ScrollView>
      <View style={styles.footer}>
        <TouchableOpacity style={[styles.submitBtn, (uploading || files.length === 0) && styles.submitBtnDisabled]} onPress={submit} disabled={uploading || files.length === 0}>
          {uploading ? <ActivityIndicator color="#fff" /> : (<><Ionicons name="checkmark-circle" size={18} color="#fff" /><Text style={styles.submitText}>Enviar {files.length > 0 ? `(${files.length})` : ''}</Text></>)}
        </TouchableOpacity>
      </View>
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: '#fff' },
  headerBar: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', padding: 14, borderBottomWidth: 1, borderBottomColor: '#eee' },
  title: { flex: 1, textAlign: 'center', fontSize: 16, fontWeight: '700' },
  scroll: { padding: 18, paddingBottom: 120 },
  head: { marginBottom: 18 },
  docLabel: { fontSize: 18, fontWeight: '700', color: '#222' },
  required: { color: '#E52224', fontSize: 12, fontWeight: '700', marginTop: 4 },
  minCount: { color: '#555', marginTop: 6, fontSize: 13 },
  pickBtn: { flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 8, backgroundColor: '#EFF6FF', borderWidth: 1, borderColor: '#3B82F6', borderStyle: 'dashed', borderRadius: 10, paddingVertical: 18 },
  pickBtnText: { color: '#3B82F6', fontWeight: '700' },
  hint: { fontSize: 11, color: '#777', marginTop: 6 },
  list: { marginTop: 16 },
  fileRow: { flexDirection: 'row', alignItems: 'center', backgroundColor: '#f9fafb', padding: 10, borderRadius: 8, marginBottom: 8 },
  fileName: { fontSize: 13, color: '#222', fontWeight: '600' },
  fileSize: { fontSize: 11, color: '#777' },
  error: { color: '#991B1B', marginTop: 14, fontSize: 13, textAlign: 'center' },
  footer: { position: 'absolute', bottom: 0, left: 0, right: 0, padding: 14, paddingBottom: 24, backgroundColor: '#fff', borderTopWidth: 1, borderTopColor: '#eee' },
  submitBtn: { flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 8, backgroundColor: '#E52224', paddingVertical: 14, borderRadius: 10 },
  submitBtnDisabled: { backgroundColor: '#aaa' },
  submitText: { color: '#fff', fontWeight: '700', fontSize: 16 },
});

export default DocumentUploadForm;
