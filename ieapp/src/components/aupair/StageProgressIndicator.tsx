import React from 'react';
import { View, Text, StyleSheet } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { AuPairStageSummary, AuPairStage } from '../../types/aupair';

interface Props {
  stages: AuPairStageSummary[];
  compact?: boolean;
}

const STAGE_META: Record<AuPairStage, { color: string; icon: keyof typeof Ionicons.glyphMap }> = {
  admission: { color: '#3B82F6', icon: 'document-text-outline' },
  application: { color: '#8B5CF6', icon: 'clipboard-outline' },
  match_visa: { color: '#F59E0B', icon: 'airplane-outline' },
  support: { color: '#10B981', icon: 'headset-outline' },
  completed: { color: '#6B7280', icon: 'checkmark-done-circle' },
};

const StageProgressIndicator: React.FC<Props> = ({ stages, compact }) => {
  return (
    <View style={styles.wrap}>
      {stages.map((s, idx) => {
        const meta = STAGE_META[s.key];
        const isActive = s.state === 'in_progress';
        const isComplete = s.state === 'complete';
        const dotColor = isComplete ? meta.color : isActive ? meta.color : '#E5E7EB';
        const textColor = isActive || isComplete ? '#222' : '#9CA3AF';

        return (
          <React.Fragment key={s.key}>
            <View style={styles.step}>
              <View
                style={[
                  styles.dot,
                  { backgroundColor: dotColor, borderColor: dotColor },
                  isActive && styles.dotActive,
                ]}
              >
                {isComplete ? (
                  <Ionicons name="checkmark" size={14} color="#fff" />
                ) : (
                  <Ionicons name={meta.icon} size={14} color={isActive ? '#fff' : '#9CA3AF'} />
                )}
              </View>
              {!compact && (
                <Text style={[styles.label, { color: textColor }]} numberOfLines={2}>
                  {s.label}
                </Text>
              )}
            </View>
            {idx < stages.length - 1 && (
              <View style={[styles.line, { backgroundColor: isComplete ? meta.color : '#E5E7EB' }]} />
            )}
          </React.Fragment>
        );
      })}
    </View>
  );
};

const styles = StyleSheet.create({
  wrap: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    justifyContent: 'space-between',
    paddingVertical: 8,
  },
  step: { alignItems: 'center', width: 60 },
  dot: {
    width: 28,
    height: 28,
    borderRadius: 14,
    borderWidth: 2,
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#E5E7EB',
  },
  dotActive: {
    shadowColor: '#000',
    shadowOpacity: 0.15,
    shadowOffset: { width: 0, height: 1 },
    shadowRadius: 3,
    elevation: 2,
  },
  label: { fontSize: 10, marginTop: 6, textAlign: 'center', lineHeight: 12 },
  line: { flex: 1, height: 2, marginTop: 13, marginHorizontal: -4 },
});

export default StageProgressIndicator;
