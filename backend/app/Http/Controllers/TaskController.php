<?php

namespace App\Http\Controllers;

use App\Enums\TaskStatus;

class TaskController extends Controller
{
    public function index()
    {
        // id, title, description, status
        return [
            [
                'id' => 1,
                'title' => 'Task 1',
                'description' => 'Description for Task 1',
                'status' => TaskStatus::Pending->value,
            ],
            [
                'id' => 2,
                'title' => 'Task 2',
                'description' => 'Description for Task 2',
                'status' => TaskStatus::InProgress->value,
            ],
            [
                'id' => 3,
                'title' => 'Task 3',
                'description' => 'Description for Task 3',
                'status' => TaskStatus::Done->value,
            ],
            [
                'id' => 4,
                'title' => 'Task 4',
                'description' => 'Description for Task 4',
                'status' => TaskStatus::Pending->value,
            ],
            [
                'id' => 5,
                'title' => 'Task 5',
                'description' => 'Description for Task 5',
                'status' => TaskStatus::Pending->value,
            ],
            [
                'id' => 6,
                'title' => 'Task 6',
                'description' => 'Description for Task 6',
                'status' => TaskStatus::InProgress->value,
            ],
            [
                'id' => 7,
                'title' => 'Task 7',
                'description' => 'Description for Task 7',
                'status' => TaskStatus::Done->value,
            ],
        ];
    }
}
