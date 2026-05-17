import React from 'react';
import { View, Text, StyleSheet, TouchableOpacity, Image } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { PublicProgram } from '../../services/api/publicService';

interface Props {
  program: PublicProgram;
  onPress: (program: PublicProgram) => void;
}

const PublicProgramCard: React.FC<Props> = ({ program, onPress }) => {
  const available = program.is_available_in_app;

  return (
    <TouchableOpacity activeOpacity={0.85} onPress={() => onPress(program)} style={styles.card}>
      <View style={styles.imageWrap}>
        {program.image_url ? (
          <Image source={{ uri: program.image_url }} style={styles.image} resizeMode="cover" />
        ) : (
          <View style={[styles.image, styles.imageFallback]}>
            <Ionicons name="globe-outline" size={42} color="#bbb" />
          </View>
        )}
        <View style={[styles.badge, available ? styles.badgeAvailable : styles.badgeSoon]}>
          <Text style={styles.badgeText}>
            {available ? 'Disponible' : 'Próximamente'}
          </Text>
        </View>
      </View>
      <View style={styles.body}>
        <Text style={styles.name} numberOfLines={2}>{program.name}</Text>
        {program.country ? (
          <View style={styles.metaRow}>
            <Ionicons name="location-outline" size={14} color="#777" />
            <Text style={styles.meta}>{program.country}</Text>
          </View>
        ) : null}
        {program.description ? (
          <Text style={styles.description} numberOfLines={2}>{program.description}</Text>
        ) : null}
      </View>
    </TouchableOpacity>
  );
};

const styles = StyleSheet.create({
  card: {
    backgroundColor: '#fff',
    borderRadius: 12,
    overflow: 'hidden',
    marginBottom: 14,
    elevation: 2,
    shadowColor: '#000',
    shadowOpacity: 0.08,
    shadowRadius: 4,
    shadowOffset: { width: 0, height: 2 },
  },
  imageWrap: { position: 'relative' },
  image: { width: '100%', height: 140, backgroundColor: '#f4f4f4' },
  imageFallback: { alignItems: 'center', justifyContent: 'center' },
  badge: {
    position: 'absolute',
    top: 10,
    right: 10,
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: 12,
  },
  badgeAvailable: { backgroundColor: '#10B981' },
  badgeSoon: { backgroundColor: '#6B7280' },
  badgeText: { color: '#fff', fontSize: 11, fontWeight: '700' },
  body: { padding: 12 },
  name: { fontSize: 16, fontWeight: '700', color: '#222', marginBottom: 4 },
  metaRow: { flexDirection: 'row', alignItems: 'center', marginBottom: 6 },
  meta: { fontSize: 12, color: '#777', marginLeft: 4 },
  description: { fontSize: 13, color: '#555', lineHeight: 18 },
});

export default PublicProgramCard;
