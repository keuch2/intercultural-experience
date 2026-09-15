import React, { useCallback, useEffect, useState } from 'react';
import { View, Text, StyleSheet, ScrollView, ActivityIndicator, TouchableOpacity, RefreshControl, Linking } from 'react-native';
import { SafeAreaView } from '../../components/SafeArea';
import { Ionicons } from '@expo/vector-icons';
import { useNavigation } from '@react-navigation/native';
import { programEngineService } from '../../services/api';
import { useProgram } from '../../contexts/ProgramContext';
import { PlacementData } from '../../types/programEngine';
import EmptyState from '../../components/EmptyState';
import ScreenHeader from '../../components/program/ScreenHeader';
import { downloadAndOpen } from '../../utils/downloadFile';
import StatusPill from '../../components/aupair/StatusPill';

const STATUS_STEPS = ['pending', 'in_progress', 'documents_complete', 'ds_shipped', 'completed'];
const STATUS_COLOR: Record<string, string> = { pending: 'missing', in_progress: 'in_progress', documents_complete: 'pending', ds_shipped: 'in_progress', completed: 'complete', cancelled: 'rejected' };

/** Job Placement (solo lectura): la oferta asignada, fechas, Sponsor y datos SEVIS / DS-2019. */
const JobPlacementScreen: React.FC = () => {
  const navigation = useNavigation<any>();
  const { slug } = useProgram();
  const [data, setData] = useState<PlacementData | null>(null);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const load = useCallback(async () => {
    if (!slug) { setLoading(false); return; }
    try { setData(await programEngineService.getPlacement(slug)); } finally { setLoading(false); setRefreshing(false); }
  }, [slug]);

  useEffect(() => { load(); }, [load]);

  if (loading) {
    return <SafeAreaView style={styles.safe}><ScreenHeader title="Job Placement" /><ActivityIndicator size="large" color="#E52224" style={{ marginTop: 80 }} /></SafeAreaView>;
  }

  const offer = data?.offer;
  const p = data?.placement;
  const stepIdx = p ? STATUS_STEPS.indexOf(p.status) : -1;

  return (
    <SafeAreaView style={styles.safe}>
      <ScreenHeader title="Job Placement" />
      <ScrollView contentContainerStyle={styles.scroll} refreshControl={<RefreshControl refreshing={refreshing} onRefresh={() => { setRefreshing(true); load(); }} />}>
        {!data?.has_assignment || !offer ? (
          <EmptyState icon="business-outline" title="Sin oferta asignada" message="Cuando selecciones una oferta en el Pool, acá vas a ver tu Job Placement." actionLabel="Ir al Pool de Ofertas" onAction={() => navigation.navigate('JobPool')} />
        ) : (
          <>
            <View style={styles.offerCard}>
              <View style={styles.offerHead}>
                <Ionicons name="business" size={26} color="#E52224" />
                <View style={{ flex: 1, marginLeft: 10 }}>
                  <Text style={styles.employer}>{offer.job_title || offer.employer_name}</Text>
                  {!!offer.job_title && <Text style={styles.location}>{offer.employer_name}</Text>}
                  <Text style={styles.location}>{offer.city}, {offer.state}</Text>
                </View>
                {p && <StatusPill status={STATUS_COLOR[p.status] || 'missing'} label={p.status_label} small />}
              </View>
              <View style={styles.grid}>
                <Field label="Fecha de aceptación" value={p?.acceptance_date || offer.selected_at?.slice(0, 10)} />
                <Field label="Sponsor" value={p?.sponsor} />
                <Field label="Inicio del programa" value={p?.program_start_date} />
                <Field label="Fin del programa" value={p?.program_end_date} />
              </View>
              {offer.pdf_url && (
                <TouchableOpacity style={styles.pdfBtn} onPress={() => downloadAndOpen(offer.pdf_url!, `oferta-${offer.job_title || offer.employer_name}.pdf`, 'application/pdf')}>
                  <Ionicons name="document-text-outline" size={16} color="#444" /><Text style={styles.pdfBtnText}>Descargar PDF de la oferta</Text>
                </TouchableOpacity>
              )}
            </View>

            <View style={styles.card}>
              <Text style={styles.sectionTitle}>Estado del placement</Text>
              {STATUS_STEPS.map((s, i) => {
                const done = stepIdx >= i && p?.status !== 'cancelled';
                const labels: Record<string, string> = { pending: 'Oferta asignada', in_progress: 'Sponsor y documentación en proceso', documents_complete: 'Documentación del Sponsor completa', ds_shipped: 'DS-2019 enviado', completed: 'DS-2019 recibido · Placement completo' };
                return (
                  <View key={s} style={styles.stepRow}>
                    <Ionicons name={done ? 'checkmark-circle' : 'ellipse-outline'} size={20} color={done ? '#10B981' : '#D1D5DB'} />
                    <Text style={[styles.stepText, done && styles.stepDone]}>{labels[s]}</Text>
                  </View>
                );
              })}
              {p?.status === 'cancelled' && <Text style={styles.cancelled}>Placement cancelado. Contactá al equipo IE.</Text>}
            </View>

            {(p?.sevis_number || p?.ds2019_number || p?.ds_tracking_number) && (
              <View style={styles.card}>
                <Text style={styles.sectionTitle}>SEVIS y DS-2019</Text>
                <View style={styles.grid}>
                  <Field label="Número SEVIS" value={p?.sevis_number} mono />
                  <Field label="Número DS-2019" value={p?.ds2019_number} mono />
                  <Field label="Envío del DS-2019" value={p?.ds_tracking_number ? `${p.ds_tracking_carrier ? p.ds_tracking_carrier + ' · ' : ''}${p.ds_tracking_number}` : null} mono />
                  <Field label="Fecha de recepción" value={p?.ds_received_at} />
                </View>
              </View>
            )}
            {p?.terms_accepted_at && <Text style={styles.terms}><Ionicons name="checkmark" size={12} color="#10B981" /> Términos y condiciones del Sponsor aceptados el {p.terms_accepted_at.slice(0, 10)}.</Text>}
          </>
        )}
      </ScrollView>
    </SafeAreaView>
  );
};

