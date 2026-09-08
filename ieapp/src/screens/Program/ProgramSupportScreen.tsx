import React, { useCallback, useEffect, useState } from 'react';
import { View, Text, StyleSheet, ScrollView, ActivityIndicator, RefreshControl } from 'react-native';
import { SafeAreaView } from '../../components/SafeArea';
import { Ionicons } from '@expo/vector-icons';
import { programEngineService } from '../../services/api';
import { useProgram } from '../../contexts/ProgramContext';
import { AuPairSupportLog } from '../../types/aupair';
import EmptyState from '../../components/EmptyState';
import ScreenHeader from '../../components/program/ScreenHeader';

const SEVERITY_COLOR: Record<string, string> = { low: '#10B981', medium: '#F59E0B', high: '#EF4444', critical: '#7F1D1D' };

const ProgramSupportScreen: React.FC = () => {
  const { slug } = useProgram();
  const [logs, setLogs] = useState<AuPairSupportLog[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const load = useCallback(async () => {
    if (!slug) { setLoading(false); return; }
    try { setLogs(await programEngineService.getSupportLogs(slug)); } finally { setLoading(false); setRefreshing(false); }
  }, [slug]);

  useEffect(() => { load(); }, [load]);

  return (
    <SafeAreaView style={styles.safe}>
      <ScreenHeader title="Support" />
      {loading ? <ActivityIndicator size="large" color="#E52224" style={{ marginTop: 80 }} /> : (
        <ScrollView contentContainerStyle={styles.scroll} refreshControl={<RefreshControl refreshing={refreshing} onRefresh={() => { setRefreshing(true); load(); }} />}>
          {logs.length === 0 ? (
            <EmptyState icon="headset-outline" title="Sin registros de soporte" message="Cuando ya estés en el programa, los seguimientos de tu coordinador aparecerán acá." />
          ) : logs.map(log => (
            <View key={log.id} style={styles.card}>
              <View style={styles.cardHead}>
                <Text style={styles.type}>{log.log_type_label}</Text>
                {log.severity && <View style={[styles.sevDot, { backgroundColor: SEVERITY_COLOR[log.severity] || '#777' }]}><Text style={styles.sevText}>{(log.severity_label || log.severity).toUpperCase()}</Text></View>}
              </View>
              {log.title && <Text style={styles.cardTitle}>{log.title}</Text>}
              {log.description && <Text style={styles.description}>{log.description}</Text>}
              <Text style={styles.date}>{log.log_date}</Text>
              {log.resolution && <View style={styles.resolution}><Ionicons name="checkmark-circle" size={14} color="#10B981" /><Text style={styles.resolutionText}>{log.resolution}</Text></View>}
            </View>
          ))}
        </ScrollView>
      )}
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: '#f4f4f5' },
  scroll: { padding: 16, paddingBottom: 40 },
  card: { backgroundColor: '#fff', padding: 14, borderRadius: 10, marginBottom: 10 },
  cardHead: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
  type: { fontSize: 12, fontWeight: '700', color: '#777', letterSpacing: 0.3 },
  sevDot: { paddingHorizontal: 6, paddingVertical: 2, borderRadius: 8 },
  sevText: { color: '#fff', fontSize: 9, fontWeight: '800' },
  cardTitle: { fontSize: 15, fontWeight: '700', color: '#222', marginTop: 4 },
  description: { color: '#444', marginTop: 4, lineHeight: 18 },
  date: { fontSize: 11, color: '#777', marginTop: 6 },
  resolution: { flexDirection: 'row', alignItems: 'flex-start', gap: 4, marginTop: 8, backgroundColor: '#ECFDF5', padding: 8, borderRadius: 6 },
  resolutionText: { color: '#065F46', fontSize: 12, flex: 1, lineHeight: 16 },
});

export default ProgramSupportScreen;
