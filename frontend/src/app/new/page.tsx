'use client';

import { useState } from 'react';
import { useRouter } from 'next/navigation';
import type { Task } from '@/types/task';
import { createTask } from '@/lib/api';

export default function NewTaskPage() {
  const router = useRouter();

  const [form, setForm] = useState<Omit<Task, 'id'>>({
    title: '',
    description: '',
    status: 'Pending',
  });

  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState<string | null>(null);

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    setSubmitting(true);
    setError(null);

    try {
      await createTask(form);
      router.push('/'); // redirect back to list
      router.refresh(); // reload tasks
    } catch (err) {
      setError('Failed to create task. Please try again.');
    } finally {
      setSubmitting(false);
    }
  }

  return (
    <main className="min-h-screen bg-gray-50">
      <div className="max-w-2xl px-4 py-10 mx-auto">
        <h1 className="mb-6 text-2xl font-bold text-gray-900">New Task</h1>

        <form
          onSubmit={handleSubmit}
          className="grid gap-6 p-6 bg-white border border-gray-200 rounded-lg shadow-sm"
        >
          {error && (
            <div className="p-3 text-sm text-red-700 rounded bg-red-50">{error}</div>
          )}

          <div className="grid gap-2">
            <label className="text-sm font-medium text-gray-700">Title</label>
            <input
              type="text"
              value={form.title}
              onChange={(e) => setForm({ ...form, title: e.target.value })}
              required
              className="px-3 py-2 text-sm text-gray-900 border rounded shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
            />
          </div>

          <div className="grid gap-2">
            <label className="text-sm font-medium text-gray-700">Description</label>
            <textarea
              value={form.description}
              onChange={(e) => setForm({ ...form, description: e.target.value })}
              className="px-3 py-2 text-sm text-gray-900 border rounded shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
            />
          </div>

          <div className="grid gap-2">
            <label className="text-sm font-medium text-gray-700">Status</label>
            <select
              value={form.status}
              onChange={(e) =>
                setForm({ ...form, status: e.target.value as Task['status'] })
              }
              className="px-3 py-2 text-sm text-gray-900 border rounded shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
            >
              <option value="Pending">Pending</option>
              <option value="In Progress">In Progress</option>
              <option value="Done">Done</option>
            </select>
          </div>

          <button
            type="submit"
            disabled={submitting}
            className="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded shadow-sm w-fit hover:bg-blue-700 disabled:opacity-50"
          >
            {submitting ? 'Saving…' : 'Save Task'}
          </button>
        </form>
      </div>
    </main>
  );
}
