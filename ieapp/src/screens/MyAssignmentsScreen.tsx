import React, { useState, useEffect, useCallback } from 'react';
import {
  View,
  Text,
  StyleSheet,
  FlatList,
  TouchableOpacity,
  RefreshControl,
  Alert,
  Image,
  Dimensions,
  ActivityIndicator,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useFocusEffect } from '@react-navigation/native';
import { assignmentService, Assignment } from '../services/api/assignmentService';

const { width } = Dimensions.get('window');

interface Props {
  navigation: any;
}

const MyAssignmentsScreen: React.FC<Props> = ({ navigation }) => {
  const [assignments, setAssignments] = useState<Assignment[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [stats, setStats] = useState<any>(null);
  const [filter, setFilter] = useState<'all' | 'assigned' | 'applied' | 'accepted'>('all');

  useFocusEffect(
    useCallback(() => {
      loadAssignments();
      loadStats();
    }, [filter])
  );

  const loadAssignments = async (isRefresh = false) => {
    try {
      if (!isRefresh) setLoading(true);
      
      const filters = filter !== 'all' ? { status: filter } : {};
      const response = await assignmentService.getAssignments(filters);
      
      if (response.success) {
        setAssignments(response.assignments);
      }
    } catch (error: any) {
      Alert.alert('Error', error.message);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  const loadStats = async () => {
    try {
      const response = await assignmentService.getMyStats();
      if (response.success) {
        setStats(response.stats);
      }
    } catch (error) {
      // Stats are optional, don't show error
    }
  };

  const onRefresh = () => {
    setRefreshing(true);
    loadAssignments(true);
    loadStats();
  };

  const handleAssignmentPress = (assignment: Assignment) => {
    navigation.navigate('AssignmentDetail', { 
      assignmentId: assignment.id,
      assignment: assignment 
    });
  };

  const handleApplyPress = async (assignment: Assignment) => {
    if (!assignment.can_apply_now) {
      Alert.alert(
        'No disponible',
        'No puedes aplicar a este programa en este momento.'
      );
      return;
    }

    Alert.alert(
      'Aplicar al Programa',
      `¿Estás seguro que quieres aplicar al programa "${assignment.program.name}"?`,
      [
        { text: 'Cancelar', style: 'cancel' },
        {
          text: 'Aplicar',
          onPress: async () => {
            try {
              setLoading(true);
              const response = await assignmentService.applyToProgram(assignment.id);
              
              if (response.success) {
                Alert.alert('¡Éxito!', response.message);
                loadAssignments();
              }
            } catch (error: any) {
              Alert.alert('Error', error.message);
            } finally {
              setLoading(false);
            }
          },
        },
      ]
    );
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

  const renderFilterButton = (filterValue: typeof filter, label: string, count?: number) => (
    <TouchableOpacity
      style={[
        styles.filterButton,
        filter === filterValue && styles.filterButtonActive,
      ]}
      onPress={() => setFilter(filterValue)}
    >
      <Text
        style={[
          styles.filterButtonText,
          filter === filterValue && styles.filterButtonTextActive,
        ]}
      >
        {label}
        {count !== undefined && ` (${count})`}
      </Text>
    </TouchableOpacity>
  );

  const renderAssignmentCard = ({ item: assignment }: { item: Assignment }) => (
    <TouchableOpacity
      style={styles.assignmentCard}
      onPress={() => handleAssignmentPress(assignment)}
    >
      <View style={styles.cardHeader}>
        <View style={styles.programInfo}>
          <Text style={styles.programName}>{assignment.program.name}</Text>
          <Text style={styles.programCountry}>
            <Ionicons name="location-outline" size={14} color="#666" />
            {' '}{assignment.program.country}
          </Text>
        </View>
        <View style={styles.statusContainer}>
          <View
            style={[
              styles.statusBadge,
              { backgroundColor: getStatusColor(assignment.status) },
            ]}
          >
            <Text style={styles.statusText}>{assignment.status_name}</Text>
          </View>
          {assignment.is_priority && (
            <Ionicons name="star" size={16} color="#ffc107" style={styles.priorityIcon} />
          )}
        </View>
      </View>

      {assignment.program.image_url && (
        <Image
          source={{ uri: assignment.program.image_url }}
          style={styles.programImage}
          resizeMode="cover"
        />
      )}

      <View style={styles.cardBody}>
        <Text style={styles.programDescription} numberOfLines={2}>
          {assignment.program.description}
        </Text>

        {/* Progress Bar */}
        <View style={styles.progressContainer}>
          <Text style={styles.progressLabel}>Progreso: {assignment.progress_percentage}%</Text>
          <View style={styles.progressBar}>
            <View
              style={[
                styles.progressFill,
                {
                  width: `${assignment.progress_percentage}%`,
                  backgroundColor: assignmentService.getProgressColor(assignment.progress_percentage),
                },
              ]}
            />
          </View>
        </View>

        {/* Deadline */}
        {assignment.application_deadline && (
          <View style={styles.deadlineContainer}>
            <Ionicons
              name={assignment.is_overdue ? "time" : "calendar-outline"}
              size={16}
              color={assignment.is_overdue ? "#dc3545" : "#666"}
            />
            <Text
              style={[
                styles.deadlineText,
                assignment.is_overdue && styles.overdueText,
              ]}
            >
              {assignmentService.getDeadlineText(assignment.days_until_deadline)}
            </Text>
          </View>
        )}

        {/* Assignment Notes */}
        {assignment.assignment_notes && (
          <View style={styles.notesContainer}>
            <Ionicons name="information-circle-outline" size={16} color="#007bff" />
            <Text style={styles.notesText} numberOfLines={2}>
              {assignment.assignment_notes}
            </Text>
          </View>
        )}
      </View>

      <View style={styles.cardFooter}>
        <Text style={styles.assignedDate}>
          Asignado: {new Date(assignment.assigned_at).toLocaleDateString()}
        </Text>
        
        {assignment.can_apply_now && (
          <TouchableOpacity
            style={styles.applyButton}
            onPress={() => handleApplyPress(assignment)}
          >
            <Text style={styles.applyButtonText}>Aplicar</Text>
          </TouchableOpacity>
        )}
        
        {assignment.status === 'applied' && assignment.applied_at && (
          <Text style={styles.appliedDate}>
            Aplicado: {new Date(assignment.applied_at).toLocaleDateString()}
          </Text>
        )}
      </View>
    </TouchableOpacity>
  );

  const renderStatsCard = () => {
    if (!stats) return null;

    return (
      <View style={styles.statsContainer}>
        <Text style={styles.statsTitle}>Mis Estadísticas</Text>
        <View style={styles.statsGrid}>
          <View style={styles.statItem}>
            <Text style={styles.statNumber}>{stats.total_assignments}</Text>
            <Text style={styles.statLabel}>Total</Text>
          </View>
          <View style={styles.statItem}>
            <Text style={styles.statNumber}>{stats.pending_applications}</Text>
            <Text style={styles.statLabel}>Pendientes</Text>
          </View>
          <View style={styles.statItem}>
            <Text style={styles.statNumber}>{stats.accepted}</Text>
            <Text style={styles.statLabel}>Aceptados</Text>
          </View>
          <View style={styles.statItem}>
            <Text style={styles.statNumber}>{stats.completed}</Text>
            <Text style={styles.statLabel}>Completados</Text>
          </View>
        </View>
      </View>
    );
  };

  const renderEmptyState = () => (
    <View style={styles.emptyContainer}>
      <Ionicons name="clipboard-outline" size={64} color="#ccc" />
      <Text style={styles.emptyTitle}>Sin asignaciones</Text>
      <Text style={styles.emptySubtitle}>
        {filter === 'all'
          ? 'No tienes asignaciones de programas aún.'
          : `No tienes asignaciones con estado "${filter}".`}
      </Text>
      {filter !== 'all' && (
        <TouchableOpacity
          style={styles.showAllButton}
          onPress={() => setFilter('all')}
        >
          <Text style={styles.showAllButtonText}>Ver todas</Text>
        </TouchableOpacity>
      )}
    </View>
  );

  if (loading && !refreshing) {
    return (
      <View style={styles.loadingContainer}>
        <ActivityIndicator size="large" color="#007bff" />
        <Text style={styles.loadingText}>Cargando asignaciones...</Text>
      </View>
    );
  }

  return (
    <View style={styles.container}>
      {/* Stats Card */}
      {renderStatsCard()}

      {/* Filter Buttons */}
      <View style={styles.filterContainer}>
        {renderFilterButton('all', 'Todas', stats?.total_assignments)}
        {renderFilterButton('assigned', 'Asignadas', stats?.assigned)}
        {renderFilterButton('applied', 'Aplicadas', stats?.applied)}
        {renderFilterButton('accepted', 'Aceptadas', stats?.accepted)}
      </View>

      {/* Assignments List */}
      <FlatList
        data={assignments}
        renderItem={renderAssignmentCard}
        keyExtractor={(item) => item.id.toString()}
        refreshControl={
          <RefreshControl refreshing={refreshing} onRefresh={onRefresh} />
        }
        contentContainerStyle={assignments.length === 0 ? styles.emptyList : styles.list}
        ListEmptyComponent={renderEmptyState}
        showsVerticalScrollIndicator={false}
      />
    </View>
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
  statsContainer: {
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
  statsTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 12,
  },
  statsGrid: {
    flexDirection: 'row',
    justifyContent: 'space-around',
  },
  statItem: {
    alignItems: 'center',
  },
  statNumber: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#007bff',
  },
  statLabel: {
    fontSize: 12,
    color: '#666',
    marginTop: 4,
  },
  filterContainer: {
    flexDirection: 'row',
    paddingHorizontal: 16,
    marginBottom: 8,
  },
  filterButton: {
    paddingHorizontal: 16,
    paddingVertical: 8,
    marginRight: 8,
    borderRadius: 20,
    backgroundColor: '#e9ecef',
  },
  filterButtonActive: {
    backgroundColor: '#007bff',
  },
  filterButtonText: {
    fontSize: 14,
    color: '#666',
    fontWeight: '500',
  },
  filterButtonTextActive: {
    color: '#fff',
  },
  list: {
    paddingHorizontal: 16,
    paddingBottom: 16,
  },
  emptyList: {
    flexGrow: 1,
  },
  assignmentCard: {
    backgroundColor: '#fff',
    borderRadius: 12,
    marginBottom: 16,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 8,
    elevation: 3,
  },
  cardHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
    padding: 16,
    paddingBottom: 8,
  },
  programInfo: {
    flex: 1,
  },
  programName: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 4,
  },
  programCountry: {
    fontSize: 14,
    color: '#666',
  },
  statusContainer: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  statusBadge: {
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 12,
  },
  statusText: {
    fontSize: 12,
    color: '#fff',
    fontWeight: '600',
  },
  priorityIcon: {
    marginLeft: 8,
  },
  programImage: {
    width: '100%',
    height: 120,
    marginHorizontal: 16,
    marginBottom: 8,
    borderRadius: 8,
    width: width - 64,
  },
  cardBody: {
    paddingHorizontal: 16,
  },
  programDescription: {
    fontSize: 14,
    color: '#666',
    lineHeight: 20,
    marginBottom: 12,
  },
  progressContainer: {
    marginBottom: 12,
  },
  progressLabel: {
    fontSize: 12,
    color: '#666',
    marginBottom: 4,
  },
  progressBar: {
    height: 6,
    backgroundColor: '#e9ecef',
    borderRadius: 3,
    overflow: 'hidden',
  },
  progressFill: {
    height: '100%',
    borderRadius: 3,
  },
  deadlineContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 8,
  },
  deadlineText: {
    fontSize: 14,
    color: '#666',
    marginLeft: 6,
  },
  overdueText: {
    color: '#dc3545',
    fontWeight: '600',
  },
  notesContainer: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    marginBottom: 8,
  },
  notesText: {
    fontSize: 14,
    color: '#007bff',
    marginLeft: 6,
    flex: 1,
  },
  cardFooter: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: 16,
    borderTopWidth: 1,
    borderTopColor: '#e9ecef',
  },
  assignedDate: {
    fontSize: 12,
    color: '#666',
  },
  appliedDate: {
    fontSize: 12,
    color: '#28a745',
    fontWeight: '500',
  },
  applyButton: {
    backgroundColor: '#28a745',
    paddingHorizontal: 16,
    paddingVertical: 8,
    borderRadius: 6,
  },
  applyButtonText: {
    color: '#fff',
    fontSize: 14,
    fontWeight: '600',
  },
  emptyContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    paddingHorizontal: 32,
  },
  emptyTitle: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#666',
    marginTop: 16,
    marginBottom: 8,
  },
  emptySubtitle: {
    fontSize: 16,
    color: '#999',
    textAlign: 'center',
    lineHeight: 24,
  },
  showAllButton: {
    backgroundColor: '#007bff',
    paddingHorizontal: 24,
    paddingVertical: 12,
    borderRadius: 6,
    marginTop: 16,
  },
  showAllButtonText: {
    color: '#fff',
    fontSize: 16,
    fontWeight: '600',
  },
});

export default MyAssignmentsScreen; 