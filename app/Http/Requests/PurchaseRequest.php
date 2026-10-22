<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'purchase_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'At least one product item must be added to the purchase.',
            'items.min' => 'At least one product item must be added to the purchase.',
            'items.*.product_id.required' => 'Please select a product for each line.',
            'items.*.quantity.min' => 'Quantity must be at least 1.',
            'items.*.unit_cost.min' => 'Unit cost must be at least 0.',
        ];
    }
}
