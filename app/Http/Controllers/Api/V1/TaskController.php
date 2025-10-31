<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\User;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Task::with('project', 'userAssignee');

        if ($user->role === User::ROLE_DEVELOPER) {
            $projectIds = $user->projectsCreated()->pluck('id');
            
            $query->where(function ($q) use ($user, $projectIds) {
                $q->where('user_assignee_id', $user->id)
                  ->orWhereIn('project_id', $projectIds);
            });
        }
        
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('status') && in_array($request->status, [Task::STATUS_PENDING, Task::STATUS_IN_PROGRESS, Task::STATUS_COMPLETED])) {
            $query->where('status', $request->status);
        }

        if ($request->filled('user_assignee_id')) {
            $query->where('user_assignee_id', $request->user_assignee_id);
        }

        $tasks = $query->latest()->get();

        return response()->json($tasks);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'project_id' => ['required', 'integer', 'exists:projects,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'user_assignee_id' => ['nullable', 'integer', 'exists:users,id'],
            'status' => ['nullable', 'string', Rule::in([Task::STATUS_PENDING, Task::STATUS_IN_PROGRESS, Task::STATUS_COMPLETED])],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();

        $project = Project::findOrFail($validatedData['project_id']);
        Gate::authorize('createTask', $project);

        $task = Task::create($validatedData);
        
        ProjectController::calculateProgress($project);

        return response()->json([
            'message' => 'Task created successfully',
            'task' => $task->load('project', 'userAssignee'),
        ], 201);
    }

    public function show(Task $task)
    {
        Gate::authorize('view', $task);

        return response()->json($task->load('project', 'userAssignee'));
    }

    public function update(Request $request, Task $task)
    {
        Gate::authorize('update', $task);
        
        $rules = [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'user_assignee_id' => ['sometimes', 'nullable', 'integer', 'exists:users,id'],
            'status' => ['sometimes', 'required', 'string', Rule::in([Task::STATUS_PENDING, Task::STATUS_IN_PROGRESS, Task::STATUS_COMPLETED])],
        ];

        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();
        
        $oldStatus = $task->status;
        
        $task->update($validatedData);

        if ($task->status !== $oldStatus) {
            ProjectController::calculateProgress($task->project);
        }

        return response()->json([
            'message' => 'Task updated successfully (including potential reassignment)',
            'task' => $task->load('project', 'userAssignee'),
        ]);
    }

    public function destroy(Task $task)
    {
        Gate::authorize('delete', $task);
        
        $project = $task->project;
        $task->delete();
        
        ProjectController::calculateProgress($project);

        return response()->json(['message' => 'Task deleted successfully'], 204);
    }

   public function taskSummary()
{
    $possibleStatuses = [
        Task::STATUS_PENDING,
        Task::STATUS_IN_PROGRESS,
        Task::STATUS_COMPLETED,
    ];
    
    $summary = [];

    foreach ($possibleStatuses as $status) {
        $count = Task::query()
            ->where('user_assignee_id', auth()->id())
            ->where('status', $status)
            ->count();
            
        $normalizedStatus = strtolower($status); 
        $summary[$normalizedStatus] = $count;
    }

    return response()->json($summary);
}
}
