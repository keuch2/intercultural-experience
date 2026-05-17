import React, { useCallback, useEffect, useState } from 'react';
import {
  View, Text, StyleSheet, ScrollView, Image, ActivityIndicator,
  TouchableOpacity, SafeAreaView, Linking, Alert,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useNavigation, useRoute, RouteProp } from '@react-navigation/native';
import { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { publicService, PublicProgram, settingsService } from '../../services/api';
import EmptyState from '../../components/EmptyState';
import { PublicStackParamList } from '../../navigation/PublicNavigator';
import { usePublicAuth } from '../../contexts/PublicAuthContext';

type Nav = NativeStackNavigationProp<PublicStackParamList>;
type RouteP = RouteProp<PublicStackParamList, 'PublicProgramDetail'>;

const PublicProgramDetailScreen: React.FC = () => {
  const navigation = useNavigation<Nav>();
  const route = useRoute<RouteP>();
  const { requestAuth } = usePublicAuth();
  const { id } = route.params;

  const [program, setProgram] = useState<PublicProgram | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const load = useCallback(async () => {
    try {
      setLoading(true);
      setError(null);
      const data = await publicService.getPublicProgram(id);
      if (!data) {
        setError('Programa no encontrado.');
      } else {
        setProgram(data);
      }
    } catch (e: any) {
      setError(e?.message || 'Error al cargar el programa.');
    } finally {
      setLoading(false);
    }
  }, [id]);

  useEffect(() => { load(); }, [load]);

  const openWhatsApp = async () => {
    try {
      const settings = await settingsService.getWhatsAppSettings();
      const number = settings?.data?.whatsapp_support_number;
      if (!number) {
        Alert.alert('WhatsApp no disponible', 'Por favor escribinos por otro medio.');
        return;
      }
      const message = `Hola! Me interesa el programa "${program?.name}". ¿Podrían darme más información?`;
      const urls = settingsService.createWhatsAppUrl(number, message);
      const canOpen = await Linking.canOpenURL(urls.appUrl);
      await Linking.openURL(canOpen ? urls.appUrl : urls.webUrl);
    } catch (err) {
      Alert.alert('Error', 'No pudimos abrir WhatsApp.');
    }
  };

  const handleApply = () => {
    // V1: postular requiere crear cuenta o iniciar sesión.
    // AppNavigator detecta el authRequest y monta AuthNavigator.
    requestAuth({ redirectTo: 'AuPairOnboarding', programId: id });
  };

  if (loading) {
    return (
      <SafeAreaView style={styles.safe}>
        <ActivityIndicator size="large" color="#E52224" style={{ marginTop: 80 }} />
      </SafeAreaView>
    );
  }

  if (error || !program) {
    return (
      <SafeAreaView style={styles.safe}>
        <View style={styles.headerBar}>
          <TouchableOpacity onPress={() => navigation.goBack()}>
            <Ionicons name="arrow-back" size={24} color="#222" />
          </TouchableOpacity>
        </View>
        <EmptyState
          icon="alert-circle-outline"
          title="No pudimos cargar"
          message={error || 'Programa no encontrado'}
          actionLabel="Reintentar"
          onAction={load}
        />
      </SafeAreaView>
    );
  }

  const available = program.is_available_in_app;

  return (
    <SafeAreaView style={styles.safe}>
      <ScrollView contentContainerStyle={styles.scroll}>
        <View style={styles.headerBar}>
          <TouchableOpacity onPress={() => navigation.goBack()}>
            <Ionicons name="arrow-back" size={26} color="#fff" />
          </TouchableOpacity>
        </View>

        {program.image_url ? (
          <Image source={{ uri: program.image_url }} style={styles.heroImage} />
        ) : (
          <View style={[styles.heroImage, styles.heroFallback]}>
            <Ionicons name="globe-outline" size={64} color="#bbb" />
          </View>
        )}

        <View style={styles.content}>
          <View style={[styles.statusBadge, available ? styles.badgeOk : styles.badgeSoon]}>
            <Ionicons
              name={available ? 'checkmark-circle' : 'time'}
              size={14}
              color="#fff"
            />
            <Text style={styles.statusText}>
              {available ? 'Disponible en la app' : 'Próximamente en la app'}
            </Text>
          </View>

          <Text style={styles.name}>{program.name}</Text>

          <View style={styles.metaRow}>
            {program.country ? (
              <View style={styles.metaItem}>
                <Ionicons name="location-outline" size={16} color="#666" />
                <Text style={styles.metaText}>{program.country}</Text>
              </View>
            ) : null}
            {program.duration ? (
              <View style={styles.metaItem}>
                <Ionicons name="time-outline" size={16} color="#666" />
                <Text style={styles.metaText}>{program.duration}</Text>
              </View>
            ) : null}
          </View>

          <Text style={styles.sectionTitle}>Descripción</Text>
          <Text style={styles.description}>
            {program.description || 'Sin descripción disponible.'}
          </Text>

          {available ? (
            <View style={styles.callout}>
              <Ionicons name="information-circle" size={20} color="#3B82F6" />
              <Text style={styles.calloutText}>
                Postulate desde la app y completá el proceso paso a paso: documentación,
                pagos, entrevista y matching con familia anfitriona.
              </Text>
            </View>
          ) : (
            <View style={styles.calloutSoon}>
              <Ionicons name="time" size={20} color="#6B7280" />
              <Text style={styles.calloutText}>
                Este programa estará disponible para postulación desde la app muy pronto.
                Mientras tanto, podés consultar con un asesor por WhatsApp.
              </Text>
            </View>
          )}
        </View>
      </ScrollView>

      <View style={styles.footer}>
        {available ? (
          <TouchableOpacity style={styles.primaryBtn} onPress={handleApply}>
            <Ionicons name="rocket-outline" size={18} color="#fff" />
            <Text style={styles.primaryBtnText}>Postular ahora</Text>
          </TouchableOpacity>
        ) : (
          <TouchableOpacity style={styles.whatsappBtn} onPress={openWhatsApp}>
            <Ionicons name="logo-whatsapp" size={18} color="#fff" />
            <Text style={styles.primaryBtnText}>Contactar por WhatsApp</Text>
          </TouchableOpacity>
        )}
      </View>
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: '#fafafa' },
  scroll: { paddingBottom: 100 },
  headerBar: {
    position: 'absolute',
    top: 12,
    left: 12,
    zIndex: 10,
    backgroundColor: 'rgba(0,0,0,0.35)',
    borderRadius: 24,
    padding: 6,
  },
  heroImage: { width: '100%', height: 240, backgroundColor: '#eee' },
  heroFallback: { alignItems: 'center', justifyContent: 'center' },
  content: { padding: 20 },
  statusBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    alignSelf: 'flex-start',
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: 12,
    marginBottom: 10,
  },
  badgeOk: { backgroundColor: '#10B981' },
  badgeSoon: { backgroundColor: '#6B7280' },
  statusText: { color: '#fff', fontSize: 11, fontWeight: '700', marginLeft: 4 },
  name: { fontSize: 24, fontWeight: '800', color: '#222', marginBottom: 12 },
  metaRow: { flexDirection: 'row', marginBottom: 18, flexWrap: 'wrap' },
  metaItem: { flexDirection: 'row', alignItems: 'center', marginRight: 16, marginBottom: 6 },
  metaText: { color: '#666', marginLeft: 4, fontSize: 13 },
  sectionTitle: { fontSize: 16, fontWeight: '700', color: '#222', marginTop: 8, marginBottom: 8 },
  description: { fontSize: 14, lineHeight: 22, color: '#444' },
  callout: {
    flexDirection: 'row',
    backgroundColor: '#EBF2FE',
    borderRadius: 10,
    padding: 14,
    marginTop: 22,
    alignItems: 'flex-start',
  },
  calloutSoon: {
    flexDirection: 'row',
    backgroundColor: '#f4f4f5',
    borderRadius: 10,
    padding: 14,
    marginTop: 22,
    alignItems: 'flex-start',
  },
  calloutText: { flex: 1, color: '#444', fontSize: 13, lineHeight: 20, marginLeft: 10 },
  footer: {
    position: 'absolute',
    bottom: 0,
    left: 0,
    right: 0,
    padding: 14,
    paddingBottom: 24,
    backgroundColor: '#fff',
    borderTopWidth: 1,
    borderTopColor: '#eee',
  },
  primaryBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#E52224',
    paddingVertical: 14,
    borderRadius: 10,
    gap: 8,
  },
  whatsappBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#25D366',
    paddingVertical: 14,
    borderRadius: 10,
    gap: 8,
  },
  primaryBtnText: { color: '#fff', fontSize: 16, fontWeight: '700' },
});

export default PublicProgramDetailScreen;
