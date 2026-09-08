import React, { useCallback, useState } from 'react';
import {
  View, Text, StyleSheet, ScrollView, ActivityIndicator, TouchableOpacity, RefreshControl
} from 'react-native';
import { SafeAreaView } from '../../components/SafeArea';
import { Ionicons } from '@expo/vector-icons';
import { useNavigation } from '@react-navigation/native';
import { useAuth } from '../../contexts/AuthContext';
import { useProgram } from '../../contexts/ProgramContext';
import StageProgress, { stageColor } from '../../components/program/StageProgress';
import NextActionCard from '../../components/program/NextActionCard';
import EmptyState from '../../components/EmptyState';
import { MODULE_ICONS, MODULE_SCREENS } from '../../navigation/programFlowRegistry';
import { EngineNextAction } from '../../types/programEngine';

/**
 * Dashboard genérico para programas del motor. Todo lo que muestra viene del
 * envelope: etapas, próxima acción, módulos habilitados, gates y checklist.
 */
const ProgramDashboardScreen: React.FC = () => {
  const navigation = useNavigation<any>();
  const { user } = useAuth();
  const { flow, loading, envelope, refresh } = useProgram();
  const [refreshing, setRefreshing] = useState(false);

  const onRefresh = useCallback(async () => { setRefreshing(true); await refresh(); setRefreshing(false); }, [refresh]);

  const go = (screen: string | null, params?: Record<string, any> | null) => {
    if (!screen) return;
    try { navigation.navigate(screen, params || undefined); } catch { /* pantalla no registrada */ }
  };

  if (loading && !envelope) {
    return <SafeAreaView style={styles.safe}><ActivityIndicator size="large" color="#E52224" style={{ marginTop: 80 }} /></SafeAreaView>;
  }

  if (flow === 'aupair') {
    // El usuario tiene postulación Au Pair: su flujo es el específico.
    navigation.reset({ index: 0, routes: [{ name: 'AuPairDashboard' }] });
    return null;
  }

  if (!envelope) {
    return (
      <SafeAreaView style={styles.safe}>
        <EmptyState
          icon="rocket-outline"
          title="¡Bienvenido!"
          message="Todavía no postulaste a un programa. Elegí uno del catálogo para comenzar."
          actionLabel="Ver programas"
          onAction={() => navigation.navigate('PublicPrograms')}
        />
      </SafeAreaView>
    );
  }

  const approved = envelope.application_approved;
  const currentIdx = Math.max(0, envelope.stages.findIndex(s => s.key === envelope.current_stage));
  const color = stageColor(currentIdx);
  const currentStage = envelope.stages[currentIdx];
  const modules = Object.entries(envelope.modules).filter(([, m]) => m.enabled && m.implemented);

  return (
    <SafeAreaView style={styles.safe}>
      <ScrollView refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} />} contentContainerStyle={styles.scroll}>
        <View style={styles.header}>
          <View style={{ flex: 1 }}>
            <Text style={styles.hello}>Hola, {user?.name?.split(' ')[0] || 'participante'} 👋</Text>
            <Text style={styles.subtitle}>{envelope.program.name}</Text>
            {currentStage && <Text style={[styles.stageChip, { color }]}>● {currentStage.label}</Text>}
          </View>
          <View style={[styles.progressBubble, { borderColor: color }]}><Text style={[styles.progressNumber, { color }]}>{envelope.progress_pct}%</Text></View>
        </View>

        {envelope.status === 'cancelled' && (
          <View style={[styles.banner, styles.bannerDanger]}><Ionicons name="close-circle-outline" size={22} color="#991B1B" /><View style={{ flex: 1, marginLeft: 10 }}><Text style={[styles.bannerTitle, { color: '#991B1B' }]}>Proceso cancelado</Text><Text style={[styles.bannerText, { color: '#991B1B' }]}>Contactá al equipo IE para más información.</Text></View></View>
        )}
        {envelope.status === 'active' && !approved && (
          <View style={styles.banner}>
            <Ionicons name="time-outline" size={22} color="#92400E" />
            <View style={{ flex: 1, marginLeft: 10 }}>
              <Text style={styles.bannerTitle}>Aprobación pendiente</Text>
              <Text style={styles.bannerText}>El equipo de IE está revisando tu postulación. Cuando sea aprobada vas a poder cargar tus documentos y continuar con el proceso.</Text>
            </View>
          </View>
        )}
        {envelope.status === 'completed' && (
          <View style={[styles.banner, styles.bannerSuccess]}><Ionicons name="trophy-outline" size={22} color="#065F46" /><View style={{ flex: 1, marginLeft: 10 }}><Text style={[styles.bannerTitle, { color: '#065F46' }]}>¡Programa completado!</Text><Text style={[styles.bannerText, { color: '#065F46' }]}>Gracias por vivir esta experiencia con IE.</Text></View></View>
        )}

        {approved && envelope.status === 'active' && (
          <NextActionCard action={envelope.next_action} color={color} onPress={(a: EngineNextAction) => go(a.screen, a.params)} />
        )}

        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Tu recorrido</Text>
          <View style={styles.stagesWrap}><StageProgress stages={envelope.stages} /></View>
        </View>

        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Accesos rápidos</Text>
          <View style={styles.grid}>
            {approved && <Shortcut icon="document-text-outline" label="Documentos" onPress={() => navigation.navigate('ProgramDocuments')} />}
            <Shortcut icon="card-outline" label="Pagos" onPress={() => navigation.navigate('Payments')} />
            {modules.map(([key, m]) => {
              const screen = MODULE_SCREENS[key] || m.screen;
              const locked = key === 'job_pool' && !m.access;
              return (
                <Shortcut key={key} icon={(MODULE_ICONS[key] as any) || 'apps-outline'} label={m.label} badge={locked ? 'lock-closed' : undefined} onPress={() => go(screen)} />
              );
            })}
          </View>
        </View>

        {(envelope.gates.length > 0 || envelope.checklist.length > 0) && (
          <View style={styles.section}>
            <Text style={styles.sectionTitle}>Estado actual</Text>
            <View style={styles.statusList}>
              {envelope.gates.map(g => <StatusRow key={g.key} label={g.label} value={g.verified ? 'Verificado' : 'Pendiente'} ok={g.verified} />)}
              {envelope.checklist.filter(c => c.stage_key === envelope.current_stage || !c.stage_key).map(c => <StatusRow key={c.key} label={c.label} value={c.done ? 'Completo' : 'Pendiente'} ok={c.done} />)}
              {envelope.modules.english_test?.enabled && (
                <StatusRow label={`Inglés (mín. ${envelope.modules.english_test.min_level})`} value={envelope.modules.english_test.best_level || 'Sin evaluar'} ok={!!envelope.modules.english_test.meets_minimum} />
              )}
            </View>
          </View>
        )}
      </ScrollView>
    </SafeAreaView>
  );
};

