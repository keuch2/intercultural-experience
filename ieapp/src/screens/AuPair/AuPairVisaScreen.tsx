import React, { useCallback, useEffect, useState } from 'react';
import {
  View, Text, StyleSheet, ScrollView, ActivityIndicator,
  TouchableOpacity, SafeAreaView, RefreshControl,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useNavigation } from '@react-navigation/native';
import { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { auPairService } from '../../services/api';
import { AuPairVisaProcessData } from '../../types/aupair';
import VisaTimeline from '../../components/aupair/VisaTimeline';
import EmptyState from '../../components/EmptyState';
import { RootStackParamList } from '../../navigation/AppNavigator';

type Nav = NativeStackNavigationProp<RootStackParamList>;

const AuPairVisaScreen: React.FC = () => {
  const navigation = useNavigation<Nav>();
  const [data, setData] = useState<AuPairVisaProcessData | null>(null);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const load = useCallback(async () => {
    try {
      const d = await auPairService.getVisaProcess();
      setData(d);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, []);

  useEffect(() => { load(); }, [load]);

  const onRefresh = () => { setRefreshing(true); load(); };

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
        <Text style={styles.title}>Proceso de Visa</Text>
        <View style={{ width: 24 }} />
      </View>

      <ScrollView
        contentContainerStyle={styles.scroll}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} />}
      >
        {!data?.has_visa_process ? (
          <EmptyState
            icon="airplane-outline"
            title="Visa no iniciada"
            message={data?.message || 'El proceso de visa empezará cuando completes admisión y aplicación.'}
          />
        ) : (
          <>
            <View style={styles.progressBox}>
              <Text style={styles.progressLabel}>Progreso</Text>
              <Text style={styles.progressValue}>{data.progress_pct ?? 0}%</Text>
              <View style={styles.bar}>
                <View style={[styles.barFill, { width: `${data.progress_pct ?? 0}%` }]} />
              </View>
            </View>

            {data.interview && (data.interview.date || data.interview.embassy) && (
              <View style={styles.card}>
                <Text style={styles.sectionTitle}>Entrevista consular</Text>
                {data.interview.date && (
                  <Text style={styles.cardLine}>📅 {data.interview.date} {data.interview.time ?? ''}</Text>
                )}
                {data.interview.embassy && (
                  <Text style={styles.cardLine}>📍 {data.interview.embassy}</Text>
                )}
                {data.interview.result_label && (
                  <Text style={styles.cardLine}>Resultado: <Text style={{ fontWeight: '700' }}>{data.interview.result_label}</Text></Text>
                )}
              </View>
            )}

            <View style={styles.card}>
              <Text style={styles.sectionTitle}>Línea de tiempo</Text>
              {data.timeline ? <VisaTimeline items={data.timeline} /> : null}
            </View>

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
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: '#f4f4f5' },
  headerBar: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', padding: 14, backgroundColor: '#fff' },
  title: { fontSize: 17, fontWeight: '700' },
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

export default AuPairVisaScreen;
