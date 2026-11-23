import React, { useCallback, useEffect, useMemo, useState } from 'react';
import { View, Text, TextInput, StyleSheet, ScrollView, ActivityIndicator, TouchableOpacity, Alert, Switch } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useNavigation, useRoute } from '@react-navigation/native';
import { SafeAreaView } from '../../components/SafeArea';
import ScreenHeader from '../../components/program/ScreenHeader';
import DropdownField from '../../components/DropdownField';
import CityField from '../../components/CityField';
import { allCountries } from '../../data/countries';
import { personalInfoService } from '../../services/api/personalInfoService';
import { useOptionalProgram } from '../../contexts/ProgramContext';
import { useAuth } from '../../contexts/AuthContext';
import { displayToIsoDate, isoToDisplayDate, maskDate } from '../../utils/dates';
import { EmergencyContactInput, Option, PersonalInfoData, PersonalInfoResponse, PersonalInfoStatus } from '../../types/personalInfo';

const RED = '#E52224';

/** Fechas de experiencia laboral: se editan como DD/MM/AAAA. */
interface WorkRow {
  id?: number | null;
  company: string;
  position: string;
  start: string;
  end: string;
  is_current: boolean;
  description: string;
}

type Errors = Record<string, string>;

const SECTION_FIELDS: Record<string, string[]> = {
  personal: ['name', 'phone', 'birth_date', 'gender', 'nationality', 'ci_number', 'marital_status'],
  address: ['country', 'city', 'city_id', 'address'],
  academic: ['academic_level', 'university', 'career', 'academic_year', 'english_level'],
  health: ['medical_conditions', 'allergies', 'medications'],
  emergency: ['emergency_contacts'],
  work: ['work_experiences'],
};

const sectionOf = (errorKey: string): string | undefined =>
  Object.keys(SECTION_FIELDS).find((s) => SECTION_FIELDS[s].some((f) => errorKey === f || errorKey.startsWith(`${f}.`)));

const countryValue = (stored?: string | null): string => {
  if (!stored) return 'paraguay';
  const match = allCountries.find((c) => c.label.toLowerCase() === stored.trim().toLowerCase() || c.value === stored.trim().toLowerCase());
  return match?.value ?? stored;
};

const emptyContact = (): EmergencyContactInput => ({ name: '', relationship: '', phone: '', alternative_phone: '', email: '' });

/**
 * Formulario de información personal de Admisión (pedido de IE, nov 2026).
 * Precargado con lo que IE ya tiene; todo obligatorio salvo Experiencia laboral. Con una postulación
 * activa, "Confirmar" lo deja confirmado para ese programa (habilita la carga de documentos).
 */
