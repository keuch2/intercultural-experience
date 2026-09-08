import React, { useCallback, useEffect, useMemo, useState } from 'react';
import {
  View, Text, StyleSheet, ScrollView, ActivityIndicator, TouchableOpacity, RefreshControl, Alert
} from 'react-native';
import { SafeAreaView } from '../../components/SafeArea';
import { Ionicons } from '@expo/vector-icons';
import { useNavigation, useRoute, RouteProp } from '@react-navigation/native';
import { programEngineService } from '../../services/api';
import { useProgram } from '../../contexts/ProgramContext';
import { ProgramDocumentEntry, EngineDocumentGroup } from '../../types/programEngine';
import DocumentCard from '../../components/aupair/DocumentCard';
import EmptyState from '../../components/EmptyState';
import ScreenHeader from '../../components/program/ScreenHeader';
import { downloadAndOpen } from '../../utils/downloadFile';

type RouteP = RouteProp<{ ProgramDocuments: { group?: string } }, 'ProgramDocuments'>;

/**
 * Documentos por grupo (tab). Los grupos, su orden y su bloqueo (aprobación,
 * etapa o gate de pago) vienen del envelope.
 */
const ProgramDocumentsScreen: React.FC = () => {
  const navigation = useNavigation<any>();
  const route = useRoute<RouteP>();
  const { envelope, slug, refresh } = useProgram();
  const groups: EngineDocumentGroup[] = envelope?.document_groups ?? [];
  const defaultGroup = route.params?.group || groups.find(g => g.stage_key === envelope?.current_stage)?.key || groups[0]?.key;
  const [group, setGroup] = useState<string | undefined>(defaultGroup);
  const [entries, setEntries] = useState<ProgramDocumentEntry[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => { if (!group && defaultGroup) setGroup(defaultGroup); }, [defaultGroup, group]);

  const load = useCallback(async (g?: string) => {
    if (!slug || !g) { setLoading(false); return; }
    try {
      setError(null);
      const { entries: data } = await programEngineService.getDocuments(slug, g);
      setEntries(data);
    } catch (e: any) {
      setError(e?.message || 'Error al cargar documentos');
    } finally {
      setLoading(false); setRefreshing(false);
    }
  }, [slug]);

  useEffect(() => { setLoading(true); load(group); }, [group, load]);

  const current = groups.find(g => g.key === group);
  const counts = useMemo(() => {
    const required = entries.filter(e => e.required);
    return { done: required.filter(e => e.status === 'approved').length, total: required.length };
  }, [entries]);

  const handleUpload = (entry: ProgramDocumentEntry) => navigation.navigate('ProgramDocumentUpload', { entry });
  const handleDelete = (fileId: number, entry: ProgramDocumentEntry) => {
    Alert.alert('Eliminar documento', `¿Eliminar el archivo subido de "${entry.label}"? Podrás volver a subir otro.`, [
      { text: 'Cancelar', style: 'cancel' },
      { text: 'Eliminar', style: 'destructive', onPress: async () => {
        try { await programEngineService.deleteDocument(slug!, fileId); load(group); refresh(); }
        catch (e: any) { Alert.alert('Error', e?.response?.data?.message || 'No pudimos eliminar el documento.'); }
      } },
    ]);
  };

  const lockMessage = (g?: EngineDocumentGroup) => {
    if (!g || g.unlocked) return null;
    if (g.lock_reason === 'pending_approval') return 'El equipo de IE debe aprobar tu postulación antes de habilitar la carga de documentos.';
    if (g.lock_reason === 'stage_locked') return 'Estos documentos se habilitan cuando llegues a esta etapa del proceso.';
    if (g.lock_reason?.startsWith('gate:')) {
      const gate = envelope?.gates.find(x => x.key === g.lock_reason!.slice(5));
      return `Estos documentos se habilitan al verificar el pago: ${gate?.label ?? 'requerido'}.`;
    }
    return 'Documentos bloqueados.';
  };

  if (!envelope) {
    return <SafeAreaView style={styles.safe}><ScreenHeader title="Documentos" /><EmptyState icon="folder-open-outline" title="Sin postulación activa" /></SafeAreaView>;
  }
  if (!envelope.application_approved) {
    return <SafeAreaView style={styles.safe}><ScreenHeader title="Documentos" /><EmptyState icon="lock-closed-outline" title="Aprobación pendiente" message="El equipo de IE debe aprobar tu postulación antes de habilitar la carga de documentos. Te avisaremos cuando esté lista." /></SafeAreaView>;
  }

  return (
    <SafeAreaView style={styles.safe}>
      <ScreenHeader title="Documentos" />
      <ScrollView horizontal showsHorizontalScrollIndicator={false} style={styles.tabScroll} contentContainerStyle={styles.tabRow}>
        {groups.map(g => (
          <TouchableOpacity key={g.key} style={[styles.tab, group === g.key && styles.tabActive]} onPress={() => setGroup(g.key)}>
            {!g.unlocked && <Ionicons name="lock-closed" size={11} color="#9CA3AF" style={{ marginRight: 4 }} />}
            <Text style={[styles.tabText, group === g.key && styles.tabTextActive]}>{g.label}</Text>
            {g.counts.pending > 0 && <View style={styles.dotPending} />}
          </TouchableOpacity>
        ))}
      </ScrollView>

      {current && !current.unlocked && (
        <View style={styles.lockBanner}><Ionicons name="lock-closed-outline" size={16} color="#92400E" /><Text style={styles.lockText}>{lockMessage(current)}</Text></View>
      )}

      {!loading && counts.total > 0 && (
        <View style={styles.summary}>
          <Text style={styles.summaryText}>{counts.done}/{counts.total} documentos requeridos aprobados</Text>
          <View style={styles.bar}><View style={[styles.barFill, { width: `${(counts.done / counts.total) * 100}%` }]} /></View>
        </View>
      )}

      {loading ? <ActivityIndicator size="large" color="#E52224" style={{ marginTop: 30 }} />
        : error ? <EmptyState icon="cloud-offline-outline" title="Error al cargar" message={error} actionLabel="Reintentar" onAction={() => load(group)} />
        : (
          <ScrollView contentContainerStyle={styles.list} refreshControl={<RefreshControl refreshing={refreshing} onRefresh={() => { setRefreshing(true); load(group); refresh(); }} />}>
            {entries.length === 0 ? <EmptyState icon="folder-open-outline" title="Sin documentos en este grupo" /> : entries.map(e => (
              <DocumentCard key={e.document_type} entry={e} onUpload={current?.unlocked ? handleUpload : undefined} onDelete={handleDelete} onDownload={f => f.download_url && downloadAndOpen(f.download_url, f.original_filename || undefined)} />
            ))}
          </ScrollView>
        )}
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: '#f4f4f5' },
  tabScroll: { backgroundColor: '#fff', maxHeight: 46, borderBottomWidth: 1, borderBottomColor: '#eee' },
  tabRow: { paddingHorizontal: 8, paddingTop: 6 },
  tab: { flexDirection: 'row', alignItems: 'center', paddingVertical: 10, paddingHorizontal: 12, borderBottomWidth: 2, borderBottomColor: 'transparent' },
  tabActive: { borderBottomColor: '#E52224' },
  tabText: { color: '#777', fontWeight: '600', fontSize: 12 },
  tabTextActive: { color: '#E52224' },
  dotPending: { width: 6, height: 6, borderRadius: 3, backgroundColor: '#F59E0B', marginLeft: 4 },
  lockBanner: { flexDirection: 'row', alignItems: 'center', gap: 8, backgroundColor: '#FEF3C7', margin: 12, padding: 10, borderRadius: 8 },
  lockText: { color: '#92400E', fontSize: 12, flex: 1, lineHeight: 16 },
  summary: { paddingHorizontal: 16, paddingTop: 12, paddingBottom: 6 },
  summaryText: { fontSize: 12, color: '#555', marginBottom: 6 },
  bar: { height: 6, backgroundColor: '#e5e7eb', borderRadius: 4, overflow: 'hidden' },
  barFill: { height: '100%', backgroundColor: '#10B981', borderRadius: 4 },
  list: { padding: 16, paddingBottom: 30 },
});

export default ProgramDocumentsScreen;
