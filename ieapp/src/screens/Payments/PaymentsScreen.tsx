import React, { useCallback, useEffect, useState } from 'react';
import {
  View, Text, StyleSheet, ScrollView, ActivityIndicator,
  TouchableOpacity, SafeAreaView, RefreshControl,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useNavigation } from '@react-navigation/native';
import { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { paymentService, auPairService } from '../../services/api';
import { Payment, InstallmentPlan } from '../../types/payment';
import PaymentCard from '../../components/payments/PaymentCard';
import CurrencyAmount from '../../components/payments/CurrencyAmount';
import EmptyState from '../../components/EmptyState';
import { RootStackParamList } from '../../navigation/AppNavigator';

type Nav = NativeStackNavigationProp<RootStackParamList>;

const PaymentsScreen: React.FC = () => {
  const navigation = useNavigation<Nav>();
  const [payments, setPayments] = useState<Payment[]>([]);
  const [plan, setPlan] = useState<InstallmentPlan | null>(null);
  const [applicationId, setApplicationId] = useState<number | null>(null);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const load = useCallback(async () => {
    try {
      setError(null);
      // Resolver applicationId desde el AuPairProcess del user
      const process = await auPairService.getProcess();
      if (!process) {
        setApplicationId(null);
        setPayments([]);
        setPlan(null);
        return;
      }
      setApplicationId(process.application_id);
      const [pays, ins] = await Promise.all([
        paymentService.getPayments(process.application_id),
        paymentService.getInstallments(process.application_id),
      ]);
      setPayments(pays);
      setPlan(ins);
    } catch (e: any) {
      setError(e?.message || 'No pudimos cargar tus pagos.');
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, []);

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
            message="Cuando inicies tu postulación Au Pair podrás registrar tus pagos acá."
          />
        ) : (
          <>
            {plan && (
              <View style={styles.planCard}>
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