const PersonalInfoScreen: React.FC = () => {
  const navigation = useNavigation<any>();
  const route = useRoute<any>();
  const program = useOptionalProgram();
  const { updateUserData } = useAuth();
  const applicationId: number | null = route.params?.application_id ?? program?.applicationId ?? null;

  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [loadError, setLoadError] = useState(false);
  const [status, setStatus] = useState<PersonalInfoStatus | null>(null);
  const [options, setOptions] = useState<PersonalInfoResponse['options'] | null>(null);
  const [form, setForm] = useState<PersonalInfoData | null>(null);
  const [birthDate, setBirthDate] = useState('');
  const [country, setCountry] = useState('paraguay');
  const [contacts, setContacts] = useState<EmergencyContactInput[]>([emptyContact()]);
  const [work, setWork] = useState<WorkRow[]>([]);
  const [errors, setErrors] = useState<Errors>({});

  const apply = useCallback((res: PersonalInfoResponse) => {
    setStatus(res.personal_info);
    setOptions(res.options);
    setForm(res.data);
    setBirthDate(isoToDisplayDate(res.data.birth_date));
    setCountry(countryValue(res.data.country));
    setContacts(res.data.emergency_contacts.length ? res.data.emergency_contacts.map((c) => ({ ...c })) : [emptyContact()]);
    setWork(res.data.work_experiences.map((w) => ({
      id: w.id, company: w.company ?? '', position: w.position ?? '', start: isoToDisplayDate(w.start_date),
      end: isoToDisplayDate(w.end_date), is_current: !!w.is_current, description: w.description ?? '',
    })));
  }, []);

  const load = useCallback(async () => {
    setLoadError(false);
    try {
      apply(await personalInfoService.get(applicationId));
    } catch {
      setLoadError(true);
    } finally {
      setLoading(false);
    }
  }, [apply, applicationId]);

  useEffect(() => { load(); }, [load]);

  const set = (field: keyof PersonalInfoData, value: any) => {
    setForm((f) => (f ? { ...f, [field]: value } : f));
    setErrors((e) => {
      if (!e[field as string]) return e;
      const { [field as string]: _, ...rest } = e;
      return rest;
    });
  };

  const countryLabel = allCountries.find((c) => c.value === country)?.label ?? country;

  const sectionHasError = useMemo(() => {
    const out: Record<string, boolean> = {};
    Object.keys(errors).forEach((k) => { const s = sectionOf(k); if (s) out[s] = true; });
    return out;
  }, [errors]);

  const submit = async () => {
    if (!form) return;
    const localErrors: Errors = {};
    const birthIso = displayToIsoDate(birthDate);
    if (!birthIso) localErrors.birth_date = 'Ingresá una fecha válida (DD/MM/AAAA).';

    const workPayload = work.map((w, i) => {
      const start = displayToIsoDate(w.start);
      const end = w.is_current ? null : (w.end ? displayToIsoDate(w.end) : null);
      if (!start) localErrors[`work_experiences.${i}.start_date`] = 'Fecha de inicio inválida (DD/MM/AAAA).';
      if (!w.is_current && w.end && !end) localErrors[`work_experiences.${i}.end_date`] = 'Fecha de fin inválida (DD/MM/AAAA).';
      return { id: w.id, company: w.company, position: w.position, start_date: start ?? w.start, end_date: end, is_current: w.is_current, description: w.description || null };
    });

    if (Object.keys(localErrors).length) {
      setErrors(localErrors);
      Alert.alert('Revisá los datos', 'Hay fechas con un formato inválido.');
      return;
    }

    setSaving(true);
    try {
      const res = await personalInfoService.save({
        ...form,
        birth_date: birthIso,
        country: countryLabel,
        emergency_contacts: contacts,
        work_experiences: workPayload as any,
      }, applicationId);
      apply(res);
      setErrors({});
      // Perfil local al día (refreshUser pondría la app en "cargando").
      const { emergency_contacts: _c, work_experiences: _w, ...userFields } = res.data;
      updateUserData(userFields);
      await program?.refresh?.();
      Alert.alert(
        applicationId ? '¡Listo!' : 'Guardado',
        applicationId ? 'Tu información personal quedó confirmada. Ya podés continuar con tu proceso.' : 'Tu información personal quedó guardada.',
        [{ text: 'OK', onPress: () => (navigation.canGoBack() ? navigation.goBack() : null) }],
      );
    } catch (error: any) {
      const serverErrors = error?.response?.data?.errors as Record<string, string[]> | undefined;
      if (serverErrors) {
        const mapped: Errors = {};
        Object.entries(serverErrors).forEach(([k, v]) => { mapped[k] = v?.[0] ?? 'Campo inválido'; });
        setErrors(mapped);
        const sections = Array.from(new Set(Object.keys(mapped).map(sectionOf).filter(Boolean)));
        const labels: Record<string, string> = { personal: 'Datos personales', address: 'Domicilio', academic: 'Información académica', health: 'Salud', emergency: 'Contacto de emergencia', work: 'Experiencia laboral' };
        Alert.alert('Faltan datos', `Completá los campos marcados en: ${sections.map((s) => labels[s as string]).join(', ')}.`);
      } else {
        Alert.alert('Error', error?.response?.data?.message || 'No se pudo guardar. Revisá tu conexión e intentá de nuevo.');
      }
    } finally {
      setSaving(false);
    }
  };

  if (loading) {
    return <SafeAreaView style={styles.safe}><ScreenHeader title="Información personal" /><ActivityIndicator size="large" color={RED} style={{ marginTop: 80 }} /></SafeAreaView>;
  }
  if (loadError || !form || !options) {
    return (
      <SafeAreaView style={styles.safe}>
        <ScreenHeader title="Información personal" />
        <View style={styles.center}>
          <Text style={styles.muted}>No se pudo cargar tu información.</Text>
          <TouchableOpacity style={styles.retry} onPress={() => { setLoading(true); load(); }}><Text style={styles.retryText}>Reintentar</Text></TouchableOpacity>
        </View>
      </SafeAreaView>
    );
  }

  const text = (field: keyof PersonalInfoData, label: string, props: Partial<React.ComponentProps<typeof TextInput>> = {}) => (
    <Field label={label} error={errors[field as string]}>
      <TextInput style={[styles.input, errors[field as string] && styles.inputError]} value={(form[field] as string) ?? ''} onChangeText={(v) => set(field, v)} {...props} />
    </Field>
  );
  const select = (field: keyof PersonalInfoData, label: string, opts: Option[]) => (
    <DropdownField label={label} options={opts} value={(form[field] as string) ?? ''} onSelect={(v) => set(field, v)} error={errors[field as string]} placeholder="Seleccioná una opción" labelStyle={styles.label} />
  );
  const health = (field: keyof PersonalInfoData, label: string, none: string) => (
    <Field label={label} error={errors[field as string]}>
      <TextInput style={[styles.input, styles.multiline, errors[field as string] && styles.inputError]} value={(form[field] as string) ?? ''} onChangeText={(v) => set(field, v)} multiline placeholder={`Si no tenés, elegí "${none}"`} />
      <TouchableOpacity style={[styles.chip, form[field] === none && styles.chipActive]} onPress={() => set(field, none)}>
        <Text style={[styles.chipText, form[field] === none && styles.chipTextActive]}>{none}</Text>
      </TouchableOpacity>
    </Field>
  );

  const updateContact = (i: number, field: keyof EmergencyContactInput, value: string) => {
    setContacts((cs) => cs.map((c, idx) => (idx === i ? { ...c, [field]: value } : c)));
    setErrors((e) => { const k = `emergency_contacts.${i}.${field}`; if (!e[k] && !e.emergency_contacts) return e; const { [k]: _, emergency_contacts: __, ...rest } = e; return rest; });
  };
  const updateWork = (i: number, patch: Partial<WorkRow>) => {
    setWork((ws) => ws.map((w, idx) => (idx === i ? { ...w, ...patch } : w)));
    setErrors((e) => Object.fromEntries(Object.entries(e).filter(([k]) => !k.startsWith(`work_experiences.${i}.`))));
  };

  const missing = new Set(status?.missing_sections ?? []);
  const sectionTitle = (key: string, title: string, optional = false) => (
    <View style={styles.sectionHeader}>
      <Text style={styles.sectionTitle}>{title}</Text>
      {sectionHasError[key] || (!optional && missing.has(key))
        ? <Ionicons name="alert-circle" size={18} color={sectionHasError[key] ? RED : '#E0A800'} />
        : !optional ? <Ionicons name="checkmark-circle" size={18} color="#2E9E5B" /> : null}
    </View>
  );

  return (
    <SafeAreaView style={styles.safe}>
      <ScreenHeader title="Información personal" />
      <ScrollView contentContainerStyle={styles.scroll} automaticallyAdjustKeyboardInsets keyboardDismissMode="on-drag" keyboardShouldPersistTaps="handled">
        <View style={[styles.banner, status?.confirmed ? styles.bannerOk : null]}>
          <Ionicons name={status?.confirmed ? 'checkmark-circle' : 'information-circle'} size={20} color={status?.confirmed ? '#2E9E5B' : RED} />
          <Text style={styles.bannerText}>
            {status?.confirmed
              ? 'Ya confirmaste tu información para este programa. Si cambió algo, actualizala y volvé a guardar.'
              : 'Revisá y completá tus datos. Todos los campos son obligatorios, salvo Experiencia laboral.'}
          </Text>
        </View>

        <View style={styles.card}>
          {sectionTitle('personal', 'Datos personales')}
          {text('name', 'Nombre y apellido', { autoCapitalize: 'words' })}
          {text('phone', 'Celular', { keyboardType: 'phone-pad', placeholder: '0981 123 456' })}
          <Field label="Fecha de nacimiento" error={errors.birth_date}>
            <TextInput style={[styles.input, errors.birth_date && styles.inputError]} value={birthDate} onChangeText={(v) => { setBirthDate(maskDate(v)); set('birth_date', v); }} keyboardType="number-pad" placeholder="DD/MM/AAAA" maxLength={10} />
          </Field>
          {select('gender', 'Género', options.gender)}
          {text('nationality', 'Nacionalidad', { autoCapitalize: 'words', placeholder: 'Ej.: paraguaya' })}
          {text('ci_number', 'Cédula de identidad', { keyboardType: 'numbers-and-punctuation' })}
          {select('marital_status', 'Estado civil', options.marital_status)}
        </View>

        <View style={styles.card}>
          {sectionTitle('address', 'Domicilio')}
          <DropdownField label="País" options={allCountries} value={country} onSelect={(v) => { setCountry(v); set('country', v); }} searchable error={errors.country} labelStyle={styles.label} />
          <CityField
            label="Ciudad"
            country={countryLabel}
            value={{ city_id: form.city_id ?? null, city: form.city ?? '' }}
            onChange={(v) => { setForm((f) => (f ? { ...f, city: v.city, city_id: v.city_id } : f)); setErrors(({ city: _, city_id: __, ...rest }) => rest); }}
            error={errors.city || errors.city_id}
            labelStyle={styles.label}
          />
          {text('address', 'Dirección (calle, número, barrio)', { autoCapitalize: 'sentences' })}
        </View>

        <View style={styles.card}>
          {sectionTitle('academic', 'Información académica')}
          {select('academic_level', 'Nivel académico', options.academic_level)}
          {text('university', 'Institución educativa (colegio o universidad)', { autoCapitalize: 'words' })}
          {text('career', 'Carrera / orientación', { autoCapitalize: 'sentences' })}
          {text('academic_year', 'Año o curso', { placeholder: 'Ej.: 3er año, egresado/a 2024' })}
          {select('english_level', 'Nivel de inglés', options.english_level)}
        </View>

        <View style={styles.card}>
          {sectionTitle('health', 'Salud')}
          {health('medical_conditions', 'Condiciones médicas', 'Ninguna')}
          {health('allergies', 'Alergias', 'Ninguna')}
          {health('medications', 'Medicamentos actuales', 'Ninguno')}
        </View>

        <View style={styles.card}>
          {sectionTitle('emergency', 'Contacto de emergencia')}
          {!!errors.emergency_contacts && <Text style={styles.error}>{errors.emergency_contacts}</Text>}
          {contacts.map((c, i) => (
            <View key={c.id ?? `new-${i}`} style={i > 0 ? styles.subBlock : undefined}>
              {contacts.length > 1 && (
                <View style={styles.rowBetween}>
                  <Text style={styles.subTitle}>Contacto {i + 1}</Text>
                  <TouchableOpacity onPress={() => setContacts((cs) => cs.filter((_, idx) => idx !== i))}><Text style={styles.remove}>Quitar</Text></TouchableOpacity>
                </View>
              )}
              <Field label="Nombre y apellido" error={errors[`emergency_contacts.${i}.name`]}>
                <TextInput style={[styles.input, errors[`emergency_contacts.${i}.name`] && styles.inputError]} testID={`ec-${i}-name`} value={c.name} onChangeText={(v) => updateContact(i, 'name', v)} autoCapitalize="words" />
              </Field>
              <Field label="Relación" error={errors[`emergency_contacts.${i}.relationship`]}>
                <TextInput style={[styles.input, errors[`emergency_contacts.${i}.relationship`] && styles.inputError]} testID={`ec-${i}-relationship`} value={c.relationship} onChangeText={(v) => updateContact(i, 'relationship', v)} placeholder="Madre, padre, hermano/a…" autoCapitalize="sentences" />
              </Field>
              <Field label="Teléfono" error={errors[`emergency_contacts.${i}.phone`]}>
                <TextInput style={[styles.input, errors[`emergency_contacts.${i}.phone`] && styles.inputError]} testID={`ec-${i}-phone`} value={c.phone} onChangeText={(v) => updateContact(i, 'phone', v)} keyboardType="phone-pad" />
              </Field>
              <Field label="Email (opcional)" error={errors[`emergency_contacts.${i}.email`]}>
                <TextInput style={[styles.input, errors[`emergency_contacts.${i}.email`] && styles.inputError]} testID={`ec-${i}-email`} value={c.email ?? ''} onChangeText={(v) => updateContact(i, 'email', v)} keyboardType="email-address" autoCapitalize="none" />
              </Field>
            </View>
          ))}
          {contacts.length < 3 && (
            <TouchableOpacity style={styles.addBtn} onPress={() => setContacts((cs) => [...cs, emptyContact()])}>
              <Ionicons name="add-circle-outline" size={18} color={RED} /><Text style={styles.addText}>Agregar otro contacto</Text>
            </TouchableOpacity>
          )}
        </View>

        <View style={styles.card}>
          {sectionTitle('work', 'Experiencia laboral (opcional)', true)}
          {work.length === 0 && <Text style={styles.muted}>Si trabajaste o trabajás, agregá tu experiencia. Si no, dejala vacía.</Text>}
          {work.map((w, i) => (
            <View key={w.id ?? `w-${i}`} style={styles.subBlock}>
              <View style={styles.rowBetween}>
                <Text style={styles.subTitle}>Trabajo {i + 1}</Text>
                <TouchableOpacity onPress={() => setWork((ws) => ws.filter((_, idx) => idx !== i))}><Text style={styles.remove}>Quitar</Text></TouchableOpacity>
              </View>
              <Field label="Empresa" error={errors[`work_experiences.${i}.company`]}>
                <TextInput style={[styles.input, errors[`work_experiences.${i}.company`] && styles.inputError]} value={w.company} onChangeText={(v) => updateWork(i, { company: v })} autoCapitalize="words" />
              </Field>
              <Field label="Cargo" error={errors[`work_experiences.${i}.position`]}>
                <TextInput style={[styles.input, errors[`work_experiences.${i}.position`] && styles.inputError]} value={w.position} onChangeText={(v) => updateWork(i, { position: v })} autoCapitalize="sentences" />
              </Field>
              <Field label="Desde" error={errors[`work_experiences.${i}.start_date`]}>
                <TextInput style={[styles.input, errors[`work_experiences.${i}.start_date`] && styles.inputError]} value={w.start} onChangeText={(v) => updateWork(i, { start: maskDate(v) })} keyboardType="number-pad" placeholder="DD/MM/AAAA" maxLength={10} />
              </Field>
              <View style={styles.switchRow}>
                <Text style={styles.label}>Trabajo aquí actualmente</Text>
                <Switch value={w.is_current} onValueChange={(v) => updateWork(i, { is_current: v })} trackColor={{ true: RED }} />
              </View>
              {!w.is_current && (
                <Field label="Hasta" error={errors[`work_experiences.${i}.end_date`]}>
                  <TextInput style={[styles.input, errors[`work_experiences.${i}.end_date`] && styles.inputError]} value={w.end} onChangeText={(v) => updateWork(i, { end: maskDate(v) })} keyboardType="number-pad" placeholder="DD/MM/AAAA" maxLength={10} />
                </Field>
              )}
              <Field label="Tareas (opcional)">
                <TextInput style={[styles.input, styles.multiline]} value={w.description} onChangeText={(v) => updateWork(i, { description: v })} multiline />
              </Field>
            </View>
          ))}
          {work.length < 10 && (
            <TouchableOpacity style={styles.addBtn} onPress={() => setWork((ws) => [...ws, { company: '', position: '', start: '', end: '', is_current: false, description: '' }])}>
              <Ionicons name="add-circle-outline" size={18} color={RED} /><Text style={styles.addText}>Agregar experiencia laboral</Text>
            </TouchableOpacity>
          )}
        </View>

        <TouchableOpacity style={[styles.submit, saving && { opacity: 0.6 }]} onPress={submit} disabled={saving} accessibilityRole="button">
          {saving ? <ActivityIndicator color="#fff" /> : <Text style={styles.submitText}>{applicationId ? 'Confirmar mi información' : 'Guardar'}</Text>}
        </TouchableOpacity>
        {!!applicationId && <Text style={styles.footnote}>Al confirmar declarás que los datos son correctos. IE los usa para tu postulación.</Text>}
      </ScrollView>
    </SafeAreaView>
  );
};

