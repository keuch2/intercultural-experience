import React, { useState } from 'react';
import { Modal, View, Text, TextInput, TouchableOpacity, Switch, StyleSheet, ActivityIndicator, Alert, KeyboardAvoidingView, Platform, ScrollView } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { SupportReportPayload } from '../../types/aupair';

/**
 * Formulario para que el participante envíe un reporte / consulta o un incidente a IE.
 * Compartido por el flujo Au Pair y el motor de programas.
 */
const NewSupportReportModal: React.FC<{
  visible: boolean;
  onClose: () => void;
  onSubmit: (payload: SupportReportPayload) => Promise<void>;
}> = ({ visible, onClose, onSubmit }) => {
  const [type, setType] = useState<SupportReportPayload['log_type']>('participant_report');
  const [title, setTitle] = useState('');
  const [description, setDescription] = useState('');
  const [urgent, setUrgent] = useState(false);
  const [sending, setSending] = useState(false);

  const reset = () => { setType('participant_report'); setTitle(''); setDescription(''); setUrgent(false); };

  const submit = async () => {
    if (title.trim().length < 3 || description.trim().length < 5) {
      Alert.alert('Faltan datos', 'Escribí un título y contanos qué pasó.');
      return;
    }
    setSending(true);
    try {
      await onSubmit({ log_type: type, title: title.trim(), description: description.trim(), urgent });
      reset();
      onClose();
      Alert.alert('Enviado', 'Tu reporte llegó al equipo IE. Te responderán a la brevedad.');
    } catch {
      Alert.alert('No pudimos enviar tu reporte', 'Revisá tu conexión e intentá de nuevo.');
    } finally {
      setSending(false);
    }
  };

  return (
    <Modal visible={visible} transparent animationType="slide" onRequestClose={onClose}>
      <KeyboardAvoidingView behavior={Platform.OS === 'ios' ? 'padding' : undefined} style={styles.backdrop}>
        <View style={styles.sheet}>
          <View style={styles.head}>
            <Text style={styles.title}>Nuevo reporte</Text>
            <TouchableOpacity onPress={onClose} accessibilityLabel="Cerrar"><Ionicons name="close" size={24} color="#444" /></TouchableOpacity>
          </View>
          <ScrollView keyboardShouldPersistTaps="handled">
            <Text style={styles.label}>¿Qué querés enviar?</Text>
            <View style={styles.segment}>
              {([['participant_report', 'Reporte / consulta', 'chatbubble-ellipses-outline'], ['incident', 'Incidente', 'alert-circle-outline']] as const).map(([key, label, icon]) => (
                <TouchableOpacity key={key} style={[styles.segItem, type === key && styles.segActive]} onPress={() => setType(key)}>
                  <Ionicons name={icon} size={16} color={type === key ? '#fff' : '#444'} />
                  <Text style={[styles.segText, type === key && styles.segTextActive]}>{label}</Text>
                </TouchableOpacity>
              ))}
            </View>
            <Text style={styles.label}>Título</Text>
            <TextInput style={styles.input} value={title} onChangeText={setTitle} placeholder="Ej: Consulta sobre mi horario" maxLength={255} />
            <Text style={styles.label}>Contanos qué pasó</Text>
            <TextInput style={[styles.input, styles.textarea]} value={description} onChangeText={setDescription} placeholder="Detalle de la situación, fechas, personas involucradas…" multiline maxLength={2000} textAlignVertical="top" />
            <View style={styles.switchRow}>
              <View style={{ flex: 1 }}>
                <Text style={styles.switchLabel}>Es urgente</Text>
                <Text style={styles.switchHint}>Marcalo si necesitás respuesta inmediata.</Text>
              </View>
              <Switch value={urgent} onValueChange={setUrgent} trackColor={{ true: '#E52224' }} />
            </View>
            <TouchableOpacity style={[styles.btn, sending && { opacity: 0.6 }]} onPress={submit} disabled={sending}>
              {sending ? <ActivityIndicator color="#fff" /> : (<><Ionicons name="send" size={16} color="#fff" /><Text style={styles.btnText}>Enviar a IE</Text></>)}
            </TouchableOpacity>
          </ScrollView>
        </View>
      </KeyboardAvoidingView>
    </Modal>
  );
};

const styles = StyleSheet.create({
  backdrop: { flex: 1, backgroundColor: 'rgba(0,0,0,0.45)', justifyContent: 'flex-end' },
  sheet: { backgroundColor: '#fff', borderTopLeftRadius: 18, borderTopRightRadius: 18, padding: 18, maxHeight: '90%' },
  head: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 6 },
  title: { fontSize: 18, fontWeight: '800', color: '#222' },
  label: { fontSize: 12, fontWeight: '700', color: '#666', marginTop: 12, marginBottom: 6, textTransform: 'uppercase' },
  segment: { flexDirection: 'row', gap: 8 },
  segItem: { flex: 1, flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 6, borderWidth: 1, borderColor: '#ddd', borderRadius: 10, paddingVertical: 10 },
  segActive: { backgroundColor: '#E52224', borderColor: '#E52224' },
  segText: { fontWeight: '700', color: '#444', fontSize: 13 },
  segTextActive: { color: '#fff' },
  input: { borderWidth: 1, borderColor: '#ddd', borderRadius: 10, padding: 12, fontSize: 15, backgroundColor: '#fafafa' },
  textarea: { minHeight: 110 },
  switchRow: { flexDirection: 'row', alignItems: 'center', marginTop: 14, gap: 10 },
  switchLabel: { fontWeight: '700', color: '#222' },
  switchHint: { color: '#777', fontSize: 12 },
  btn: { backgroundColor: '#E52224', borderRadius: 12, paddingVertical: 14, alignItems: 'center', justifyContent: 'center', flexDirection: 'row', gap: 8, marginTop: 18, marginBottom: 8 },
  btnText: { color: '#fff', fontWeight: '800', fontSize: 15 },
});

export default NewSupportReportModal;
