import React from 'react';
import { View, Text, StyleSheet } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { EngineStage } from '../../types/programEngine';

/** Paleta por posición: las etapas las define el admin, no hay claves fijas. */
export const STAGE_PALETTE = ['#3B82F6', '#8B5CF6', '#F59E0B', '#0EA5E9', '#EF4444', '#10B981', '#6B7280'];
export const stageColor = (index: number) => STAGE_PALETTE[index % STAGE_PALETTE.length];

const ICONS: Record<string, keyof typeof Ionicons.glyphMap> = {
  admission: 'document-text-outline', application: 'clipboard-outline', job_pool: 'briefcase-outline',
  placement: 'business-outline', visa: 'airplane-outline', match_visa: 'airplane-outline',
  support: 'headset-outline', completed: 'checkmark-done-circle',
};

const StageProgress: React.FC<{ stages: EngineStage[]; compact?: boolean }> = ({ stages, compact }) => (
  <View style={styles.wrap}>
    {stages.map((s, idx) => {
      const color = stageColor(idx);
      const isActive = s.state === 'in_progress';
      const isComplete = s.state === 'complete';
      const dotColor = isActive || isComplete ? color : '#E5E7EB';
      return (
        <React.Fragment key={s.key}>
          <View style={styles.step}>
            <View style={[styles.dot, { backgroundColor: dotColor, borderColor: dotColor }, isActive && styles.dotActive]}>
              {isComplete
                ? <Ionicons name="checkmark" size={14} color="#fff" />
                : <Ionicons name={ICONS[s.key] || 'ellipse-outline'} size={14} color={isActive ? '#fff' : '#9CA3AF'} />}
            </View>
            {!compact && <Text style={[styles.label, { color: isActive || isComplete ? '#222' : '#9CA3AF' }]} numberOfLines={2}>{s.label}</Text>}
          </View>
          {idx < stages.length - 1 && <View style={[styles.line, { backgroundColor: isComplete ? color : '#E5E7EB' }]} />}
        </React.Fragment>
      );
    })}
  </View>
);

const styles = StyleSheet.create({
  wrap: { flexDirection: 'row', alignItems: 'flex-start', justifyContent: 'space-between', paddingVertical: 8 },
  step: { alignItems: 'center', width: 52 },
  dot: { width: 28, height: 28, borderRadius: 14, borderWidth: 2, alignItems: 'center', justifyContent: 'center', backgroundColor: '#E5E7EB' },
  dotActive: { shadowColor: '#000', shadowOpacity: 0.15, shadowOffset: { width: 0, height: 1 }, shadowRadius: 3, elevation: 2 },
  label: { fontSize: 9, marginTop: 6, textAlign: 'center', lineHeight: 11 },
  line: { flex: 1, height: 2, marginTop: 13, marginHorizontal: -2 },
});

export default StageProgress;
