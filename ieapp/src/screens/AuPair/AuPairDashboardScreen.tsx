import React, { useCallback, useEffect, useState } from 'react';
import {
  View, Text, StyleSheet, ScrollView, ActivityIndicator,
  TouchableOpacity, SafeAreaView, RefreshControl,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useNavigation } from '@react-navigation/native';
import { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { auPairService } from '../../services/api';
import { AuPairProcess } from '../../types/aupair';
import StageProgressIndicator from '../../components/aupair/StageProgressIndicator';
import NextActionCard from '../../components/aupair/NextActionCard';
import EmptyState from '../../components/EmptyState';
import { useAuth } from '../../contexts/AuthContext';
import { RootStackParamList } from '../../navigation/AppNavigator';

type Nav = NativeStackNavigationProp<RootStackParamList>;

const AuPairDashboardScreen: React.FC = () => {
  const navigation = useNavigation<Nav>();
  const { user } = useAuth();
  const [process, setProcess] = useState<AuPairProcess | null>(null);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [notFound, setNotFound] = useState(false);

  const load = useCallback(async () => {
    try {
      setError(null);
      const data = await auPairService.getProcess();
      if (!data) {
        setNotFound(true);
      } else {
        setProcess(data);
      }
    } catch (e: any) {
      setError(e?.message || 'Error al cargar tu proceso Au Pair');
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, []);

  useEffect(() => { load(); }, [load]);

  const onRefresh = () => { setRefreshing(true); load(); };

  const handleNextAction = (action: any) => {
    if (!action.screen) return;
    // En V1 algunas pantallas Au Pair aún no existen; las que sí, navegan.
    const target = action.screen as any;
    try {
      (navigation as any).navigate(target, action.params || {});
    } catch {
      // si la pantalla no está registrada, ignorar
    }
  };

  if (loading) {
    return (
      <SafeAreaView style={styles.safe}>
        <ActivityIndicator size="large" color="#E52224" style={{ marginTop: 80 }} />
      </SafeAreaView>
    );
  }

  if (notFound) {
    return (
      <SafeAreaView style={styles.safe}>
        <EmptyState
          icon="rocket-outline"
          title="¡Bienvenido!"
          message="Todavía no postulaste a Au Pair. Comenzá tu postulación desde Programas."
          actionLabel="Ver programas"
          onAction={() => navigation.navigate('Programs')}
        />
      </SafeAreaView>
    );
  }

  if (error || !process) {
    return (
      <SafeAreaView style={styles.safe}>
        <EmptyState
          icon="cloud-offline-outline"
          title="No pudimos cargar"
          message={error || 'Algo salió mal'}
          actionLabel="Reintentar"
          onAction={load}
        />
      </SafeAreaView>
    );
  }

  return (
    <SafeAreaView style={styles.safe}>
      <ScrollView
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} />}
        contentContainerStyle={styles.scroll}
      >
        <View style={styles.header}>
          <View>
            <Text style={styles.hello}>Hola, {user?.name?.split(' ')[0] || 'participante'} 👋</Text>
            <Text style={styles.subtitle}>Tu proceso Au Pair</Text>
          </View>
          <View style={styles.progressBubble}>
            <Text style={styles.progressNumber}>{process.progress_pct}%</Text>
          </View>
        </View>

        <NextActionCard
          action={process.next_action}
          stage={process.current_stage}
          onPress={handleNextAction}
        />

        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Tu recorrido</Text>
          <View style={styles.stagesWrap}>
            <StageProgressIndicator stages={process.stages} />
          </View>
        </View>

        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Accesos rápidos</Text>
          <View style={styles.grid}>
            <Shortcut
              icon="document-text-outline"
              label="Documentos"
              onPress={() => (navigation as any).navigate('AuPairDocuments', { stage: 'admission' })}
            />
            <Shortcut
              icon="card-outline"
              label="Pagos"
              onPress={() => navigation.navigate('Payments')}
            />
            <Shortcut
              icon="school-outline"
              label="Test de inglés"
              onPress={() => navigation.navigate('AuPairEnglishTest')}
            />
            <Shortcut
              icon="airplane-outline"
              label="Visa"
              onPress={() => navigation.navigate('AuPairVisa')}
            />
          </View>
        </View>

        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Estado actual</Text>
          <View style={styles.statusList}>
            <StatusRow label="Pago de inscripción" value={process.flags.payment_1_verified ? 'Verificado' : 'Pendiente'} ok={process.flags.payment_1_verified} />
            <StatusRow label="Contrato firmado" value={process.flags.contract_signed ? 'Sí' : 'Pendiente'} ok={process.flags.contract_signed} />
            <StatusRow label="ITEP (test inglés)" value={process.flags.itep_completed ? 'Completo' : 'Pendiente'} ok={process.flags.itep_completed} />
          </View>
        </View>
      </ScrollView>
    </SafeAreaView>
  );
};

const Shortcut: React.FC<{ icon: keyof typeof Ionicons.glyphMap; label: string; onPress: () => void }> = ({ icon, label, onPress }) => (
  <TouchableOpacity style={styles.shortcut} onPress={onPress}>
    <Ionicons name={icon} size={24} color="#E52224" />
    <Text style={styles.shortcutLabel}>{label}</Text>
  </TouchableOpacity>
);

const StatusRow: React.FC<{ label: string; value: string; ok: boolean }> = ({ label, value, ok }) => (
  <View style={styles.statusRow}>
    <Text style={styles.statusLabel}>{label}</Text>
    <View style={styles.statusRight}>
      <Ionicons
        name={ok ? 'checkmark-circle' : 'time-outline'}
        size={16}
        color={ok ? '#10B981' : '#F59E0B'}
      />
      <Text style={[styles.statusValue, { color: ok ? '#065F46' : '#92400E' }]}>{value}</Text>
    </View>
  </View>
);

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: '#f4f4f5' },
  scroll: { paddingBottom: 100 },
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: 18,
    paddingTop: 14,
    paddingBottom: 6,
  },
  hello: { fontSize: 20, fontWeight: '800', color: '#222' },
  subtitle: { color: '#666', marginTop: 2 },
  progressBubble: {
    width: 56, height: 56, borderRadius: 28,
    borderWidth: 3, borderColor: '#E52224',
    alignItems: 'center', justifyContent: 'center',
  },
  progressNumber: { color: '#E52224', fontWeight: '800', fontSize: 14 },
  section: { paddingHorizontal: 18, marginTop: 18 },
  sectionTitle: { fontSize: 14, fontWeight: '700', color: '#555', marginBottom: 8 },
  stagesWrap: { backgroundColor: '#fff', borderRadius: 12, padding: 12 },
  grid: { flexDirection: 'row', flexWrap: 'wrap', gap: 10 },
  shortcut: {
    width: '48%',
    backgroundColor: '#fff',
    paddingVertical: 18,
    borderRadius: 12,
    alignItems: 'center',
  },
  shortcutLabel: { marginTop: 6, fontWeight: '600', color: '#333' },
  statusList: { backgroundColor: '#fff', borderRadius: 12, paddingVertical: 4 },
  statusRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: 14,
    paddingVertical: 12,
    borderBottomWidth: 1,
    borderBottomColor: '#f4f4f5',
  },
  statusLabel: { color: '#444' },
  statusRight: { flexDirection: 'row', alignItems: 'center', gap: 6 },
  statusValue: { fontWeight: '600', fontSize: 13 },
});

export default AuPairDashboardScreen;
