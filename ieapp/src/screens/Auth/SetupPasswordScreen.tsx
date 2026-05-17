import React, { useState } from 'react';
import {
  View, Text, StyleSheet, TouchableOpacity, ActivityIndicator,
  SafeAreaView, ScrollView, Alert, TextInput,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useNavigation, useRoute, RouteProp } from '@react-navigation/native';
import { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { useAuth } from '../../contexts/AuthContext';
import { RootStackParamList } from '../../navigation/AppNavigator';

type Nav = NativeStackNavigationProp<RootStackParamList>;
type RouteP = RouteProp<{ SetupPassword: { email: string; name?: string } }, 'SetupPassword'>;

/**
 * Pantalla "primer ingreso": un postulante existente, cargado por el admin
 * desde el backoffice, llega acá la primera vez que abre la app. Setea su
 * contraseña, queda autenticado automáticamente.
 */
const SetupPasswordScreen: React.FC = () => {
  const navigation = useNavigation<Nav>();
  const route = useRoute<RouteP>();
  const { email, name } = route.params;
  const { setupPassword, isLoading } = useAuth();

  const [password, setPassword] = useState('');
  const [confirm, setConfirm] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [errors, setErrors] = useState<{ password?: string; confirm?: string }>({});

  const submit = async () => {
    const errs: typeof errors = {};
    if (!password) errs.password = 'Ingresá una contraseña';
    else if (password.length < 8) errs.password = 'Mínimo 8 caracteres';
    if (!confirm) errs.confirm = 'Confirmá tu contraseña';
    else if (password !== confirm) errs.confirm = 'Las contraseñas no coinciden';
    setErrors(errs);
    if (Object.keys(errs).length > 0) return;

    const result = await setupPassword(email, password, confirm);
    if (!result.success) {
      Alert.alert('No pudimos crear tu contraseña', result.message || 'Intentá nuevamente.');
    }
    // Si fue exitoso, el AuthContext flipea isAuthenticated y AppNavigator monta MainStack.
  };

  return (
    <SafeAreaView style={styles.safe}>
      <ScrollView contentContainerStyle={styles.scroll}>
        <TouchableOpacity style={styles.back} onPress={() => navigation.goBack()}>
          <Ionicons name="arrow-back" size={24} color="#222" />
        </TouchableOpacity>

        <View style={styles.iconWrap}>
          <Ionicons name="key-outline" size={36} color="#E52224" />
        </View>

        <Text style={styles.title}>
          {name ? `Hola, ${name.split(' ')[0]}!` : '¡Bienvenido!'}
        </Text>
        <Text style={styles.subtitle}>
          Es tu primer ingreso desde la app. Creá una contraseña para tu cuenta:
        </Text>

        <View style={styles.emailBox}>
          <Ionicons name="mail-outline" size={16} color="#666" />
          <Text style={styles.emailText}>{email}</Text>
        </View>

        <View style={styles.field}>
          <Text style={styles.label}>Nueva contraseña</Text>
          <View style={styles.inputRow}>
            <TextInput
              style={styles.input}
              value={password}
              onChangeText={setPassword}
              secureTextEntry={!showPassword}
              placeholder="Mínimo 8 caracteres"
              placeholderTextColor="#aaa"
              autoCapitalize="none"
              autoComplete="password-new"
            />
            <TouchableOpacity onPress={() => setShowPassword(!showPassword)}>
              <Ionicons name={showPassword ? 'eye-off' : 'eye'} size={20} color="#666" />
            </TouchableOpacity>
          </View>
          {errors.password ? <Text style={styles.error}>{errors.password}</Text> : null}
        </View>

        <View style={styles.field}>
          <Text style={styles.label}>Confirmar contraseña</Text>
          <View style={styles.inputRow}>
            <TextInput
              style={styles.input}
              value={confirm}
              onChangeText={setConfirm}
              secureTextEntry={!showPassword}
              placeholder="Repetí tu contraseña"
              placeholderTextColor="#aaa"
              autoCapitalize="none"
              autoComplete="password-new"
            />
          </View>
          {errors.confirm ? <Text style={styles.error}>{errors.confirm}</Text> : null}
        </View>

        <View style={styles.tipsBox}>
          <Text style={styles.tipsTitle}>Recomendaciones</Text>
          <Tip text="Usá al menos 8 caracteres" />
          <Tip text="Combiná mayúsculas, minúsculas y números" />
          <Tip text="No uses tu fecha de nacimiento o nombre" />
        </View>

        <TouchableOpacity
          style={[styles.btn, isLoading && styles.btnDisabled]}
          onPress={submit}
          disabled={isLoading}
        >
          {isLoading ? (
            <ActivityIndicator color="#fff" />
          ) : (
            <>
              <Ionicons name="checkmark" size={18} color="#fff" />
              <Text style={styles.btnText}>Crear contraseña e ingresar</Text>
            </>
          )}
        </TouchableOpacity>
      </ScrollView>
    </SafeAreaView>
  );
};

const Tip: React.FC<{ text: string }> = ({ text }) => (
  <View style={{ flexDirection: 'row', alignItems: 'flex-start', marginVertical: 2 }}>
    <Ionicons name="checkmark" size={13} color="#10B981" style={{ marginRight: 6, marginTop: 3 }} />
    <Text style={{ color: '#555', fontSize: 12, flex: 1 }}>{text}</Text>
  </View>
);

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: '#fff' },
  scroll: { padding: 22, paddingTop: 30 },
  back: { padding: 6, marginLeft: -6, marginBottom: 12 },
  iconWrap: {
    width: 64, height: 64, borderRadius: 32,
    backgroundColor: '#FEF2F2', alignItems: 'center', justifyContent: 'center',
    marginBottom: 16,
  },
  title: { fontSize: 24, fontWeight: '800', color: '#222' },
  subtitle: { color: '#555', marginTop: 6, lineHeight: 20 },
  emailBox: {
    flexDirection: 'row', alignItems: 'center', gap: 6,
    backgroundColor: '#f4f4f5', padding: 10, borderRadius: 8, marginTop: 16,
  },
  emailText: { color: '#333', fontWeight: '600' },
  field: { marginTop: 18 },
  label: { fontWeight: '600', color: '#444', marginBottom: 6, fontSize: 13 },
  inputRow: {
    flexDirection: 'row', alignItems: 'center',
    borderWidth: 1, borderColor: '#ddd', borderRadius: 8,
    paddingHorizontal: 12,
  },
  input: { flex: 1, paddingVertical: 12, color: '#222' },
  error: { color: '#991B1B', fontSize: 12, marginTop: 4 },
  tipsBox: { backgroundColor: '#f9fafb', padding: 12, borderRadius: 8, marginTop: 18 },
  tipsTitle: { fontWeight: '700', color: '#222', marginBottom: 6, fontSize: 12 },
  btn: {
    flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 8,
    backgroundColor: '#E52224', paddingVertical: 14, borderRadius: 10, marginTop: 24,
  },
  btnDisabled: { backgroundColor: '#aaa' },
  btnText: { color: '#fff', fontWeight: '700', fontSize: 15 },
});

export default SetupPasswordScreen;
