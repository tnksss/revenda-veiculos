<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\FuelType;
use App\Enums\TransmissionType;
use App\Enums\VehicleStatus;
use App\Observers\VehicleObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Spatie\Image\Enums\AlignPosition;
use Spatie\Image\Enums\Fit;
use Spatie\Image\Enums\Unit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @property int $id
 * @property string $brand
 * @property string $model
 * @property string $version
 * @property string $slug
 * @property int $year_manufacture
 * @property int $year_model
 * @property int $mileage
 * @property string $price
 * @property string|null $minimum_price
 * @property string|null $cost_price
 * @property string|null $plate
 * @property-read string $plate_ending
 * @property-read string|null $cover_url
 * @property string $color
 * @property TransmissionType $transmission
 * @property FuelType $fuel_type
 * @property VehicleStatus $status
 * @property bool $is_featured
 * @property string|null $description
 * @property array<int, string>|null $features
 * @property string|null $internal_notes
 * @property int|null $created_by
 * @property Carbon|null $sold_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
#[ObservedBy([VehicleObserver::class])]
class Vehicle extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    use SoftDeletes;

    protected $fillable = [
        'brand',
        'model',
        'version',
        'slug',
        'year_manufacture',
        'year_model',
        'mileage',
        'price',
        'minimum_price',
        'cost_price',
        'plate',
        'color',
        'transmission',
        'fuel_type',
        'status',
        'is_featured',
        'description',
        'features',
        'internal_notes',
        'created_by',
        'sold_at',
    ];

    protected $hidden = [
        'plate',
        'cost_price',
        'minimum_price',
        'internal_notes',
    ];

    protected function casts(): array
    {
        return [
            'year_manufacture' => 'integer',
            'year_model' => 'integer',
            'mileage' => 'integer',
            'price' => 'decimal:2',
            'minimum_price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'is_featured' => 'boolean',
            'features' => 'array',
            'transmission' => TransmissionType::class,
            'fuel_type' => FuelType::class,
            'status' => VehicleStatus::class,
            'sold_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Vehicle $vehicle): void {
            if (empty($vehicle->slug)) {
                $baseSlug = Str::slug("{$vehicle->brand} {$vehicle->model} {$vehicle->version} {$vehicle->year_model}");
                $vehicle->slug = "{$baseSlug}-".strtolower(Str::random(6));
            }
        });
    }

    protected function plate(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value): ?string => $value !== null
                ? strtoupper((string) preg_replace('/[^A-Za-z0-9]/', '', $value))
                : null,
        );
    }

    protected function plateEnding(): Attribute
    {
        return Attribute::make(
            get: fn (): string => $this->plate !== null && $this->plate !== ''
                ? substr($this->plate, -1)
                : '*',
        );
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('gallery')
            ->useDisk('public')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->nonQueued()
            ->fit(Fit::Crop, 480, 300)
            ->format('webp');

        $large = $this->addMediaConversion('large')
            ->nonQueued();

        $large->fit(Fit::Max, 1280, 800)
            ->format('webp');

        $watermarkPath = public_path('images/watermark.png');

        if (file_exists($watermarkPath)) {
            $large->watermark(
                watermarkImage: $watermarkPath,
                position: AlignPosition::BottomRight,
                paddingX: 24,
                paddingY: 24,
                paddingUnit: Unit::Pixel,
                width: 22,
                widthUnit: Unit::Percent,
                alpha: 85,
            );
        }
    }

    public function getCoverUrlAttribute(): ?string
    {
        $url = $this->getFirstMediaUrl('gallery', 'thumb');

        return $url !== '' ? $url : null;
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(VehicleLog::class)->latest();
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query->whereIn('status', [
            VehicleStatus::Available->value,
            VehicleStatus::Reserved->value,
        ]);
    }

    public function recordMinimumPriceConsultation(?User $user, ?string $context = null): void
    {
        $consultor = $user !== null ? $user->name : 'Usuário';
        $formattedMin = $this->minimum_price !== null
            ? 'R$ '.number_format((float) $this->minimum_price, 2, ',', '.')
            : 'sem piso cadastrado';

        $suffix = $context !== null && $context !== '' ? " ({$context})" : '';

        $this->logs()->create([
            'user_id' => $user?->id,
            'action' => 'minimum_price_consulted',
            'new_value' => $formattedMin,
            'notes' => "{$consultor} consultou o piso de negociação ({$formattedMin}){$suffix}.",
            'created_at' => now(),
        ]);
    }
}
