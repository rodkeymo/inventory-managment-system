<?php

namespace App\Http\Requests\Product;

use Illuminate\Support\Str;
use Illuminate\Foundation\Http\FormRequest;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use App\Models\Product;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'product_image'     => 'image|file|max:2048',
            'name'              => 'required|string',
            'slug'              => 'required|unique:products',
            'category_id'       => 'required|integer',
            'unit_id'           => 'required|integer',
            'quantity'          => 'required|integer',
            'buying_price'      => 'required|integer',
            'selling_price'     => 'required|integer',
            'quantity_alert'    => 'required|integer',
            'tax'               => 'nullable|numeric',
            'tax_type'          => 'nullable|integer',
            'notes'             => 'nullable|max:1000'
        ];
    }

    /**
     * Prepare the data before validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => Str::slug($this->name, '-'),
            'code' => $this->generateUniqueProductCode()
        ]);
    }

    /**
     * Generate a unique product code.
     *
     * @return string
     */
    private function generateUniqueProductCode(): string
    {
        $prefix = 'PC';
        $uniqueCode = $prefix . str_pad(mt_rand(100000, 999999), 6, '0', STR_PAD_LEFT);

        // Check if the generated code already exists
        while (Product::where('code', $uniqueCode)->exists()) {
            // If it exists, regenerate the code
            $uniqueCode = $prefix . hexdec(uniqid());
        }

        return $uniqueCode;
    }
}
