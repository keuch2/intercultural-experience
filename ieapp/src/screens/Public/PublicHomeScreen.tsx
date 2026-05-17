import React, { useCallback, useEffect, useState } from 'react';
import {
  View, Text, StyleSheet, ScrollView, TouchableOpacity, ActivityIndicator, SafeAreaView,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useNavigation } from '@react-navigation/native';
import { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { publicService, PublicProgram } from '../../services/api';
import PublicProgramCard from '../../components/public/PublicProgramCard';
import EmptyState from '../../components/EmptyState';
import { PublicStackParamList } from '../../navigation/PublicNavigator';
import { usePublicAuth } from '../../contexts/PublicAuthContext';

type Nav = NativeStackNavigationProp<PublicStackParamList>;

const PublicHomeScreen: React.FC = () => {
  const navigation = useNavigation<Nav>();
  const { requestAuth } = usePublicAuth();
  const [programs, setPrograms] = useState<PublicProgram[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const load = useCallback(async () => {
    try {
      setLoading(true);
      setError(null);
      const data = await publicService.getPublicPrograms();
      setPrograms(data);
    } catch (e: any) {
      setError(e?.message || 'No pudimos cargar los programas.');
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    load();
  }, [load]);

  const auPairProgram = programs.find(p => p.is_available_in_app);

  return (
    <SafeAreaView style={styles.safe}>
      <ScrollView contentContainerStyle={styles.scroll}>
        {/* Header */}
        <View style={styles.header}>
          <View>
            <Text style={styles.brand}>Experiencia Intercultural</Text>
            <Text style={styles.subtitle}>Tu pasaporte al mundo</Text>
          </View>
          <TouchableOpacity
            style={styles.loginBtn}
            onPress={() => requestAuth()}
          >
            <Text style={styles.loginBtnText}>Iniciar sesión</Text>
          </TouchableOpacity>
        </View>

        {/* Hero CTA Au Pair */}
        {auPairProgram && (
          <TouchableOpacity
            style={styles.hero}
            activeOpacity={0.9}
            onPress={() => navigation.navigate('PublicProgramDetail', { id: auPairProgram.id })}
          >
            <View style={styles.heroBadge}>
              <Ionicons name="star" size={12} color="#fff" />
              <Text style={styles.heroBadgeText}>POSTULACIÓN ABIERTA</Text>
            </View>
            <Text style={styles.heroTitle}>Postulá como Au Pair</Text>
            <Text style={styles.heroSubtitle}>
              Viajá a Estados Unidos, viví con una familia anfitriona y vivencía un año inolvidable.
            </Text>
            <View style={styles.heroCta}>
              <Text style={styles.heroCtaText}>Conocé más</Text>
              <Ionicons name="arrow-forward" size={18} color="#fff" />
            </View>
          </TouchableOpacity>
        )}

        {/* Sección de programas */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Nuestros programas</Text>
          <Text style={styles.sectionSubtitle}>
            Explorá todas nuestras opciones de intercambio cultural
          </Text>
        </View>

        {loading ? (
          <ActivityIndicator size="large" color="#E52224" style={{ marginTop: 24 }} />
        ) : error ? (
          <EmptyState
            icon="cloud-offline-outline"
            title="Error al cargar"
            message={error}
            actionLabel="Reintentar"
            onAction={load}
          />
        ) : programs.length === 0 ? (
          <EmptyState
            icon="folder-open-outline"
            title="No hay programas disponibles"
            message="Pronto publicaremos nuevas oportunidades. Volvé a chequear más tarde."
          />
        ) : (
          <View style={styles.list}>
            {programs.map(p => (
              <PublicProgramCard
                key={p.id}
                program={p}
                onPress={() => navigation.navigate('PublicProgramDetail', { id: p.id })}
              />
            ))}
          </View>
        )}

        <View style={styles.footer}>
          <Text style={styles.footerText}>¿Ya tenés cuenta?</Text>
          <TouchableOpacity onPress={() => requestAuth()}>
            <Text style={styles.footerLink}>Iniciar sesión</Text>
          </TouchableOpacity>
        </View>
      </ScrollView>
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: '#fafafa' },
  scroll: { paddingBottom: 40 },
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: 18,
    paddingTop: 16,
    paddingBottom: 8,
  },
  brand: { fontSize: 20, fontWeight: '800', color: '#E52224' },
  subtitle: { fontSize: 12, color: '#777', marginTop: 2 },
  loginBtn: {
    paddingHorizontal: 14,
    paddingVertical: 8,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#E52224',
  },
  loginBtnText: { color: '#E52224', fontWeight: '700', fontSize: 13 },
  hero: {
    margin: 18,
    padding: 22,
    borderRadius: 16,
    backgroundColor: '#3B82F6',
  },
  heroBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    alignSelf: 'flex-start',
    backgroundColor: 'rgba(255,255,255,0.2)',
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: 12,
    marginBottom: 12,
  },
  heroBadgeText: { color: '#fff', fontSize: 10, fontWeight: '800', marginLeft: 4, letterSpacing: 0.5 },
  heroTitle: { color: '#fff', fontSize: 24, fontWeight: '800', marginBottom: 8 },
  heroSubtitle: { color: '#e8f0fe', fontSize: 14, lineHeight: 20 },
  heroCta: {
    flexDirection: 'row',
    alignItems: 'center',
    marginTop: 18,
    alignSelf: 'flex-start',
    backgroundColor: 'rgba(0,0,0,0.18)',
    paddingHorizontal: 16,
    paddingVertical: 10,
    borderRadius: 8,
  },
  heroCtaText: { color: '#fff', fontWeight: '700', marginRight: 6 },
  section: { paddingHorizontal: 18, paddingTop: 10, paddingBottom: 6 },
  sectionTitle: { fontSize: 18, fontWeight: '700', color: '#222' },
  sectionSubtitle: { fontSize: 13, color: '#777', marginTop: 4 },
  list: { paddingHorizontal: 18, paddingTop: 8 },
  footer: {
    flexDirection: 'row',
    justifyContent: 'center',
    alignItems: 'center',
    marginTop: 18,
    gap: 6,
  },
  footerText: { color: '#777' },
  footerLink: { color: '#E52224', fontWeight: '700', marginLeft: 6 },
});

export default PublicHomeScreen;
