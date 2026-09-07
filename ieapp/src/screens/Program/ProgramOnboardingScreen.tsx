import React, { useEffect, useState } from 'react';
import {
  View, Text, StyleSheet, ScrollView, ActivityIndicator, TouchableOpacity, SafeAreaView, Alert, Switch,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useNavigation, useRoute, RouteProp } from '@react-navigation/native';
import { programService, publicService } from '../../services/api';
import type { PublicProgram } from '../../services/api';
import { usePublicAuth } from '../../contexts/PublicAuthContext';
import { useProgram } from '../../contexts/ProgramContext';

type RouteP = RouteProp<{ ProgramOnboarding: { programId?: number } }, 'ProgramOnboarding'>;

/**
 * Onboarding genérico para programas del motor. Los textos (título, intro,
 * pasos, términos, mayoría de edad) vienen de `program.onboarding`, que el admin
 * edita en "Configurar motor → Onboarding".
 */
const ProgramOnboardingScreen: React.FC = () => {
  const navigation = useNavigation<any>();
  const route = useRoute<RouteP>();
  const { authRequest, clearAuthRequest } = usePublicAuth();
  const { refresh } = useProgram();

  const programId = route.params?.programId ?? authRequest?.programId ?? null;
  const [program, setProgram] = useState<PublicProgram | null>(null);
  const [loadingProgram, setLoadingProgram] = useState(true);
  const [step, setStep] = useState(1);
  const [acceptTerms, setAcceptTerms] = useState(false);
  const [isAdult, setIsAdult] = useState(false);
  const [submitting, setSubmitting] = useState(false);

  useEffect(() => {
    let cancelled = false;
    (async () => {
      try {
        if (programId) setProgram(await publicService.getPublicProgram(programId));
      } finally {
        if (!cancelled) setLoadingProgram(false);
      }
    })();
    return () => { cancelled = true; };
  }, [programId]);

  const ob = program?.onboarding ?? {};
  const steps = ob.steps && ob.steps.length > 0 ? ob.steps : [
    { title: 'Admisión', body: 'Completá tus datos y subí tus documentos de admisión.' },
    { title: 'Documentación y pagos', body: 'Registrá tus pagos y subí la documentación de cada etapa.' },
    { title: 'Seguimiento', body: 'Seguí tu proceso paso a paso desde la app.' },
  ];
  const requiresAdult = ob.requires_adult !== false;
  const totalSteps = requiresAdult ? 3 : 2;

  const next = () => {
    if (step === 1 && !acceptTerms) { Alert.alert('Términos', 'Debés aceptar los términos para continuar.'); return; }
    if (step === 2 && requiresAdult && !isAdult) { Alert.alert('Mayoría de edad', 'Confirmá que sos mayor de 18 años.'); return; }
    if (step < totalSteps) setStep(step + 1);
  };

  const goToDashboard = async () => {
    await refresh();
    navigation.reset({ index: 0, routes: [{ name: 'ProgramDashboard' }] });
  };

  const finish = async () => {
    if (!programId) { Alert.alert('Programa no disponible', 'No pudimos identificar el programa. Volvé al catálogo y elegí uno.'); return; }
    try {
      setSubmitting(true);
      const app = await programService.applyForProgram(programId, {});
      clearAuthRequest();
      if (app?.id) await goToDashboard();
      else Alert.alert('Postulación registrada', 'Tu postulación se creó pero no pudimos abrir tu panel. Volvé a ingresar a la app.');
    } catch (e: any) {
      const alreadyApplied = e?.response?.status === 409 || e?.response?.data?.code === 'already_applied';
      if (alreadyApplied) { clearAuthRequest(); await goToDashboard(); return; }
      Alert.alert('Error', e?.response?.data?.message || e?.message || 'No pudimos crear tu postulación.');
    } finally {
      setSubmitting(false);
    }
  };

  if (loadingProgram) {
    return <SafeAreaView style={styles.safe}><ActivityIndicator size="large" color="#E52224" style={{ marginTop: 80 }} /></SafeAreaView>;
  }

  const lastStep = step === totalSteps;

  return (
    <SafeAreaView style={styles.safe}>
      <View style={styles.headerBar}><Text style={styles.stepIndicator}>Paso {step} de {totalSteps}</Text></View>
      <View style={styles.bar}><View style={[styles.barFill, { width: `${(step / totalSteps) * 100}%` }]} /></View>

      <ScrollView contentContainerStyle={styles.scroll}>
        {step === 1 && (
          <View>
            <Ionicons name="rocket-outline" size={42} color="#3B82F6" style={{ marginBottom: 12 }} />
            <Text style={styles.title}>{ob.title || `Postulá a ${program?.name ?? 'este programa'}`}</Text>
            {ob.intro ? <Text style={styles.text}>{ob.intro}</Text> : program?.description ? <Text style={styles.text}>{program.description}</Text> : null}
            <Text style={styles.text}>
              Para postular necesitamos que aceptes nuestros <Text style={styles.link}>Términos y Condiciones</Text> y la <Text style={styles.link}>Política de Privacidad</Text>.
            </Text>
            {ob.terms_text ? <View style={styles.termsBox}><Text style={styles.termsText}>{ob.terms_text}</Text></View> : null}
            <View style={styles.switchRow}><Switch value={acceptTerms} onValueChange={setAcceptTerms} /><Text style={styles.switchLabel}>Acepto los términos y la política de privacidad.</Text></View>
          </View>
        )}
        {step === 2 && requiresAdult && (
          <View>
            <Ionicons name="person-outline" size={42} color="#8B5CF6" style={{ marginBottom: 12 }} />
            <Text style={styles.title}>Confirmemos algunos datos</Text>
            <Text style={styles.text}>{program?.name ?? 'El programa'} requiere ser mayor de 18 años al momento de postular.</Text>
            <View style={styles.switchRow}><Switch value={isAdult} onValueChange={setIsAdult} /><Text style={styles.switchLabel}>Confirmo que soy mayor de 18 años.</Text></View>
          </View>
        )}
        {lastStep && (
          <View>
            <Ionicons name="checkmark-circle-outline" size={42} color="#10B981" style={{ marginBottom: 12 }} />
            <Text style={styles.title}>¿Todo listo para postular?</Text>
            <Text style={styles.text}>Al confirmar, crearemos tu postulación a {program?.name ?? 'este programa'}. Así será tu recorrido:</Text>
            <View style={styles.bullets}>
              {steps.map((s, i) => (
                <View key={i} style={styles.bullet}>
                  <View style={styles.bulletNum}><Text style={styles.bulletNumText}>{i + 1}</Text></View>
                  <View style={{ flex: 1 }}><Text style={styles.bulletTitle}>{s.title}</Text>{s.body ? <Text style={styles.bulletBody}>{s.body}</Text> : null}</View>
                </View>
              ))}
            </View>
          </View>
        )}
      </ScrollView>

      <View style={styles.footer}>
        {step > 1 && <TouchableOpacity style={styles.backBtn} onPress={() => setStep(step - 1)}><Text style={styles.backText}>Atrás</Text></TouchableOpacity>}
        {!lastStep ? (
          <TouchableOpacity style={styles.nextBtn} onPress={next}><Text style={styles.nextText}>Continuar</Text><Ionicons name="arrow-forward" size={18} color="#fff" /></TouchableOpacity>
        ) : (
          <TouchableOpacity style={[styles.nextBtn, submitting && styles.nextBtnDisabled]} onPress={finish} disabled={submitting}>
            {submitting ? <ActivityIndicator color="#fff" /> : (<><Text style={styles.nextText}>Confirmar postulación</Text><Ionicons name="checkmark" size={18} color="#fff" /></>)}
          </TouchableOpacity>
        )}
      </View>
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: '#fff' },
  headerBar: { flexDirection: 'row', justifyContent: 'flex-end', alignItems: 'center', padding: 18 },
  stepIndicator: { color: '#777', fontWeight: '600', fontSize: 12 },
  bar: { height: 4, backgroundColor: '#f4f4f5', marginHorizontal: 18, borderRadius: 2, overflow: 'hidden' },
  barFill: { height: '100%', backgroundColor: '#E52224' },
  scroll: { padding: 22 },
  title: { fontSize: 24, fontWeight: '800', color: '#222', marginBottom: 12, lineHeight: 30 },
  text: { color: '#444', fontSize: 14, lineHeight: 22, marginBottom: 10 },
  link: { color: '#3B82F6', textDecorationLine: 'underline', fontWeight: '600' },
  termsBox: { backgroundColor: '#f8f9fa', padding: 12, borderRadius: 8, marginTop: 6 },
  termsText: { color: '#555', fontSize: 12, lineHeight: 18 },
  switchRow: { flexDirection: 'row', alignItems: 'center', gap: 10, marginTop: 18, backgroundColor: '#f8f9fa', padding: 12, borderRadius: 8 },
  switchLabel: { flex: 1, color: '#222', fontSize: 14 },
  bullets: { marginTop: 14, gap: 12 },
  bullet: { flexDirection: 'row', alignItems: 'flex-start', gap: 10 },
  bulletNum: { width: 26, height: 26, borderRadius: 13, backgroundColor: '#E52224', alignItems: 'center', justifyContent: 'center' },
  bulletNumText: { color: '#fff', fontWeight: '800', fontSize: 12 },
  bulletTitle: { fontWeight: '700', color: '#222' },
  bulletBody: { color: '#555', fontSize: 13, lineHeight: 18, marginTop: 2 },
  footer: { flexDirection: 'row', padding: 18, gap: 10, borderTopWidth: 1, borderTopColor: '#eee' },
  backBtn: { paddingHorizontal: 16, paddingVertical: 12, borderRadius: 8, borderWidth: 1, borderColor: '#ddd' },
  backText: { color: '#555', fontWeight: '600' },
  nextBtn: { flex: 1, flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 6, backgroundColor: '#E52224', paddingVertical: 14, borderRadius: 8 },
  nextBtnDisabled: { backgroundColor: '#aaa' },
  nextText: { color: '#fff', fontWeight: '700', fontSize: 15 },
});

export default ProgramOnboardingScreen;
