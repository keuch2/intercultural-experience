import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  ScrollView,
  StyleSheet,
  Alert,
  TouchableOpacity,
  ActivityIndicator,
} from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import DynamicField from './DynamicField';
import { FormData, FormField, FormSection } from '../types/forms';
import formService from '../services/api/formService';

interface DynamicFormProps {
  programId: number;
  formId: number;
  onSubmit: (data: FormData) => void;
  onCancel: () => void;
  initialData?: FormData;
  isLoading?: boolean;
}

const DynamicForm: React.FC<DynamicFormProps> = ({
  programId,
  formId,
  onSubmit,
  onCancel,
  initialData = {},
  isLoading = false,
}) => {
  const [formStructure, setFormStructure] = useState<{
    sections: FormSection[];
    fields: FormField[];
    settings: any;
  } | null>(null);
  const [formData, setFormData] = useState<FormData>(initialData);
  const [errors, setErrors] = useState<Record<string, string>>({});
  const [currentSection, setCurrentSection] = useState(0);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    loadFormStructure();
  }, [formId]);

  const loadFormStructure = async () => {
    try {
      setLoading(true);
      
      // Obtener la estructura del formulario desde la API
      const response = await formService.getFormStructure(formId);
      
      // Extraer los datos del formulario
      const form = response.data.form || response.form;
      const structure = response.data.structure || response.structure || {};
      
      // Construir la estructura esperada por el componente
      const formStructure = {
        sections: form.sections || [
          {
            id: 'section_1',
            name: 'general',
            title: 'Información General',
            description: 'Datos del formulario',
          }
        ],
        fields: form.fields || [],
        settings: {
          requires_signature: form.requires_signature || false,
          requires_parent_signature: form.requires_parent_signature || false,
        }
      };
      
      setFormStructure(formStructure);
    } catch (error) {
      console.error('Error loading form structure:', error);
      Alert.alert('Error', 'No se pudo cargar el formulario');
    } finally {
      setLoading(false);
    }
  };

  const handleFieldChange = (fieldKey: string, value: any) => {
    setFormData(prev => ({
      ...prev,
      [fieldKey]: value,
    }));

    // Limpiar error si existe
    if (errors[fieldKey]) {
      setErrors(prev => {
        const newErrors = { ...prev };
        delete newErrors[fieldKey];
        return newErrors;
      });
    }
  };

  const validateSection = (sectionIndex: number): boolean => {
    if (!formStructure) return false;

    const section = formStructure.sections[sectionIndex];
    const sectionFields = formStructure.fields.filter(
      field => field.section_name === section.name
    );

    const newErrors: Record<string, string> = {};

    sectionFields.forEach(field => {
      if (field.is_required) {
        const value = formData[field.field_key];
        if (!value || (typeof value === 'string' && value.trim() === '')) {
          newErrors[field.field_key] = `${field.field_label} es requerido`;
        }
      }

      // Validaciones específicas por tipo
      if (field.field_type === 'email' && formData[field.field_key]) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(formData[field.field_key])) {
          newErrors[field.field_key] = 'Formato de email inválido';
        }
      }
    });

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleNextSection = () => {
    if (validateSection(currentSection)) {
      if (currentSection < (formStructure?.sections.length || 0) - 1) {
        setCurrentSection(currentSection + 1);
      } else {
        handleSubmit();
      }
    }
  };

  const handlePrevSection = () => {
    if (currentSection > 0) {
      setCurrentSection(currentSection - 1);
    }
  };

  const handleSubmit = () => {
    // Validar todo el formulario
    let isValid = true;
    for (let i = 0; i < (formStructure?.sections.length || 0); i++) {
      if (!validateSection(i)) {
        isValid = false;
        setCurrentSection(i);
        break;
      }
    }

    if (isValid) {
      onSubmit(formData);
    }
  };

  if (loading) {
    return (
      <SafeAreaView style={styles.container}>
        <View style={styles.loadingContainer}>
          <ActivityIndicator size="large" color="#007bff" />
          <Text style={styles.loadingText}>Cargando formulario...</Text>
        </View>
      </SafeAreaView>
    );
  }

  if (!formStructure) {
    return (
      <SafeAreaView style={styles.container}>
        <View style={styles.errorContainer}>
          <Text style={styles.errorText}>Error al cargar el formulario</Text>
          <TouchableOpacity style={styles.retryButton} onPress={loadFormStructure}>
            <Text style={styles.retryButtonText}>Reintentar</Text>
          </TouchableOpacity>
        </View>
      </SafeAreaView>
    );
  }

  const currentSectionData = formStructure.sections[currentSection];
  const currentSectionFields = formStructure.fields
    .filter(field => field.section_name === currentSectionData.name)
    .sort((a, b) => a.sort_order - b.sort_order);

  return (
    <SafeAreaView style={styles.container}>
      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity onPress={onCancel} style={styles.cancelButton}>
          <Text style={styles.cancelButtonText}>Cancelar</Text>
        </TouchableOpacity>
        <View style={styles.progressContainer}>
          <Text style={styles.progressText}>
            {currentSection + 1} de {formStructure.sections.length}
          </Text>
          <View style={styles.progressBar}>
            <View
              style={[
                styles.progressFill,
                {
                  width: `${((currentSection + 1) / formStructure.sections.length) * 100}%`,
                },
              ]}
            />
          </View>
        </View>
      </View>

      {/* Section Content */}
      <ScrollView style={styles.content} showsVerticalScrollIndicator={false}>
        <View style={styles.sectionHeader}>
          <Text style={styles.sectionTitle}>{currentSectionData.title}</Text>
          {currentSectionData.description && (
            <Text style={styles.sectionDescription}>
              {currentSectionData.description}
            </Text>
          )}
        </View>

        <View style={styles.fieldsContainer}>
          {currentSectionFields.map(field => (
            <DynamicField
              key={field.id}
              field={field}
              value={formData[field.field_key]}
              error={errors[field.field_key]}
              onChange={(value) => handleFieldChange(field.field_key, value)}
            />
          ))}
        </View>
      </ScrollView>

      {/* Navigation */}
      <View style={styles.navigation}>
        {currentSection > 0 && (
          <TouchableOpacity
            style={[styles.navButton, styles.prevButton]}
            onPress={handlePrevSection}
          >
            <Text style={styles.prevButtonText}>Anterior</Text>
          </TouchableOpacity>
        )}
        
        <TouchableOpacity
          style={[styles.navButton, styles.nextButton]}
          onPress={handleNextSection}
          disabled={isLoading}
        >
          {isLoading ? (
            <ActivityIndicator size="small" color="#fff" />
          ) : (
            <Text style={styles.nextButtonText}>
              {currentSection === formStructure.sections.length - 1 ? 'Enviar' : 'Siguiente'}
            </Text>
          )}
        </TouchableOpacity>
      </View>
    </SafeAreaView>
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
  },
  loadingText: {
    marginTop: 16,
    fontSize: 16,
    color: '#6c757d',
  },
  errorContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: 20,
  },
  errorText: {
    fontSize: 16,
    color: '#dc3545',
    textAlign: 'center',
    marginBottom: 20,
  },
  retryButton: {
    backgroundColor: '#007bff',
    paddingHorizontal: 20,
    paddingVertical: 10,
    borderRadius: 8,
  },
  retryButtonText: {
    color: '#fff',
    fontSize: 16,
    fontWeight: '600',
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    padding: 16,
    backgroundColor: '#fff',
    borderBottomWidth: 1,
    borderBottomColor: '#e9ecef',
  },
  cancelButton: {
    padding: 8,
  },
  cancelButtonText: {
    color: '#6c757d',
    fontSize: 16,
  },
  progressContainer: {
    flex: 1,
    alignItems: 'center',
    marginLeft: 16,
  },
  progressText: {
    fontSize: 14,
    color: '#6c757d',
    marginBottom: 4,
  },
  progressBar: {
    width: '100%',
    height: 4,
    backgroundColor: '#e9ecef',
    borderRadius: 2,
  },
  progressFill: {
    height: '100%',
    backgroundColor: '#007bff',
    borderRadius: 2,
  },
  content: {
    flex: 1,
  },
  sectionHeader: {
    padding: 20,
    backgroundColor: '#fff',
    marginBottom: 16,
  },
  sectionTitle: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#212529',
    marginBottom: 8,
  },
  sectionDescription: {
    fontSize: 16,
    color: '#6c757d',
    lineHeight: 24,
  },
  fieldsContainer: {
    padding: 16,
  },
  navigation: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    padding: 16,
    backgroundColor: '#fff',
    borderTopWidth: 1,
    borderTopColor: '#e9ecef',
  },
  navButton: {
    paddingHorizontal: 24,
    paddingVertical: 12,
    borderRadius: 8,
    minWidth: 100,
    alignItems: 'center',
  },
  prevButton: {
    backgroundColor: '#6c757d',
  },
  prevButtonText: {
    color: '#fff',
    fontSize: 16,
    fontWeight: '600',
  },
  nextButton: {
    backgroundColor: '#007bff',
  },
  nextButtonText: {
    color: '#fff',
    fontSize: 16,
    fontWeight: '600',
  },
});

export default DynamicForm; 