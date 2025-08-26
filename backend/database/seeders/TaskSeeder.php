<?php

namespace Database\Seeders;

use App\Enums\TaskStatus;
use App\Models\Task;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $tasks = [
            ['title' => 'Task 1', 'description' => 'Description for Task 1', 'status' => TaskStatus::Pending->value],
            ['title' => 'Task 2', 'description' => 'Description for Task 2', 'status' => TaskStatus::InProgress->value],
            ['title' => 'Task 3', 'description' => 'Description for Task 3', 'status' => TaskStatus::Done->value],
            ['title' => 'Task 4', 'description' => 'Description for Task 4', 'status' => TaskStatus::Pending->value],
            ['title' => 'Task 5', 'description' => 'Description for Task 5', 'status' => TaskStatus::Pending->value],
            ['title' => 'Task 6', 'description' => 'Description for Task 6', 'status' => TaskStatus::InProgress->value],
            ['title' => 'Task 7', 'description' => 'Description for Task 7', 'status' => TaskStatus::Done->value],
        ];

        foreach ($tasks as $task) {
            Task::create($task);
        }
    }
}
