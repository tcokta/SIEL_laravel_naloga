<?php

namespace Tests\Feature;

use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskApiTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // GET /api/tasks
    // -------------------------------------------------------------------------

    public function test_index_returns_all_tasks(): void
    {
        Task::create(['title' => 'Task A', 'status' => 'todo',        'priority' => 'low']);
        Task::create(['title' => 'Task B', 'status' => 'in_progress', 'priority' => 'high']);

        $response = $this->getJson('/api/tasks');

        $response->assertOk()->assertJsonCount(2);
    }

    public function test_index_filters_by_status(): void
    {
        Task::create(['title' => 'Todo task',  'status' => 'todo',        'priority' => 'low']);
        Task::create(['title' => 'Done task',  'status' => 'done',        'priority' => 'low']);

        $response = $this->getJson('/api/tasks?status=todo');

        $response->assertOk()
                 ->assertJsonCount(1)
                 ->assertJsonFragment(['status' => 'todo']);
    }

    public function test_index_filters_by_priority(): void
    {
        Task::create(['title' => 'High task',   'status' => 'todo', 'priority' => 'high']);
        Task::create(['title' => 'Low task',    'status' => 'todo', 'priority' => 'low']);

        $response = $this->getJson('/api/tasks?priority=high');

        $response->assertOk()
                 ->assertJsonCount(1)
                 ->assertJsonFragment(['priority' => 'high']);
    }

    // -------------------------------------------------------------------------
    // GET /api/tasks/{id}
    // -------------------------------------------------------------------------

    public function test_show_returns_single_task(): void
    {
        $task = Task::create(['title' => 'Single task', 'status' => 'todo', 'priority' => 'medium']);

        $this->getJson("/api/tasks/{$task->id}")
             ->assertOk()
             ->assertJsonFragment(['title' => 'Single task']);
    }

    public function test_show_returns_404_for_missing_task(): void
    {
        $this->getJson('/api/tasks/9999')->assertNotFound();
    }

    // -------------------------------------------------------------------------
    // POST /api/tasks
    // -------------------------------------------------------------------------

    public function test_store_creates_task_with_valid_data(): void
    {
        $payload = [
            'title'       => 'Nova naloga',
            'description' => 'Opis naloge',
            'status'      => 'todo',
            'priority'    => 'medium',
            'due_date'    => now()->addDays(5)->toDateString(),
        ];

        $this->postJson('/api/tasks', $payload)
             ->assertCreated()
             ->assertJsonFragment(['title' => 'Nova naloga']);

        $this->assertDatabaseHas('tasks', ['title' => 'Nova naloga']);
    }

    public function test_store_requires_title(): void
    {
        $this->postJson('/api/tasks', ['status' => 'todo', 'priority' => 'low'])
             ->assertUnprocessable()
             ->assertJsonValidationErrors(['title']);
    }

    public function test_store_rejects_invalid_status(): void
    {
        $this->postJson('/api/tasks', ['title' => 'Test', 'status' => 'invalid_status'])
             ->assertUnprocessable()
             ->assertJsonValidationErrors(['status']);
    }

    public function test_store_rejects_invalid_priority(): void
    {
        $this->postJson('/api/tasks', ['title' => 'Test', 'priority' => 'urgent'])
             ->assertUnprocessable()
             ->assertJsonValidationErrors(['priority']);
    }

    public function test_store_rejects_title_exceeding_255_characters(): void
    {
        $this->postJson('/api/tasks', ['title' => str_repeat('a', 256)])
             ->assertUnprocessable()
             ->assertJsonValidationErrors(['title']);
    }

    // -------------------------------------------------------------------------
    // Business rule: status "done" cannot have a future due_date
    // -------------------------------------------------------------------------

    public function test_store_rejects_done_task_with_future_due_date(): void
    {
        $payload = [
            'title'    => 'Zaključena naloga',
            'status'   => 'done',
            'priority' => 'low',
            'due_date' => now()->addDays(10)->toDateString(),
        ];

        $this->postJson('/api/tasks', $payload)
             ->assertUnprocessable()
             ->assertJsonValidationErrors(['due_date']);
    }

    public function test_store_allows_done_task_with_past_due_date(): void
    {
        $payload = [
            'title'    => 'Zaključena naloga',
            'status'   => 'done',
            'priority' => 'low',
            'due_date' => now()->subDays(1)->toDateString(),
        ];

        $this->postJson('/api/tasks', $payload)->assertCreated();
    }

    public function test_store_allows_done_task_without_due_date(): void
    {
        $this->postJson('/api/tasks', [
            'title'    => 'Zaključena brez roka',
            'status'   => 'done',
            'priority' => 'medium',
        ])->assertCreated();
    }

    // -------------------------------------------------------------------------
    // PUT /api/tasks/{id}
    // -------------------------------------------------------------------------

    public function test_update_modifies_task(): void
    {
        $task = Task::create(['title' => 'Old title', 'status' => 'todo', 'priority' => 'low']);

        $this->putJson("/api/tasks/{$task->id}", ['title' => 'New title'])
             ->assertOk()
             ->assertJsonFragment(['title' => 'New title']);

        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'title' => 'New title']);
    }

    public function test_update_rejects_done_status_with_future_due_date(): void
    {
        $task = Task::create(['title' => 'Task', 'status' => 'todo', 'priority' => 'medium']);

        $this->putJson("/api/tasks/{$task->id}", [
            'status'   => 'done',
            'due_date' => now()->addDays(5)->toDateString(),
        ])->assertUnprocessable()
          ->assertJsonValidationErrors(['due_date']);
    }

    public function test_update_returns_404_for_missing_task(): void
    {
        $this->putJson('/api/tasks/9999', ['title' => 'X'])->assertNotFound();
    }

    // -------------------------------------------------------------------------
    // DELETE /api/tasks/{id}
    // -------------------------------------------------------------------------

    public function test_destroy_deletes_task(): void
    {
        $task = Task::create(['title' => 'To delete', 'status' => 'todo', 'priority' => 'low']);

        $this->deleteJson("/api/tasks/{$task->id}")->assertNoContent();

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function test_destroy_returns_404_for_missing_task(): void
    {
        $this->deleteJson('/api/tasks/9999')->assertNotFound();
    }
}
