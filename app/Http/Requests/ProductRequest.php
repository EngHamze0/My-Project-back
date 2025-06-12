<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
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
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'type' => ['required', Rule::in(['battery', 'solar_panel', 'inverter'])],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'primary_image_index' => 'nullable|integer|min:0',
        ];
        
        // Add type-specific validation rules for specifications
        if ($this->type === 'battery') {
            $rules['specifications.capacity'] = 'required|numeric|min:0';
            $rules['specifications.voltage'] = 'required|numeric|min:0';
            $rules['specifications.chemistry'] = 'required|string';
            $rules['specifications.cycle_life'] = 'nullable|numeric|min:0';
            $rules['specifications.dimensions'] = 'nullable|string';
            $rules['specifications.weight'] = 'nullable|numeric|min:0';
            $rules['specifications.brand'] = 'nullable|string';
        } elseif ($this->type === 'solar_panel') {
            $rules['specifications.power'] = 'required|numeric|min:0';
            $rules['specifications.voltage'] = 'required|numeric|min:0';
            $rules['specifications.current'] = 'required|numeric|min:0';
            $rules['specifications.dimensions'] = 'nullable|string';
            $rules['specifications.weight'] = 'nullable|numeric|min:0';
            $rules['specifications.cell_type'] = 'nullable|string';
            $rules['specifications.efficiency'] = 'nullable|numeric|min:0|max:100';
            $rules['specifications.brand'] = 'nullable|string';
        } elseif ($this->type === 'inverter') {
            $rules['specifications.power'] = 'required|numeric|min:0';
            $rules['specifications.input_voltage'] = 'required|numeric|min:0';
            $rules['specifications.output_voltage'] = 'required|numeric|min:0';
            $rules['specifications.efficiency'] = 'nullable|numeric|min:0|max:100';
            $rules['specifications.dimensions'] = 'nullable|string';
            $rules['specifications.weight'] = 'nullable|numeric|min:0';
            $rules['specifications.type'] = 'nullable|string';
            $rules['specifications.brand'] = 'nullable|string';
        }
        
        return $rules;
    }
    
    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'اسم المنتج مطلوب',
            'price.required' => 'سعر المنتج مطلوب',
            'price.numeric' => 'يجب أن يكون السعر رقماً',
            'price.min' => 'يجب أن يكون السعر أكبر من أو يساوي صفر',
            'quantity.required' => 'الكمية مطلوبة',
            'quantity.integer' => 'يجب أن تكون الكمية عدداً صحيحاً',
            'quantity.min' => 'يجب أن تكون الكمية أكبر من أو تساوي صفر',
            'type.required' => 'نوع المنتج مطلوب',
            'type.in' => 'نوع المنتج غير صالح',
            'status.in' => 'حالة المنتج غير صالحة',
            'images.array' => 'يجب أن تكون الصور عبارة عن مصفوفة',
            'images.*.image' => 'يجب أن يكون الملف صورة',
            'images.*.mimes' => 'يجب أن تكون الصورة من نوع: jpeg, png, jpg, gif',
            'images.*.max' => 'يجب ألا يتجاوز حجم الصورة 2 ميجابايت',
            'primary_image_index.integer' => 'يجب أن يكون مؤشر الصورة الرئيسية رقمًا صحيحًا',
            'primary_image_index.min' => 'يجب أن يكون مؤشر الصورة الرئيسية أكبر من أو يساوي صفر',
            
            // Battery specifications
            'specifications.capacity.required' => 'سعة البطارية مطلوبة',
            'specifications.voltage.required' => 'جهد البطارية مطلوب',
            'specifications.chemistry.required' => 'نوع كيمياء البطارية مطلوب',
            
            // Solar panel specifications
            'specifications.power.required' => 'قدرة اللوح الشمسي مطلوبة',
            'specifications.voltage.required' => 'جهد اللوح الشمسي مطلوب',
            'specifications.current.required' => 'تيار اللوح الشمسي مطلوب',
            
            // Inverter specifications
            'specifications.power.required' => 'قدرة المحول مطلوبة',
            'specifications.input_voltage.required' => 'جهد الدخل للمحول مطلوب',
            'specifications.output_voltage.required' => 'جهد الخرج للمحول مطلوب',
        ];
    }
} 