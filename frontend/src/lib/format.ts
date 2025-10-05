// Small shared formatting helpers
export function formatPrice(p: number | null | undefined, currency: string | null | undefined = 'USD') {
  if (p == null) return '—';
  try {
    return new Intl.NumberFormat(undefined, { style: 'currency', currency: currency || 'USD' }).format(p);
  } catch {
    return `$${p.toFixed(2)}`;
  }
}

export function formatDateTime(value: string | number | Date) {
  try {
    return new Date(value).toLocaleString();
  } catch {
    return String(value);
  }
}
