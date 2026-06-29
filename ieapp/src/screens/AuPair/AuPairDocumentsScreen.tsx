import React, { useCallback, useEffect, useMemo, useState } from 'react';
import {
  View, Text, StyleSheet, ScrollView, ActivityIndicator,
  TouchableOpacity, SafeAreaView, RefreshControl, Alert,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useNavigation, useRoute, RouteProp } from '@react-navigation/native';
import { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { auPairService } from '../../services/api';
import { AuPairDocumentEntry, DocStage } from '../../types/aupair';
import DocumentCard from '../../components/aupair/DocumentCard';
import EmptyState from '../../components/EmptyState';
import { RootStackParamList } from '../../navigation/AppNavigator';

type Nav = NativeStackNavigationProp<RootStackParamList>;
type RouteP = RouteProp<{ AuPairDocuments: { stage?: DocStage } }, 'AuPairDocuments'>;

const STAGE_TABS: { key: DocStage; label: string }[] = [
  { key: 'admission', label: 'Admisión' },
  { key: 'application_payment1', label: 'Docs 1' },
  { key: 'application_payment2', label: 'Docs 2' },
  { key: 'visa', label: 'Visa' },
];

const AuPairDocumentsScreen: React.FC = () => {
  const navigation = useNavigation<Nav>();
  const route = useRoute<RouteP>();
  const [stage, setStage] = useState<DocStage>(route.params?.stage || 'admission');
  const [entries, setEntries] = useState<AuPairDocumentEntry[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [approved, setApproved] = useState<boolean | null>(null);

  const load = useCallback(async (s: DocStage) => {
    try {
      setError(null);
      // Gate: confirmar que el Staff IE aprobó la postulación antes de mostrar docs.
      const proc = await auPairService.getProcess();
      const isApproved = !!proc?.application_approved;
      setApproved(isApproved);
      if (!isApproved) {
        setEntries([]);
        return;
      }
      const data = await auPairService.getDocuments(s);
      setEntries(data);
    } catch (e: any) {
      setError(e?.message || 'Error al cargar documentos');
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, []);

  useEffect(() => { setLoading(true); load(stage); }, [stage, load]);

  const onRefresh = () => { setRefreshing(true); load(stage); };

  const counts = useMemo(() => {
    const required = entries.filter(e => e.required);
    const done = required.filter(e => e.status === 'approved').length;
    return { done, total: required.length };
  }, [entries]);

  const handleUpload = (entry: AuPairDocumentEntry) => {
    (navigation as any).navigate('AuPairDocumentUpload', { entry });
  };

  const handleDelete = (fileId: number, entry: AuPairDocumentEntry) => {
    Alert.alert(
      'Eliminar documento',
      `¿Eliminar el archivo subido de "${entry.label}"? Podrás volver a subir otro.`,
      [
        { text: 'Cancelar', style: 'cancel' },
        {
          text: 'Eliminar',
          style: 'destructive',
          onPress: async () => {
            try {
              await auPairService.deleteDocument(fileId);
              load(stage);
            } catch (e: any) {
              Alert.alert('Error', e?.response?.data?.message || 'No pudimos eliminar el documento.');
            }
          },
        },
      ],
    );
  };

  return (
    <SafeAreaView style={styles.safe}>
      <View style={styles.headerBar}>
        <TouchableOpacity onPress={() => navigation.goBack()}>
          <Ionicons name="arrow-back" size={24} color="#222" />
        </TouchableOpacity>
        <Text style={styles.title}>Documentos</Text>
        <View style={{ width: 24 }} />
      </View>

      {!loading && approved === false ? (
        <EmptyState
          icon="lock-closed-outline"
          title="Aprobación pendiente"
          message="El equipo de IE debe aprobar tu postulación antes de habilitar la carga de documentos. Te avisaremos cuando esté lista."
        />
      ) : (
      <>
      <View style={styles.tabRow}>
        {STAGE_TABS.map(t => (
          <TouchableOpacity
            key={t.key}
            style={[styles.tab, stage === t.key && styles.tabActive]}
            onPress={() => setStage(t.key)}
          >
            <Text style={[styles.tabText, stage === t.key && styles.tabTextActive]}>{t.label}</Text>
          </TouchableOpacity>
        ))}
      </View>

      {!loading && counts.total > 0 && (
        <View style={styles.summary}>
          <Text style={styles.summaryText}>
            {counts.done}/{counts.total} documentos requeridos aprobados
          </Text>
          <View style={styles.bar}>
            <View style={[styles.barFill, { width: `${(counts.done / counts.total) * 100}%` }]} />
          </View>
        </View>
      )}

      {loading ? (
        <ActivityIndicator size="large" color="#E52224" style={{ marginTop: 30 }} />
      ) : error ? (
        <EmptyState
          icon="cloud-offline-outline"
          title="Error al cargar"
          message={error}
          actionLabel="Reintentar"
          onAction={() => load(stage)}
        />
      ) : (
        <ScrollView
          contentContainerStyle={styles.list}
          refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} />}
        >
          {entries.length === 0 ? (
            <EmptyState icon="folder-open-outline" title="Sin documentos en esta etapa" />
          ) : (
            entries.map(e => (
              <DocumentCard key={e.document_type} entry={e} onUpload={handleUpload} onDelete={handleDelete} />
            ))
          )}
        </ScrollView>
      )}
      </>
      )}
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: '#f4f4f5' },
  headerBar: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    padding: 14,
    backgroundColor: '#fff',
  },
  title: { fontSize: 17, fontWeight: '700', color: '#222' },
  tabRow: {
    flexDirection: 'row',
    paddingHorizontal: 8,
    paddingTop: 8,
    backgroundColor: '#fff',
    borderBottomWidth: 1,
    borderBottomColor: '#eee',
  },
  tab: {
    flex: 1,
    paddingVertical: 10,
    alignItems: 'center',
    borderBottomWidth: 2,
    borderBottomColor: 'transparent',
  },
  tabActive: { borderBottomColor: '#E52224' },
  tabText: { color: '#777', fontWeight: '600', fontSize: 12 },
  tabTextActive: { color: '#E52224' },
  summary: { paddingHorizontal: 16, paddingTop: 12, paddingBottom: 6 },
  summaryText: { fontSize: 12, color: '#555', marginBottom: 6 },
  bar: { height: 6, backgroundColor: '#e5e7eb', borderRadius: 4, overflow: 'hidden' },
  barFill: { height: '100%', backgroundColor: '#10B981', borderRadius: 4 },
  list: { padding: 16, paddingBottom: 30 },
});

export default AuPairDocumentsScreen;
