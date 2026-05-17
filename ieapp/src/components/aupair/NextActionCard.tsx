import React from 'react';
import { View, Text, StyleSheet, TouchableOpacity } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { AuPairNextAction, AuPairStage } from '../../types/aupair';

interface Props {
  action: AuPairNextAction;
  stage: AuPairStage;
  onPress?: (action: AuPairNextAction) => void;
}

const STAGE_COLORS: Record<AuPairStage, string> = {
  admission: '#3B82F6',
  application: '#8B5CF6',
  match_visa: '#F59E0B',
  support: '#10B981',
  completed: '#6B7280',
};

const NextActionCard: React.FC<Props> = ({ action, stage, onPress }) => {
  const color = STAGE_COLORS[stage] || '#3B82F6';
  const isActionable = !!action.screen && onPress;

  return (
    <View style={[styles.card, { backgroundColor: color }]}>
      <View style={styles.row}>
        <View style={styles.iconWrap}>
          <Ionicons name="flash" size={22} color="#fff" />
        </View>
        <View style={{ flex: 1 }}>
          <Text style={styles.caption}>PRÓXIMA ACCIÓN</Text>
          <Text style={styles.label}>{action.label}</Text>
        </View>
      </View>
      {isActionable && (
        <TouchableOpacity style={styles.cta} onPress={() => onPress!(action)}>
          <Text style={styles.ctaText}>Ir ahora</Text>
          <Ionicons name="arrow-forward" size={18} color={color} />
        </TouchableOpacity>
      )}
    </View>
  );
};

const styles = StyleSheet.create({
  card: {
    borderRadius: 14,
    padding: 18,
    marginHorizontal: 16,
    marginVertical: 10,
  },
  row: { flexDirection: 'row', alignItems: 'center' },
  iconWrap: {
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: 'rgba(255,255,255,0.2)',
    alignItems: 'center',
    justifyContent: 'center',
    marginRight: 12,
  },
  caption: { color: '#fff', opacity: 0.8, fontSize: 11, fontWeight: '700', letterSpacing: 0.5 },
  label: { color: '#fff', fontSize: 16, fontWeight: '700', marginTop: 2 },
  cta: {
    flexDirection: 'row',
    alignItems: 'center',
    alignSelf: 'flex-start',
    backgroundColor: '#fff',
    paddingHorizontal: 14,
    paddingVertical: 8,
    borderRadius: 8,
    marginTop: 14,
    gap: 6,
  },
  ctaText: { fontWeight: '700' },
});

export default NextActionCard;
