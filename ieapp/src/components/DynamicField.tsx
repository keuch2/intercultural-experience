import React, { useState } from 'react';
import {
  View,
  Text,
  TextInput,
  TouchableOpacity,
  StyleSheet,
  Platform,
  Alert,
} from 'react-native';
import DateTimePicker from '@react-native-community/datetimepicker';
import { Picker } from '@react-native-picker/picker';
import CheckBox from '@react-native-community/checkbox';
import * as DocumentPicker from 'expo-document-picker';
import { FormField } from '../types/forms';

interface DynamicFieldProps {
  field: FormField;
  value: any;
  error?: string;
  onChange: (value: any) => void;
}

const DynamicField: React.FC<DynamicFieldProps> = ({
  field,
  value,
  error,
  onChange,
}) => {
  const [showDatePicker, setShowDatePicker] = useState(false);
  const [selectedFile, setSelectedFile] = useState<any>(null);

  const handleDateChange = (event: any, selectedDate?: Date) => {
    setShowDatePicker(false);
    if (selectedDate) {
      onChange(selectedDate.toISOString().split('T')[0]);
    }
  };

  const handleFilePick = async () => {
    try {
      const result = await DocumentPicker.getDocumentAsync({
        type: '*/*',
        copyToCacheDirectory: true,
      });
      if (!result.canceled && result.assets && result.assets.length > 0) {
        setSelectedFile(result.assets[0]);
        onChange(result.assets[0]);
      }
    } catch (err) {
      Alert.alert('Error', 'No se pudo seleccionar el archivo');
    }
  };

  const renderField = () => {
    switch (field.field_type) {
      case 'text':
      case 'email':
      case 'tel':
        return (
          <TextInput
            style={[styles.textInput, error && styles.inputError]}
            value={value || ''}
            onChangeText={onChange}
            placeholder={field.placeholder}
            keyboardType={
              field.field_type === 'email'
                ? 'email-address'
                : field.field_type === 'tel'
                ? 'phone-pad'
                : 'default'
            }
            autoCapitalize={field.field_type === 'email' ? 'none' : 'words'}
            autoCorrect={field.field_type !== 'email'}
          />
        );

      case 'number':
        return (
          <TextInput
            style={[styles.textInput, error && styles.inputError]}
            value={value ? value.toString() : ''}
            onChangeText={(text) => onChange(parseFloat(text) || 0)}
            placeholder={field.placeholder}
            keyboardType="numeric"
          />
        );

      case 'textarea':
        return (
          <TextInput
            style={[styles.textArea, error && styles.inputError]}
            value={value || ''}
            onChangeText={onChange}
            placeholder={field.placeholder}
            multiline
            numberOfLines={4}
            textAlignVertical="top"
          />
        );

      case 'date':
        return (
          <View>
            <TouchableOpacity
              style={[styles.dateButton, error && styles.inputError]}
              onPress={() => setShowDatePicker(true)}
            >
              <Text style={value ? styles.dateText : styles.datePlaceholder}>
                {value ? new Date(value).toLocaleDateString() : field.placeholder || 'Seleccionar fecha'}
              </Text>
            </TouchableOpacity>
            {showDatePicker && (
              <DateTimePicker
                value={value ? new Date(value) : new Date()}
                mode="date"
                display={Platform.OS === 'ios' ? 'spinner' : 'default'}
                onChange={handleDateChange}
              />
            )}
          </View>
        );

      case 'select':
        return (
          <View style={[styles.pickerContainer, error && styles.inputError]}>
            <Picker
              selectedValue={value}
              onValueChange={onChange}
              style={styles.picker}
            >
              <Picker.Item label="Seleccionar..." value="" />
              {field.options?.map((option, index) => (
                <Picker.Item key={index} label={option} value={option} />
              ))}
            </Picker>
          </View>
        );

      case 'radio':
        return (
          <View style={styles.radioContainer}>
            {field.options?.map((option, index) => (
              <TouchableOpacity
                key={index}
                style={styles.radioOption}
                onPress={() => onChange(option)}
              >
                <View style={styles.radioButton}>
                  {value === option && <View style={styles.radioSelected} />}
                </View>
                <Text style={styles.radioLabel}>{option}</Text>
              </TouchableOpacity>
            ))}
          </View>
        );

      case 'checkbox':
        return (
          <View style={styles.checkboxContainer}>
            {field.options?.map((option, index) => {
              const isChecked = Array.isArray(value) && value.includes(option);
              return (
                <TouchableOpacity
                  key={index}
                  style={styles.checkboxOption}
                  onPress={() => {
                    const currentValues = Array.isArray(value) ? value : [];
                    if (isChecked) {
                      onChange(currentValues.filter(v => v !== option));
                    } else {
                      onChange([...currentValues, option]);
                    }
                  }}
                >
                  <CheckBox
                    value={isChecked}
                    onValueChange={() => {}} // Handled by TouchableOpacity
                    style={styles.checkbox}
                  />
                  <Text style={styles.checkboxLabel}>{option}</Text>
                </TouchableOpacity>
              );
            })}
          </View>
        );

      case 'boolean':
        return (
          <View style={styles.switchContainer}>
            <CheckBox
              value={value || false}
              onValueChange={onChange}
              style={styles.switch}
            />
            <Text style={styles.switchLabel}>Sí</Text>
          </View>
        );

      case 'file':
        return (
          <View>
            <TouchableOpacity
              style={[styles.fileButton, error && styles.inputError]}
              onPress={handleFilePick}
            >
              <Text style={styles.fileButtonText}>
                {selectedFile ? selectedFile.name : 'Seleccionar archivo'}
              </Text>
            </TouchableOpacity>
            {selectedFile && (
              <Text style={styles.fileName}>{selectedFile.name}</Text>
            )}
          </View>
        );

      case 'signature':
        return (
          <View style={[styles.signatureContainer, error && styles.inputError]}>
            <Text style={styles.signaturePlaceholder}>
              Área de firma (funcionalidad pendiente)
            </Text>
          </View>
        );

      case 'address':
        return (
          <TextInput
            style={[styles.textArea, error && styles.inputError]}
            value={value || ''}
            onChangeText={onChange}
            placeholder={field.placeholder || 'Dirección completa'}
            multiline
            numberOfLines={3}
            textAlignVertical="top"
          />
        );

      case 'country':
        return (
          <View style={[styles.pickerContainer, error && styles.inputError]}>
            <Picker
              selectedValue={value}
              onValueChange={onChange}
              style={styles.picker}
            >
              <Picker.Item label="Seleccionar país..." value="" />
              <Picker.Item label="Paraguay" value="Paraguay" />
              <Picker.Item label="Argentina" value="Argentina" />
              <Picker.Item label="Brasil" value="Brasil" />
              <Picker.Item label="Estados Unidos" value="Estados Unidos" />
              <Picker.Item label="España" value="España" />
              <Picker.Item label="Francia" value="Francia" />
              <Picker.Item label="Alemania" value="Alemania" />
              <Picker.Item label="Otro" value="Otro" />
            </Picker>
          </View>
        );

      default:
        return (
          <TextInput
            style={[styles.textInput, error && styles.inputError]}
            value={value || ''}
            onChangeText={onChange}
            placeholder={field.placeholder}
          />
        );
    }
  };

  return (
    <View style={styles.container}>
      <Text style={styles.label}>
        {field.field_label}
        {field.is_required && <Text style={styles.required}> *</Text>}
      </Text>
      
      {field.description && (
        <Text style={styles.description}>{field.description}</Text>
      )}
      
      {renderField()}
      
      {error && <Text style={styles.errorText}>{error}</Text>}
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    marginBottom: 20,
  },
  label: {
    fontSize: 16,
    fontWeight: '600',
    color: '#212529',
    marginBottom: 8,
  },
  required: {
    color: '#dc3545',
  },
  description: {
    fontSize: 14,
    color: '#6c757d',
    marginBottom: 8,
    lineHeight: 20,
  },
  textInput: {
    borderWidth: 1,
    borderColor: '#ced4da',
    borderRadius: 8,
    padding: 12,
    fontSize: 16,
    backgroundColor: '#fff',
  },
  textArea: {
    borderWidth: 1,
    borderColor: '#ced4da',
    borderRadius: 8,
    padding: 12,
    fontSize: 16,
    backgroundColor: '#fff',
    minHeight: 100,
  },
  inputError: {
    borderColor: '#dc3545',
  },
  dateButton: {
    borderWidth: 1,
    borderColor: '#ced4da',
    borderRadius: 8,
    padding: 12,
    backgroundColor: '#fff',
  },
  dateText: {
    fontSize: 16,
    color: '#212529',
  },
  datePlaceholder: {
    fontSize: 16,
    color: '#6c757d',
  },
  pickerContainer: {
    borderWidth: 1,
    borderColor: '#ced4da',
    borderRadius: 8,
    backgroundColor: '#fff',
  },
  picker: {
    height: 50,
  },
  radioContainer: {
    marginTop: 8,
  },
  radioOption: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 12,
  },
  radioButton: {
    width: 20,
    height: 20,
    borderRadius: 10,
    borderWidth: 2,
    borderColor: '#007bff',
    alignItems: 'center',
    justifyContent: 'center',
    marginRight: 12,
  },
  radioSelected: {
    width: 10,
    height: 10,
    borderRadius: 5,
    backgroundColor: '#007bff',
  },
  radioLabel: {
    fontSize: 16,
    color: '#212529',
  },
  checkboxContainer: {
    marginTop: 8,
  },
  checkboxOption: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 12,
  },
  checkbox: {
    marginRight: 12,
  },
  checkboxLabel: {
    fontSize: 16,
    color: '#212529',
    flex: 1,
  },
  switchContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    marginTop: 8,
  },
  switch: {
    marginRight: 12,
  },
  switchLabel: {
    fontSize: 16,
    color: '#212529',
  },
  fileButton: {
    borderWidth: 1,
    borderColor: '#ced4da',
    borderRadius: 8,
    padding: 12,
    backgroundColor: '#f8f9fa',
    alignItems: 'center',
  },
  fileButtonText: {
    fontSize: 16,
    color: '#007bff',
    fontWeight: '600',
  },
  fileName: {
    marginTop: 8,
    fontSize: 14,
    color: '#6c757d',
  },
  signatureContainer: {
    borderWidth: 1,
    borderColor: '#ced4da',
    borderRadius: 8,
    padding: 40,
    backgroundColor: '#f8f9fa',
    alignItems: 'center',
    justifyContent: 'center',
    minHeight: 120,
  },
  signaturePlaceholder: {
    fontSize: 16,
    color: '#6c757d',
    textAlign: 'center',
  },
  errorText: {
    color: '#dc3545',
    fontSize: 14,
    marginTop: 4,
  },
});

export default DynamicField; 