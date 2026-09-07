import React, { useCallback, useEffect, useState } from 'react';
import { View, Text, StyleSheet, ScrollView, ActivityIndicator, TouchableOpacity, SafeAreaView, Linking, RefreshControl, Alert } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { programEngineService } from '../../services/api';
import { useProgram } from '../../contexts/ProgramContext';
import { AuPairResource } from '../../types/aupair';
import EmptyState from '../../components/EmptyState';
import ScreenHeader from '../../components/program/ScreenHeader';

const ICON_BY_TYPE: Record<string, keyof typeof Ionicons.glyphMap> = { PDF: 'document-text-outline', DOC: 'document-outline', VIDEO: 'play-circle-outline', LINK: 'link-outline' };

const ProgramResourcesScreen: React.FC = () => {
  const { slug } = useProgram();
  const [resources, setResources] = useState<AuPairResource[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const load = useCallback(async () => {
    if (!slug) { setLoading(false); return; }
    try { setResources(await programEngineService.getResources(slug)); } finally { setLoading(false); setRefreshing(false); }
  }, [slug]);

  useEffect(() => { load(); }, [load]);

  const open = (r: AuPairResource) => {
    const url = r.external_url || r.download_url;
    if (!url) { Alert.alert('Sin archivo', 'Este recurso todavía no tiene archivo disponible.'); return; }
    Linking.openURL(url);
  };

  return (
    <SafeAreaView style={styles.safe}>
      <ScreenHeader title="Recursos del programa" />
      {loading ? <ActivityIndicator size="large" color="#E52224" style={{ marginTop: 80 }} /> : (
        <ScrollView contentContainerStyle={styles.scroll} refreshControl={<RefreshControl refreshing={refreshing} onRefresh={() => { setRefreshing(true); load(); }} />}>
          {resources.length === 0 ? (
            <EmptyState icon="folder-open-outline" title="Sin recursos disponibles" message="El equipo IE irá publicando guías y recursos para tu proceso." />
          ) : resources.map(r => (
            <TouchableOpacity key={r.id} style={styles.card} onPress={() => open(r)}>
              <View style={styles.iconBox}><Ionicons name={ICON_BY_TYPE[r.file_type || ''] || 'document-outline'} size={26} color="#E52224" /></View>
              <View style={{ flex: 1 }}>
                <Text style={styles.titleText}>{r.title}</Text>
                {r.description && <Text style={styles.desc} numberOfLines={2}>{r.description}</Text>}
                <Text style={styles.meta}>{r.file_size_formatted || (r.external_url ? 'Enlace externo' : 'Próximamente')}</Text>
              </View>
              <Ionicons name={r.external_url || r.download_url ? 'download-outline' : 'time-outline'} size={20} color="#666" />
            </TouchableOpacity>
          ))}
        </ScrollView>
      )}
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: '#f4f4f5' },
  scroll: { padding: 16, paddingBottom: 40 },
  card: { flexDirection: 'row', alignItems: 'center', gap: 12, backgroundColor: '#fff', padding: 14, borderRadius: 10, marginBottom: 10 },
  iconBox: { width: 48, height: 48, borderRadius: 24, backgroundColor: '#FEF2F2', alignItems: 'center', justifyContent: 'center' },
  titleText: { fontWeight: '700', color: '#222' },
  desc: { color: '#666', fontSize: 13, marginTop: 2 },
  meta: { fontSize: 11, color: '#999', marginTop: 4 },
});

export default ProgramResourcesScreen;
