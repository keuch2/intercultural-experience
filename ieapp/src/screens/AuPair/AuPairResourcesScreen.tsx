import React, { useCallback, useEffect, useState } from 'react';
import {
  View, Text, StyleSheet, ScrollView, ActivityIndicator,
  TouchableOpacity, SafeAreaView, Linking, RefreshControl, Alert,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useNavigation } from '@react-navigation/native';
import { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { auPairService } from '../../services/api';
import { AuPairResource } from '../../types/aupair';
import EmptyState from '../../components/EmptyState';
import { RootStackParamList } from '../../navigation/AppNavigator';

type Nav = NativeStackNavigationProp<RootStackParamList>;

const ICON_BY_TYPE: Record<string, keyof typeof Ionicons.glyphMap> = {
  PDF: 'document-text-outline',
  DOC: 'document-outline',
  VIDEO: 'play-circle-outline',
  LINK: 'link-outline',
};

const AuPairResourcesScreen: React.FC = () => {
  const navigation = useNavigation<Nav>();
  const [resources, setResources] = useState<AuPairResource[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const load = useCallback(async () => {
    try {
      setResources(await auPairService.getResources());
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, []);

  useEffect(() => { load(); }, [load]);

  const open = (r: AuPairResource) => {
    const url = r.external_url || r.download_url;
    if (!url) {
      Alert.alert('Sin archivo', 'Este recurso no tiene archivo disponible.');
      return;
    }
    Linking.openURL(url);
  };

  if (loading) {
    return <SafeAreaView style={styles.safe}><ActivityIndicator size="large" color="#E52224" style={{ marginTop: 80 }} /></SafeAreaView>;
  }

  return (
    <SafeAreaView style={styles.safe}>
      <View style={styles.headerBar}>
        <TouchableOpacity onPress={() => navigation.goBack()}>
          <Ionicons name="arrow-back" size={24} color="#222" />
        </TouchableOpacity>
        <Text style={styles.title}>Recursos</Text>
        <View style={{ width: 24 }} />
      </View>

      <ScrollView
        contentContainerStyle={styles.scroll}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={() => { setRefreshing(true); load(); }} />}
      >
        {resources.length === 0 ? (
          <EmptyState
            icon="folder-open-outline"
            title="Sin recursos disponibles"
            message="El equipo IE irá publicando guías y recursos para tu proceso."
          />
        ) : (
          resources.map(r => (
            <TouchableOpacity key={r.id} style={styles.card} onPress={() => open(r)}>
              <View style={styles.iconBox}>
                <Ionicons name={ICON_BY_TYPE[r.file_type || ''] || 'document-outline'} size={26} color="#E52224" />
              </View>
              <View style={{ flex: 1 }}>
                <Text style={styles.titleText}>{r.title}</Text>
                {r.description && <Text style={styles.desc} numberOfLines={2}>{r.description}</Text>}
                {r.file_size_formatted && <Text style={styles.meta}>{r.file_size_formatted}</Text>}
              </View>
              <Ionicons name="download-outline" size={20} color="#666" />
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
  card: { flexDirection: 'row', alignItems: 'center', gap: 12, backgroundColor: '#fff', padding: 14, borderRadius: 10, marginBottom: 10 },
  iconBox: { width: 48, height: 48, borderRadius: 24, backgroundColor: '#FEF2F2', alignItems: 'center', justifyContent: 'center' },
  titleText: { fontWeight: '700', color: '#222' },
  desc: { color: '#666', fontSize: 13, marginTop: 2 },
  meta: { fontSize: 11, color: '#999', marginTop: 4 },
});

export default AuPairResourcesScreen;
