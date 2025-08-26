import type { Task } from '@/types/task';

const BASE_URL = process.env.NEXT_PUBLIC_API_BASE_URL ?? 'http://localhost:8000';

export async function fetchTasks(): Promise<Task[]> {
	const res = await fetch(`${BASE_URL}/api/tasks`, {
		cache: 'no-store',
		headers: { Accept: 'application/json' },
	});

	if (!res.ok) throw new Error(`Failed to load tasks: ${res.status}`);

	const body = await res.json();
	const data = Array.isArray(body) ? body : body.data;

	return data as Task[];
}
