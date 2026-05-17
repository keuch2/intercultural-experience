import React, { useEffect, useState } from 'react';
import {
  View, Text, StyleSheet, ScrollView, ActivityIndicator,
  TouchableOpacity, SafeAreaView, Linking,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useNavigation, useRoute, RouteProp } from '@react-navigation/native';
import { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { auPairService } from '../../services/api';
import { AuPairMatch } from '../../types/aupair';
import StatusPill from '../../components/aupair/StatusPill';
import EmptyState from '../../components/EmptyState';
import { RootStackParamList } from '../../navigation/AppNavigator';

type Nav = NativeStackNavigationProp<RootStackParamList>;
type RouteP = RouteProp<{ AuPairMatchDetail: { id: number } }, 'AuPairMatchDetail'>;

const AuPairMatchDetailScreen: React.FC = () => {
  const navigation = useNavigation<Nav>();
  const { id } = useRoute<RouteP>().params;
  const [match, setMatch] = useState<AuPairMatch | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    (async () => {
      const m = await auPairService.getMatch(id);
      setMatch(m);
      setLoading(false);
    })();
  }, [id]);

  if (loading) {
    return <SafeAreaView style={styles.safe}><ActivityIndicator size="large" color="#E52224" style={{ marginTop: 80 }} /></SafeAreaView>;
  }

  if (!match) {
    return (
      <SafeAreaView style={styles.safe}>
        <EmptyState icon="alert-circle-outline" title="Match no encontrado" />
      </SafeAreaView>
    );
  }

  const openMail = () => match.host_email && Linking.openURL(`mailto:${match.host_email}`);
  const openPhone = () => match.host_phone && Linking.openURL(`tel:${match.host_phone}`);

  return (
    <SafeAreaView style={styles.safe}>
      <View style={styles.headerBar}>
        <TouchableOpacity onPress={() => navigation.goBack()}>
          <Ionicons name="arrow-back" size={24} color="#222" />
        </TouchableOpacity>
        <Text style={styles.title}>Detalle del Match</Text>
        <View style={{ width: 24 }} />
      </View>

      <ScrollView contentContainerStyle={styles.scroll}>
        <View style={styles.head}>
          <View style={{ flex: 1 }}>
            <Text style={styles.matchType}>{match.match_type_label}</Text>
            {match.match_date && <Text style={styles.date}>Desde {match.match_date}</Text>}
          </View>
          <StatusPill status={match.is_active ? 'approved' : 'locked'} label={match.is_active ? 'Activo' : 'Cerrado'} />
        </View>

        <View style={styles.card}>
          <Text style={styles.sectionTitle}>Ubicación</Text>
          <Text style={styles.line}>{match.host_address || '—'}</Text>
          <Text style={styles.line}>{match.host_city}, {match.host_state}</Text>
        </View>

        <View style={styles.card}>
          <Text style={styles.sectionTitle}>Familia anfitriona</Text>
          {match.host_mom_name && <Text style={styles.line}>👩 {match.host_mom_name}</Text>}
          {match.host_dad_name && <Text style={styles.line}>👨 {match.host_dad_name}</Text>}
        </View>

        <View style={styles.card}>
          <Text style={styles.sectionTitle}>Contacto</Text>
          {match.host_email && (
            <TouchableOpacity style={styles.contactRow} onPress={openMail}>
              <Ionicons name="mail-outline" size={18} color="#3B82F6" />
              <Text style={styles.contactText}>{match.host_email}</Text>
            </TouchableOpacity>
          )}
          {match.host_phone && (
            <TouchableOpacity style={styles.contactRow} onPress={openPhone}>
              <Ionicons name="call-outline" size={18} color="#3B82F6" />
              <Text style={styles.contactText}>{match.host_phone}</Text>
            </TouchableOpacity>
          )}
        </View>

        {!match.is_active && match.end_reason && (
          <View style={styles.endBox}>
            <Text style={styles.endTitle}>Match cerrado</Text>
            <Text style={styles.endLine}>Fecha: {match.ended_at || '—'}</Text>
            <Text style={styles.endLine}>Razón: {match.end_reason}</Text>
          </View>
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
  head: { flexDirection: 'row', alignItems: 'flex-start', justifyContent: 'space-between', marginBottom: 14 },
  matchType: { fontSize: 22, fontWeight: '800', color: '#222' },
  date: { color: '#666', marginTop: 4 },
  card: { backgroundColor: '#fff', borderRadius: 10, padding: 14, marginBottom: 12 },
  sectionTitle: { fontSize: 13, fontWeight: '700', color: '#777', marginBottom: 8 },
  line: { color: '#333', marginBottom: 4, fontSize: 14 },
  contactRow: { flexDirection: 'row', alignItems: 'center', gap: 8, paddingVertical: 8 },
  contactText: { color: '#3B82F6', fontWeight: '600' },
  endBox: { backgroundColor: '#FEF2F2', borderRadius: 10, padding: 14 },
  endTitle: { fontWeight: '700', color: '#991B1B', marginBottom: 6 },
  endLine: { color: '#991B1B', fontSize: 13 },
});

export default AuPairMatchDetailScreen;
