<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\LeadStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property LeadStatus $status
 * @property bool $has_trade_in
 * @property bool $wants_financing
 * @property string $phone
 * @property string $name
 * @property-read Vehicle|null $vehicle
 * @property-read User|null $assignedUser
 */
class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'assigned_to',
        'name',
        'phone',
        'email',
        'has_trade_in',
        'wants_financing',
        'message',
        'status',
        'internal_notes',
    ];

    protected function casts(): array
    {
        return [
            'has_trade_in' => 'boolean',
            'wants_financing' => 'boolean',
            'status' => LeadStatus::class,
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function getWhatsappUrlAttribute(): string
    {
        $digits = preg_replace('/\D+/', '', $this->phone) ?? '';

        if (strlen($digits) <= 11 && ! str_starts_with($digits, '55')) {
            $digits = '55'.$digits;
        }

        $vehicleTitle = $this->vehicle !== null
            ? "{$this->vehicle->brand} {$this->vehicle->model}"
            : 'nossos veículos';

        $text = urlencode("Olá {$this->name}! Recebemos sua proposta referente ao {$vehicleTitle} na AutoPrime Seminovos.");

        return "https://wa.me/{$digits}?text={$text}";
    }
}
