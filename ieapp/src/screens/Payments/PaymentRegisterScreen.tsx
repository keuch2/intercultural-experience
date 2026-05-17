import React, { useState } from 'react';
import {
  View, Text, StyleSheet, ScrollView, ActivityIndicator,
  TouchableOpacity, SafeAreaView, Alert, TextInput,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useNavigation, useRoute, RouteProp } from '@react-navigation/native';
import { NativeStackNavigationProp } from '@react-navigation/native-stack';
import * as DocumentPicker from 'expo-document-picker';
import { paymentService } from '../../services/api';
import { RootStackParamList } from '../../navigation/AppNavigator';

type Nav = NativeStackNavigationProp<RootStackParamList>;
type RouteP = RouteProp<{ PaymentRegister: { applicationId: number } }, 'PaymentRegister'>;

const CONCEPTS = [
  'Inscripción',
  'Aplicación',
  'Habilitación de perfil',
  'SEVIS',
  'Tasa consular',
  'Otro',
];

const METHODS = [
  'Transferencia bancaria',
  'Depósito',
  'Efectivo',
  'Tarjeta',
  'Otro',
];

const PaymentRegisterScreen: React.FC = () => {
  const navigation = useNavigation<Nav>();
  const { applicationId } = useRoute<RouteP>().params;

  const [concept, setConcept] = useState(CONCEPTS[0]);
  const [amount, setAmount] = useState('');
  const [currency, setCurrency] = useState<'USD' | 'PYG'>('USD');
  const [method, setMethod] = useState(METHODS[0]);
  const [reference, setReference] = useState('');
  const [paymentDate, setPaymentDate] = useState(new Date().toISOString().slice(0, 10));
  const [notes, setNotes] = useState('');
  const [receipt, setReceipt] = useState<{ uri: string; name: string; type: string } | null>(null);
  const [submitting, setSubmitting] = useState(false);
  const [errors, setErrors] = useState<Record<string, string>>({});

  const pickReceipt = async () => {
    const r = await DocumentPicker.getDocumentAsync({
      type: ['image/*', 'application/pdf'],
      copyToCacheDirectory: true,
    });
    if (r.canceled || !r.assets[0]) return;
    const a = r.assets[0];
    setReceipt({
      uri: a.uri,
      name: a.name || `receipt-${Date.now()}`,
      type: a.mimeType || 'application/octet-stream',
    });
  };

  const submit = async () => {
    const errs: Record<string, string> = {};
    const num = parseFloat(amount.replace(',', '.'));
    if (!amount || isNaN(num) || num <= 0) errs.amount = 'Monto inválido';
    if (!concept) errs.concept = 'Elegí un concepto';
    setErrors(errs);
    if (Object.keys(errs).length > 0) return;

    try {
      setSubmitting(true);
      await paymentService.createPayment({
        application_id: applicationId,
        amount: num,
        concept,
        payment_method: method,
        reference_number: reference || undefined,
        payment_date: paymentDate,
        notes: notes || undefined,
        receipt: receipt || undefined,
      });
      Alert.alert('Listo', 'Pago registrado. El equipo IE verificará el comprobante.', [
        { text: 'OK', onPress: () => navigation.goBack() },
      ]);
    } catch (e: any) {
      Alert.alert('Error', e?.response?.data?.message || e?.message || 'No pudimos registrar el pago.');
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <SafeAreaView style={styles.safe}>
      <View style={styles.headerBar}>
        <TouchableOpacity onPress={() => navigation.goBack()}>
          <Ionicons name="close" size={26} color="#222" />
        </TouchableOpacity>
        <Text style={styles.title}>Registrar pago</Text>
        <View style={{ width: 26 }} />
      </View>

      <ScrollView contentContainerStyle={styles.scroll}>
        <Field label="Concepto" error={errors.concept}>
          <View style={styles.chipsWrap}>
            {CONCEPTS.map(c => (
              <TouchableOpacity
                key={c}
                style={[styles.chip, concept === c && styles.chipActive]}
                onPress={() => setConcept(c)}
              >
                <Text style={[styles.chipText, concept === c && styles.chipTextActive]}>{c}</Text>
              </TouchableOpacity>
            ))}
          </View>
        </Field>

        <Field label="Monto" error={errors.amount}>
          <View style={styles.amountRow}>
            <View style={styles.currencyToggle}>
              {(['USD', 'PYG'] as const).map(c => (
                <TouchableOpacity
                  key={c}
                  style={[styles.currencyBtn, currency === c && styles.currencyBtnActive]}
                  onPress={() => setCurrency(c)}
                >
                  <Text style={[styles.currencyText, currency === c && styles.currencyTextActive]}>{c}</Text>
                </TouchableOpacity>
              ))}
            </View>
            <TextInput
              style={styles.amountInput}
              keyboardType="decimal-pad"
              value={amount}
              onChangeText={setAmount}
              placeholder="0.00"
              placeholderTextColor="#bbb"
            />
          </View>
        </Field>

        <Field label="Fecha de pago">
          <TextInput style={styles.input} value={paymentDate} onChangeText={setPaymentDate} placeholder="YYYY-MM-DD" />
        </Field>

        <Field label="Método">
          <View style={styles.chipsWrap}>
            {METHODS.map(m => (
              <TouchableOpacity
                key={m}
                style={[styles.chip, method === m && styles.chipActive]}
                onPress={() => setMethod(m)}
              >
                <Text style={[styles.chipText, method === m && styles.chipTextActive]}>{m}</Text>
              </TouchableOpacity>
            ))}
          </View>
        </Field>

        <Field label="Referencia (opcional)">
          <TextInput style={styles.input} value={reference} onChangeText={setReference} placeholder="Nº de operación / depósito" />
        </Field>

        <Field label="Notas (opcional)">
          <TextInput
            style={[styles.input, { height: 80, textAlignVertical: 'top' }]}
            value={notes}
            onChangeText={setNotes}
            multiline
            placeholder="Detalles adicionales..."
          />
        </Field>

        <Field label="Comprobante">
          <TouchableOpacity style={styles.receiptBtn} onPress={pickReceipt}>
            <Ionicons name={receipt ? 'document-attach' : 'cloud-upload-outline'} size={20} color="#3B82F6" />
            <Text style={styles.receiptBtnText} numberOfLines={1}>
              {receipt ? receipt.name : 'Adjuntar foto o PDF'}
            </Text>
          </TouchableOpacity>
        </Field>

        <TouchableOpacity
          style={[styles.submitBtn, submitting && styles.submitBtnDisabled]}
          onPress={submit}
          disabled={submitting}
        >
          {submitting ? <ActivityIndicator color="#fff" /> : (
            <>
              <Ionicons name="checkmark" size={18} color="#fff" />
              <Text style={styles.submitText}>Registrar pago</Text>
            </>
          )}
        </TouchableOpacity>
      </ScrollView>
    </SafeAreaView>
  );
};

const Field: React.FC<{ label: string; error?: string; children: React.ReactNode }> = ({ label, error, children }) => (
  <View style={{ marginBottom: 14 }}>
    <Text style={styles.fieldLabel}>{label}</Text>
    {children}
    {error ? <Text style={styles.errorText}>{error}</Text> : null}
  </View>
);

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: '#fff' },
  headerBar: {
    flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between',
    padding: 14, borderBottomWidth: 1, borderBottomColor: '#eee',
  },
  title: { flex: 1, textAlign: 'center', fontSize: 16, fontWeight: '700' },
  scroll: { padding: 16, paddingBottom: 40 },
  fieldLabel: { fontWeight: '600', color: '#444', marginBottom: 6 },
  chipsWrap: { flexDirection: 'row', flexWrap: 'wrap', gap: 6 },
  chip: { paddingHorizontal: 12, paddingVertical: 6, borderRadius: 16, borderWidth: 1, borderColor: '#ddd', backgroundColor: '#fff' },
  chipActive: { backgroundColor: '#E52224', borderColor: '#E52224' },
  chipText: { color: '#555', fontSize: 12, fontWeight: '600' },
  chipTextActive: { color: '#fff' },
  input: {
    borderWidth: 1, borderColor: '#ddd', borderRadius: 8,
    paddingHorizontal: 12, paddingVertical: 10, color: '#222', fontSize: 14,
  },
  amountRow: { flexDirection: 'row', gap: 8 },
  currencyToggle: { flexDirection: 'row', borderWidth: 1, borderColor: '#ddd', borderRadius: 8, overflow: 'hidden' },
  currencyBtn: { paddingHorizontal: 12, justifyContent: 'center' },
  currencyBtnActive: { backgroundColor: '#E52224' },
  currencyText: { color: '#444', fontWeight: '700' },
  currencyTextActive: { color: '#fff' },
  amountInput: {
    flex: 1, borderWidth: 1, borderColor: '#ddd', borderRadius: 8,
    paddingHorizontal: 12, paddingVertical: 10, color: '#222', fontSize: 16,
  },
  receiptBtn: {
    flexDirection: 'row', alignItems: 'center', gap: 10,
    backgroundColor: '#EFF6FF', borderWidth: 1, borderColor: '#3B82F6',
    borderStyle: 'dashed', borderRadius: 8, padding: 14,
  },
  receiptBtnText: { color: '#3B82F6', fontWeight: '600', flex: 1 },
  errorText: { color: '#991B1B', fontSize: 12, marginTop: 4 },
  submitBtn: {
    flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 8,
    backgroundColor: '#E52224', paddingVertical: 14, borderRadius: 10, marginTop: 12,
  },
  submitBtnDisabled: { backgroundColor: '#aaa' },
  submitText: { color: '#fff', fontWeight: '700', fontSize: 16 },
});

export default PaymentRegisterScreen;
