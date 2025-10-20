import React, { useState, useMemo, useCallback } from 'react';
import { View, Text, TouchableOpacity, StyleSheet, Modal, FlatList, TextInput } from 'react-native';
import { Ionicons } from '@expo/vector-icons';

interface DropdownOption {
  label: string;
  value: string;
}

interface DropdownFieldProps {
  label: string;
  options: DropdownOption[];
  value: string;
  onSelect: (value: string) => void;
  placeholder?: string;
  error?: string;
  searchable?: boolean;
  maxHeight?: number;
}

const DropdownField: React.FC<DropdownFieldProps> = React.memo(({
  label,
  options,
  value,
  onSelect,
  placeholder = "Selecciona una opción",
  error,
  searchable = false,
  maxHeight = 300
}) => {
  const [isVisible, setIsVisible] = useState(false);
  const [searchQuery, setSearchQuery] = useState('');

  // Memoize selected option to prevent unnecessary re-renders
  const selectedOption = useMemo(
    () => options.find(option => option.value === value),
    [options, value]
  );
  
  const displayText = selectedOption ? selectedOption.label : placeholder;

  // Filter options based on search query
  const filteredOptions = useMemo(() => {
    if (!searchable || !searchQuery.trim()) {
      return options;
    }
    const query = searchQuery.toLowerCase().trim();
    return options.filter(option => 
      option.label.toLowerCase().includes(query) ||
      option.value.toLowerCase().includes(query)
    );
  }, [options, searchQuery, searchable]);

  const handleSelect = useCallback((optionValue: string) => {
    onSelect(optionValue);
    setIsVisible(false);
    setSearchQuery('');
  }, [onSelect]);

  const handleModalOpen = useCallback(() => {
    setIsVisible(true);
    setSearchQuery('');
  }, []);

  const handleModalClose = useCallback(() => {
    setIsVisible(false);
    setSearchQuery('');
  }, []);

  // Optimize FlatList rendering
  const getItemLayout = useCallback((_: any, index: number) => ({
    length: 56, // Estimated item height
    offset: 56 * index,
    index,
  }), []);

  const renderOption = useCallback(({ item }: { item: DropdownOption }) => (
    <TouchableOpacity
      style={[
        styles.option,
        item.value === value ? styles.selectedOption : null
      ]}
      onPress={() => handleSelect(item.value)}
    >
      <Text style={[
        styles.optionText,
        item.value === value ? styles.selectedOptionText : null
      ]}>
        {item.label}
      </Text>
      {item.value === value && (
        <Ionicons name="checkmark" size={20} color="#E52224" />
      )}
    </TouchableOpacity>
  ), [value, handleSelect]);

  const keyExtractor = useCallback((item: DropdownOption) => item.value, []);

  return (
    <View style={styles.container}>
      <Text style={styles.label}>{label}</Text>
      
      <TouchableOpacity
        style={[
          styles.dropdown,
          error ? styles.dropdownError : null
        ]}
        onPress={handleModalOpen}
      >
        <Text style={[
          styles.dropdownText,
          !selectedOption ? styles.placeholderText : null
        ]}>
          {displayText}
        </Text>
        <Ionicons 
          name={isVisible ? "chevron-up" : "chevron-down"} 
          size={20} 
          color="#666" 
        />
      </TouchableOpacity>

      {error && <Text style={styles.errorText}>{error}</Text>}

      <Modal
        visible={isVisible}
        transparent={true}
        animationType="fade"
        onRequestClose={handleModalClose}
      >
        <TouchableOpacity
          style={styles.modalOverlay}
          activeOpacity={1}
          onPress={handleModalClose}
        >
          <View style={[styles.modalContent, { maxHeight }]}>
            <Text style={styles.modalTitle}>{label}</Text>
            
            {searchable && (
              <View style={styles.searchContainer}>
                <Ionicons name="search" size={20} color="#666" style={styles.searchIcon} />
                <TextInput
                  style={styles.searchInput}
                  placeholder="Buscar..."
                  value={searchQuery}
                  onChangeText={setSearchQuery}
                  autoFocus={false}
                />
                {searchQuery.length > 0 && (
                  <TouchableOpacity 
                    onPress={() => setSearchQuery('')}
                    style={styles.clearButton}
                  >
                    <Ionicons name="close-circle" size={20} color="#666" />
                  </TouchableOpacity>
                )}
              </View>
            )}
            
            <FlatList
              data={filteredOptions}
              keyExtractor={keyExtractor}
              renderItem={renderOption}
              getItemLayout={getItemLayout}
              removeClippedSubviews={true}
              maxToRenderPerBatch={10}
              initialNumToRender={15}
              windowSize={10}
              showsVerticalScrollIndicator={true}
              style={styles.optionsList}
            />
            
            {filteredOptions.length === 0 && searchQuery.trim() && (
              <View style={styles.noResultsContainer}>
                <Text style={styles.noResultsText}>No se encontraron resultados</Text>
              </View>
            )}
            
            <TouchableOpacity
              style={styles.closeButton}
              onPress={handleModalClose}
            >
              <Text style={styles.closeButtonText}>Cerrar</Text>
            </TouchableOpacity>
          </View>
        </TouchableOpacity>
      </Modal>
    </View>
  );
});

