import React, { useCallback, useEffect, useState } from 'react';
import {
  View, Text, StyleSheet, ScrollView, ActivityIndicator,
  TouchableOpacity, SafeAreaView, RefreshControl,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useNavigation } from '@react-navigation/native';
import { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { auPairService } from '../../services/api';
import { AuPairMatch } from '../../types/aupair';
import StatusPill from '../../components/aupair/StatusPill';
import EmptyState from '../../components/EmptyState';
import { RootStackParamList } from '../../navigation/AppNavigator';

type Nav = NativeStackNavigationProp<RootStackParamList>;

const AuPairMatchesScreen: React.FC = () => {
  const navigation = useNavigation<Nav>();
  const [matches, setMatches] = useState<AuPairMatch[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const load = useCallback(async () => {
    try {
      const data = await auPairService.getMatches();
      setMatches(data);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, []);

  useEffect(() => { load(); }, [load]);

  if (loading) {
    return (
      <SafeAreaView style={styles.safe}>
        <ActivityIndicator size="large" color="#E52224" style={{ marginTop: 80 }} />
      </SafeAreaView>
    );
  }

  return (
    <SafeAreaView style={styles.safe}>
      <View style={styles.headerBar}>
        <TouchableOpacity onPress={() => navigation.goBack()}>
          <Ionicons name="arrow-back" size={24} color="#222" />
        </TouchableOpacity>
        <Text style={styles.title}>Mis Matches</Text>
        <View style={{ width: 24 }} />
      </View>

      <ScrollView
        contentContainerStyle={styles.scroll}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={() => { setRefreshing(true); load(); }} />}
      >
        {matches.length === 0 ? (
          <EmptyState
            icon="heart-outline"
            title="Sin matches aún"
            message="Cuando una familia anfitriona te haga match, lo verás acá."
          />
        ) : (
          matches.map(m => (
            <TouchableOpacity
              key={m.id}
              style={[styles.card, !m.is_active && styles.cardInactive]}
              onPress={() => (navigation as any).navigate('AuPairMatchDetail', { id: m.id })}
            >
              <View style={styles.row}>
                <View style={{ flex: 1 }}>
                  <Text style={styles.label}>{m.match_type_label}</Text>
                  <Text style={styles.location}>📍 {m.host_city}, {m.host_state}</Text>
                  {m.match_date && <Text style={styles.date}>Desde {m.match_date}</Text>}
                </View>
                <StatusPill status={m.is_active ? 'approved' : 'locked'} small label={m.is_active ? 'Activo' : 'Cerrado'} />
              </View>
            </TouchableOpacity>
          ))
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
  card: { backgroundColor: '#fff', padding: 14, borderRadius: 10, marginBottom: 10 },
  cardInactive: { opacity: 0.6 },
  row: { flexDirection: 'row', alignItems: 'center' },
  label: { fontSize: 15, fontWeight: '700', color: '#222' },
  location: { color: '#555', marginTop: 4 },
  date: { fontSize: 12, color: '#777', marginTop: 2 },
});

export default AuPairMatchesScreen;
