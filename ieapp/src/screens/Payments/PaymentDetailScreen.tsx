import React, { useCallback, useEffect, useState } from 'react';
import {
  View, Text, StyleSheet, ScrollView, ActivityIndicator,
  TouchableOpacity, SafeAreaView, Alert, Linking, Image,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useNavigation, useRoute, RouteProp } from '@react-navigation/native';
import { NativeStackNavigationProp } from '@react-navigation/native-stack';
import * as DocumentPicker from 'expo-document-picker';
import { paymentService } from '../../services/api';
import { Payment } from '../../types/payment';
import CurrencyAmount from '../../components/payments/CurrencyAmount';
import StatusPill from '../../components/aupair/StatusPill';
import EmptyState from '../../components/EmptyState';
import { RootStackParamList } from '../../navigation/AppNavigator';

type Nav = NativeStackNavigationProp<RootStackParamList>;
type RouteP = RouteProp<{ PaymentDetail: { paymentId: number } }, 'PaymentDetail'>;

const PaymentDetailScreen: React.FC = () => {
  const navigation = useNavigation<Nav>();
  const { paymentId } = useRoute<RouteP>().params;
  const [payment, setPayment] = useState<Payment | null>(null);
  const [loading, setLoading] = useState(true);
  const [uploading, setUploading] = useState(false);

  const load = useCallback(async () => {
    setLoading(true);
    const p = await paymentService.getPayment(paymentId);
    setPayment(p);
    setLoading(false);
  }, [paymentId]);

  useEffect(() => { load(); }, [load]);

  const uploadReceipt = async () => {
    try {
      const res = await DocumentPicker.getDocumentAsync({
        type: ['image/*', 'application/pdf'],
        copyToCacheDirectory: true,
      });
      if (res.canceled || !res.assets[0]) return;
      const a = res.assets[0];
      setUploading(true);
      const updated = await paymentService.uploadReceipt(paymentId, {
        uri: a.uri,
        name: a.name || `receipt-${Date.now()}`,
        type: a.mimeType || 'application/octet-stream',
      });
      setPayment(updated);
      Alert.alert('Listo', 'Comprobante actualizado.');
    } catch (e: any) {
      Alert.alert('Error', e?.response?.data?.message || e?.message || 'No pudimos subir el comprobante.');
    } finally {
      setUploading(false);
    }
  };

  if (loading) {
    return (
      <SafeAreaView style={styles.safe}>
        <ActivityIndicator size="large" color="#E52224" style={{ marginTop: 80 }} />
      </SafeAreaView>
    );
  }

  if (!payment) {
    return (
      <SafeAreaView style={styles.safe}>
        <EmptyState icon="alert-circle-outline" title="Pago no encontrado" />
      </SafeAreaView>
    );
  }

  const isImg = payment.receipt_url && /\.(jpe?g|png)$/i.test(payment.receipt_url);
  const lockedForEdits = payment.status === 'verified';

  return (
    <SafeAreaView style={styles.safe}>
      <View style={styles.headerBar}>
        <TouchableOpacity onPress={() => navigation.goBack()}>
          <Ionicons name="arrow-back" size={24} color="#222" />
        </TouchableOpacity>
        <Text style={styles.title}>Detalle de pago</Text>
        <View style={{ width: 24 }} />
      </View>

      <ScrollView contentContainerStyle={styles.scroll}>
        <View style={styles.head}>
          <Text style={styles.concept}>{payment.concept}</Text>
          <StatusPill status={payment.status} />
        </View>

        <View style={styles.amount}>
          <CurrencyAmount amount={payment.amount} currency={payment.currency} style={{ fontSize: 28, fontWeight: '800' }} />
        </View>

        <View style={styles.metaBox}>
          <Row label="Fecha" value={payment.payment_date || '—'} />
          <Row label="Método" value={payment.payment_method || '—'} />
          <Row label="Referencia" value={payment.reference_number || '—'} />
          {payment.notes ? <Row label="Notas" value={payment.notes} /> : null}
        </View>

        <Text style={styles.sectionTitle}>Comprobante</Text>
        {payment.receipt_url ? (
          <View style={styles.receiptBox}>
            {isImg ? (
              <Image source={{ uri: payment.receipt_url }} style={styles.receiptImg} resizeMode="contain" />
            ) : (
              <TouchableOpacity style={styles.pdfRow} onPress={() => Linking.openURL(payment.receipt_url!)}>
                <Ionicons name="document-text-outline" size={24} color="#444" />
                <Text style={styles.pdfText}>Abrir PDF</Text>
                <Ionicons name="open-outline" size={18} color="#555" />
              </TouchableOpacity>
            )}
          </View>
        ) : (
          <View style={styles.noReceipt}>
            <Ionicons name="warning-outline" size={20} color="#92400E" />
            <Text style={styles.noReceiptText}>No hay comprobante adjunto todavía.</Text>
          </View>
        )}

        {!lockedForEdits && (
          <TouchableOpacity style={styles.uploadBtn} onPress={uploadReceipt} disabled={uploading}>
            {uploading ? (
              <ActivityIndicator color="#fff" />
            ) : (
              <>
                <Ionicons name="cloud-upload-outline" size={18} color="#fff" />
                <Text style={styles.uploadText}>
                  {payment.receipt_url ? 'Reemplazar comprobante' : 'Subir comprobante'}
                </Text>
              </>
            )}
          </TouchableOpacity>
        )}
      </ScrollView>
    </SafeAreaView>
  );
};

const Row: React.FC<{ label: string; value: string }> = ({ label, value }) => (
  <View style={styles.row}>
    <Text style={styles.rowLabel}>{label}</Text>
    <Text style={styles.rowValue}>{value}</Text>
  </View>
);

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: '#f4f4f5' },
  headerBar: {
    flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between',
    padding: 14, backgroundColor: '#fff',
  },
  title: { fontSize: 17, fontWeight: '700' },
  scroll: { padding: 16, paddingBottom: 30 },
  head: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'flex-start' },
  concept: { fontSize: 16, fontWeight: '700', color: '#222', flex: 1, marginRight: 12 },
  amount: { paddingVertical: 18, alignItems: 'center' },
  metaBox: { backgroundColor: '#fff', borderRadius: 10, padding: 6, marginBottom: 16 },
  row: { flexDirection: 'row', justifyContent: 'space-between', paddingHorizontal: 10, paddingVertical: 10, borderBottomWidth: 1, borderBottomColor: '#f4f4f5' },
  rowLabel: { color: '#666' },
  rowValue: { color: '#222', fontWeight: '600', flex: 1, textAlign: 'right' },
  sectionTitle: { fontSize: 14, fontWeight: '700', color: '#555', marginBottom: 8 },
  receiptBox: { backgroundColor: '#fff', borderRadius: 10, padding: 12, marginBottom: 16 },
  receiptImg: { width: '100%', height: 240 },
  pdfRow: { flexDirection: 'row', alignItems: 'center', gap: 10, padding: 8 },
  pdfText: { flex: 1, color: '#222', fontWeight: '600' },
  noReceipt: {
    flexDirection: 'row', alignItems: 'center', gap: 8,
    backgroundColor: '#FEF3C7', padding: 12, borderRadius: 8, marginBottom: 16,
  },
  noReceiptText: { color: '#92400E', flex: 1, fontSize: 13 },
  uploadBtn: {
    flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 8,
    backgroundColor: '#3B82F6',
    paddingVertical: 14, borderRadius: 10,
  },
  uploadText: { color: '#fff', fontWeight: '700' },
});

export default PaymentDetailScreen;
