<?php

declare(strict_types=1);

namespace App\Http\Requests\Public;

use App\Enums\FuelType;
use App\Enums\TransmissionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VehicleFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:100'],
            'fuel' => ['nullable', Rule::enum(FuelType::class)],
            'transmission' => ['nullable', Rule::enum(TransmissionType::class)],
            'max_price' => ['nullable', 'numeric', 'min:0'],
            'min_year' => ['nullable', 'integer', 'min:1950', 'max:'.((int) date('Y') + 1)],
            'sort' => ['nullable', Rule::in(['featured', 'price_asc', 'price_desc', 'newest'])],
        ];
    }
}
