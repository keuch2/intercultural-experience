// User types
export interface User {
  id: number;
  name: string;
  email: string;
  phone?: string;
  country?: string;
  address?: string;
  role: 'user' | 'admin';
  created_at: string;
  updated_at: string;
}

// Program types
export interface Program {
  id: number;
  name: string;
  description: string;
  duration: string;
  location: string;
  credits?: number;
  cost: number;
  application_deadline: string;
  start_date: string;
  end_date: string;
  status: 'open' | 'closed' | 'draft';
  created_at: string;
  updated_at: string;
}

// Application types
export interface Application {
  id: number;
  user_id: number;
  program_id: number;
  status: 'pending' | 'approved' | 'rejected';
  submitted_at: string;
  reviewed_at?: string;
  notes?: string;
  program: Program;
  user?: User;
  created_at: string;
  updated_at: string;
}

// Form types
export interface FormField {
  id: number;
  field_key: string;
  field_label: string;
  field_type: 'text' | 'email' | 'tel' | 'number' | 'date' | 'textarea' | 'select' | 'radio' | 'checkbox' | 'file' | 'url' | 'boolean';
  description?: string;
  placeholder?: string;
  options?: string[];
  validation_rules?: Record<string, any>;
  is_required: boolean;
  is_visible: boolean;
  sort_order: number;
  section_name: string;
}

export interface FormSection {
  id: string;
  name: string;
  title: string;
  description?: string;
  fields: FormField[];
}

export interface ProgramForm {
  id: number;
  program_id: number;
  name: string;
  version: string;
  description?: string;
  min_age?: number;
  max_age?: number;
  requires_signature: boolean;
  requires_parent_signature: boolean;
  terms_and_conditions?: string;
  status: 'draft' | 'active' | 'archived';
  sections: FormSection[];
  fields: FormField[];
  created_at: string;
  updated_at: string;
}

// Assignment types
export interface ProgramAssignment {
  id: number;
  user_id: number;
  program_id: number;
  assigned_by: number;
  assigned_at: string;
  status: 'assigned' | 'accepted' | 'declined' | 'completed';
  completion_notes?: string;
  program: Program;
  user?: User;
  assigned_by_user?: User;
  created_at: string;
  updated_at: string;
}

// Points types
export interface Point {
  id: number;
  user_id: number;
  change: number;
  reason: string;
  related_id?: number;
  created_at: string;
  updated_at: string;
}

export interface PointsBalance {
  total: number;
  earned: number;
  spent: number;
  pending: number;
}

// Reward types
export interface Reward {
  id: number;
  name: string;
  description: string;
  cost: number;
  image?: string;
  stock?: number;
  category: string;
  status: 'active' | 'inactive';
  created_at: string;
  updated_at: string;
}

// Redemption types
export interface Redemption {
  id: number;
  user_id: number;
  reward_id: number;
  points_cost: number;
  status: 'pending' | 'approved' | 'rejected' | 'delivered';
  requested_at: string;
  resolved_at?: string;
  delivered_at?: string;
  admin_notes?: string;
  tracking_number?: string;
  carrier?: string;
  delivery_notes?: string;
  reward: Reward;
  user?: User;
  created_at: string;
  updated_at: string;
}

// Notification types
export interface Notification {
  id: number;
  user_id: number;
  title: string;
  message: string;
  type: 'info' | 'success' | 'warning' | 'error';
  is_read: boolean;
  created_at: string;
  updated_at: string;
}

// Support Ticket types
export interface SupportTicket {
  id: number;
  user_id: number;
  subject: string;
  message: string;
  status: 'open' | 'in_progress' | 'resolved' | 'closed';
  priority: 'low' | 'medium' | 'high' | 'urgent';
  assigned_to?: number;
  response?: string;
  created_at: string;
  updated_at: string;
}

// API Response types
export interface ApiResponse<T> {
  data: T;
  message?: string;
  success?: boolean;
}

export interface PaginatedResponse<T> {
  data: T[];
  current_page: number;
  from: number;
  last_page: number;
  per_page: number;
  to: number;
  total: number;
}

// Auth types
export interface AuthResponse {
  user: User;
  token: string;
}

export interface LoginCredentials {
  email: string;
  password: string;
}

export interface RegisterData {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
  phone?: string;
  country?: string;
}

// Error types
export interface ApiError {
  message: string;
  errors?: Record<string, string[]>;
  status?: number;
} 