<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'type' => 'sometimes|required|in:Residential Design,Hospitality Design,Office Design,Commercial Design',
            'price' => 'nullable|numeric|min:0',
            'min_price' => 'nullable|numeric|min:0|required_with:max_price|lte:max_price',
            'max_price' => 'nullable|numeric|min:0|gte:min_price',
            'image1' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
            'image2' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Service title is required',
            'description.required' => 'Service description is required',
            'type.required' => 'Service type is required',
            'type.in' => 'Invalid service type. Must be one of: Residential Design, Hospitality Design, Office Design, Commercial Design',
            'price.numeric' => 'Price must be a valid number',
            'min_price.numeric' => 'Minimum price must be a valid number',
            'min_price.lte' => 'Minimum price must be less than or equal to maximum price',
            'max_price.numeric' => 'Maximum price must be a valid number',
            'max_price.gte' => 'Maximum price must be greater than or equal to minimum price',
            'image1.image' => 'Primary image must be a valid image file',
            'image1.max' => 'Primary image must not exceed 5MB',
            'image2.image' => 'Secondary image must be a valid image file',
            'image2.max' => 'Secondary image must not exceed 5MB',
        ];
    }
}