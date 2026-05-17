import React, { useCallback, useEffect, useState } from 'react';
import {
  View, Text, StyleSheet, ScrollView, ActivityIndicator,
  TouchableOpacity, SafeAreaView, Alert, TextInput,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useNavigation } from '@react-navigation/native';
import { NativeStackNavigationProp } from '@react-navigation/native-stack';
import * as DocumentPicker from 'expo-document-picker';
import { auPairService } from '../../services/api';
import { AuPairEnglishTestsResp, AuPairEnglishTest } from '../../types/aupair';
import EmptyState from '../../components/EmptyState';
import StatusPill from '../../components/aupair/StatusPill';
import { RootStackParamList } from '../../navigation/AppNavigator';

type Nav = NativeStackNavigationProp<RootStackParamList>;

const AuPairEnglishTestScreen: React.FC = () => {
  const navigation = useNavigation<Nav>();
  const [data, setData] = useState<AuPairEnglishTestsResp | null>(null);
  const [loading, setLoading] = useState(true);
  const [showForm, setShowForm] = useState(false);
  const [examName, setExamName] = useState('');
  const [finalScore, setFinalScore] = useState('');
  const [listening, setListening] = useState('');
  const [reading, setReading] = useState('');
  const [oralScore, setOralScore] = useState<'Good' | 'Great' | 'Excellent'>('Good');
  const [observations, setObservations] = useState('');
  const [pdf, setPdf] = useState<{ uri: string; name: string; type: string } | null>(null);
  const [submitting, setSubmitting] = useState(false);

  const load = useCallback(async () => {
    setLoading(true);
    try {
      const d = await auPairService.getEnglishTests();
      setData(d);
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => { load(); }, [load]);

  const pickPdf = async () => {
    const r = await DocumentPicker.getDocumentAsync({
      type: 'application/pdf',
      copyToCacheDirectory: true,
    });
    if (r.canceled || !r.assets[0]) return;
    setPdf({
      uri: r.assets[0].uri,
      name: r.assets[0].name || `test-${Date.now()}.pdf`,
      type: r.assets[0].mimeType || 'application/pdf',
    });
  };

  const submit = async () => {
    if (!examName || !finalScore) {
      Alert.alert('Faltan datos', 'Completá nombre del examen y puntaje final.');
      return;
    }
    const score = parseInt(finalScore, 10);
    if (isNaN(score) || score < 0 || score > 100) {
      Alert.alert('Puntaje inválido', 'El puntaje final debe estar entre 0 y 100.');
      return;
    }
    try {
      setSubmitting(true);
      await auPairService.submitEnglishTest({
        exam_name: examName,
        final_score: score,
        listening_score: listening ? parseInt(listening, 10) : undefined,
        reading_score: reading ? parseInt(reading, 10) : undefined,
        oral_score: oralScore,
        observations: observations || undefined,
        pdf: pdf || undefined,
      });
      Alert.alert('Listo', 'Test registrado.');
      setShowForm(false);
      setExamName(''); setFinalScore(''); setListening(''); setReading(''); setObservations(''); setPdf(null);
      load();
    } catch (e: any) {
      Alert.alert('Error', e?.response?.data?.message || 'No pudimos registrar el test.');
    } finally {
      setSubmitting(false);
    }
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
        <Text style={styles.title}>Test de Inglés</Text>
        <View style={{ width: 24 }} />
      </View>

      <ScrollView contentContainerStyle={styles.scroll}>
        {data && (
          <View style={styles.attemptsBox}>
            <Text style={styles.attemptsLabel}>Intentos</Text>
            <Text style={styles.attemptsValue}>{data.used_attempts} / {data.max_attempts}</Text>
            <Text style={styles.attemptsRemaining}>{data.remaining_attempts} restantes</Text>
          </View>
        )}

        {data?.tests.length === 0 && !showForm ? (
          <EmptyState
            icon="school-outline"
            title="Sin tests registrados"
            message="Registrá tu resultado de inglés (ITEP, EF SET u otro) para avanzar."
            actionLabel="Registrar test"
            onAction={() => setShowForm(true)}
          />
        ) : null}

        {data?.tests.map(t => <TestCard key={t.id} test={t} />)}

        {showForm ? (
          <View style={styles.form}>
            <Text style={styles.formTitle}>Nuevo test</Text>
            <Field label="Examen">
              <TextInput style={styles.input} value={examName} onChangeText={setExamName} placeholder="Ej: ITEP, EF SET" />
            </Field>
            <Field label="Puntaje final (0-100)">
              <TextInput style={styles.input} value={finalScore} onChangeText={setFinalScore} keyboardType="numeric" />
            </Field>
            <Field label="Listening (opcional)">
              <TextInput style={styles.input} value={listening} onChangeText={setListening} keyboardType="numeric" />
            </Field>
            <Field label="Reading (opcional)">
              <TextInput style={styles.input} value={reading} onChangeText={setReading} keyboardType="numeric" />
            </Field>
            <Field label="Oral">
              <View style={styles.oralRow}>
                {(['Good', 'Great', 'Excellent'] as const).map(o => (
                  <TouchableOpacity key={o} style={[styles.oralChip, oralScore === o && styles.oralChipActive]} onPress={() => setOralScore(o)}>
                    <Text style={[styles.oralChipText, oralScore === o && styles.oralChipTextActive]}>{o}</Text>
                  </TouchableOpacity>
                ))}
              </View>
            </Field>
            <Field label="Observaciones (opcional)">
              <TextInput style={[styles.input, { height: 70 }]} multiline value={observations} onChangeText={setObservations} />
            </Field>
            <TouchableOpacity style={styles.pdfBtn} onPress={pickPdf}>
              <Ionicons name="document-attach-outline" size={18} color="#3B82F6" />
              <Text style={styles.pdfBtnText} numberOfLines={1}>{pdf?.name || 'Adjuntar PDF del resultado (opcional)'}</Text>
            </TouchableOpacity>
            <View style={{ flexDirection: 'row', gap: 8, marginTop: 12 }}>
              <TouchableOpacity style={styles.cancelBtn} onPress={() => setShowForm(false)}>
                <Text style={styles.cancelText}>Cancelar</Text>
              </TouchableOpacity>
              <TouchableOpacity style={styles.submitBtn} onPress={submit} disabled={submitting}>
                {submitting ? <ActivityIndicator color="#fff" /> : <Text style={styles.submitText}>Registrar</Text>}
              </TouchableOpacity>
            </View>
          </View>
        ) : (
          data && data.remaining_attempts > 0 ? (
            <TouchableOpacity style={styles.addBtn} onPress={() => setShowForm(true)}>
              <Ionicons name="add-circle" size={20} color="#E52224" />
              <Text style={styles.addBtnText}>Registrar nuevo test</Text>
            </TouchableOpacity>
          ) : null
        )}
      </ScrollView>
    </SafeAreaView>
  );
};

const TestCard: React.FC<{ test: AuPairEnglishTest }> = ({ test }) => (
  <View style={styles.testCard}>
    <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'flex-start' }}>
      <View style={{ flex: 1 }}>
        <Text style={styles.examName}>{test.exam_name}</Text>
        <Text style={styles.attemptNum}>Intento #{test.attempt_number}</Text>
      </View>
      <StatusPill status={test.meets_minimum ? 'approved' : 'pending'} label={test.cefr_level || '—'} small />
    </View>
    <View style={styles.scoreRow}>
      <Score label="Final" value={test.final_score} />
      {test.listening_score !== null && <Score label="Listening" value={test.listening_score} />}
      {test.reading_score !== null && <Score label="Reading" value={test.reading_score} />}
      {test.oral_score ? <Score label="Oral" value={test.oral_score} /> : null}
    </View>
  </View>
);

