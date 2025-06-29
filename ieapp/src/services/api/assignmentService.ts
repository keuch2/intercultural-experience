import { apiClient } from './apiClient';

export interface Assignment {
  id: number;
  status: string;
  status_name: string;
  can_apply: boolean;
  is_priority: boolean;
  is_overdue: boolean;
  days_until_deadline: number | null;
  progress_percentage: number;
  assigned_at: string;
  applied_at: string | null;
  application_deadline: string | null;
  assignment_notes: string | null;
  admin_notes: string | null;
  program: {
    id: number;
    name: string;
    description: string;
    country: string;
    category: string;
    location: string;
    start_date: string | null;
    end_date: string | null;
    duration: string | null;
    image_url: string | null;
    cost_display: string;
    available_spots: number;
  };
  can_view_details: boolean;
  can_apply_now: boolean;
}

export interface AssignmentDetails {
  id: number;
  status: string;
  status_name: string;
  can_apply: boolean;
  is_priority: boolean;
  is_overdue: boolean;
  days_until_deadline: number | null;
  progress_percentage: number;
  assigned_at: string;
  applied_at: string | null;
  application_deadline: string | null;
  assignment_notes: string | null;
  admin_notes: string | null;
  assigned_by: {
    name: string;
    email: string;
  };
  program: {
    id: number;
    name: string;
    description: string;
    country: string;
    category: string;
    location: string;
    start_date: string | null;
    end_date: string | null;
    application_deadline: string | null;
    duration: string | null;
    credits: number | null;
    capacity: number;
    available_spots: number;
    image_url: string | null;
    cost_display: string;
    requisites: Array<{
      id: number;
      title: string;
      description: string;
      is_required: boolean;
      order: number;
    }>;
  };
  application: {
    id: number;
    status: string;
    submitted_at: string;
    documents_count: number;
  } | null;
}

export interface AvailableProgram {
  assignment_id: number;
  program: {
    id: number;
    name: string;
    description: string;
    country: string;
    category: string;
    image_url: string | null;
    cost_display: string;
  };
  deadline: string | null;
  is_priority: boolean;
  days_until_deadline: number | null;
}

export interface AssignmentStats {
  total_assignments: number;
  assigned: number;
  applied: number;
  under_review: number;
  accepted: number;
  rejected: number;
  completed: number;
  active_assignments: number;
  pending_applications: number;
  overdue_applications: number;
}

export interface ProgramDetails {
  id: number;
  name: string;
  description: string;
  country: string;
  category: string;
  location: string;
  start_date: string | null;
  end_date: string | null;
  application_deadline: string | null;
  duration: string | null;
  credits: number | null;
  capacity: number;
  available_spots: number;
  image_url: string | null;
  cost_display: string;
  requisites: Array<{
    id: number;
    title: string;
    description: string;
    is_required: boolean;
    order: number;
  }>;
  has_forms: boolean;
  active_form: {
    id: number;
    title: string;
    description: string;
  } | null;
}

class AssignmentService {
  /**
   * Get all assignments for the authenticated user
   */
  async getAssignments(filters?: {
    status?: string;
    active_only?: boolean;
  }): Promise<{
    success: boolean;
    assignments: Assignment[];
    total: number;
    active_assignments: number;
    message?: string;
  }> {
    try {
      const params = new URLSearchParams();
      
      if (filters?.status) {
        params.append('status', filters.status);
      }
      
      if (filters?.active_only) {
        params.append('active_only', 'true');
      }

      const response = await apiClient.get(`/assignments?${params.toString()}`);
      return response.data;
    } catch (error: any) {
      throw new Error(error.response?.data?.message || 'Error al obtener asignaciones');
    }
  }

  /**
   * Get specific assignment details
   */
  async getAssignmentDetails(assignmentId: number): Promise<{
    success: boolean;
    assignment: AssignmentDetails;
    message?: string;
  }> {
    try {
      const response = await apiClient.get(`/assignments/${assignmentId}`);
      return response.data;
    } catch (error: any) {
      throw new Error(error.response?.data?.message || 'Error al obtener detalles de la asignación');
    }
  }

  /**
   * Apply to an assigned program
   */
  async applyToProgram(assignmentId: number, applicationData?: any): Promise<{
    success: boolean;
    message: string;
    application: {
      id: number;
      status: string;
      submitted_at: string;
    };
    assignment: {
      id: number;
      status: string;
      status_name: string;
      applied_at: string;
    };
  }> {
    try {
      const response = await apiClient.post(`/assignments/${assignmentId}/apply`, {
        application_data: applicationData || {}
      });
      return response.data;
    } catch (error: any) {
      throw new Error(error.response?.data?.message || 'Error al enviar aplicación');
    }
  }

