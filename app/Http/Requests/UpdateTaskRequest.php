<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'       => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'status'      => 'sometimes|required|in:todo,in_progress,done',
            'priority'    => 'sometimes|required|in:low,medium,high',
            'due_date'    => 'nullable|date',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $task = $this->route('task');

            // Resolve effective status and due_date (merge existing with incoming)
            $status  = $this->input('status', $task->status);
            $dueDate = array_key_exists('due_date', $this->all())
                ? $this->input('due_date')
                : $task->due_date?->toDateString();

            if ($status === 'done' && $dueDate && now()->parse($dueDate)->isFuture()) {
                $validator->errors()->add(
                    'due_date',
                    'Task with status "done" cannot have a due date in the future.'
                );
            }
        });
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'message' => 'Validation failed.',
                'errors'  => $validator->errors(),
            ], 422)
        );
    }
}