const Score: React.FC<{ label: string; value: any }> = ({ label, value }) => (
  <View style={styles.scoreItem}>
    <Text style={styles.scoreLabel}>{label}</Text>
    <Text style={styles.scoreValue}>{value}</Text>
  </View>
);

const Field: React.FC<{ label: string; children: React.ReactNode }> = ({ label, children }) => (
  <View style={{ marginBottom: 10 }}>
    <Text style={styles.fieldLabel}>{label}</Text>
    {children}
  </View>
);

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: '#f4f4f5' },
  headerBar: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', padding: 14, backgroundColor: '#fff' },
  title: { fontSize: 17, fontWeight: '700' },
  scroll: { padding: 16, paddingBottom: 40 },
  attemptsBox: { backgroundColor: '#fff', borderRadius: 10, padding: 14, marginBottom: 16, alignItems: 'center' },
  attemptsLabel: { fontSize: 12, color: '#777', fontWeight: '700' },
  attemptsValue: { fontSize: 28, fontWeight: '800', color: '#222' },
  attemptsRemaining: { fontSize: 12, color: '#10B981', fontWeight: '600' },
  testCard: { backgroundColor: '#fff', borderRadius: 10, padding: 14, marginBottom: 10 },
  examName: { fontSize: 15, fontWeight: '700', color: '#222' },
  attemptNum: { fontSize: 11, color: '#777' },
  scoreRow: { flexDirection: 'row', marginTop: 10, gap: 16 },
  scoreItem: {},
  scoreLabel: { fontSize: 10, color: '#777', fontWeight: '700' },
  scoreValue: { fontSize: 14, color: '#222', fontWeight: '700' },
  addBtn: { flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 6, padding: 14, backgroundColor: '#fff', borderRadius: 10, borderWidth: 1, borderColor: '#E52224', marginTop: 12 },
  addBtnText: { color: '#E52224', fontWeight: '700' },
  form: { backgroundColor: '#fff', borderRadius: 12, padding: 14, marginTop: 12 },
  formTitle: { fontSize: 16, fontWeight: '700', marginBottom: 10 },
  fieldLabel: { fontWeight: '600', color: '#444', marginBottom: 4, fontSize: 12 },
  input: { borderWidth: 1, borderColor: '#ddd', borderRadius: 6, paddingHorizontal: 10, paddingVertical: 8 },
  oralRow: { flexDirection: 'row', gap: 6 },
  oralChip: { paddingHorizontal: 12, paddingVertical: 6, borderRadius: 14, borderWidth: 1, borderColor: '#ddd' },
  oralChipActive: { backgroundColor: '#E52224', borderColor: '#E52224' },
  oralChipText: { fontSize: 12, color: '#444', fontWeight: '600' },
  oralChipTextActive: { color: '#fff' },
  pdfBtn: { flexDirection: 'row', alignItems: 'center', gap: 6, backgroundColor: '#EFF6FF', padding: 12, borderRadius: 8, borderWidth: 1, borderColor: '#3B82F6', borderStyle: 'dashed' },
  pdfBtnText: { color: '#3B82F6', flex: 1 },
  cancelBtn: { flex: 1, padding: 12, borderRadius: 8, borderWidth: 1, borderColor: '#ddd', alignItems: 'center' },
  cancelText: { color: '#555', fontWeight: '600' },
  submitBtn: { flex: 1, padding: 12, borderRadius: 8, backgroundColor: '#E52224', alignItems: 'center' },
  submitText: { color: '#fff', fontWeight: '700' },
});

export default AuPairEnglishTestScreen;
