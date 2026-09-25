import React, { useCallback, useEffect, useState } from 'react';
import {
  View, Text, StyleSheet, FlatList, ActivityIndicator, TouchableOpacity, RefreshControl, Alert, Linking, Image, Modal, Dimensions
} from 'react-native';
import { SafeAreaView } from '../../components/SafeArea';
import { Ionicons } from '@expo/vector-icons';
import { useNavigation } from '@react-navigation/native';
import { programEngineService } from '../../services/api';
import { useProgram } from '../../contexts/ProgramContext';
import { JobPoolOffer, JobPoolOffersResp } from '../../types/programEngine';
import EmptyState from '../../components/EmptyState';
import ScreenHeader from '../../components/program/ScreenHeader';
import { downloadAndOpen } from '../../utils/downloadFile';

/**
 * Pool de Ofertas Laborales (Work & Travel). El participante habilitado por IE ve
 * las ofertas con cupo, descarga el PDF y selecciona una con confirmación. Una vez
 * asignada no puede elegir otra salvo autorización de IE.
 */
const formatDeadline = (iso: string): string => {
  const [y, m, d] = iso.slice(0, 10).split('-');
  return y && m && d ? `${d}/${m}/${y}` : iso;
};

const JobPoolScreen: React.FC = () => {
  const [flyer, setFlyer] = useState<{ uri: string; title: string } | null>(null);
  const navigation = useNavigation<any>();
  const { slug, refresh } = useProgram();
  const [data, setData] = useState<JobPoolOffersResp | null>(null);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [selecting, setSelecting] = useState<number | null>(null);
  const [error, setError] = useState<string | null>(null);

  const load = useCallback(async () => {
    if (!slug) { setLoading(false); return; }
    try {
      setError(null);
      setData(await programEngineService.getJobPoolOffers(slug));
    } catch (e: any) {
      setError(e?.message || 'No pudimos cargar las ofertas.');
    } finally {
      setLoading(false); setRefreshing(false);
    }
  }, [slug]);

  useEffect(() => { load(); }, [load]);

  const openPdf = (offer: JobPoolOffer) => {
    if (!offer.pdf_url) { Alert.alert('Sin PDF', 'Esta oferta todavía no tiene el PDF cargado.'); return; }
    downloadAndOpen(offer.pdf_url, `oferta-${offer.job_title || offer.employer_name}.pdf`, 'application/pdf');
  };

  const select = (offer: JobPoolOffer) => {
    Alert.alert(
      'Confirmar selección',
      `¿Querés seleccionar la oferta ${offer.job_title ? `"${offer.job_title}" de ` : 'de '}${offer.employer_name} en ${offer.city}, ${offer.state}?\n\nUna vez confirmada, no podrás elegir otra oferta salvo autorización de IE.`,
      [
        { text: 'Cancelar', style: 'cancel' },
        { text: 'Sí, seleccionar', style: 'default', onPress: async () => {
          try {
            setSelecting(offer.id);
            await programEngineService.selectOffer(slug!, offer.id);
            await Promise.all([load(), refresh()]);
            Alert.alert('¡Oferta seleccionada!', 'Confirmamos tu selección. El equipo IE continuará con tu Job Placement.', [
              { text: 'Ver mi placement', onPress: () => navigation.navigate('JobPlacement') },
              { text: 'OK' },
            ]);
          } catch (e: any) {
            const code = e?.response?.data?.code;
            const msg = e?.response?.data?.message || 'No pudimos registrar tu selección.';
            Alert.alert(code === 'no_positions' ? 'Sin cupo' : 'No disponible', msg);
            load();
          } finally {
            setSelecting(null);
          }
        } },
      ],
    );
  };

  if (loading) {
    return <SafeAreaView style={styles.safe}><ScreenHeader title="Pool de Ofertas" /><ActivityIndicator size="large" color="#E52224" style={{ marginTop: 80 }} /></SafeAreaView>;
  }

  const assignment = data?.my_assignment ?? null;
  const canSelect = !!data?.access && (!assignment || !!data?.allow_reselect);

  return (
    <SafeAreaView style={styles.safe}>
      <ScreenHeader title="Pool de Ofertas Laborales" />
      {!data?.access ? (
        <EmptyState icon="lock-closed-outline" title="Acceso no habilitado" message="El equipo IE habilitará tu acceso al Pool de Ofertas cuando completes los requisitos de tu proceso." actionLabel="Actualizar" onAction={() => { setLoading(true); load(); }} />
      ) : error ? (
        <EmptyState icon="cloud-offline-outline" title="Error al cargar" message={error} actionLabel="Reintentar" onAction={load} />
      ) : (
        <FlatList
          data={data.data}
          keyExtractor={o => String(o.id)}
          contentContainerStyle={styles.list}
          refreshControl={<RefreshControl refreshing={refreshing} onRefresh={() => { setRefreshing(true); load(); }} />}
          ListHeaderComponent={
            assignment ? (
              <TouchableOpacity style={styles.assignedCard} onPress={() => navigation.navigate('JobPlacement')}>
                <Ionicons name="checkmark-circle" size={26} color="#065F46" />
                <View style={{ flex: 1, marginLeft: 10 }}>
                  <Text style={styles.assignedTitle}>Tu oferta asignada</Text>
                  <Text style={styles.assignedText}>{assignment.offer.job_title ? `${assignment.offer.job_title} · ` : ''}{assignment.offer.employer_name} · {assignment.offer.city}, {assignment.offer.state}</Text>
                  <Text style={styles.assignedHint}>{data.allow_reselect ? 'IE te autorizó a cambiar de oferta.' : 'Para cambiarla, contactá al equipo IE. Tocá para ver tu Job Placement.'}</Text>
                </View>
                <Ionicons name="chevron-forward" size={18} color="#065F46" />
              </TouchableOpacity>
            ) : (
              <Text style={styles.intro}>Puestos de trabajo disponibles para la temporada. Revisá los requisitos de cada puesto; el detalle completo (salario, funciones, housing, fechas, beneficios) está en el PDF.</Text>
            )
          }
          ListEmptyComponent={<EmptyState icon="briefcase-outline" title="No hay ofertas disponibles" message="Por ahora no hay ofertas con posiciones disponibles. Te avisaremos cuando IE publique nuevas." />}
          renderItem={({ item }) => (
            <View style={styles.card}>
              {!!item.image_url && (
                <TouchableOpacity activeOpacity={0.9} onPress={() => setFlyer({ uri: item.image_url!, title: item.job_title || item.employer_name })} accessibilityLabel="Ver flyer de la oferta">
                  <Image source={{ uri: item.image_url }} style={styles.flyer} resizeMode="cover" />
                  <View style={styles.flyerHint}><Ionicons name="expand-outline" size={13} color="#fff" /><Text style={styles.flyerHintText}>Ver flyer</Text></View>
                </TouchableOpacity>
              )}
              <View style={styles.cardHead}>
                <View style={{ flex: 1 }}>
                  <Text style={styles.jobTitle}>{item.job_title || item.employer_name}</Text>
                  {!!item.job_title && <Text style={styles.employerSub}><Ionicons name="business-outline" size={13} color="#444" /> {item.employer_name}</Text>}
                  {!!item.sponsor && <Text style={styles.employerSub}><Ionicons name="ribbon-outline" size={13} color="#444" /> Sponsor: {item.sponsor.name}</Text>}
                  <Text style={styles.location}><Ionicons name="location-outline" size={13} color="#666" /> {item.city}, {item.state}</Text>
                  {/* La fecha límite se muestra mientras la oferta siga abierta (sin seleccionado) */}
                  {!!item.application_deadline && item.positions_available > 0 && (
                    <Text style={styles.deadline}><Ionicons name="time-outline" size={13} color="#B45309" /> Postulá hasta el {formatDeadline(item.application_deadline)}</Text>
                  )}
                </View>
                <View style={styles.positionsBadge}>
                  <Text style={styles.positionsNum}>{item.positions_available}</Text>
                  <Text style={styles.positionsLabel}>{item.positions_available === 1 ? 'posición' : 'posiciones'}</Text>
                </View>
              </View>
              {!!item.requirements && (
                <View style={styles.reqBox}>
                  <Text style={styles.reqLabel}>Requisitos del puesto</Text>
                  <Text style={styles.reqText}>{item.requirements}</Text>
                </View>
              )}
              <View style={styles.actions}>
                <TouchableOpacity style={styles.btnSecondary} onPress={() => openPdf(item)}>
                  <Ionicons name="document-text-outline" size={16} color="#444" />
                  <Text style={styles.btnSecondaryText}>Descargar PDF</Text>
                </TouchableOpacity>
                {canSelect && (
                  <TouchableOpacity style={[styles.btnPrimary, selecting === item.id && styles.btnDisabled]} onPress={() => select(item)} disabled={selecting !== null}>
                    {selecting === item.id ? <ActivityIndicator color="#fff" size="small" /> : (<><Ionicons name="checkmark-circle-outline" size={16} color="#fff" /><Text style={styles.btnPrimaryText}>Seleccionar oferta</Text></>)}
                  </TouchableOpacity>
                )}
              </View>
            </View>
          )}
        />
      )}
      <Modal visible={!!flyer} transparent animationType="fade" onRequestClose={() => setFlyer(null)}>
        <View style={styles.viewer}>
          <TouchableOpacity style={styles.viewerClose} onPress={() => setFlyer(null)} accessibilityLabel="Cerrar"><Ionicons name="close" size={28} color="#fff" /></TouchableOpacity>
          {!!flyer && <Text style={styles.viewerTitle}>{flyer.title}</Text>}
          {!!flyer && <Image source={{ uri: flyer.uri }} style={styles.viewerImage} resizeMode="contain" />}
        </View>
      </Modal>
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: '#f4f4f5' },
  list: { padding: 16, paddingBottom: 40 },
  intro: { color: '#555', fontSize: 13, lineHeight: 18, marginBottom: 14 },
  assignedCard: { flexDirection: 'row', alignItems: 'center', backgroundColor: '#D1FAE5', borderColor: '#10B981', borderWidth: 1, borderRadius: 12, padding: 14, marginBottom: 14 },
  assignedTitle: { fontWeight: '800', color: '#065F46' },
  assignedText: { color: '#065F46', marginTop: 2, fontWeight: '600' },
  assignedHint: { color: '#047857', fontSize: 12, marginTop: 4 },
  card: { backgroundColor: '#fff', borderRadius: 12, padding: 14, marginBottom: 12, borderWidth: 1, borderColor: '#eee' },
  cardHead: { flexDirection: 'row', alignItems: 'flex-start' },
  employer: { fontSize: 16, fontWeight: '800', color: '#222' },
  location: { color: '#666', marginTop: 4, fontSize: 13 },
  jobTitle: { fontSize: 18, fontWeight: '800', color: '#222' },
  flyer: { width: '100%', height: 170, borderRadius: 10, marginBottom: 10, backgroundColor: '#eee' },
  flyerHint: { position: 'absolute', right: 8, bottom: 18, flexDirection: 'row', alignItems: 'center', gap: 4, backgroundColor: 'rgba(0,0,0,0.55)', paddingHorizontal: 8, paddingVertical: 4, borderRadius: 8 },
  flyerHintText: { color: '#fff', fontSize: 11, fontWeight: '600' },
  viewer: { flex: 1, backgroundColor: 'rgba(0,0,0,0.95)', justifyContent: 'center', alignItems: 'center', padding: 16 },
  viewerClose: { position: 'absolute', top: 44, right: 16, zIndex: 2, padding: 6 },
  viewerTitle: { color: '#fff', fontWeight: '700', fontSize: 16, marginBottom: 10, textAlign: 'center' },
  viewerImage: { width: Dimensions.get('window').width - 32, height: Dimensions.get('window').height * 0.75 },
  employerSub: { fontSize: 13, color: '#444', marginTop: 2 },
  reqBox: { backgroundColor: '#F8FAFC', borderRadius: 8, padding: 10, marginTop: 10, borderLeftWidth: 3, borderLeftColor: '#E52224' },
  reqLabel: { fontSize: 11, fontWeight: '700', color: '#666', textTransform: 'uppercase', marginBottom: 3 },
  reqText: { fontSize: 13, color: '#333', lineHeight: 18 },
  deadline: { fontSize: 12, color: '#B45309', fontWeight: '600', marginTop: 6 },
  positionsBadge: { alignItems: 'center', backgroundColor: '#FEF2F2', borderRadius: 10, paddingHorizontal: 12, paddingVertical: 6, marginLeft: 8 },
  positionsNum: { fontSize: 18, fontWeight: '800', color: '#E52224' },
  positionsLabel: { fontSize: 10, color: '#E52224', fontWeight: '600' },
  actions: { flexDirection: 'row', gap: 8, marginTop: 12 },
  btnSecondary: { flexDirection: 'row', alignItems: 'center', gap: 6, backgroundColor: '#F3F4F6', paddingHorizontal: 12, paddingVertical: 10, borderRadius: 8 },
  btnSecondaryText: { color: '#444', fontWeight: '600', fontSize: 13 },
  btnPrimary: { flex: 1, flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 6, backgroundColor: '#E52224', paddingVertical: 10, borderRadius: 8 },
  btnPrimaryText: { color: '#fff', fontWeight: '700', fontSize: 13 },
  btnDisabled: { backgroundColor: '#aaa' },
});

export default JobPoolScreen;