  /**
   * Get program details for an assignment
   */
  async getProgramDetails(assignmentId: number): Promise<{
    success: boolean;
    program: ProgramDetails;
    assignment_status: string;
    can_apply: boolean;
    message?: string;
  }> {
    try {
      const response = await apiClient.get(`/assignments/${assignmentId}/program`);
      return response.data;
    } catch (error: any) {
      throw new Error(error.response?.data?.message || 'Error al obtener detalles del programa');
    }
  }

  /**
   * Get available programs for application
   */
  async getAvailablePrograms(): Promise<{
    success: boolean;
    programs: AvailableProgram[];
    total: number;
    message?: string;
  }> {
    try {
      const response = await apiClient.get('/available-programs');
      return response.data;
    } catch (error: any) {
      throw new Error(error.response?.data?.message || 'Error al obtener programas disponibles');
    }
  }

  /**
   * Get assignment statistics for user
   */
  async getMyStats(): Promise<{
    success: boolean;
    stats: AssignmentStats;
    message?: string;
  }> {
    try {
      const response = await apiClient.get('/my-stats');
      return response.data;
    } catch (error: any) {
      throw new Error(error.response?.data?.message || 'Error al obtener estadísticas');
    }
  }

  /**
   * Get assignments by status
   */
  async getAssignmentsByStatus(status: string): Promise<{
    success: boolean;
    assignments: Assignment[];
    total: number;
    message?: string;
  }> {
    return this.getAssignments({ status });
  }

  /**
   * Get active assignments only
   */
  async getActiveAssignments(): Promise<{
    success: boolean;
    assignments: Assignment[];
    total: number;
    message?: string;
  }> {
    return this.getAssignments({ active_only: true });
  }

  /**
   * Check if user can apply to a specific program
   */
  async canApplyToProgram(assignmentId: number): Promise<boolean> {
    try {
      const result = await this.getAssignmentDetails(assignmentId);
      return result.assignment.can_apply && result.assignment.status === 'assigned';
    } catch (error) {
      return false;
    }
  }

  /**
   * Get assignments that are overdue
   */
  async getOverdueAssignments(): Promise<Assignment[]> {
    try {
      const result = await this.getAssignments();
      return result.assignments.filter(assignment => assignment.is_overdue);
    } catch (error) {
      return [];
    }
  }

  /**
   * Get priority assignments
   */
  async getPriorityAssignments(): Promise<Assignment[]> {
    try {
      const result = await this.getAssignments();
      return result.assignments.filter(assignment => assignment.is_priority);
    } catch (error) {
      return [];
    }
  }

  /**
   * Get assignments that can be applied to
   */
  async getApplicableAssignments(): Promise<Assignment[]> {
    try {
      const result = await this.getAssignments({ status: 'assigned' });
      return result.assignments.filter(assignment => assignment.can_apply_now);
    } catch (error) {
      return [];
    }
  }

  /**
   * Format assignment status for display
   */
  formatStatus(status: string): { text: string; color: string } {
    const statusConfig = {
      assigned: { text: 'Asignado', color: '#ffc107' },
      applied: { text: 'Aplicado', color: '#17a2b8' },
      under_review: { text: 'En Revisión', color: '#007bff' },
      accepted: { text: 'Aceptado', color: '#28a745' },
      rejected: { text: 'Rechazado', color: '#dc3545' },
      completed: { text: 'Completado', color: '#6c757d' },
      cancelled: { text: 'Cancelado', color: '#6c757d' },
    };

    return statusConfig[status as keyof typeof statusConfig] || 
           { text: status, color: '#6c757d' };
  }

  /**
   * Get days until deadline text
   */
  getDeadlineText(daysUntilDeadline: number | null): string {
    if (daysUntilDeadline === null) {
      return 'Sin fecha límite';
    }

    if (daysUntilDeadline < 0) {
      return `Vencida hace ${Math.abs(daysUntilDeadline)} día(s)`;
    }

    if (daysUntilDeadline === 0) {
      return 'Vence hoy';
    }

    if (daysUntilDeadline === 1) {
      return 'Vence mañana';
    }

    return `${daysUntilDeadline} día(s) restantes`;
  }

  /**
   * Get progress color based on percentage
   */
  getProgressColor(percentage: number): string {
    if (percentage >= 80) return '#28a745'; // green
    if (percentage >= 60) return '#17a2b8'; // blue
    if (percentage >= 40) return '#ffc107'; // yellow
    if (percentage >= 20) return '#fd7e14'; // orange
    return '#dc3545'; // red
  }
}

export const assignmentService = new AssignmentService(); 