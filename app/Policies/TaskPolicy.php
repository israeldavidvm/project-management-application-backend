<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Task;
use Illuminate\Auth\Access\Response;

class TaskPolicy
{
    
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isAdmin()) {
            return true;
        }
        return null;
    }

    
    public function view(User $user, Task $task): bool
    {
        if ($user->id === $task->project->user_creator_id) {
            return true;
        }
        
        return $user->id === $task->user_assignee_id;
    }

    
    public function update(User $user, Task $task): bool
    {
        if ($user->id === $task->project->user_creator_id) {
            return true;
        }

        return $user->id === $task->user_assignee_id;
    }

    
    public function delete(User $user, Task $task): bool
    {
        return $user->id === $task->project->user_creator_id;
    }
}
