import React from 'react';
import { View, Text, StyleSheet, TouchableOpacity } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { AuPairDocumentEntry } from '../../types/aupair';
import StatusPill from './StatusPill';

interface Props {
  entry: AuPairDocumentEntry;
  onUpload?: (entry: AuPairDocumentEntry) => void;
  onViewFiles?: (entry: AuPairDocumentEntry) => void;
  onDelete?: (fileId: number, entry: AuPairDocumentEntry) => void;
}

const DocumentCard: React.FC<Props> = ({ entry, onUpload, onViewFiles, onDelete }) => {
  const isStaff = entry.uploaded_by === 'staff';
  const isMulti = (entry.min_count != null && entry.min_count > 1) || !!entry.allow_multiple;
  const needsMore = entry.min_count && entry.count < entry.min_count;
  const rejected = entry.files.find(f => f.status === 'rejected');
  // Para tipos de un solo archivo: el pendiente del participante se puede eliminar
  // y volver a subir; el aprobado no se toca (solo IE).
  const pendingFile = entry.files.find(f => f.status === 'pending');
  const isApprovedSingle = !isMulti && entry.status === 'approved';
  const isPendingSingle = !isMulti && entry.status === 'pending' && !!pendingFile;

  return (
    <View style={styles.card}>
      <View style={styles.headRow}>
        <View style={{ flex: 1 }}>
          <Text style={styles.label}>
            {entry.label} {entry.required && <Text style={styles.req}>*</Text>}
          </Text>
          {entry.min_count ? (
            <Text style={styles.helper}>
              {entry.count}/{entry.min_count} archivos
            </Text>
          ) : null}
        </View>
        <StatusPill status={entry.status} small />
      </View>

      {rejected?.rejection_reason && (
        <View style={styles.rejectBox}>
          <Ionicons name="alert-circle" size={14} color="#991B1B" />
          <Text style={styles.rejectText}>{rejected.rejection_reason}</Text>
        </View>
      )}

      {isStaff ? (
        <Text style={styles.staffNote}>
          <Ionicons name="lock-closed" size={11} color="#6B7280" /> Este documento lo carga el equipo IE.
        </Text>
      ) : isApprovedSingle ? (
        <Text style={styles.staffNote}>
          <Ionicons name="lock-closed" size={11} color="#6B7280" /> Aprobado — solo el equipo IE puede modificarlo.
        </Text>
      ) : isPendingSingle ? (
        // Single-file en revisión: solo permitir eliminar el archivo subido para
        // corregirlo (no acumular). Tras eliminar, vuelve a "Falta" y puede subir.
        <View style={styles.actions}>
          {onDelete && pendingFile && (
            <TouchableOpacity style={styles.btnDanger} onPress={() => onDelete(pendingFile.id, entry)}>
              <Ionicons name="trash-outline" size={14} color="#fff" />
              <Text style={styles.btnPrimaryText}>Eliminar</Text>
            </TouchableOpacity>
          )}
        </View>
      ) : (
        <View style={styles.actions}>
          {entry.count > 0 && onViewFiles && (
            <TouchableOpacity style={styles.btnSecondary} onPress={() => onViewFiles(entry)}>
              <Ionicons name="folder-open-outline" size={14} color="#444" />
              <Text style={styles.btnSecondaryText}>Ver ({entry.count})</Text>
            </TouchableOpacity>
          )}
          {onUpload && (
            <TouchableOpacity style={styles.btnPrimary} onPress={() => onUpload(entry)}>
              <Ionicons
                name={needsMore || entry.status === 'missing' ? 'cloud-upload-outline' : 'add-circle-outline'}
                size={14}
                color="#fff"
              />
              <Text style={styles.btnPrimaryText}>
                {entry.status === 'missing' ? 'Subir' : needsMore ? 'Subir más' : 'Reemplazar'}
              </Text>
            </TouchableOpacity>
          )}
        </View>
      )}
    </View>
  );
};

const styles = StyleSheet.create({
  card: {
    backgroundColor: '#fff',
    padding: 14,
    borderRadius: 10,
    marginBottom: 10,
    borderWidth: 1,
    borderColor: '#eee',
  },
  headRow: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' },
  label: { fontSize: 14, fontWeight: '600', color: '#222' },
  req: { color: '#E52224' },
  helper: { fontSize: 11, color: '#777', marginTop: 2 },
  rejectBox: {
    flexDirection: 'row',
    backgroundColor: '#FEF2F2',
    padding: 8,
    borderRadius: 6,
    marginTop: 8,
    alignItems: 'flex-start',
    gap: 6,
  },
  rejectText: { color: '#991B1B', fontSize: 12, flex: 1, lineHeight: 16 },
  staffNote: { color: '#6B7280', fontSize: 12, marginTop: 8, fontStyle: 'italic' },
  actions: { flexDirection: 'row', marginTop: 12, gap: 8 },
  btnPrimary: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
    backgroundColor: '#E52224',
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 6,
  },
  btnPrimaryText: { color: '#fff', fontSize: 12, fontWeight: '700' },
  btnDanger: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
    backgroundColor: '#DC2626',
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 6,
  },
  btnSecondary: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
    backgroundColor: '#F3F4F6',
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 6,
  },
  btnSecondaryText: { color: '#444', fontSize: 12, fontWeight: '600' },
});

export default DocumentCard;
