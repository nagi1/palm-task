import type { Task } from '@/types/task';

const BASE_URL = process.env.NEXT_PUBLIC_API_BASE_URL ?? 'http://localhost:8000';

export async function fetchTasks(): Promise<Task[]> {
	const res = await fetch(`${BASE_URL}/api/tasks`, {
		cache: 'no-store',
		headers: { Accept: 'application/json' },
	});

	if (!res.ok) throw new Error(`Failed to load tasks: ${res.status}`);

	const body = await res.json();

	return Array.isArray(body) ? (body as Task[]) : (body.data as Task[]);
}

export async function createTask(task: Omit<Task, 'id'>): Promise<Task> {
	const res = await fetch(`${BASE_URL}/api/tasks`, {
		method: 'POST',
		headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
		body: JSON.stringify(task),
	});

	if (!res.ok) throw new Error(`Failed to create task: ${res.status}`);

	return res.json() as Promise<Task>;
}
