import React, { useCallback, useEffect, useState } from 'react';
import {
  View, Text, StyleSheet, ScrollView, ActivityIndicator,
  TouchableOpacity, RefreshControl
} from 'react-native';
import { SafeAreaView } from '../../components/SafeArea';
import { Ionicons } from '@expo/vector-icons';
import { useNavigation } from '@react-navigation/native';
import { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { paymentService, auPairService } from '../../services/api';
import { Payment, InstallmentPlan, PaymentSummary } from '../../types/payment';
import PaymentCard from '../../components/payments/PaymentCard';
import CurrencyAmount from '../../components/payments/CurrencyAmount';
import EmptyState from '../../components/EmptyState';
import { RootStackParamList } from '../../navigation/AppNavigator';
import { useOptionalProgram } from '../../contexts/ProgramContext';

type Nav = NativeStackNavigationProp<RootStackParamList>;

const PaymentsScreen: React.FC = () => {
  const navigation = useNavigation<Nav>();
  const program = useOptionalProgram();
  const [payments, setPayments] = useState<Payment[]>([]);
  const [plan, setPlan] = useState<InstallmentPlan | null>(null);
  const [summary, setSummary] = useState<PaymentSummary | null>(null);
  const [applicationId, setApplicationId] = useState<number | null>(null);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const load = useCallback(async () => {
    try {
      setError(null);
      // Resolver applicationId: programas del motor → ProgramContext; Au Pair → AuPairProcess.
      let appId: number | null = program?.flow === 'engine' ? (program.applicationId ?? null) : null;
      if (!appId) {
        const process = await auPairService.getProcess();
        appId = process?.application_id ?? program?.applicationId ?? null;
      }
      if (!appId) {
        setApplicationId(null);
        setPayments([]);
        setPlan(null);
        setSummary(null);
        return;
      }
      setApplicationId(appId);
      const [pays, ins, sum] = await Promise.all([
        paymentService.getPayments(appId),
        paymentService.getInstallments(appId),
        paymentService.getSummary(appId).catch(() => null),
      ]);
      setPayments(pays);
      setPlan(ins);
      setSummary(sum);
    } catch (e: any) {
      setError(e?.message || 'No pudimos cargar tus pagos.');
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, [program?.flow, program?.applicationId]);

  useEffect(() => { load(); }, [load]);

  const onRefresh = () => { setRefreshing(true); load(); };

  const goRegister = () => {
    if (!applicationId) return;
    (navigation as any).navigate('PaymentRegister', { applicationId });
  };

  const goDetail = (p: Payment) => {
    (navigation as any).navigate('PaymentDetail', { paymentId: p.id });
  };

  if (loading) {
    return (
      <SafeAreaView style={styles.safe}>
        <ActivityIndicator size="large" color="#E52224" style={{ marginTop: 80 }} />
      </SafeAreaView>
    );
  }

  return (
    <SafeAreaView style={styles.safe}>
      <View style={styles.headerBar}>
        <TouchableOpacity onPress={() => navigation.goBack()}>
          <Ionicons name="arrow-back" size={24} color="#222" />
        </TouchableOpacity>
        <Text style={styles.title}>Pagos</Text>
        <View style={{ width: 24 }} />
      </View>

      <ScrollView
        contentContainerStyle={styles.scroll}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} />}
      >
        {!applicationId ? (
          <EmptyState
            icon="card-outline"
            title="Sin postulación activa"
            message="Cuando inicies tu postulación podrás registrar tus pagos acá."
          />
        ) : (
          <>
            {summary && (
              <View style={styles.summaryCard}>
                <Text style={styles.planTitle}>Resumen financiero</Text>
                {summary.total_cost > 0 ? (
                  <>
                    <View style={styles.summaryRow}>
                      <Text style={styles.summaryLabel}>Costo total del programa</Text>
                      <CurrencyAmount amount={summary.total_cost} currency={summary.currency} bold />
                    </View>
                    <View style={styles.summaryRow}>
                      <Text style={styles.summaryLabel}>Total pagado (verificado)</Text>
                      <CurrencyAmount amount={summary.amount_paid} currency={summary.currency} bold style={{ color: '#065F46' }} />
                    </View>
                    <View style={[styles.summaryRow, styles.summaryRowStrong]}>
                      <Text style={styles.summaryLabelStrong}>Saldo restante</Text>
                      <CurrencyAmount amount={summary.balance} currency={summary.currency} bold style={{ color: summary.balance > 0 ? '#B45309' : '#065F46', fontSize: 16 }} />
                    </View>
                    <View style={styles.bar}>
                      <View style={[styles.barFill, { width: `${summary.progress_pct}%` }]} />
                    </View>
                    <Text style={styles.planMeta}>{summary.progress_pct}% pagado{summary.payment_deadline ? ` · vence ${summary.payment_deadline}` : ''}</Text>
                  </>
                ) : (
                  <>
                    <Text style={styles.summaryHint}>El costo total del programa aún no fue asignado por IE.</Text>
                    <View style={styles.summaryRow}>
                      <Text style={styles.summaryLabel}>Total pagado (verificado)</Text>
                      <CurrencyAmount amount={summary.amount_paid} currency={summary.currency} bold style={{ color: '#065F46' }} />
                    </View>
                  </>
                )}
                {summary.pending_amount > 0 && (
                  <View style={styles.pendingBox}>
                    <Ionicons name="time-outline" size={14} color="#92400E" />
                    <Text style={styles.pendingText}>
                      Pendiente de verificación por IE: <Text style={{ fontWeight: '700' }}>{summary.currency} {summary.pending_amount.toLocaleString('es-PY')}</Text> ({summary.pending_count} pago{summary.pending_count === 1 ? '' : 's'})
                    </Text>
                  </View>
                )}
              </View>
            )}

            {plan && (              <View style={styles.planCard}>
                <Text style={styles.planTitle}>Plan de cuotas</Text>
                <Text style={styles.planSubtitle}>{plan.plan_name}</Text>
                <View style={styles.planRow}>
                  <Text style={styles.planLabel}>Total</Text>
                  <CurrencyAmount amount={Number(plan.total_amount)} currency={plan.currency} bold />
                </View>
                <Text style={styles.planMeta}>
                  {plan.total_installments} cuotas · {plan.details.filter(d => d.status === 'paid').length} pagadas
                </Text>
              </View>
            )}

            <View style={styles.section}>
              <View style={styles.sectionHead}>
                <Text style={styles.sectionTitle}>Mis pagos</Text>
                <TouchableOpacity style={styles.addBtn} onPress={goRegister}>
                  <Ionicons name="add" size={16} color="#fff" />
                  <Text style={styles.addText}>Registrar</Text>
                </TouchableOpacity>
              </View>

              {error ? (
                <Text style={styles.errorText}>{error}</Text>
              ) : payments.length === 0 ? (
                <EmptyState
                  icon="receipt-outline"
                  title="Aún no registraste pagos"
                  message="Cuando hagas un pago de inscripción, aplicación o visa, registralo acá con el comprobante."
                  actionLabel="Registrar pago"
                  onAction={goRegister}
                />
              ) : (
                payments.map(p => (
                  <PaymentCard key={p.id} payment={p} onPress={goDetail} />
                ))
              )}
            </View>
          </>
        )}
      </ScrollView>
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: '#f4f4f5' },
  headerBar: {
    flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between',
    padding: 14, backgroundColor: '#fff',
  },
  title: { fontSize: 17, fontWeight: '700', color: '#222' },
  scroll: { padding: 16, paddingBottom: 100 },
  summaryCard: {
    backgroundColor: '#fff',
    padding: 16,
    borderRadius: 12,
    marginBottom: 16,
    borderWidth: 1,
    borderColor: '#eee',
  },
  summaryRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingVertical: 8, borderBottomWidth: 1, borderBottomColor: '#f4f4f5' },
  summaryRowStrong: { borderBottomWidth: 0, paddingTop: 10 },
  summaryLabel: { color: '#666', fontSize: 13 },
  summaryLabelStrong: { color: '#222', fontWeight: '700' },
  summaryHint: { color: '#777', fontSize: 12, marginTop: 6, marginBottom: 4 },
  bar: { height: 8, backgroundColor: '#e5e7eb', borderRadius: 4, overflow: 'hidden', marginTop: 8 },
  barFill: { height: '100%', backgroundColor: '#10B981', borderRadius: 4 },
  pendingBox: { flexDirection: 'row', alignItems: 'flex-start', gap: 6, backgroundColor: '#FEF3C7', padding: 8, borderRadius: 6, marginTop: 10 },
  pendingText: { color: '#92400E', fontSize: 12, flex: 1, lineHeight: 16 },
  planCard: {
    backgroundColor: '#fff',
    padding: 16,
    borderRadius: 12,
    marginBottom: 16,
    borderWidth: 1,
    borderColor: '#eee',
  },
  planTitle: { fontSize: 12, color: '#777', fontWeight: '700', letterSpacing: 0.5 },
  planSubtitle: { fontSize: 16, fontWeight: '700', color: '#222', marginTop: 2, marginBottom: 8 },
  planRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingTop: 6,
    borderTopWidth: 1,
    borderTopColor: '#f4f4f5',
  },
  planLabel: { color: '#666' },
  planMeta: { fontSize: 12, color: '#777', marginTop: 6 },
  section: { marginTop: 4 },
  sectionHead: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', marginBottom: 10 },
  sectionTitle: { fontSize: 16, fontWeight: '700', color: '#222' },
  addBtn: {
    flexDirection: 'row', alignItems: 'center', gap: 4,
    backgroundColor: '#E52224',
    paddingHorizontal: 12, paddingVertical: 6, borderRadius: 8,
  },
  addText: { color: '#fff', fontWeight: '700', fontSize: 13 },
  errorText: { color: '#991B1B', textAlign: 'center', padding: 14 },
});

export default PaymentsScreen;
