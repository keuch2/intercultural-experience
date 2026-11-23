import React, { useCallback, useEffect, useState } from 'react';
import {
  View, Text, StyleSheet, ScrollView, TouchableOpacity, ActivityIndicator
} from 'react-native';
import { SafeAreaView } from '../../components/SafeArea';
import { useNavigation } from '@react-navigation/native';
import { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { publicService, PublicProgram } from '../../services/api';
import PublicProgramCard from '../../components/public/PublicProgramCard';
import EmptyState from '../../components/EmptyState';
import BannerCarousel from '../../components/BannerCarousel';
import { bannerService, AppBanner } from '../../services/api/bannerService';
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

  const [banners, setBanners] = useState<AppBanner[]>([]);
  useEffect(() => {
    bannerService.publicList().then(setBanners).catch(() => setBanners([]));
  }, []);

  return (
    <SafeAreaView style={styles.safe}>
      <ScrollView contentContainerStyle={styles.scroll}>
        {/* Header — solo botón de login alineado a la derecha */}
        <View style={styles.header}>
          <TouchableOpacity
            style={styles.loginBtn}
            onPress={() => requestAuth()}
          >
            <Text style={styles.loginBtnText}>Iniciar sesión</Text>
          </TouchableOpacity>
        </View>

        {/* Banners gestionados desde el admin (antes: banner fijo de Au Pair) */}
        <BannerCarousel banners={banners} onOpenProgram={(id) => navigation.navigate('PublicProgramDetail', { id })} />

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
    justifyContent: 'flex-end',
    alignItems: 'center',
    paddingHorizontal: 18,
    paddingTop: 16,
    paddingBottom: 8,
  },
  loginBtn: {
    paddingHorizontal: 14,
    paddingVertical: 8,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#E52224',
  },
  loginBtnText: { color: '#E52224', fontWeight: '700', fontSize: 13 },
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
