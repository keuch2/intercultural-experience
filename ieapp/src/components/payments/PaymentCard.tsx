import React from 'react';
import { View, Text, StyleSheet, TouchableOpacity } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { Payment } from '../../types/payment';
import StatusPill from '../aupair/StatusPill';
import CurrencyAmount from './CurrencyAmount';

interface Props {
  payment: Payment;
  onPress?: (p: Payment) => void;
}

const PaymentCard: React.FC<Props> = ({ payment, onPress }) => (
  <TouchableOpacity
    style={styles.card}
    activeOpacity={onPress ? 0.7 : 1}
    onPress={onPress ? () => onPress(payment) : undefined}
  >
    <View style={styles.row}>
      <View style={{ flex: 1 }}>
        <Text style={styles.concept}>{payment.concept}</Text>
        <Text style={styles.date}>{payment.payment_date || '—'}</Text>
      </View>
      <View style={{ alignItems: 'flex-end' }}>
        <CurrencyAmount amount={payment.amount} currency={payment.currency} bold />
        <StatusPill status={payment.status} small />
      </View>
    </View>
    {payment.reference_number ? (
      <View style={styles.metaRow}>
        <Ionicons name="receipt-outline" size={13} color="#777" />
        <Text style={styles.meta}>Ref: {payment.reference_number}</Text>
      </View>
    ) : null}
    {!payment.receipt_url && payment.status === 'pending' && (
      <View style={styles.warn}>
        <Ionicons name="warning-outline" size={13} color="#92400E" />
        <Text style={styles.warnText}>Sin comprobante adjunto</Text>
      </View>
    )}
  </TouchableOpacity>
);

const styles = StyleSheet.create({
  card: {
    backgroundColor: '#fff',
    padding: 14,
    borderRadius: 10,
    marginBottom: 10,
    borderWidth: 1,
    borderColor: '#eee',
  },
  row: { flexDirection: 'row', alignItems: 'center', gap: 8 },
  concept: { fontWeight: '700', color: '#222', marginBottom: 2 },
  date: { fontSize: 11, color: '#777' },
  metaRow: { flexDirection: 'row', alignItems: 'center', marginTop: 8, gap: 4 },
  meta: { color: '#666', fontSize: 12 },
  warn: { flexDirection: 'row', alignItems: 'center', gap: 4, marginTop: 6 },
  warnText: { color: '#92400E', fontSize: 12, fontWeight: '600' },
});

export default PaymentCard;
