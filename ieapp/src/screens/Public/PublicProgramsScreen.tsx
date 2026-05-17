import React, { useCallback, useEffect, useMemo, useState } from 'react';
import {
  View, Text, StyleSheet, ScrollView, TextInput, ActivityIndicator,
  TouchableOpacity, SafeAreaView,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useNavigation } from '@react-navigation/native';
import { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { publicService, PublicProgram } from '../../services/api';
import PublicProgramCard from '../../components/public/PublicProgramCard';
import EmptyState from '../../components/EmptyState';
import { PublicStackParamList } from '../../navigation/PublicNavigator';

type Nav = NativeStackNavigationProp<PublicStackParamList>;

type Filter = 'all' | 'available' | 'soon';

const PublicProgramsScreen: React.FC = () => {
  const navigation = useNavigation<Nav>();
  const [programs, setPrograms] = useState<PublicProgram[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [query, setQuery] = useState('');
  const [filter, setFilter] = useState<Filter>('all');

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

  useEffect(() => { load(); }, [load]);

  const filtered = useMemo(() => {
    const q = query.trim().toLowerCase();
    return programs.filter(p => {
      if (filter === 'available' && !p.is_available_in_app) return false;
      if (filter === 'soon' && p.is_available_in_app) return false;
      if (!q) return true;
      return (
        p.name.toLowerCase().includes(q) ||
        (p.country || '').toLowerCase().includes(q) ||
        (p.subcategory || '').toLowerCase().includes(q)
      );
    });
  }, [programs, query, filter]);

  return (
    <SafeAreaView style={styles.safe}>
      <View style={styles.headerBar}>
        <TouchableOpacity onPress={() => navigation.goBack()}>
          <Ionicons name="arrow-back" size={24} color="#222" />
        </TouchableOpacity>
        <Text style={styles.title}>Programas</Text>
        <View style={{ width: 24 }} />
      </View>

      <View style={styles.searchWrap}>
        <Ionicons name="search" size={18} color="#888" />
        <TextInput
          style={styles.searchInput}
          placeholder="Buscar por nombre, país..."
          placeholderTextColor="#999"
          value={query}
          onChangeText={setQuery}
        />
      </View>

      <View style={styles.filterRow}>
        {(['all', 'available', 'soon'] as Filter[]).map(f => (
          <TouchableOpacity
            key={f}
            style={[styles.chip, filter === f && styles.chipActive]}
            onPress={() => setFilter(f)}
          >
            <Text style={[styles.chipText, filter === f && styles.chipTextActive]}>
              {f === 'all' ? 'Todos' : f === 'available' ? 'Disponibles' : 'Próximamente'}
            </Text>
          </TouchableOpacity>
        ))}
      </View>

      {loading ? (
        <ActivityIndicator size="large" color="#E52224" style={{ marginTop: 32 }} />
      ) : error ? (
        <EmptyState
          icon="cloud-offline-outline"
          title="Error al cargar"
          message={error}
          actionLabel="Reintentar"
          onAction={load}
        />
      ) : filtered.length === 0 ? (
        <EmptyState
          icon="search-outline"
          title="Sin resultados"
          message="Ajustá los filtros o tu búsqueda."
        />
      ) : (
        <ScrollView contentContainerStyle={styles.list}>
          {filtered.map(p => (
            <PublicProgramCard
              key={p.id}
              program={p}
              onPress={() => navigation.navigate('PublicProgramDetail', { id: p.id })}
            />
          ))}
        </ScrollView>
      )}
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: '#fafafa' },
  headerBar: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: 16,
    paddingTop: 12,
    paddingBottom: 8,
  },
  title: { fontSize: 18, fontWeight: '700', color: '#222' },
  searchWrap: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#fff',
    marginHorizontal: 16,
    paddingHorizontal: 12,
    borderRadius: 10,
    borderWidth: 1,
    borderColor: '#eee',
  },
  searchInput: { flex: 1, paddingVertical: 10, marginLeft: 8, color: '#222', fontSize: 14 },
  filterRow: { flexDirection: 'row', paddingHorizontal: 16, marginTop: 10, marginBottom: 8, gap: 8 },
  chip: {
    paddingHorizontal: 14,
    paddingVertical: 6,
    borderRadius: 16,
    borderWidth: 1,
    borderColor: '#ddd',
    backgroundColor: '#fff',
  },
  chipActive: { backgroundColor: '#E52224', borderColor: '#E52224' },
  chipText: { color: '#555', fontSize: 12, fontWeight: '600' },
  chipTextActive: { color: '#fff' },
  list: { paddingHorizontal: 16, paddingTop: 10, paddingBottom: 30 },
});

export default PublicProgramsScreen;
