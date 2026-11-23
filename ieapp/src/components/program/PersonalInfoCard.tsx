import React from 'react';
import { View, Text, StyleSheet, TouchableOpacity } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { PersonalInfoStatus } from '../../types/personalInfo';

/**
 * Acceso al formulario de información personal de Admisión desde los paneles (motor y Au Pair).
 * Sin confirmar: tarjeta destacada. Confirmada: fila compacta para revisar/editar.
 */
const PersonalInfoCard: React.FC<{ status?: PersonalInfoStatus | null; onPress: () => void; style?: object }> = ({ status, onPress, style }) => {
  if (!status) return null;

  if (status.confirmed) {
    return (
      <TouchableOpacity style={[styles.compact, style]} onPress={onPress} accessibilityRole="button">
        <Ionicons name="person-circle-outline" size={20} color="#2E9E5B" />
        <Text style={styles.compactText}>Información personal confirmada</Text>
        <Ionicons name="chevron-forward" size={18} color="#999" />
      </TouchableOpacity>
    );
  }

  return (
    <TouchableOpacity style={[styles.card, style]} onPress={onPress} accessibilityRole="button">
      <Ionicons name="person-circle-outline" size={28} color="#E52224" />
      <View style={{ flex: 1, marginLeft: 10 }}>
        <Text style={styles.title}>Completá tu información personal</Text>
        <Text style={styles.text}>
          {status.required
            ? 'Es obligatoria para continuar: revisá tus datos y confirmalos antes de subir documentos.'
            : 'Revisá tus datos personales, de salud y de contacto de emergencia, y confirmalos.'}
        </Text>
      </View>
      <Ionicons name="chevron-forward" size={20} color="#E52224" />
    </TouchableOpacity>
  );
};

const styles = StyleSheet.create({
  card: { flexDirection: 'row', alignItems: 'center', backgroundColor: '#FFF5F5', borderWidth: 1, borderColor: '#F5C2C3', borderRadius: 12, padding: 14, marginHorizontal: 18, marginTop: 12 },
  title: { fontSize: 15, fontWeight: '700', color: '#222' },
  text: { fontSize: 13, color: '#555', marginTop: 2, lineHeight: 18 },
  compact: { flexDirection: 'row', alignItems: 'center', gap: 8, backgroundColor: '#fff', borderRadius: 12, paddingVertical: 12, paddingHorizontal: 14, marginHorizontal: 18, marginTop: 12 },
  compactText: { flex: 1, fontSize: 14, color: '#333', fontWeight: '500' },
});

export default PersonalInfoCard;
