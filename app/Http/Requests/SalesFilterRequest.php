<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SalesFilterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Авторизация уже проверяется в middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'dateFrom' => 'nullable|date|before_or_equal:dateTo',
            'dateTo' => 'nullable|date|after_or_equal:dateFrom',
            'category' => 'nullable|string|max:255',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'dateFrom.before_or_equal' => 'Дата "от" должна быть раньше или равна дате "до"',
            'dateTo.after_or_equal' => 'Дата "до" должна быть позже или равна дате "от"',
            'per_page.max' => 'Максимальное количество записей на странице: 100',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'dateFrom' => 'дата от',
            'dateTo' => 'дата до',
            'category' => 'категория',
            'page' => 'страница',
            'per_page' => 'количество на странице',
        ];
    }
}
