import React, { useCallback, useEffect, useState } from 'react';
import { View, Text, StyleSheet, ScrollView, ActivityIndicator, RefreshControl } from 'react-native';
import { SafeAreaView } from '../../components/SafeArea';
import { programEngineService } from '../../services/api';
import { useProgram } from '../../contexts/ProgramContext';
import { AuPairVisaProcessData } from '../../types/aupair';
import VisaTimeline from '../../components/aupair/VisaTimeline';
import EmptyState from '../../components/EmptyState';
import ScreenHeader from '../../components/program/ScreenHeader';

const ProgramVisaScreen: React.FC = () => {
  const { slug } = useProgram();
  const [data, setData] = useState<AuPairVisaProcessData | null>(null);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const load = useCallback(async () => {
    if (!slug) { setLoading(false); return; }
    try { setData(await programEngineService.getVisaProcess(slug)); } finally { setLoading(false); setRefreshing(false); }
  }, [slug]);

  useEffect(() => { load(); }, [load]);

  return (
    <SafeAreaView style={styles.safe}>
      <ScreenHeader title="Gestión de Visa J1" />
      {loading ? <ActivityIndicator size="large" color="#E52224" style={{ marginTop: 80 }} /> : (
        <ScrollView contentContainerStyle={styles.scroll} refreshControl={<RefreshControl refreshing={refreshing} onRefresh={() => { setRefreshing(true); load(); }} />}>
          {!data?.has_visa_process ? (
            <EmptyState icon="airplane-outline" title="Visa no iniciada" message={data?.message || 'El proceso de visa empezará cuando completes las etapas anteriores.'} />
          ) : (
            <>
              <View style={styles.progressBox}>
                <Text style={styles.progressLabel}>Progreso</Text>
                <Text style={styles.progressValue}>{data.progress_pct ?? 0}%</Text>
                <View style={styles.bar}><View style={[styles.barFill, { width: `${data.progress_pct ?? 0}%` }]} /></View>
              </View>
              {data.interview && (data.interview.date || data.interview.embassy || data.interview.result_label) && (
                <View style={styles.card}>
                  <Text style={styles.sectionTitle}>Entrevista consular</Text>
                  {data.interview.date && <Text style={styles.cardLine}>📅 {data.interview.date} {data.interview.time ?? ''}</Text>}
                  {data.interview.embassy && <Text style={styles.cardLine}>📍 {data.interview.embassy}</Text>}
                  {data.interview.result_label && <Text style={styles.cardLine}>Resultado: <Text style={{ fontWeight: '700' }}>{data.interview.result_label}</Text></Text>}
                </View>
              )}
              <View style={styles.card}><Text style={styles.sectionTitle}>Línea de tiempo</Text>{data.timeline ? <VisaTimeline items={data.timeline} /> : null}</View>
              {data.travel && (data.travel.departure || data.travel.arrival_usa) && (
                <View style={styles.card}>
                  <Text style={styles.sectionTitle}>Viaje</Text>
                  {data.travel.departure && <Text style={styles.cardLine}>Salida: {data.travel.departure}</Text>}
                  {data.travel.arrival_usa && <Text style={styles.cardLine}>Llegada USA: {data.travel.arrival_usa}</Text>}
                </View>
              )}
            </>
          )}
        </ScrollView>
      )}
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: '#f4f4f5' },
  scroll: { padding: 16, paddingBottom: 40 },
  progressBox: { backgroundColor: '#fff', borderRadius: 10, padding: 14, marginBottom: 14 },
  progressLabel: { fontSize: 12, color: '#777', fontWeight: '700' },
  progressValue: { fontSize: 26, fontWeight: '800', color: '#222' },
  bar: { height: 6, backgroundColor: '#e5e7eb', borderRadius: 4, marginTop: 8, overflow: 'hidden' },
  barFill: { height: '100%', backgroundColor: '#F59E0B' },
  card: { backgroundColor: '#fff', borderRadius: 10, padding: 14, marginBottom: 14 },
  sectionTitle: { fontSize: 14, fontWeight: '700', color: '#444', marginBottom: 10 },
  cardLine: { color: '#333', marginBottom: 4 },
});

export default ProgramVisaScreen;
