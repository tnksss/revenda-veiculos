<?php



declare(strict_types=1);

namespace App\Models;

use App\Enums\FuelType;
use App\Enums\TransmissionType;
use App\Enums\VehicleStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Observers\VehicleObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy([VehicleObserver::class])]
class Vehicle extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'brand',
        'model',
        'version',
        'slug',
        'year_manufacture',
        'year_model',
        'price',
        'cost_price',
        'mileage',
        'plate',
        'fuel_type',
        'transmission',
        'color',
        'status',
        'is_featured',
        'features',
        'description',
        'internal_notes',
    ];

    protected $hidden = [
        'plate',
        'cost_price',
        'internal_notes',
    ];

    protected function casts(): array
    {
        return [
            'year_manufacture' => 'integer',
            'year_model' => 'integer',
            'price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'mileage' => 'integer',
            'fuel_type' => FuelType::class,
            'transmission' => TransmissionType::class,
            'status' => VehicleStatus::class,
            'is_featured' => 'boolean',
            'features' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Vehicle $vehicle): void {
            $vehicle->plate = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $vehicle->plate));

            if (empty($vehicle->slug)) {
                $baseSlug = Str::slug("{$vehicle->brand}-{$vehicle->model}-{$vehicle->version}-{$vehicle->year_model}");
                $vehicle->slug = "{$baseSlug}-" . Str::lower(Str::random(5));
            }
        });
    }

    protected function plateEnding(): Attribute
    {
        return Attribute::get(
            fn (): string => substr((string) $this->plate, -1)
        );
    }

    protected function fullTitle(): Attribute
    {
        return Attribute::get(
            fn (): string => "{$this->brand} {$this->model} {$this->version}"
        );
    }

    public function photos(): HasMany
    {
        return $this->hasMany(VehiclePhoto::class)->orderBy('sort_order');
    }

    public function coverPhoto(): HasOne
    {
        return $this->hasOne(VehiclePhoto::class)->orderBy('sort_order');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(VehicleLog::class)->latest('created_at');
    }

    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query->whereIn('status', [
            VehicleStatus::Available->value,
            VehicleStatus::Reserved->value,
        ]);
    }
}