const styles = StyleSheet.create({
  container: {
    marginBottom: 20,
  },
  label: {
    fontSize: 16,
    fontWeight: '600',
    marginBottom: 8,
    color: '#333',
  },
  dropdown: {
    borderWidth: 1,
    borderColor: '#ddd',
    borderRadius: 8,
    padding: 15,
    backgroundColor: '#fff',
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    minHeight: 50,
  },
  dropdownError: {
    borderColor: '#E52224',
  },
  dropdownText: {
    fontSize: 16,
    color: '#333',
    flex: 1,
  },
  placeholderText: {
    color: '#999',
  },
  errorText: {
    color: '#E52224',
    fontSize: 14,
    marginTop: 5,
  },
  modalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.5)',
    justifyContent: 'center',
    alignItems: 'center',
  },
  modalContent: {
    backgroundColor: '#fff',
    borderRadius: 12,
    padding: 20,
    width: '85%',
    flex: 1,
    maxHeight: '70%',
  },
  searchContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#f8f9fa',
    borderRadius: 8,
    paddingHorizontal: 12,
    marginBottom: 15,
    borderWidth: 1,
    borderColor: '#e9ecef',
  },
  searchIcon: {
    marginRight: 8,
  },
  searchInput: {
    flex: 1,
    paddingVertical: 12,
    fontSize: 16,
    color: '#333',
  },
  clearButton: {
    padding: 4,
  },
  optionsList: {
    flex: 1,
  },
  noResultsContainer: {
    padding: 20,
    alignItems: 'center',
  },
  noResultsText: {
    fontSize: 16,
    color: '#999',
    fontStyle: 'italic',
  },
  modalTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    marginBottom: 15,
    textAlign: 'center',
    color: '#333',
  },
  option: {
    paddingVertical: 15,
    paddingHorizontal: 10,
    borderBottomWidth: 1,
    borderBottomColor: '#f0f0f0',
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  selectedOption: {
    backgroundColor: '#f8f9fa',
  },
  optionText: {
    fontSize: 16,
    color: '#333',
    flex: 1,
  },
  selectedOptionText: {
    color: '#E52224',
    fontWeight: '600',
  },
  closeButton: {
    marginTop: 15,
    backgroundColor: '#E52224',
    paddingVertical: 12,
    borderRadius: 8,
    alignItems: 'center',
  },
  closeButtonText: {
    color: '#fff',
    fontSize: 16,
    fontWeight: '600',
  },
});

export default DropdownField;