const Shortcut: React.FC<{ icon: keyof typeof Ionicons.glyphMap; label: string; badge?: keyof typeof Ionicons.glyphMap; onPress: () => void }> = ({ icon, label, badge, onPress }) => (
  <TouchableOpacity style={styles.shortcut} onPress={onPress}>
    <Ionicons name={icon} size={24} color="#E52224" />
    <Text style={styles.shortcutLabel} numberOfLines={1}>{label}</Text>
    {badge && <Ionicons name={badge} size={12} color="#9CA3AF" style={styles.shortcutBadge} />}
  </TouchableOpacity>
);

const StatusRow: React.FC<{ label: string; value: string; ok: boolean }> = ({ label, value, ok }) => (
  <View style={styles.statusRow}>
    <Text style={styles.statusLabel} numberOfLines={1}>{label}</Text>
    <View style={styles.statusRight}>
      <Ionicons name={ok ? 'checkmark-circle' : 'time-outline'} size={16} color={ok ? '#10B981' : '#F59E0B'} />
      <Text style={[styles.statusValue, { color: ok ? '#065F46' : '#92400E' }]}>{value}</Text>
    </View>
  </View>
);

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: '#f4f4f5' },
  scroll: { paddingBottom: 100 },
  header: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingHorizontal: 18, paddingTop: 14, paddingBottom: 6 },
  hello: { fontSize: 20, fontWeight: '800', color: '#222' },
  subtitle: { color: '#666', marginTop: 2 },
  stageChip: { marginTop: 4, fontSize: 12, fontWeight: '700' },
  progressBubble: { width: 56, height: 56, borderRadius: 28, borderWidth: 3, alignItems: 'center', justifyContent: 'center' },
  progressNumber: { fontWeight: '800', fontSize: 14 },
  banner: { flexDirection: 'row', alignItems: 'flex-start', backgroundColor: '#FEF3C7', borderColor: '#F59E0B', borderWidth: 1, borderRadius: 12, padding: 14, marginHorizontal: 18, marginTop: 12 },
  bannerDanger: { backgroundColor: '#FEE2E2', borderColor: '#EF4444' },
  bannerSuccess: { backgroundColor: '#D1FAE5', borderColor: '#10B981' },
  bannerTitle: { fontWeight: '800', color: '#92400E', fontSize: 15, marginBottom: 2 },
  bannerText: { color: '#92400E', fontSize: 13, lineHeight: 18 },
  section: { paddingHorizontal: 18, marginTop: 18 },
  sectionTitle: { fontSize: 14, fontWeight: '700', color: '#555', marginBottom: 8 },
  stagesWrap: { backgroundColor: '#fff', borderRadius: 12, padding: 12 },
  grid: { flexDirection: 'row', flexWrap: 'wrap', gap: 10 },
  shortcut: { width: '48%', backgroundColor: '#fff', paddingVertical: 18, borderRadius: 12, alignItems: 'center', position: 'relative' },
  shortcutLabel: { marginTop: 6, fontWeight: '600', color: '#333', fontSize: 13 },
  shortcutBadge: { position: 'absolute', top: 8, right: 8 },
  statusList: { backgroundColor: '#fff', borderRadius: 12, paddingVertical: 4 },
  statusRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingHorizontal: 14, paddingVertical: 12, borderBottomWidth: 1, borderBottomColor: '#f4f4f5' },
  statusLabel: { color: '#444', flex: 1, marginRight: 8 },
  statusRight: { flexDirection: 'row', alignItems: 'center', gap: 6 },
  statusValue: { fontWeight: '600', fontSize: 13 },
});

export default ProgramDashboardScreen;
