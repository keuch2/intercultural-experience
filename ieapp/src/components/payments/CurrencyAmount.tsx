import React from 'react';
import { Text, StyleSheet, TextStyle } from 'react-native';

interface Props {
  amount: number;
  currency?: string | null;
  style?: TextStyle;
  bold?: boolean;
}

const formatNumber = (n: number, decimals = 2) =>
  n.toLocaleString('es-PY', { minimumFractionDigits: decimals, maximumFractionDigits: decimals });

const CurrencyAmount: React.FC<Props> = ({ amount, currency, style, bold }) => {
  const code = (currency || 'USD').toUpperCase();
  const decimals = code === 'PYG' ? 0 : 2;
  return (
    <Text style={[styles.base, bold && styles.bold, style]}>
      {code} {formatNumber(amount, decimals)}
    </Text>
  );
};

const styles = StyleSheet.create({
  base: { color: '#222', fontSize: 14 },
  bold: { fontWeight: '700' },
});

export default CurrencyAmount;
