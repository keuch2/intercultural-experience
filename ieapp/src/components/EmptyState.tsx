import React from 'react';
import { View, Text, StyleSheet, TouchableOpacity } from 'react-native';
import { Ionicons } from '@expo/vector-icons';

interface Props {
  icon?: keyof typeof Ionicons.glyphMap;
  title: string;
  message?: string;
  actionLabel?: string;
  onAction?: () => void;
}

const EmptyState: React.FC<Props> = ({ icon = 'information-circle-outline', title, message, actionLabel, onAction }) => (
  <View style={styles.wrap}>
    <Ionicons name={icon} size={56} color="#cfcfcf" />
    <Text style={styles.title}>{title}</Text>
    {message ? <Text style={styles.message}>{message}</Text> : null}
    {actionLabel && onAction ? (
      <TouchableOpacity style={styles.btn} onPress={onAction}>
        <Text style={styles.btnText}>{actionLabel}</Text>
      </TouchableOpacity>
    ) : null}
  </View>
);

const styles = StyleSheet.create({
  wrap: { alignItems: 'center', justifyContent: 'center', padding: 32 },
  title: { fontSize: 16, fontWeight: '700', color: '#333', marginTop: 12, textAlign: 'center' },
  message: { fontSize: 14, color: '#666', marginTop: 6, textAlign: 'center', lineHeight: 20 },
  btn: {
    marginTop: 18,
    backgroundColor: '#E52224',
    paddingHorizontal: 22,
    paddingVertical: 12,
    borderRadius: 8,
  },
  btnText: { color: '#fff', fontWeight: '700' },
});

export default EmptyState;
