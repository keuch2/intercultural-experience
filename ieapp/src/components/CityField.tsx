import React, { useEffect, useMemo, useState } from 'react';
import { View, Text, TextInput, TouchableOpacity, StyleSheet, ActivityIndicator } from 'react-native';
import DropdownField from './DropdownField';
import { cityService, City } from '../services/api/cityService';

const OTHER = 'other';

export interface CityValue {
  /** Ciudad del catálogo, o null si eligió "Otra" / no es de Paraguay. */
  city_id: number | null;
  /** Nombre de la ciudad (el del catálogo o el texto libre de "Otra"). */
  city: string;
}

interface CityFieldProps {
  label: string;
  /** Nombre del país elegido. El catálogo se usa solo para Paraguay (o sin país todavía). */
  country: string;
  value: CityValue;
  onChange: (value: CityValue) => void;
  error?: string;
  /** Estilos para el texto libre, así combina con el formulario que lo usa. */
  inputStyle?: object;
  labelStyle?: object;
}

/**
 * Ciudad con lista desplegable + buscador (catálogo de Paraguay) y opción "Otra" con texto libre.
 * Para otros países, texto libre. Pedido de IE (nov 2026): unificar cómo se escriben las ciudades.
 */
const CityField: React.FC<CityFieldProps> = ({ label, country, value, onChange, error, inputStyle, labelStyle }) => {
  const usesCatalog = !country || country.trim().toLowerCase() === 'paraguay';
  const [cities, setCities] = useState<City[] | null>(null);
  const [loadError, setLoadError] = useState(false);
  // "Otra" elegida (aunque todavía no haya escrito nada).
  const [otherSelected, setOtherSelected] = useState(!value.city_id && !!value.city);

  const load = () => {
    setLoadError(false);
    cityService.list().then(setCities).catch(() => setLoadError(true));
  };

  useEffect(() => {
    if (usesCatalog && !cities) load();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [usesCatalog]);

  // Perfil viejo con la ciudad escrita a mano que coincide con el catálogo: se muestra seleccionada.
  useEffect(() => {
    if (!cities || value.city_id || !value.city) return;
    const match = cities.find((c) => c.name.toLowerCase() === value.city.trim().toLowerCase());
    if (match) {
      setOtherSelected(false);
      onChange({ city_id: match.id, city: match.name });
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [cities]);

  const options = useMemo(
    () => [...(cities ?? []).map((c) => ({ label: c.label, value: String(c.id) })), { label: 'Otra (no está en la lista)', value: OTHER }],
    [cities]
  );

  if (!usesCatalog) {
    return (
      <View style={styles.container}>
        <Text style={[styles.label, labelStyle]}>{label}</Text>
        <TextInput
          style={[styles.input, inputStyle, error ? styles.inputError : null]}
          value={value.city}
          onChangeText={(text) => onChange({ city_id: null, city: text })}
          placeholder="Tu ciudad"
          autoCapitalize="words"
        />
        {!!error && <Text style={styles.error}>{error}</Text>}
      </View>
    );
  }

  if (!cities) {
    return (
      <View style={styles.container}>
        <Text style={[styles.label, labelStyle]}>{label}</Text>
        {loadError ? (
          <TouchableOpacity onPress={load} style={styles.retry}>
            <Text style={styles.retryText}>No se pudo cargar la lista de ciudades. Tocá para reintentar.</Text>
          </TouchableOpacity>
        ) : (
          <ActivityIndicator color="#E52224" style={{ alignSelf: 'flex-start' }} />
        )}
      </View>
    );
  }

  const selected = value.city_id ? String(value.city_id) : otherSelected ? OTHER : '';

  return (
    <View>
      <DropdownField
        label={label}
        options={options}
        value={selected}
        onSelect={(v) => {
          if (v === OTHER) {
            setOtherSelected(true);
            onChange({ city_id: null, city: '' });
          } else {
            setOtherSelected(false);
            const city = cities.find((c) => String(c.id) === v);
            onChange({ city_id: city?.id ?? null, city: city?.name ?? '' });
          }
        }}
        placeholder="Seleccioná tu ciudad"
        error={selected === OTHER ? undefined : error}
        searchable
        maxHeight={520}
      />
      {selected === OTHER && (
        <View style={styles.container}>
          <Text style={[styles.label, labelStyle]}>Especificá tu ciudad</Text>
          <TextInput
            style={[styles.input, inputStyle, error ? styles.inputError : null]}
            value={value.city}
            onChangeText={(text) => onChange({ city_id: null, city: text })}
            placeholder="Nombre de tu ciudad"
            autoCapitalize="words"
          />
          {!!error && <Text style={styles.error}>{error}</Text>}
        </View>
      )}
    </View>
  );
};

const styles = StyleSheet.create({
  container: { marginBottom: 20, width: '100%' },
  label: { fontSize: 16, fontWeight: '600', marginBottom: 8, color: '#333' },
  input: { borderWidth: 1, borderColor: '#ddd', borderRadius: 8, paddingHorizontal: 12, paddingVertical: 12, fontSize: 16, backgroundColor: '#f9f9f9', color: '#333' },
  inputError: { borderColor: '#E52224' },
  error: { color: '#E52224', fontSize: 12, marginTop: 4 },
  retry: { padding: 12, borderRadius: 8, backgroundColor: '#FEF2F2' },
  retryText: { color: '#B91C1C', fontSize: 14 },
});

export default CityField;
