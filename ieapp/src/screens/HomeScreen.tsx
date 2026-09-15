import React, { useCallback, useEffect, useState } from 'react';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity, Image, RefreshControl, ActivityIndicator } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useNavigation } from '@react-navigation/native';
import { SafeAreaView } from '../components/SafeArea';
import { useAuth } from '../contexts/AuthContext';
import { useProgram, flowForApplication } from '../contexts/ProgramContext';
import { publicService } from '../services/api';
import type { PublicProgram } from '../services/api';
import { screensFor } from '../navigation/programFlowRegistry';
import { APPLICATION_STATUS_LABELS } from '../types/applications';
import { describeProgramStart } from '../utils/programDates';

/**
 * Home general del participante (tras el login): postulación actual con acceso
 * a su proceso, programas disponibles para postular y accesos rápidos.
 */
const HomeScreen: React.FC = () => {
  const navigation = useNavigation<any>();
  const { user } = useAuth();
  const { flow, loading, selectedApplication, applications, envelope, auPairProcess, refresh } = useProgram();
  const [programs, setPrograms] = useState<PublicProgram[]>([]);
  const [loadingPrograms, setLoadingPrograms] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const loadPrograms = useCallback(async () => {
    try { setPrograms(await publicService.getPublicPrograms()); } catch { /* se muestra vacío */ } finally { setLoadingPrograms(false); }
  }, []);

  useEffect(() => { loadPrograms(); }, [loadPrograms]);

  const onRefresh = async () => { setRefreshing(true); await Promise.all([refresh(), loadPrograms()]); setRefreshing(false); };

  const process = envelope ?? auPairProcess;
  const currentStage = process?.stages?.find(s => s.key === process.current_stage);
  const processHome = screensFor(flow).home;
  const goProcess = () => navigation.navigate(flow === 'none' ? 'PublicPrograms' : processHome);
  const available = programs.filter(p => p.is_available_in_app);
  const others = programs.filter(p => !p.is_available_in_app);
  const firstName = user?.name?.split(' ')[0] || 'participante';

  return (
    <SafeAreaView style={styles.safe}>
      <ScrollView contentContainerStyle={styles.scroll} refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} />}>
        <View style={styles.header}>
          <Image source={require('../../assets/images/ie-icon.png')} style={styles.brandLogo} resizeMode="contain" />
          <View style={{ flex: 1 }}>
            <Text style={styles.hello}>Hola, {firstName} 👋</Text>
            <Text style={styles.subtitle}>Bienvenido a ie · intercultural experience</Text>
          </View>
          <TouchableOpacity onPress={() => navigation.navigate('Profile')} accessibilityLabel="Mi perfil">
            {user?.avatar_url ? <Image source={{ uri: user.avatar_url }} style={styles.avatar} /> : <View style={[styles.avatar, styles.avatarFallback]}><Ionicons name="person" size={22} color="#E52224" /></View>}
          </TouchableOpacity>
        </View>

        {/* Mi postulación actual */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Mi postulación actual</Text>
          {loading && !selectedApplication ? (
            <View style={styles.card}><ActivityIndicator color="#E52224" /></View>
          ) : selectedApplication ? (
            <TouchableOpacity style={[styles.card, styles.cardAccent]} onPress={goProcess} activeOpacity={0.9}>
              <View style={styles.cardHead}>
                <View style={{ flex: 1 }}>
                  <Text style={styles.cardProgram}>{selectedApplication.program?.name}</Text>
                  <Text style={styles.cardMeta}>
                    {process?.application_approved === false
                      ? 'Aprobación pendiente'
                      : currentStage?.label ? `Etapa: ${currentStage.label}` : (APPLICATION_STATUS_LABELS[selectedApplication.status] ?? selectedApplication.status)}
                  </Text>
                </View>
                <View style={styles.progressBubble}><Text style={styles.progressNumber}>{process?.progress_pct ?? selectedApplication.progress_percentage ?? 0}%</Text></View>
              </View>
              {!!process?.program_start_date && (
                <Text style={styles.startDate}><Ionicons name="airplane-outline" size={13} color="#0369A1" /> {describeProgramStart(process.program_start_date)?.label}</Text>
              )}
              {process?.next_action?.label && (
                <View style={styles.nextAction}><Ionicons name="flash" size={14} color="#E52224" /><Text style={styles.nextActionText} numberOfLines={2}>{process.next_action.label}</Text></View>
              )}
              <View style={styles.cardCta}><Text style={styles.cardCtaText}>Ver mi proceso</Text><Ionicons name="arrow-forward" size={16} color="#E52224" /></View>
            </TouchableOpacity>
          ) : (
            <View style={styles.card}>
              <View style={styles.emptyRow}>
                <Ionicons name="rocket-outline" size={28} color="#E52224" />
                <View style={{ flex: 1, marginLeft: 12 }}>
                  <Text style={styles.cardProgram}>Todavía no postulaste a un programa</Text>
                  <Text style={styles.cardMeta}>Elegí uno de los programas disponibles y empezá tu experiencia.</Text>
                </View>
              </View>
              <TouchableOpacity style={styles.primaryBtn} onPress={() => navigation.navigate('PublicPrograms')}>
                <Text style={styles.primaryBtnText}>Ver programas</Text>
              </TouchableOpacity>
            </View>
          )}
          {applications.length > 1 && (
            <TouchableOpacity style={styles.linkRow} onPress={() => navigation.navigate('MyApplications')}>
              <Text style={styles.link}>Ver todas mis postulaciones ({applications.length})</Text><Ionicons name="chevron-forward" size={14} color="#E52224" />
            </TouchableOpacity>
          )}
        </View>

        {/* Programas disponibles */}
        <View style={styles.section}>
          <View style={styles.sectionHead}>
            <Text style={styles.sectionTitle}>Programas disponibles</Text>
            <TouchableOpacity onPress={() => navigation.navigate('PublicPrograms')}><Text style={styles.link}>Ver todos</Text></TouchableOpacity>
          </View>
          {loadingPrograms ? <ActivityIndicator color="#E52224" style={{ marginVertical: 20 }} /> : (
            <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={{ gap: 12, paddingRight: 18 }}>
              {[...available, ...others].map(p => {
                const mine = applications.find(a => a.program_id === p.id);
                return (
                  <TouchableOpacity key={p.id} style={styles.programCard} onPress={() => navigation.navigate('PublicProgramDetail', { id: p.id })} activeOpacity={0.9}>
                    {p.image_url ? <Image source={{ uri: p.image_url }} style={styles.programImage} /> : <View style={[styles.programImage, styles.programImageFallback]}><Ionicons name="earth-outline" size={30} color="#fff" /></View>}
                    <View style={styles.programBody}>
                      <Text style={styles.programName} numberOfLines={2}>{p.name}</Text>
                      {p.country && <Text style={styles.programMeta} numberOfLines={1}><Ionicons name="location-outline" size={11} /> {p.country}</Text>}
                      <View style={[styles.badge, mine ? styles.badgeMine : p.is_available_in_app ? styles.badgeOpen : styles.badgeSoon]}>
                        <Text style={styles.badgeText}>{mine ? 'Ya postulaste' : p.is_available_in_app ? 'Postulación abierta' : 'Consultar'}</Text>
                      </View>
                    </View>
                  </TouchableOpacity>
                );
              })}
              {programs.length === 0 && <Text style={styles.cardMeta}>No hay programas publicados por el momento.</Text>}
            </ScrollView>
          )}
        </View>

        {/* Accesos rápidos */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Accesos rápidos</Text>
          <View style={styles.grid}>
            <Shortcut icon="documents-outline" label="Mis postulaciones" onPress={() => navigation.navigate('MyApplications')} />
            <Shortcut icon="map-outline" label="Mi proceso" onPress={goProcess} disabled={flow === 'none'} />
            <Shortcut icon="person-circle-outline" label="Mis datos" onPress={() => navigation.navigate('Profile')} />
            <Shortcut icon="notifications-outline" label="Avisos" onPress={() => navigation.navigate('Notifications')} />
          </View>
        </View>
      </ScrollView>
    </SafeAreaView>
  );
};

