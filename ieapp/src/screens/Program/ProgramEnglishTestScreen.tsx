import React, { useCallback, useEffect, useState } from 'react';
import { View, Text, StyleSheet, ScrollView, ActivityIndicator, SafeAreaView } from 'react-native';
import { programEngineService } from '../../services/api';
import { useProgram } from '../../contexts/ProgramContext';
import { EngineEnglishTestsResp } from '../../types/programEngine';
import EmptyState from '../../components/EmptyState';
import StatusPill from '../../components/aupair/StatusPill';
import ScreenHeader from '../../components/program/ScreenHeader';

const ProgramEnglishTestScreen: React.FC = () => {
  const { slug } = useProgram();
  const [data, setData] = useState<EngineEnglishTestsResp | null>(null);
  const [loading, setLoading] = useState(true);

  const load = useCallback(async () => {
    if (!slug) { setLoading(false); return; }
    setLoading(true);
    try { setData(await programEngineService.getEnglishTests(slug)); } finally { setLoading(false); }
  }, [slug]);

  useEffect(() => { load(); }, [load]);

  return (
    <SafeAreaView style={styles.safe}>
      <ScreenHeader title="Test de Inglés" />
      {loading ? <ActivityIndicator size="large" color="#E52224" style={{ marginTop: 80 }} /> : (
        <ScrollView contentContainerStyle={styles.scroll}>
          {data && (
            <View style={styles.attemptsBox}>
              <Text style={styles.attemptsLabel}>Intentos</Text>
              <Text style={styles.attemptsValue}>{data.used_attempts} / {data.max_attempts}</Text>
              <Text style={styles.attemptsRemaining}>{data.remaining_attempts} restantes · nivel mínimo {data.min_level ?? 'B1'}</Text>
              {data.best_level && <View style={{ marginTop: 8 }}><StatusPill status={data.meets_minimum ? 'approved' : 'pending'} label={`Mejor nivel: ${data.best_level}`} /></View>}
            </View>
          )}
          {data?.tests.length === 0 && (
            <EmptyState icon="school-outline" title="Sin resultados todavía" message="Tus resultados de inglés los carga el equipo de IE en la oficina. Acá vas a ver tu resultado una vez registrado." />
          )}
          {data?.tests.map(t => (
            <View key={t.id} style={styles.testCard}>
              <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'flex-start' }}>
                <View style={{ flex: 1 }}><Text style={styles.examName}>{t.exam_name}</Text><Text style={styles.attemptNum}>Intento #{t.attempt_number}</Text></View>
                <StatusPill status={t.meets_minimum ? 'approved' : 'pending'} label={t.cefr_level || '—'} small />
              </View>
              <View style={styles.scoreRow}>
                <Score label="Final" value={t.final_score} />
                {t.listening_score !== null && <Score label="Listening" value={t.listening_score} />}
                {t.reading_score !== null && <Score label="Reading" value={t.reading_score} />}
                {t.oral_score ? <Score label="Oral" value={t.oral_score} /> : null}
              </View>
              {t.observations && <Text style={styles.obs}>{t.observations}</Text>}
            </View>
          ))}
        </ScrollView>
      )}
    </SafeAreaView>
  );
};

const Score: React.FC<{ label: string; value: any }> = ({ label, value }) => (
  <View><Text style={styles.scoreLabel}>{label}</Text><Text style={styles.scoreValue}>{value}</Text></View>
);

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: '#f4f4f5' },
  scroll: { padding: 16, paddingBottom: 40 },
  attemptsBox: { backgroundColor: '#fff', borderRadius: 10, padding: 14, marginBottom: 16, alignItems: 'center' },
  attemptsLabel: { fontSize: 12, color: '#777', fontWeight: '700' },
  attemptsValue: { fontSize: 28, fontWeight: '800', color: '#222' },
  attemptsRemaining: { fontSize: 12, color: '#10B981', fontWeight: '600' },
  testCard: { backgroundColor: '#fff', borderRadius: 10, padding: 14, marginBottom: 10 },
  examName: { fontSize: 15, fontWeight: '700', color: '#222' },
  attemptNum: { fontSize: 11, color: '#777' },
  scoreRow: { flexDirection: 'row', marginTop: 10, gap: 16 },
  scoreLabel: { fontSize: 10, color: '#777', fontWeight: '700' },
  scoreValue: { fontSize: 14, color: '#222', fontWeight: '700' },
  obs: { marginTop: 8, fontSize: 12, color: '#555', fontStyle: 'italic' },
});

export default ProgramEnglishTestScreen;
