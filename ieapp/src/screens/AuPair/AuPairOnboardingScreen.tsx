import React, { useEffect, useState } from 'react';
import {
  View, Text, StyleSheet, ScrollView, ActivityIndicator,
  TouchableOpacity, SafeAreaView, Alert, Switch,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useNavigation, useRoute, RouteProp } from '@react-navigation/native';
import { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { programService, publicService } from '../../services/api';
import { usePublicAuth } from '../../contexts/PublicAuthContext';
import { RootStackParamList } from '../../navigation/AppNavigator';

type Nav = NativeStackNavigationProp<RootStackParamList>;
type RouteP = RouteProp<{ AuPairOnboarding: { programId?: number } }, 'AuPairOnboarding'>;

/**
 * Onboarding post-registro/login para visitantes que vinieron desde
 * "Postular Au Pair". 3 pasos:
 *  1. Bienvenida + términos
 *  2. Confirmar edad y nacionalidad
 *  3. Confirmación final + crear Application Au Pair vía POST /applications
 *
 * Al finalizar limpia `authRequest` y deja al user en AuPairDashboard.
 */
const AuPairOnboardingScreen: React.FC = () => {
  const navigation = useNavigation<Nav>();
  const route = useRoute<RouteP>();
  const { authRequest, clearAuthRequest } = usePublicAuth();

  // Resolución del programId con 3 niveles de fallback:
  //  1. Params explícitos (PublicProgramDetail → requestAuth → MainNavigator → Onboarding).
  //  2. authRequest del context (mismo origen, redundancia por si los params se pierden).
  //  3. Auto-resolución: buscar el primer programa Au Pair disponible en el catálogo público.
  //     Esto cubre el caso "el user entró al Onboarding manualmente o el wizard se reinició".
  const initialProgramId = route.params?.programId ?? authRequest?.programId ?? null;
  const [programId, setProgramId] = useState<number | null>(initialProgramId);
  const [resolving, setResolving] = useState(initialProgramId === null);

  useEffect(() => {
    if (programId !== null) return;
    let cancelled = false;
    (async () => {
      try {
        const programs = await publicService.getPublicPrograms();
        const auPair = programs.find(p => p.is_available_in_app);
        if (!cancelled && auPair) setProgramId(auPair.id);
      } catch {
        // si falla, programId queda en null y el botón Confirmar mostrará error
      } finally {
        if (!cancelled) setResolving(false);
      }
    })();
    return () => { cancelled = true; };
  }, [programId]);

  const [step, setStep] = useState<1 | 2 | 3>(1);
  const [acceptTerms, setAcceptTerms] = useState(false);
  const [isAdult, setIsAdult] = useState(false);
  const [submitting, setSubmitting] = useState(false);

  const next = () => {
    if (step === 1 && !acceptTerms) {
      Alert.alert('Términos', 'Debés aceptar los términos para continuar.');
      return;
    }
    if (step === 2 && !isAdult) {
      Alert.alert('Mayoría de edad', 'Confirmá que sos mayor de 18 años.');
      return;
    }
    if (step < 3) setStep((step + 1) as 1 | 2 | 3);
  };

  const goToDashboard = () => {
    navigation.reset({
      index: 0,
      routes: [{ name: 'AuPairDashboard' as any }],
    });
  };

  const finish = async () => {
    if (!programId) {
      Alert.alert(
        'Programa no disponible',
        'No pudimos cargar el catálogo de programas. Verificá tu conexión y volvé a intentar.',
      );
      return;
    }
    try {
      setSubmitting(true);
      const app = await programService.applyForProgram(programId, {});
      clearAuthRequest();
      if (app?.id) {
        goToDashboard();
      } else {
        // No debería ocurrir: la postulación se creó pero la respuesta no trae id.
        console.error('applyForProgram: respuesta sin id', app);
        Alert.alert(
          'Postulación registrada',
          'Tu postulación se creó pero no pudimos abrir tu panel. Volvé a ingresar a la app.',
        );
      }
    } catch (e: any) {
      console.error('Error al crear la postulación:', e?.response?.data || e);
      // Si ya existe una postulación activa (409), no es un error para el usuario:
      // llevémoslo a su dashboard en lugar de dejarlo atrapado en el onboarding.
      const alreadyApplied =
        e?.response?.status === 409 || e?.response?.data?.code === 'already_applied';
      if (alreadyApplied) {
        clearAuthRequest();
        goToDashboard();
        return;
      }
      const msg =
        e?.response?.data?.message || e?.message || 'No pudimos crear tu postulación.';
      Alert.alert('Error', msg);
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <SafeAreaView style={styles.safe}>
      <View style={styles.headerBar}>
        <Text style={styles.stepIndicator}>Paso {step} de 3</Text>
      </View>

      <View style={styles.bar}>
        <View style={[styles.barFill, { width: `${(step / 3) * 100}%` }]} />
      </View>

      <ScrollView contentContainerStyle={styles.scroll}>
        {step === 1 && (
          <View>
            <Ionicons name="rocket-outline" size={42} color="#3B82F6" style={{ marginBottom: 12 }} />
            <Text style={styles.title}>Estás a un paso de tu aventura Au Pair</Text>
            <Text style={styles.text}>
              Vivirás un año en Estados Unidos con una familia anfitriona, cuidando niños,
              practicando inglés y conociendo una nueva cultura.
            </Text>
            <Text style={styles.text}>
              Para postular necesitamos que aceptes nuestros{' '}
              <Text style={styles.link}>Términos y Condiciones</Text> y la{' '}
              <Text style={styles.link}>Política de Privacidad</Text>.
            </Text>
            <View style={styles.switchRow}>
              <Switch value={acceptTerms} onValueChange={setAcceptTerms} />
              <Text style={styles.switchLabel}>Acepto los términos y la política de privacidad.</Text>
            </View>
          </View>
        )}

        {step === 2 && (
          <View>
            <Ionicons name="person-outline" size={42} color="#8B5CF6" style={{ marginBottom: 12 }} />
            <Text style={styles.title}>Confirmemos algunos datos</Text>
            <Text style={styles.text}>
              El programa Au Pair USA requiere ser mayor de 18 años al momento de postular.
            </Text>
            <View style={styles.switchRow}>
              <Switch value={isAdult} onValueChange={setIsAdult} />
              <Text style={styles.switchLabel}>Confirmo que soy mayor de 18 años.</Text>
            </View>
          </View>
        )}

        {step === 3 && (
          <View>
            <Ionicons name="checkmark-circle-outline" size={42} color="#10B981" style={{ marginBottom: 12 }} />
            <Text style={styles.title}>¿Lista para postular?</Text>
            <Text style={styles.text}>
              Al confirmar, crearemos tu postulación Au Pair USA. Después podrás:
            </Text>
            <View style={styles.bullets}>
              <Bullet text="Subir tus documentos paso a paso" />
              <Bullet text="Registrar tus pagos y comprobantes" />
              <Bullet text="Seguir el proceso de visa y match" />
            </View>
          </View>
        )}
      </ScrollView>

      <View style={styles.footer}>
        {step > 1 && (
          <TouchableOpacity style={styles.backBtn} onPress={() => setStep((step - 1) as 1 | 2 | 3)}>
            <Text style={styles.backText}>Atrás</Text>
          </TouchableOpacity>
        )}
        {step < 3 ? (
          <TouchableOpacity style={styles.nextBtn} onPress={next}>
            <Text style={styles.nextText}>Continuar</Text>
            <Ionicons name="arrow-forward" size={18} color="#fff" />
          </TouchableOpacity>
        ) : (
          <TouchableOpacity
            style={[styles.nextBtn, submitting && styles.nextBtnDisabled]}
            onPress={finish}
            disabled={submitting}
          >
            {submitting ? <ActivityIndicator color="#fff" /> : (
              <>
                <Text style={styles.nextText}>Confirmar postulación</Text>
                <Ionicons name="checkmark" size={18} color="#fff" />
              </>
            )}
          </TouchableOpacity>
        )}
      </View>
    </SafeAreaView>
  );
};

const Bullet: React.FC<{ text: string }> = ({ text }) => (
  <View style={{ flexDirection: 'row', alignItems: 'flex-start', marginVertical: 4 }}>
    <Ionicons name="checkmark-circle" size={16} color="#10B981" style={{ marginRight: 6, marginTop: 2 }} />
    <Text style={{ flex: 1, color: '#444', lineHeight: 20 }}>{text}</Text>
  </View>
);

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
  switchRow: { flexDirection: 'row', alignItems: 'center', gap: 10, marginTop: 18, backgroundColor: '#f8f9fa', padding: 12, borderRadius: 8 },
  switchLabel: { flex: 1, color: '#222', fontSize: 14 },
  bullets: { marginTop: 14 },
  footer: { flexDirection: 'row', padding: 18, gap: 10, borderTopWidth: 1, borderTopColor: '#eee' },
  backBtn: { paddingHorizontal: 16, paddingVertical: 12, borderRadius: 8, borderWidth: 1, borderColor: '#ddd' },
  backText: { color: '#555', fontWeight: '600' },
  nextBtn: { flex: 1, flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 6, backgroundColor: '#E52224', paddingVertical: 14, borderRadius: 8 },
  nextBtnDisabled: { backgroundColor: '#aaa' },
  nextText: { color: '#fff', fontWeight: '700', fontSize: 15 },
});

export default AuPairOnboardingScreen;