const Shortcut: React.FC<{ icon: keyof typeof Ionicons.glyphMap; label: string; onPress: () => void; disabled?: boolean }> = ({ icon, label, onPress, disabled }) => (
  <TouchableOpacity style={[styles.shortcut, disabled && { opacity: 0.5 }]} onPress={onPress} disabled={disabled}>
    <Ionicons name={icon} size={24} color="#E52224" />
    <Text style={styles.shortcutLabel} numberOfLines={1}>{label}</Text>
  </TouchableOpacity>
);

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: '#f4f4f5' },
  scroll: { paddingBottom: 100 },
  header: { flexDirection: 'row', alignItems: 'center', paddingHorizontal: 18, paddingTop: 14, paddingBottom: 6 },
  brandLogo: { width: 38, height: 38, marginRight: 10 },
  hello: { fontSize: 20, fontWeight: '800', color: '#222' },
  subtitle: { color: '#666', marginTop: 2, fontSize: 13 },
  avatar: { width: 44, height: 44, borderRadius: 22 },
  avatarFallback: { backgroundColor: '#FEF2F2', alignItems: 'center', justifyContent: 'center' },
  section: { paddingHorizontal: 18, marginTop: 18 },
  sectionHead: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 8 },
  sectionTitle: { fontSize: 14, fontWeight: '700', color: '#555', marginBottom: 8 },
  card: { backgroundColor: '#fff', borderRadius: 14, padding: 16, borderWidth: 1, borderColor: '#eee' },
  cardAccent: { borderColor: '#E52224', borderLeftWidth: 4 },
  cardHead: { flexDirection: 'row', alignItems: 'center' },
  cardProgram: { fontSize: 16, fontWeight: '800', color: '#222' },
  cardMeta: { color: '#666', marginTop: 2, fontSize: 13 },
  progressBubble: { width: 52, height: 52, borderRadius: 26, borderWidth: 3, borderColor: '#E52224', alignItems: 'center', justifyContent: 'center', marginLeft: 10 },
  progressNumber: { color: '#E52224', fontWeight: '800', fontSize: 13 },
  startDate: { marginTop: 8, fontSize: 12, color: '#0369A1', fontWeight: '600' },
  nextAction: { flexDirection: 'row', alignItems: 'center', gap: 6, backgroundColor: '#FEF2F2', padding: 8, borderRadius: 8, marginTop: 12 },
  nextActionText: { color: '#7F1D1D', fontSize: 12, flex: 1 },
  cardCta: { flexDirection: 'row', alignItems: 'center', gap: 4, marginTop: 12, alignSelf: 'flex-end' },
  cardCtaText: { color: '#E52224', fontWeight: '700' },
  emptyRow: { flexDirection: 'row', alignItems: 'center' },
  primaryBtn: { backgroundColor: '#E52224', paddingVertical: 12, borderRadius: 10, alignItems: 'center', marginTop: 14 },
  primaryBtnText: { color: '#fff', fontWeight: '700' },
  linkRow: { flexDirection: 'row', alignItems: 'center', gap: 4, marginTop: 8, alignSelf: 'flex-end' },
  link: { color: '#E52224', fontWeight: '600', fontSize: 13 },
  programCard: { width: 190, backgroundColor: '#fff', borderRadius: 14, overflow: 'hidden', borderWidth: 1, borderColor: '#eee' },
  programImage: { width: '100%', height: 100 },
  programImageFallback: { backgroundColor: '#8B5CF6', alignItems: 'center', justifyContent: 'center' },
  programBody: { padding: 10 },
  programName: { fontWeight: '700', color: '#222', fontSize: 13, minHeight: 34 },
  programMeta: { color: '#777', fontSize: 11, marginTop: 4 },
  badge: { alignSelf: 'flex-start', paddingHorizontal: 8, paddingVertical: 3, borderRadius: 8, marginTop: 8 },
  badgeOpen: { backgroundColor: '#D1FAE5' },
  badgeSoon: { backgroundColor: '#F3F4F6' },
  badgeMine: { backgroundColor: '#DBEAFE' },
  badgeText: { fontSize: 10, fontWeight: '700', color: '#333' },
  grid: { flexDirection: 'row', flexWrap: 'wrap', gap: 10 },
  shortcut: { width: '48%', backgroundColor: '#fff', paddingVertical: 18, borderRadius: 12, alignItems: 'center' },
  shortcutLabel: { marginTop: 6, fontWeight: '600', color: '#333', fontSize: 13 },
});

export default HomeScreen;
