import React, { useState } from 'react';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity, RefreshControl, ActivityIndicator } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useNavigation } from '@react-navigation/native';
import { SafeAreaView } from '../components/SafeArea';
import ScreenHeader from '../components/program/ScreenHeader';
import EmptyState from '../components/EmptyState';
import { useProgram, flowForApplication, isApplicationActive } from '../contexts/ProgramContext';
import { screensFor } from '../navigation/programFlowRegistry';
import { APPLICATION_STATUS_LABELS, UserApplication } from '../types/applications';

/**
 * Historial de postulaciones del participante. Abrir una la selecciona como
 * "Mi proceso" y entra a su dashboard (Au Pair o motor).
 */
const MyApplicationsScreen: React.FC = () => {
  const navigation = useNavigation<any>();
  const { applications, selectedApplication, loading, refresh, selectApplication } = useProgram();
  const [refreshing, setRefreshing] = useState(false);
  const [opening, setOpening] = useState<number | null>(null);

  const open = async (app: UserApplication) => {
    const flow = flowForApplication(app);
    if (flow === 'none') { navigation.navigate('PublicProgramDetail', { id: app.program_id }); return; }
    setOpening(app.id);
    try { await selectApplication(app.id); } finally { setOpening(null); }
    navigation.navigate(screensFor(flow).home);
  };

  const current = applications.filter(isApplicationActive);
  const past = applications.filter(a => !isApplicationActive(a));

  const Card = ({ app }: { app: UserApplication }) => {
    const isSelected = selectedApplication?.id === app.id;
    const status = app.completed_at ? 'completed' : app.status;
    const color = status === 'approved' ? '#065F46' : status === 'rejected' || status === 'cancelled' ? '#991B1B' : status === 'completed' ? '#374151' : '#92400E';
    return (
      <TouchableOpacity style={[styles.card, isSelected && styles.cardSelected]} onPress={() => open(app)} activeOpacity={0.9}>
        <View style={styles.cardHead}>
          <View style={{ flex: 1 }}>
            <Text style={styles.program}>{app.program?.name ?? 'Programa'}</Text>
            <Text style={styles.meta}>{app.program?.subcategory}{app.applied_at ? ` · postulaste el ${app.applied_at.slice(0, 10)}` : ''}</Text>
          </View>
          {isSelected && <View style={styles.currentBadge}><Text style={styles.currentBadgeText}>Actual</Text></View>}
        </View>
        <View style={styles.row}>
          <Text style={[styles.status, { color }]}>{APPLICATION_STATUS_LABELS[status] ?? status}</Text>
          {typeof app.progress_percentage === 'number' && <Text style={styles.meta}>{app.progress_percentage}%</Text>}
        </View>
        {typeof app.progress_percentage === 'number' && (
          <View style={styles.bar}><View style={[styles.barFill, { width: `${Math.min(100, app.progress_percentage)}%` }]} /></View>
        )}
        <View style={styles.cta}>
          {opening === app.id ? <ActivityIndicator size="small" color="#E52224" /> : (<><Text style={styles.ctaText}>{flowForApplication(app) === 'none' ? 'Ver programa' : 'Ver proceso'}</Text><Ionicons name="arrow-forward" size={14} color="#E52224" /></>)}
        </View>
      </TouchableOpacity>
    );
  };

  return (
    <SafeAreaView style={styles.safe}>
      <ScreenHeader title="Mis postulaciones" />
      <ScrollView contentContainerStyle={styles.scroll} refreshControl={<RefreshControl refreshing={refreshing} onRefresh={async () => { setRefreshing(true); await refresh(); setRefreshing(false); }} />}>
        {loading && applications.length === 0 ? <ActivityIndicator size="large" color="#E52224" style={{ marginTop: 60 }} /> : applications.length === 0 ? (
          <EmptyState icon="documents-outline" title="Sin postulaciones" message="Cuando postules a un programa vas a poder seguir tu proceso desde acá." actionLabel="Ver programas" onAction={() => navigation.navigate('PublicPrograms')} />
        ) : (
          <>
            {current.length > 0 && (<><Text style={styles.sectionTitle}>En curso</Text>{current.map(a => <Card key={a.id} app={a} />)}</>)}
            {past.length > 0 && (<><Text style={styles.sectionTitle}>Anteriores</Text>{past.map(a => <Card key={a.id} app={a} />)}</>)}
            <TouchableOpacity style={styles.newBtn} onPress={() => navigation.navigate('PublicPrograms')}>
              <Ionicons name="add-circle-outline" size={18} color="#E52224" /><Text style={styles.newBtnText}>Postular a un nuevo programa</Text>
            </TouchableOpacity>
          </>
        )}
      </ScrollView>
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: '#f4f4f5' },
  scroll: { padding: 16, paddingBottom: 100 },
  sectionTitle: { fontSize: 13, fontWeight: '700', color: '#555', marginBottom: 8, marginTop: 6 },
  card: { backgroundColor: '#fff', borderRadius: 12, padding: 14, marginBottom: 12, borderWidth: 1, borderColor: '#eee' },
  cardSelected: { borderColor: '#E52224', borderLeftWidth: 4 },
  cardHead: { flexDirection: 'row', alignItems: 'flex-start' },
  program: { fontSize: 15, fontWeight: '800', color: '#222' },
  meta: { color: '#777', fontSize: 12, marginTop: 2 },
  currentBadge: { backgroundColor: '#FEF2F2', paddingHorizontal: 8, paddingVertical: 3, borderRadius: 8 },
  currentBadgeText: { color: '#E52224', fontSize: 10, fontWeight: '800' },
  row: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginTop: 10 },
  status: { fontWeight: '700', fontSize: 13 },
  bar: { height: 6, backgroundColor: '#e5e7eb', borderRadius: 4, overflow: 'hidden', marginTop: 6 },
  barFill: { height: '100%', backgroundColor: '#10B981' },
  cta: { flexDirection: 'row', alignItems: 'center', gap: 4, alignSelf: 'flex-end', marginTop: 10, minHeight: 20 },
  ctaText: { color: '#E52224', fontWeight: '700', fontSize: 13 },
  newBtn: { flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 6, borderWidth: 1, borderColor: '#E52224', borderRadius: 10, paddingVertical: 12, marginTop: 8 },
  newBtnText: { color: '#E52224', fontWeight: '700' },
});

export default MyApplicationsScreen;
