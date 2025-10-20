import React from 'react';
import { View, Text, StyleSheet } from 'react-native';

interface PasswordStrengthIndicatorProps {
  password: string;
}

interface PasswordRequirement {
  label: string;
  met: boolean;
}

const PasswordStrengthIndicator: React.FC<PasswordStrengthIndicatorProps> = ({ password }) => {
  const requirements: PasswordRequirement[] = [
    {
      label: 'Mínimo 8 caracteres',
      met: password.length >= 8
    },
    {
      label: 'Una mayúscula',
      met: /[A-Z]/.test(password)
    },
    {
      label: 'Una minúscula',
      met: /[a-z]/.test(password)
    },
    {
      label: 'Un número',
      met: /[0-9]/.test(password)
    },
    {
      label: 'Un carácter especial (@$!%*#?&)',
      met: /[@$!%*#?&]/.test(password)
    }
  ];

  const metCount = requirements.filter(req => req.met).length;
  const strength = metCount === 0 ? 0 : (metCount / requirements.length) * 100;

  const getStrengthColor = () => {
    if (strength === 0) return '#E0E0E0';
    if (strength < 40) return '#F44336';
    if (strength < 80) return '#FF9800';
    return '#4CAF50';
  };

  const getStrengthLabel = () => {
    if (strength === 0) return '';
    if (strength < 40) return 'Débil';
    if (strength < 80) return 'Media';
    return 'Fuerte';
  };

  return (
    <View style={styles.container}>
      {password.length > 0 && (
        <>
          <View style={styles.strengthBarContainer}>
            <View 
              style={[
                styles.strengthBar, 
                { width: `${strength}%`, backgroundColor: getStrengthColor() }
              ]} 
            />
          </View>
          
          {strength > 0 && (
            <Text style={[styles.strengthLabel, { color: getStrengthColor() }]}>
              Contraseña {getStrengthLabel()}
            </Text>
          )}
        </>
      )}

      <View style={styles.requirementsContainer}>
        {requirements.map((req, index) => (
          <View key={index} style={styles.requirementRow}>
            <Text style={[styles.checkmark, req.met && styles.checkmarkMet]}>
              {req.met ? '✓' : '○'}
            </Text>
            <Text style={[styles.requirementText, req.met && styles.requirementMet]}>
              {req.label}
            </Text>
          </View>
        ))}
      </View>
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    marginVertical: 12,
  },
  strengthBarContainer: {
    height: 4,
    backgroundColor: '#E0E0E0',
    borderRadius: 2,
    overflow: 'hidden',
    marginBottom: 8,
  },
  strengthBar: {
    height: '100%',
    borderRadius: 2,
  },
  strengthLabel: {
    fontSize: 12,
    fontWeight: '600',
    marginBottom: 12,
  },
  requirementsContainer: {
    gap: 6,
  },
  requirementRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
  },
  checkmark: {
    fontSize: 16,
    color: '#9E9E9E',
    width: 20,
  },
  checkmarkMet: {
    color: '#4CAF50',
  },
  requirementText: {
    fontSize: 13,
    color: '#757575',
  },
  requirementMet: {
    color: '#4CAF50',
    fontWeight: '500',
  },
});

export default PasswordStrengthIndicator;
