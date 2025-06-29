import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  Alert,
  Image,
  Dimensions,
  ActivityIndicator,
  Linking,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { assignmentService, AssignmentDetails } from '../services/api/assignmentService';

const { width } = Dimensions.get('window');

interface Props {
  navigation: any;
  route: {
    params: {
      assignmentId: number;
      assignment?: any;
    };
  };
}

const AssignmentDetailScreen: React.FC<Props> = ({ navigation, route }) => {
  const { assignmentId } = route.params;
  const [assignment, setAssignment] = useState<AssignmentDetails | null>(null);
  const [loading, setLoading] = useState(true);
  const [applying, setApplying] = useState(false);

  useEffect(() => {
    loadAssignmentDetails();
  }, [assignmentId]);

  const loadAssignmentDetails = async () => {
    try {
      setLoading(true);
      const response = await assignmentService.getAssignmentDetails(assignmentId);
      
      if (response.success) {
        setAssignment(response.assignment);
      }
    } catch (error: any) {
      Alert.alert('Error', error.message);
      navigation.goBack();
    } finally {
      setLoading(false);
    }
  };

  const handleApply = async () => {
    if (!assignment?.can_apply) {
      Alert.alert(
        'No disponible',
        'No puedes aplicar a este programa en este momento.'
      );
      return;
    }

    Alert.alert(
      'Aplicar al Programa',
      `¿Estás seguro que quieres aplicar al programa "${assignment.program.name}"?\n\nEsta acción no se puede deshacer.`,
      [
        { text: 'Cancelar', style: 'cancel' },
        {
          text: 'Aplicar',
          style: 'default',
          onPress: confirmApplication,
        },
      ]
    );
  };

  const confirmApplication = async () => {
    try {
      setApplying(true);
      const response = await assignmentService.applyToProgram(assignmentId);
      
      if (response.success) {
        Alert.alert(
          '¡Aplicación Enviada!',
          response.message,
          [
            {
              text: 'OK',
              onPress: () => {
                loadAssignmentDetails(); // Refresh data
              },
            },
          ]
        );
      }
    } catch (error: any) {
      Alert.alert('Error', error.message);
    } finally {
      setApplying(false);
    }
  };

  const handleViewProgram = () => {
    if (assignment) {
      navigation.navigate('ProgramDetail', {
        programId: assignment.program.id,
        program: assignment.program,
      });
    }
  };

  const handleViewForm = () => {
    if (assignment?.program.active_form) {
      navigation.navigate('FormScreen', {
        formId: assignment.program.active_form.id,
        programId: assignment.program.id,
      });
    }
  };

  const getStatusColor = (status: string) => {
    const colors = {
      assigned: '#ffc107',
      applied: '#17a2b8',
      under_review: '#007bff',
      accepted: '#28a745',
      rejected: '#dc3545',
      completed: '#6c757d',
      cancelled: '#6c757d',
    };
    return colors[status as keyof typeof colors] || '#6c757d';
  };

  const getStatusIcon = (status: string) => {
    const icons = {
      assigned: 'time-outline',
      applied: 'paper-plane-outline',
      under_review: 'eye-outline',
      accepted: 'checkmark-circle-outline',
      rejected: 'close-circle-outline',
      completed: 'flag-outline',
      cancelled: 'ban-outline',
    };
    return icons[status as keyof typeof icons] || 'help-circle-outline';
  };

  if (loading) {
    return (
      <View style={styles.loadingContainer}>
        <ActivityIndicator size="large" color="#007bff" />
        <Text style={styles.loadingText}>Cargando detalles...</Text>
      </View>
    );
  }

  if (!assignment) {
    return (
      <View style={styles.errorContainer}>
        <Ionicons name="alert-circle-outline" size={64} color="#dc3545" />
        <Text style={styles.errorText}>No se pudo cargar la asignación</Text>
        <TouchableOpacity style={styles.retryButton} onPress={loadAssignmentDetails}>
          <Text style={styles.retryButtonText}>Reintentar</Text>
        </TouchableOpacity>
      </View>
    );
  }

  return (
    <ScrollView style={styles.container} showsVerticalScrollIndicator={false}>
      {/* Program Header */}
      <View style={styles.headerContainer}>
        {assignment.program.image_url && (
          <Image
            source={{ uri: assignment.program.image_url }}
            style={styles.programImage}
            resizeMode="cover"
          />
        )}
        <View style={styles.headerOverlay}>
          <Text style={styles.programName}>{assignment.program.name}</Text>
          <View style={styles.locationContainer}>
            <Ionicons name="location" size={16} color="#fff" />
            <Text style={styles.locationText}>
              {assignment.program.location}, {assignment.program.country}
            </Text>
          </View>
        </View>
      </View>

      {/* Status Card */}
      <View style={styles.statusCard}>
        <View style={styles.statusHeader}>
          <View style={styles.statusInfo}>
            <Ionicons
              name={getStatusIcon(assignment.status)}
              size={24}
              color={getStatusColor(assignment.status)}
            />
            <View style={styles.statusTexts}>
              <Text style={styles.statusTitle}>Estado de Asignación</Text>
              <Text
                style={[
                  styles.statusValue,
                  { color: getStatusColor(assignment.status) },
                ]}
              >
                {assignment.status_name}
              </Text>
            </View>
          </View>
          {assignment.is_priority && (
            <View style={styles.priorityBadge}>
              <Ionicons name="star" size={16} color="#fff" />
              <Text style={styles.priorityText}>Prioridad</Text>
            </View>
          )}
        </View>

        {/* Progress Bar */}
        <View style={styles.progressContainer}>
          <Text style={styles.progressLabel}>
            Progreso: {assignment.progress_percentage}%
          </Text>
          <View style={styles.progressBar}>
            <View
              style={[
                styles.progressFill,
                {
                  width: `${assignment.progress_percentage}%`,
                  backgroundColor: getStatusColor(assignment.status),
                },
              ]}
            />
          </View>
        </View>
      </View>

      {/* Assignment Information */}
      <View style={styles.infoCard}>
        <Text style={styles.cardTitle}>Información de Asignación</Text>
        
        <View style={styles.infoRow}>
          <Ionicons name="calendar-outline" size={20} color="#666" />
          <View style={styles.infoContent}>
            <Text style={styles.infoLabel}>Fecha de Asignación</Text>
            <Text style={styles.infoValue}>
              {new Date(assignment.assigned_at).toLocaleDateString('es-ES', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
              })}
            </Text>
          </View>
        </View>

        {assignment.application_deadline && (
          <View style={styles.infoRow}>
            <Ionicons
              name={assignment.is_overdue ? "alert-circle" : "time-outline"}
              size={20}
              color={assignment.is_overdue ? "#dc3545" : "#666"}
            />
            <View style={styles.infoContent}>
              <Text style={styles.infoLabel}>Fecha Límite</Text>
              <Text
                style={[
                  styles.infoValue,
                  assignment.is_overdue && styles.overdueText,
                ]}
              >
                {new Date(assignment.application_deadline).toLocaleDateString('es-ES')}
                {assignment.days_until_deadline !== null && (
                  <Text style={styles.deadlineSubtext}>
                    {'\n'}
                    {assignmentService.getDeadlineText(assignment.days_until_deadline)}
                  </Text>
                )}
              </Text>
            </View>
          </View>
        )}

        <View style={styles.infoRow}>
          <Ionicons name="person-outline" size={20} color="#666" />
          <View style={styles.infoContent}>
            <Text style={styles.infoLabel}>Asignado por</Text>
            <Text style={styles.infoValue}>{assignment.assigned_by.name}</Text>
          </View>
        </View>

        {assignment.applied_at && (
          <View style={styles.infoRow}>
            <Ionicons name="checkmark-circle-outline" size={20} color="#28a745" />
            <View style={styles.infoContent}>
              <Text style={styles.infoLabel}>Fecha de Aplicación</Text>
              <Text style={[styles.infoValue, { color: '#28a745' }]}>
                {new Date(assignment.applied_at).toLocaleDateString('es-ES', {
                  year: 'numeric',
                  month: 'long',
                  day: 'numeric',
                  hour: '2-digit',
                  minute: '2-digit',
                })}
              </Text>
            </View>
          </View>
        )}
      </View>

      {/* Assignment Notes */}
      {assignment.assignment_notes && (
        <View style={styles.notesCard}>
          <Text style={styles.cardTitle}>Notas de Asignación</Text>
          <Text style={styles.notesText}>{assignment.assignment_notes}</Text>
        </View>
      )}

      {/* Admin Notes */}
      {assignment.admin_notes && (
        <View style={styles.notesCard}>
          <Text style={styles.cardTitle}>Notas Administrativas</Text>
          <Text style={styles.notesText}>{assignment.admin_notes}</Text>
        </View>
      )}

      {/* Program Information */}
      <View style={styles.infoCard}>
        <View style={styles.cardHeader}>
          <Text style={styles.cardTitle}>Información del Programa</Text>
          <TouchableOpacity onPress={handleViewProgram}>
            <Text style={styles.viewMoreText}>Ver más</Text>
          </TouchableOpacity>
        </View>

        <Text style={styles.programDescription}>
          {assignment.program.description}
        </Text>

        <View style={styles.programDetails}>
          {assignment.program.category && (
            <View style={styles.detailItem}>
              <Ionicons name="folder-outline" size={16} color="#666" />
              <Text style={styles.detailText}>{assignment.program.category}</Text>
            </View>
          )}

          {assignment.program.duration && (
            <View style={styles.detailItem}>
              <Ionicons name="time-outline" size={16} color="#666" />
              <Text style={styles.detailText}>{assignment.program.duration}</Text>
            </View>
          )}

          {assignment.program.credits && (
            <View style={styles.detailItem}>
              <Ionicons name="school-outline" size={16} color="#666" />
              <Text style={styles.detailText}>{assignment.program.credits} créditos</Text>
            </View>
          )}

          <View style={styles.detailItem}>
            <Ionicons name="people-outline" size={16} color="#666" />
            <Text style={styles.detailText}>
              {assignment.program.available_spots} espacios disponibles
            </Text>
          </View>
        </View>

        {assignment.program.start_date && (
          <View style={styles.datesContainer}>
            <View style={styles.dateItem}>
              <Text style={styles.dateLabel}>Inicio</Text>
              <Text style={styles.dateValue}>
                {new Date(assignment.program.start_date).toLocaleDateString('es-ES')}
              </Text>
            </View>
            {assignment.program.end_date && (
              <View style={styles.dateItem}>
                <Text style={styles.dateLabel}>Fin</Text>
                <Text style={styles.dateValue}>
                  {new Date(assignment.program.end_date).toLocaleDateString('es-ES')}
                </Text>
              </View>
            )}
          </View>
        )}
      </View>

      {/* Program Requirements */}
      {assignment.program.requisites.length > 0 && (
        <View style={styles.requirementsCard}>
          <Text style={styles.cardTitle}>Requisitos del Programa</Text>
          {assignment.program.requisites.map((requisite) => (
            <View key={requisite.id} style={styles.requirementItem}>
              <Ionicons
                name={requisite.is_required ? "checkmark-circle" : "information-circle"}
                size={20}
                color={requisite.is_required ? "#28a745" : "#007bff"}
              />
              <View style={styles.requirementContent}>
                <Text style={styles.requirementTitle}>{requisite.title}</Text>
                <Text style={styles.requirementDescription}>
                  {requisite.description}
                </Text>
                {requisite.is_required && (
                  <Text style={styles.requiredLabel}>Requerido</Text>
                )}
              </View>
            </View>
          ))}
        </View>
      )}

      {/* Application Status */}
      {assignment.application && (
        <View style={styles.applicationCard}>
          <Text style={styles.cardTitle}>Mi Aplicación</Text>
          <View style={styles.applicationStatus}>
            <Ionicons name="document-text-outline" size={24} color="#007bff" />
            <View style={styles.applicationInfo}>
              <Text style={styles.applicationStatusText}>
                Estado: {assignment.application.status}
              </Text>
              <Text style={styles.applicationDate}>
                Enviada: {new Date(assignment.application.submitted_at).toLocaleDateString('es-ES')}
              </Text>
              <Text style={styles.documentsCount}>
                Documentos: {assignment.application.documents_count}
              </Text>
            </View>
          </View>
        </View>
      )}

      {/* Actions */}
      <View style={styles.actionsContainer}>
        {assignment.can_apply && (
          <TouchableOpacity
            style={[styles.actionButton, styles.applyButton]}
            onPress={handleApply}
            disabled={applying}
          >
            {applying ? (
              <ActivityIndicator color="#fff" />
            ) : (
              <>
                <Ionicons name="paper-plane" size={20} color="#fff" />
                <Text style={styles.actionButtonText}>Aplicar al Programa</Text>
              </>
            )}
          </TouchableOpacity>
        )}

        {assignment.program.has_forms && assignment.program.active_form && (
          <TouchableOpacity
            style={[styles.actionButton, styles.formButton]}
            onPress={handleViewForm}
          >
            <Ionicons name="document-text" size={20} color="#fff" />
            <Text style={styles.actionButtonText}>Completar Formulario</Text>
          </TouchableOpacity>
        )}

        <TouchableOpacity
          style={[styles.actionButton, styles.programButton]}
          onPress={handleViewProgram}
        >
          <Ionicons name="information-circle" size={20} color="#fff" />
          <Text style={styles.actionButtonText}>Ver Programa Completo</Text>
        </TouchableOpacity>
      </View>

      <View style={styles.bottomSpacer} />
    </ScrollView>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f8f9fa',
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#f8f9fa',
  },
  loadingText: {
    marginTop: 16,
    fontSize: 16,
    color: '#666',
  },
  errorContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    paddingHorizontal: 32,
  },
  errorText: {
    fontSize: 18,
    color: '#dc3545',
    marginTop: 16,
    textAlign: 'center',
  },
  retryButton: {
    backgroundColor: '#007bff',
    paddingHorizontal: 24,
    paddingVertical: 12,
    borderRadius: 6,
    marginTop: 16,
  },
  retryButtonText: {
    color: '#fff',
    fontSize: 16,
    fontWeight: '600',
  },
  headerContainer: {
    position: 'relative',
    height: 200,
  },
  programImage: {
    width: '100%',
    height: '100%',
  },
  headerOverlay: {
    position: 'absolute',
    bottom: 0,
    left: 0,
    right: 0,
    backgroundColor: 'rgba(0, 0, 0, 0.6)',
    padding: 16,
  },
  programName: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#fff',
    marginBottom: 8,
  },
  locationContainer: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  locationText: {
    fontSize: 16,
    color: '#fff',
    marginLeft: 6,
  },
  statusCard: {
    backgroundColor: '#fff',
    margin: 16,
    padding: 16,
    borderRadius: 12,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 8,
    elevation: 3,
  },
  statusHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 16,
  },
  statusInfo: {
    flexDirection: 'row',
    alignItems: 'center',
    flex: 1,
  },
  statusTexts: {
    marginLeft: 12,
  },
  statusTitle: {
    fontSize: 14,
    color: '#666',
  },
  statusValue: {
    fontSize: 18,
    fontWeight: 'bold',
    marginTop: 2,
  },
  priorityBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#ffc107',
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 12,
  },
  priorityText: {
    color: '#fff',
    fontSize: 12,
    fontWeight: '600',
    marginLeft: 4,
  },
  progressContainer: {
    marginTop: 8,
  },
  progressLabel: {
    fontSize: 14,
    color: '#666',
    marginBottom: 8,
  },
  progressBar: {
    height: 8,
    backgroundColor: '#e9ecef',
    borderRadius: 4,
    overflow: 'hidden',
  },
  progressFill: {
    height: '100%',
    borderRadius: 4,
  },
  infoCard: {
    backgroundColor: '#fff',
    marginHorizontal: 16,
    marginBottom: 16,
    padding: 16,
    borderRadius: 12,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 8,
    elevation: 3,
  },
  cardTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 16,
  },
  cardHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 16,
  },
  viewMoreText: {
    color: '#007bff',
    fontSize: 14,
    fontWeight: '600',
  },
  infoRow: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    marginBottom: 16,
  },
  infoContent: {
    marginLeft: 12,
    flex: 1,
  },
  infoLabel: {
    fontSize: 14,
    color: '#666',
    marginBottom: 2,
  },
  infoValue: {
    fontSize: 16,
    color: '#333',
    fontWeight: '500',
  },
  overdueText: {
    color: '#dc3545',
  },
  deadlineSubtext: {
    fontSize: 14,
    color: '#dc3545',
    fontStyle: 'italic',
  },
  notesCard: {
    backgroundColor: '#fff',
    marginHorizontal: 16,
    marginBottom: 16,
    padding: 16,
    borderRadius: 12,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 8,
    elevation: 3,
  },
  notesText: {
    fontSize: 16,
    color: '#333',
    lineHeight: 24,
  },
  programDescription: {
    fontSize: 16,
    color: '#333',
    lineHeight: 24,
    marginBottom: 16,
  },
  programDetails: {
    marginBottom: 16,
  },
  detailItem: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 8,
  },
  detailText: {
    fontSize: 14,
    color: '#666',
    marginLeft: 8,
  },
  datesContainer: {
    flexDirection: 'row',
    justifyContent: 'space-around',
    marginTop: 16,
    paddingTop: 16,
    borderTopWidth: 1,
    borderTopColor: '#e9ecef',
  },
  dateItem: {
    alignItems: 'center',
  },
  dateLabel: {
    fontSize: 12,
    color: '#666',
    marginBottom: 4,
  },
  dateValue: {
    fontSize: 16,
    color: '#333',
    fontWeight: '600',
  },
  requirementsCard: {
    backgroundColor: '#fff',
    marginHorizontal: 16,
    marginBottom: 16,
    padding: 16,
    borderRadius: 12,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 8,
    elevation: 3,
  },
  requirementItem: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    marginBottom: 16,
  },
  requirementContent: {
    marginLeft: 12,
    flex: 1,
  },
  requirementTitle: {
    fontSize: 16,
    fontWeight: '600',
    color: '#333',
    marginBottom: 4,
  },
  requirementDescription: {
    fontSize: 14,
    color: '#666',
    lineHeight: 20,
  },
  requiredLabel: {
    fontSize: 12,
    color: '#28a745',
    fontWeight: '600',
    marginTop: 4,
  },
  applicationCard: {
    backgroundColor: '#fff',
    marginHorizontal: 16,
    marginBottom: 16,
    padding: 16,
    borderRadius: 12,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 8,
    elevation: 3,
  },
  applicationStatus: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  applicationInfo: {
    marginLeft: 12,
    flex: 1,
  },
  applicationStatusText: {
    fontSize: 16,
    fontWeight: '600',
    color: '#333',
    marginBottom: 4,
  },
  applicationDate: {
    fontSize: 14,
    color: '#666',
    marginBottom: 2,
  },
  documentsCount: {
    fontSize: 14,
    color: '#666',
  },
  actionsContainer: {
    paddingHorizontal: 16,
    paddingBottom: 16,
  },
  actionButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 12,
    paddingHorizontal: 24,
    borderRadius: 8,
    marginBottom: 12,
  },
  applyButton: {
    backgroundColor: '#28a745',
  },
  formButton: {
    backgroundColor: '#007bff',
  },
  programButton: {
    backgroundColor: '#17a2b8',
  },
  actionButtonText: {
    color: '#fff',
    fontSize: 16,
    fontWeight: '600',
    marginLeft: 8,
  },
  bottomSpacer: {
    height: 20,
  },
});

export default AssignmentDetailScreen; 