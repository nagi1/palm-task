import { fetchProduct } from '@/lib/api';
import type { Product } from '@/types/product';
import Link from 'next/link';
import { formatPrice, formatDateTime } from '@/lib/format';

export const dynamic = 'force-dynamic';

interface Params { id: string }

export default async function ProductDetail({ params }: { params: Params }) {
  const { id } = params;
  let product: Product | null = null;
  let error: string | null = null;
  try {
    product = await fetchProduct(id);
  } catch (e) {
    error = e instanceof Error ? e.message : 'Failed to load product';
  }

  if (error) {
    return (
      <main className="min-h-screen bg-gray-50">
        <div className="max-w-3xl px-4 py-10 mx-auto">
          <Link href="/" className="text-sm text-blue-600 hover:underline">← Back</Link>
          <h1 className="mt-4 text-2xl font-bold text-gray-900">Error</h1>
          <p className="mt-2 text-sm text-red-600">{error}</p>
        </div>
      </main>
    );
  }

  if (!product) return null;

  const meta: { label: string; value: string | number | null | undefined }[] = [
    { label: 'ID', value: product.id },
    { label: 'Source', value: product.source || '—' },
    { label: 'ASIN', value: product.asin || '—' },
    { label: 'Currency', value: product.currency || 'USD' },
    { label: 'Created', value: formatDateTime(product.created_at) },
    { label: 'Updated', value: formatDateTime(product.updated_at) },
  ];

  return (
    <main className="min-h-screen bg-gradient-to-br from-gray-50 via-white to-gray-100">
      <div className="max-w-5xl px-4 py-8 mx-auto">
        <div className="flex items-center gap-2 text-xs text-gray-500">
          <Link href="/products" className="transition hover:text-blue-600">Products</Link>
          <span>/</span>
          <span className="text-gray-700">{product.id}</span>
        </div>
        <div className="grid items-start gap-10 mt-4 md:grid-cols-2">
          <div className="p-4 border rounded-lg shadow-sm bg-white/70 backdrop-blur-sm">
            {product.image_url ? (
              // eslint-disable-next-line @next/next/no-img-element
              <img
                src={product.image_url}
                alt={product.title}
                className="w-full max-h-[480px] object-contain rounded-md"
              />
            ) : (
              <div className="w-full h-[420px] flex items-center justify-center text-xs text-gray-400 bg-gray-100 rounded-md">
                No Image
              </div>
            )}
          </div>
          <div className="flex flex-col gap-6">
            <header className="space-y-3">
              <h1 className="text-3xl font-semibold leading-tight tracking-tight text-gray-900">
                {product.title}
              </h1>
              <p className="font-mono text-2xl text-gray-800" suppressHydrationWarning>
                {formatPrice(product.price, product.currency)}
              </p>
            </header>
            <ul className="grid grid-cols-2 text-xs gap-x-6 gap-y-2">
              {meta.map(m => (
                <li key={m.label} className="flex flex-col">
                  <span className="uppercase tracking-wide text-[10px] text-gray-500 font-medium">
                    {m.label}
                  </span>
                  <span className="text-gray-800 break-all">{m.value}</span>
                </li>
              ))}
            </ul>
            <div className="flex flex-wrap gap-3 pt-2">
              <Link
                href="/products"
                className="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 transition bg-white border rounded hover:bg-gray-50 active:scale-95"
              >
                ← Back
              </Link>
              {product.url && (
                <a
                  href={product.url}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="inline-flex items-center px-4 py-2 text-sm font-medium text-white transition bg-blue-600 rounded shadow-sm hover:bg-blue-700 active:scale-95"
                >
                  View Source
                </a>
              )}
            </div>
          </div>
        </div>
      </div>
    </main>
  );
}
