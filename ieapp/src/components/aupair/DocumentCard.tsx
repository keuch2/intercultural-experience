import React from 'react';
import { View, Text, StyleSheet, TouchableOpacity } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { AuPairDocumentEntry } from '../../types/aupair';
import { ProgramDocumentEntry } from '../../types/programEngine';
import StatusPill from './StatusPill';

/** Acepta entradas Au Pair y del motor genérico (misma forma; stage abierto). */
type EntryLike = AuPairDocumentEntry | ProgramDocumentEntry;
type FileLike = EntryLike['files'][number];

interface Props<T extends EntryLike> {
  entry: T;
  onUpload?: (entry: T) => void;
  onDelete?: (fileId: number, entry: T) => void;
  onDownload?: (file: FileLike, entry: T) => void;
}

/**
 * Card de un requisito documental.
 * Reglas:
 *  - Los archivos (incluidos los que carga IE) siempre se listan y se pueden descargar.
 *  - Aprobado y completo (single, o multi con >= min_count aprobados) → bloqueado: solo IE lo modifica.
 *  - Multi aprobado parcialmente → "Subir más (x/n)".
 *  - Single pendiente → solo eliminar el pendiente para reemplazarlo.
 */
function DocumentCard<T extends EntryLike>({ entry, onUpload, onDelete, onDownload }: Props<T>) {
  const isStaff = entry.uploaded_by === 'staff';
  const isMulti = (entry.min_count != null && entry.min_count > 1) || !!entry.allow_multiple;
  const required = Math.max(1, entry.min_count ?? 1);
  const approvedCount = entry.files.filter(f => f.status === 'approved').length;
  const pendingFile = entry.files.find(f => f.status === 'pending');
  const rejected = entry.files.find(f => f.status === 'rejected');

  const isLocked = entry.status === 'approved' && (!isMulti || approvedCount >= required);
  const isPendingSingle = !isMulti && !isLocked && !!pendingFile;
  const needsMore = isMulti && entry.count < required;

  return (
    <View style={styles.card}>
      <View style={styles.headRow}>
        <View style={{ flex: 1 }}>
          <Text style={styles.label}>
            {entry.label} {entry.required && <Text style={styles.req}>*</Text>}
          </Text>
          {isMulti ? (
            <Text style={styles.helper}>{approvedCount}/{required} aprobados{entry.count > approvedCount ? ` · ${entry.count} archivos` : ''}</Text>
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

      {entry.files.length > 0 && (
        <View style={styles.files}>
          {entry.files.map(f => (
            <View key={f.id} style={styles.fileRow}>
              <Ionicons name={f.uploaded_by_type === 'staff' ? 'shield-checkmark-outline' : 'document-outline'} size={16} color="#555" />
              <View style={{ flex: 1, marginHorizontal: 8 }}>
                <Text style={styles.fileName} numberOfLines={1}>{f.original_filename || `Archivo ${f.id}`}</Text>
                <Text style={styles.fileMeta}>
                  {f.file_size_formatted || ''}{f.uploaded_by_type === 'staff' ? ' · cargado por IE' : ''}
                </Text>
              </View>
              <StatusPill status={f.status} small />
              {onDownload && (
                <TouchableOpacity
                  style={[styles.iconBtn, !f.download_url && styles.iconBtnDisabled]}
                  onPress={() => onDownload(f, entry)}
                  disabled={!f.download_url}
                  accessibilityLabel={`Descargar ${f.original_filename || ''}`}
                >
                  <Ionicons name="download-outline" size={18} color={f.download_url ? '#E52224' : '#bbb'} />
                </TouchableOpacity>
              )}
            </View>
          ))}
        </View>
      )}

      {isStaff ? (
        <Text style={styles.staffNote}>
          <Ionicons name="lock-closed" size={11} color="#6B7280" /> Este documento lo carga el equipo IE.
        </Text>
      ) : isLocked ? (
        <Text style={styles.staffNote}>
          <Ionicons name="lock-closed" size={11} color="#6B7280" /> Aprobado — solo el equipo IE puede modificarlo.
        </Text>
      ) : isPendingSingle ? (
        <View style={styles.actions}>
          {onDelete && pendingFile && (
            <TouchableOpacity style={styles.btnDanger} onPress={() => onDelete(pendingFile.id, entry)}>
              <Ionicons name="trash-outline" size={14} color="#fff" />
              <Text style={styles.btnPrimaryText}>Eliminar y volver a subir</Text>
            </TouchableOpacity>
          )}
        </View>
      ) : onUpload ? (
        <View style={styles.actions}>
          <TouchableOpacity style={styles.btnPrimary} onPress={() => onUpload(entry)}>
            <Ionicons name={entry.status === 'missing' || needsMore ? 'cloud-upload-outline' : 'add-circle-outline'} size={14} color="#fff" />
            <Text style={styles.btnPrimaryText}>
              {entry.status === 'missing' ? 'Subir' : isMulti ? `Subir más (${entry.count}/${required})` : 'Reemplazar'}
            </Text>
          </TouchableOpacity>
        </View>
      ) : null}
    </View>
  );
}

const styles = StyleSheet.create({
  card: { backgroundColor: '#fff', padding: 14, borderRadius: 10, marginBottom: 10, borderWidth: 1, borderColor: '#eee' },
  headRow: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' },
  label: { fontSize: 14, fontWeight: '600', color: '#222' },
  req: { color: '#E52224' },
  helper: { fontSize: 11, color: '#777', marginTop: 2 },
  rejectBox: { flexDirection: 'row', backgroundColor: '#FEF2F2', padding: 8, borderRadius: 6, marginTop: 8, alignItems: 'flex-start', gap: 6 },
  rejectText: { color: '#991B1B', fontSize: 12, flex: 1, lineHeight: 16 },
  files: { marginTop: 10, borderTopWidth: 1, borderTopColor: '#f4f4f5', paddingTop: 6 },
  fileRow: { flexDirection: 'row', alignItems: 'center', paddingVertical: 6 },
  fileName: { fontSize: 12, color: '#222', fontWeight: '600' },
  fileMeta: { fontSize: 10, color: '#888', marginTop: 1 },
  iconBtn: { padding: 6, marginLeft: 4 },
  iconBtnDisabled: { opacity: 0.5 },
  staffNote: { color: '#6B7280', fontSize: 12, marginTop: 8, fontStyle: 'italic' },
  actions: { flexDirection: 'row', marginTop: 12, gap: 8 },
  btnPrimary: { flexDirection: 'row', alignItems: 'center', gap: 4, backgroundColor: '#E52224', paddingHorizontal: 12, paddingVertical: 6, borderRadius: 6 },
  btnPrimaryText: { color: '#fff', fontSize: 12, fontWeight: '700' },
  btnDanger: { flexDirection: 'row', alignItems: 'center', gap: 4, backgroundColor: '#DC2626', paddingHorizontal: 12, paddingVertical: 6, borderRadius: 6 },
});

export default DocumentCard;
