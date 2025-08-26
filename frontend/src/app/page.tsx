import { fetchTasks } from '@/lib/api';
import type { Task } from '@/types/task';
import Link from 'next/link';

export const dynamic = 'force-dynamic';

function StatusBadge({ status }: { status: Task['status'] }) {
  const base =
    'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold';

  switch (status) {
    case 'Pending':
      return <span className={`${base} bg-yellow-100 text-yellow-800`}>Pending</span>;
    case 'In Progress':
      return <span className={`${base} bg-blue-100 text-blue-800`}>In Progress</span>;
    case 'Done':
      return <span className={`${base} bg-green-100 text-green-800`}>Done</span>;
  }
}

export default async function Page() {
  const tasks = await fetchTasks();

  return (
    <main className="min-h-screen bg-gray-50">
      <div className="max-w-6xl px-4 py-10 mx-auto">
        <header className="mb-8 flex items-center justify-between">
          <h1 className="text-3xl font-bold tracking-tight text-gray-900">Dev Tickets</h1>
          <Link
            href="/new"
            className="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded hover:bg-blue-700"
          >
            New Task
          </Link>
        </header>

        <ul className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
          {tasks.map((task) => (
            <li
              key={task.id}
              className="p-5 transition bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md"
            >
              <div className="flex items-center justify-between mb-3">
                <h2 className="text-lg font-medium text-gray-900">{task.title}</h2>
                <StatusBadge status={task.status} />
              </div>
              <p className="text-sm leading-snug text-gray-700">{task.description}</p>
            </li>
          ))}
        </ul>
      </div>
    </main>
  );
}
