<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $loggedInUser = $this->user();
        $targetUser = $this->route('user');

        if ($loggedInUser->id != $targetUser->id) {
            return $loggedInUser->can('users.edit', $targetUser) &&
                $targetUser->roles->first()->id >= $loggedInUser->roles->first()->id;
        }

        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email,'.$this->route('user')->id,
            ],
            'role' => 'required|exists:roles,id',
            'name' => 'required|string|max:255',
            'password' => 'nullable|string|min:8|max:255',
            'is_active' => 'nullable|boolean',
        ];
    }

    protected function prepareForValidation()
    {
        if (empty($this->input('password'))) {
            $this->offsetUnset('password');
        }

        $this->merge([
            'is_active' => $this->has('is_active'),
        ]);
    }
}
