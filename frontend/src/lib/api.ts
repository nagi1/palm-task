import type { Product } from '@/types/product';

const BASE_URL = process.env.NEXT_PUBLIC_API_BASE_URL ?? 'http://localhost:8000';

export async function fetchProducts(): Promise<Product[]> {
  const res = await fetch(`${BASE_URL}/api/products?per_page=50`, {
    cache: 'no-store',
    headers: { Accept: 'application/json' },
  });
  if (!res.ok) throw new Error(`Failed to load products: ${res.status}`);
  const body = await res.json();
  if (Array.isArray(body)) return body as Product[];
  return body.data as Product[];
}

export async function fetchProduct(id: number | string): Promise<Product> {
  const res = await fetch(`${BASE_URL}/api/products/${id}`, {
    cache: 'no-store',
    headers: { Accept: 'application/json' },
  });
  if (res.status === 404) throw new Error('Product not found');
  if (!res.ok) throw new Error(`Failed to load product ${id}: ${res.status}`);
  const body = await res.json();
  return body.data ?? body;
}
