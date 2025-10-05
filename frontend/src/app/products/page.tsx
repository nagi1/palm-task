import { Suspense } from 'react';
import { ProductsList } from './products-list';

export const dynamic = 'force-dynamic';

export default function ProductsPage() {
  return (
    <main className="mx-auto max-w-6xl p-6">
      <h1 className="text-2xl font-semibold mb-6">Products</h1>
      <Suspense fallback={<p className="text-sm text-gray-500">Loading…</p>}>
        <ProductsList />
      </Suspense>
    </main>
  );
}