const Field: React.FC<{ label: string; error?: string; children: React.ReactNode }> = ({ label, error, children }) => (
  <View style={styles.field}>
    <Text style={styles.label}>{label}</Text>
    {children}
    {!!error && <Text style={styles.error}>{error}</Text>}
  </View>
);

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: '#F5F6F8' },
  scroll: { padding: 16, paddingBottom: 120 },
  center: { alignItems: 'center', marginTop: 80, paddingHorizontal: 24 },
  muted: { color: '#666', fontSize: 14, marginBottom: 8 },
  retry: { marginTop: 12, backgroundColor: RED, borderRadius: 8, paddingHorizontal: 20, paddingVertical: 10 },
  retryText: { color: '#fff', fontWeight: '700' },
  banner: { flexDirection: 'row', gap: 8, backgroundColor: '#FDECEC', borderRadius: 10, padding: 12, marginBottom: 12, alignItems: 'flex-start' },
  bannerOk: { backgroundColor: '#E8F6EE' },
  bannerText: { flex: 1, color: '#333', fontSize: 14, lineHeight: 20 },
  card: { backgroundColor: '#fff', borderRadius: 12, padding: 16, marginBottom: 12 },
  sectionHeader: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', marginBottom: 12 },
  sectionTitle: { fontSize: 16, fontWeight: '700', color: '#222' },
  field: { marginBottom: 14 },
  label: { fontSize: 14, color: '#333', marginBottom: 6, fontWeight: '500' },
  input: { borderWidth: 1, borderColor: '#DDD', borderRadius: 8, paddingHorizontal: 12, paddingVertical: 10, fontSize: 15, color: '#222', backgroundColor: '#FAFAFA' },
  inputError: { borderColor: RED },
  multiline: { minHeight: 64, textAlignVertical: 'top' },
  error: { color: RED, fontSize: 12, marginTop: 4 },
  chip: { alignSelf: 'flex-start', marginTop: 8, borderWidth: 1, borderColor: '#CCC', borderRadius: 16, paddingHorizontal: 12, paddingVertical: 5 },
  chipActive: { backgroundColor: RED, borderColor: RED },
  chipText: { color: '#444', fontSize: 13 },
  chipTextActive: { color: '#fff', fontWeight: '600' },
  subBlock: { borderTopWidth: 1, borderTopColor: '#EEE', paddingTop: 12, marginTop: 4 },
  subTitle: { fontWeight: '600', color: '#444' },
  rowBetween: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 8 },
  remove: { color: RED, fontSize: 13 },
  switchRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 14 },
  addBtn: { flexDirection: 'row', alignItems: 'center', gap: 6, paddingVertical: 8 },
  addText: { color: RED, fontWeight: '600' },
  submit: { backgroundColor: RED, borderRadius: 10, paddingVertical: 15, alignItems: 'center', marginTop: 4 },
  submitText: { color: '#fff', fontWeight: '700', fontSize: 16 },
  footnote: { color: '#777', fontSize: 12, textAlign: 'center', marginTop: 8 },
});

export default PersonalInfoScreen;
