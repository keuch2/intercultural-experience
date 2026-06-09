import React, { useCallback, useEffect, useState } from 'react';
import {
  View, Text, StyleSheet, ScrollView, ActivityIndicator,
  TouchableOpacity, SafeAreaView,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useNavigation } from '@react-navigation/native';
import { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { auPairService } from '../../services/api';
import { AuPairEnglishTestsResp, AuPairEnglishTest } from '../../types/aupair';
import EmptyState from '../../components/EmptyState';
import StatusPill from '../../components/aupair/StatusPill';
import { RootStackParamList } from '../../navigation/AppNavigator';

type Nav = NativeStackNavigationProp<RootStackParamList>;

const AuPairEnglishTestScreen: React.FC = () => {
  const navigation = useNavigation<Nav>();
  const [data, setData] = useState<AuPairEnglishTestsResp | null>(null);
  const [loading, setLoading] = useState(true);

  const load = useCallback(async () => {
    setLoading(true);
    try {
      const d = await auPairService.getEnglishTests();
      setData(d);
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => { load(); }, [load]);

  if (loading) {
    return (
      <SafeAreaView style={styles.safe}>
        <ActivityIndicator size="large" color="#E52224" style={{ marginTop: 80 }} />
      </SafeAreaView>
    );
  }

  return (
    <SafeAreaView style={styles.safe}>
      <View style={styles.headerBar}>
        <TouchableOpacity onPress={() => navigation.goBack()}>
          <Ionicons name="arrow-back" size={24} color="#222" />
        </TouchableOpacity>
        <Text style={styles.title}>Test de Inglés</Text>
        <View style={{ width: 24 }} />
      </View>

      <ScrollView contentContainerStyle={styles.scroll}>
        {data && (
          <View style={styles.attemptsBox}>
            <Text style={styles.attemptsLabel}>Intentos</Text>
            <Text style={styles.attemptsValue}>{data.used_attempts} / {data.max_attempts}</Text>
            <Text style={styles.attemptsRemaining}>{data.remaining_attempts} restantes</Text>
          </View>
        )}

        {data?.tests.length === 0 ? (
          <EmptyState
            icon="school-outline"
            title="Sin resultados todavía"
            message="Tus resultados de inglés los carga el equipo de IE en la oficina. Acá vas a ver tu resultado una vez registrado."
          />
        ) : null}

        {data?.tests.map(t => <TestCard key={t.id} test={t} />)}
      </ScrollView>
    </SafeAreaView>
  );
};

const TestCard: React.FC<{ test: AuPairEnglishTest }> = ({ test }) => (
  <View style={styles.testCard}>
    <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'flex-start' }}>
      <View style={{ flex: 1 }}>
        <Text style={styles.examName}>{test.exam_name}</Text>
        <Text style={styles.attemptNum}>Intento #{test.attempt_number}</Text>
      </View>
      <StatusPill status={test.meets_minimum ? 'approved' : 'pending'} label={test.cefr_level || '—'} small />
    </View>
    <View style={styles.scoreRow}>
      <Score label="Final" value={test.final_score} />
      {test.listening_score !== null && <Score label="Listening" value={test.listening_score} />}
      {test.reading_score !== null && <Score label="Reading" value={test.reading_score} />}
      {test.oral_score ? <Score label="Oral" value={test.oral_score} /> : null}
    </View>
  </View>
);

const Score: React.FC<{ label: string; value: any }> = ({ label, value }) => (
  <View style={styles.scoreItem}>
    <Text style={styles.scoreLabel}>{label}</Text>
    <Text style={styles.scoreValue}>{value}</Text>
  </View>
);

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: '#f4f4f5' },
  headerBar: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', padding: 14, backgroundColor: '#fff' },
  title: { fontSize: 17, fontWeight: '700' },
  scroll: { padding: 16, paddingBottom: 40 },
  attemptsBox: { backgroundColor: '#fff', borderRadius: 10, padding: 14, marginBottom: 16, alignItems: 'center' },
  attemptsLabel: { fontSize: 12, color: '#777', fontWeight: '700' },
  attemptsValue: { fontSize: 28, fontWeight: '800', color: '#222' },
  attemptsRemaining: { fontSize: 12, color: '#10B981', fontWeight: '600' },
  testCard: { backgroundColor: '#fff', borderRadius: 10, padding: 14, marginBottom: 10 },
  examName: { fontSize: 15, fontWeight: '700', color: '#222' },
  attemptNum: { fontSize: 11, color: '#777' },
  scoreRow: { flexDirection: 'row', marginTop: 10, gap: 16 },
  scoreItem: {},
  scoreLabel: { fontSize: 10, color: '#777', fontWeight: '700' },
  scoreValue: { fontSize: 14, color: '#222', fontWeight: '700' },
});

export default AuPairEnglishTestScreen;
