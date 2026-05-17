import React from 'react';
import { View, Text, StyleSheet } from 'react-native';

type Status = 'missing' | 'pending' | 'approved' | 'rejected' | 'locked' | 'in_progress' | 'complete' | 'verified';

interface Props {
  status: Status | string;
  label?: string;
  small?: boolean;
}

const COLORS: Record<string, { bg: string; fg: string; label: string }> = {
  missing: { bg: '#F3F4F6', fg: '#6B7280', label: 'Falta' },
  pending: { bg: '#FEF3C7', fg: '#92400E', label: 'En revisión' },
  approved: { bg: '#D1FAE5', fg: '#065F46', label: 'Aprobado' },
  verified: { bg: '#D1FAE5', fg: '#065F46', label: 'Verificado' },
  rejected: { bg: '#FEE2E2', fg: '#991B1B', label: 'Rechazado' },
  locked: { bg: '#F3F4F6', fg: '#9CA3AF', label: 'Bloqueado' },
  in_progress: { bg: '#DBEAFE', fg: '#1E40AF', label: 'En curso' },
  complete: { bg: '#D1FAE5', fg: '#065F46', label: 'Completo' },
};

const StatusPill: React.FC<Props> = ({ status, label, small }) => {
  const cfg = COLORS[status] || { bg: '#F3F4F6', fg: '#6B7280', label: status };
  return (
    <View style={[styles.pill, { backgroundColor: cfg.bg }, small && styles.small]}>
      <Text style={[styles.text, { color: cfg.fg }, small && styles.textSmall]}>
        {label || cfg.label}
      </Text>
    </View>
  );
};

const styles = StyleSheet.create({
  pill: {
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: 10,
    alignSelf: 'flex-start',
  },
  small: { paddingHorizontal: 8, paddingVertical: 2 },
  text: { fontSize: 12, fontWeight: '700' },
  textSmall: { fontSize: 10 },
});

export default StatusPill;
