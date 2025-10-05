import { fetchProducts } from '@/lib/api';
import type { Product } from '@/types/product';
import Link from 'next/link';

export const dynamic = 'force-dynamic';

export default async function Page() {
  const products = await fetchProducts();

  return (
    <main className="min-h-screen bg-gray-50">
      <div className="max-w-6xl px-4 py-10 mx-auto">
        <header className="flex items-center justify-between mb-8">
          <h1 className="text-3xl font-bold tracking-tight text-gray-900">Products</h1>
        </header>

        {products.length === 0 ? (
          <p className="text-sm text-gray-600">No products found.</p>
        ) : (
          <ul className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            {products.map((product: Product) => (
              <li
                key={product.id}
                className="p-0 overflow-hidden transition bg-gray-200 border border-gray-200 rounded-lg shadow-sm hover:shadow-md group"
              >
                <Link href={`/products/${product.id}`} className="flex flex-col h-full gap-3 p-5">
                {product.image_url && (
                  <div className="relative w-full h-40 overflow-hidden bg-gray-100 rounded-md">
                    {/* eslint-disable-next-line @next/next/no-img-element */}
                    <img
                      src={product.image_url}
                      alt={product.title}
                      className="object-cover w-full h-full"
                      loading="lazy"
                    />
                  </div>
                )}
                <div className="flex flex-col flex-1 gap-2">
                  <h2 className="text-lg font-medium text-gray-900 line-clamp-2">
                    {product.title}
                  </h2>
                  <div className="text-sm font-semibold text-gray-800">
                    {product.price !== null ? `$${product.price.toFixed(2)}` : '—'}
                  </div>
                  <time
                    dateTime={product.created_at}
                    className="text-xs text-gray-500"
                  >
                    Added {new Date(product.created_at).toLocaleDateString()}
                  </time>
                </div>
                </Link>
              </li>
            ))}
          </ul>
        )}
      </div>
    </main>
  );
}