const Field: React.FC<{ label: string; value?: string | null; mono?: boolean }> = ({ label, value, mono }) => (
  <View style={styles.field}>
    <Text style={styles.fieldLabel}>{label}</Text>
    <Text style={[styles.fieldValue, mono && { fontFamily: 'monospace' }, !value && { color: '#9CA3AF' }]}>{value || 'Pendiente'}</Text>
  </View>
);

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: '#f4f4f5' },
  scroll: { padding: 16, paddingBottom: 40 },
  offerCard: { backgroundColor: '#fff', borderRadius: 12, padding: 16, marginBottom: 14, borderWidth: 1, borderColor: '#eee' },
  offerHead: { flexDirection: 'row', alignItems: 'center', marginBottom: 12 },
  employer: { fontSize: 17, fontWeight: '800', color: '#222' },
  location: { color: '#666', marginTop: 2 },
  grid: { flexDirection: 'row', flexWrap: 'wrap', gap: 10 },
  field: { width: '47%' },
  fieldLabel: { fontSize: 10, color: '#777', fontWeight: '700', textTransform: 'uppercase' },
  fieldValue: { color: '#222', fontWeight: '600', marginTop: 2 },
  pdfBtn: { flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 6, backgroundColor: '#F3F4F6', paddingVertical: 10, borderRadius: 8, marginTop: 14 },
  pdfBtnText: { color: '#444', fontWeight: '600', fontSize: 13 },
  card: { backgroundColor: '#fff', borderRadius: 12, padding: 16, marginBottom: 14 },
  sectionTitle: { fontSize: 14, fontWeight: '700', color: '#444', marginBottom: 10 },
  stepRow: { flexDirection: 'row', alignItems: 'center', gap: 10, paddingVertical: 6 },
  stepText: { color: '#9CA3AF', flex: 1 },
  stepDone: { color: '#222', fontWeight: '600' },
  cancelled: { color: '#991B1B', marginTop: 8, fontWeight: '600' },
  terms: { color: '#555', fontSize: 12, marginTop: 4 },
});

export default JobPlacementScreen;
