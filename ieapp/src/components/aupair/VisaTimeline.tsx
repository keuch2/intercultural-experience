import React from 'react';
import { View, Text, StyleSheet } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { VisaTimelineItem } from '../../types/aupair';

interface Props {
  items: VisaTimelineItem[];
}

const VisaTimeline: React.FC<Props> = ({ items }) => (
  <View style={styles.wrap}>
    {items.map((it, idx) => {
      const isLast = idx === items.length - 1;
      const completed = it.completed;
      return (
        <View key={it.key} style={styles.row}>
          <View style={styles.iconCol}>
            <View
              style={[
                styles.dot,
                completed ? styles.dotDone : styles.dotPending,
              ]}
            >
              <Ionicons
                name={completed ? 'checkmark' : 'ellipse-outline'}
                size={14}
                color={completed ? '#fff' : '#9CA3AF'}
              />
            </View>
            {!isLast && <View style={[styles.line, completed && styles.lineDone]} />}
          </View>
          <View style={styles.content}>
            <Text style={[styles.label, completed && styles.labelDone]}>{it.label}</Text>
            {it.meta ? <Text style={styles.meta}>{it.meta}</Text> : null}
          </View>
        </View>
      );
    })}
  </View>
);

const styles = StyleSheet.create({
  wrap: { paddingHorizontal: 6 },
  row: { flexDirection: 'row', minHeight: 56 },
  iconCol: { alignItems: 'center', width: 28 },
  dot: {
    width: 24, height: 24, borderRadius: 12,
    alignItems: 'center', justifyContent: 'center',
    borderWidth: 2,
  },
  dotDone: { backgroundColor: '#10B981', borderColor: '#10B981' },
  dotPending: { backgroundColor: '#fff', borderColor: '#D1D5DB' },
  line: { flex: 1, width: 2, backgroundColor: '#E5E7EB', marginTop: 2 },
  lineDone: { backgroundColor: '#10B981' },
  content: { flex: 1, paddingLeft: 12, paddingBottom: 12 },
  label: { color: '#555', fontWeight: '600' },
  labelDone: { color: '#222' },
  meta: { fontSize: 12, color: '#777', marginTop: 2 },
});

export default VisaTimeline;
