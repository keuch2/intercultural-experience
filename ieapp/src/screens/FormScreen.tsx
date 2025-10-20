import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  Alert,
  ActivityIndicator,
  TouchableOpacity,
} from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useRoute, useNavigation, RouteProp } from '@react-navigation/native';
import { NativeStackNavigationProp } from '@react-navigation/native-stack';
import DynamicForm from '../components/DynamicForm';
import formService from '../services/api/formService';
import { FormData, ProgramForm } from '../types/forms';

type RootStackParamList = {
  Form: {
    programId: number;
    formId?: number;
    submissionId?: number;
  };
};

type FormScreenRouteProp = RouteProp<RootStackParamList, 'Form'>;
type FormScreenNavigationProp = NativeStackNavigationProp<RootStackParamList, 'Form'>;

const FormScreen: React.FC = () => {
  const route = useRoute<FormScreenRouteProp>();
  const navigation = useNavigation<FormScreenNavigationProp>();
  const { programId, formId, submissionId } = route.params;

  const [activeForm, setActiveForm] = useState<ProgramForm | null>(null);
  const [initialData, setInitialData] = useState<FormData>({});
  const [loading, setLoading] = useState(true);
  const [submitting, setSubmitting] = useState(false);

  useEffect(() => {
    loadForm();
  }, [programId, formId, submissionId]);

  const loadForm = async () => {
    try {
      setLoading(true);

      let form: ProgramForm | null = null;

      if (formId) {
        // Cargar formulario específico
        const response = await formService.getFormStructure(formId);
        form = response.form;
      } else {
        // Cargar formulario activo del programa
        const response = await formService.getActiveForm(programId);
        form = response;
      }

      if (!form) {
        Alert.alert(
          'Sin formulario',
          'No hay formularios disponibles para este programa.',
          [{ text: 'OK', onPress: () => navigation.goBack() }]
        );
        return;
      }

      setActiveForm(form);

      // Si hay un submissionId, cargar datos existentes
      if (submissionId) {
        try {
          const submission = await formService.getSubmission(submissionId);
          setInitialData(submission.form_data || {});
        } catch (error) {
          console.error('Error loading submission data:', error);
        }
      }
    } catch (error) {
      console.error('Error loading form:', error);
      Alert.alert(
        'Error',
        'No se pudo cargar el formulario. Inténtelo nuevamente.',
        [
          { text: 'Reintentar', onPress: loadForm },
          { text: 'Cancelar', onPress: () => navigation.goBack() },
        ]
      );
    } finally {
      setLoading(false);
    }
  };

  const handleSubmit = async (formData: FormData) => {
    try {
      setSubmitting(true);

      let response;
      if (submissionId) {
        // Actualizar borrador existente y enviarlo
        response = await formService.submitForm(activeForm!.id, formData);
      } else {
        // Enviar nuevo formulario
        response = await formService.submitForm(activeForm!.id, formData);
      }

      if (response.success) {
        Alert.alert(
          'Éxito',
          'Formulario enviado exitosamente. Recibirá una notificación cuando sea revisado.',
          [
            {
              text: 'OK',
              onPress: () => {
                navigation.goBack();
                // Navegar a pantalla de aplicaciones o confirmación
                // navigation.navigate('ApplicationConfirm', { submissionId: response.submission_id });
              },
            },
          ]
        );
      } else {
        Alert.alert(
          'Errores en el formulario',
          response.errors?.map(e => e.message).join('\n') || 'Hay errores en el formulario.'
        );
      }
    } catch (error) {
      console.error('Error submitting form:', error);
      Alert.alert(
        'Error',
        'No se pudo enviar el formulario. Verifique su conexión e inténtelo nuevamente.'
      );
    } finally {
      setSubmitting(false);
    }
  };

  const handleCancel = () => {
    Alert.alert(
      'Cancelar formulario',
      '¿Está seguro de que desea cancelar? Los cambios no guardados se perderán.',
      [
        { text: 'Continuar editando', style: 'cancel' },
        { text: 'Cancelar', style: 'destructive', onPress: () => navigation.goBack() },
      ]
    );
  };

  const handleSaveDraft = async (formData: FormData) => {
    try {
      let response;
      if (submissionId) {
        response = await formService.updateDraft(submissionId, formData);
      } else {
        response = await formService.saveDraft(activeForm!.id, formData);
      }

      if (response.success) {
        Alert.alert('Borrador guardado', 'Sus cambios han sido guardados.');
      }
    } catch (error) {
      console.error('Error saving draft:', error);
      Alert.alert('Error', 'No se pudo guardar el borrador.');
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

  if (!activeForm) {
    return (
      <SafeAreaView style={styles.container}>
        <View style={styles.errorContainer}>
          <Text style={styles.errorText}>No se pudo cargar el formulario</Text>
          <TouchableOpacity style={styles.retryButton} onPress={loadForm}>
            <Text style={styles.retryButtonText}>Reintentar</Text>
          </TouchableOpacity>
        </View>
      </SafeAreaView>
    );
  }

  return (
    <DynamicForm
      programId={programId}
      formId={activeForm.id}
      onSubmit={handleSubmit}
      onCancel={handleCancel}
      initialData={initialData}
      isLoading={submitting}
    />
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
});

export default FormScreen; 