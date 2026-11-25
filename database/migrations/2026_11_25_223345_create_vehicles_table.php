<?php

declare(strict_types=1);

use App\Enums\VehicleStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('brand');
            $table->string('model');
            $table->string('version');
            $table->string('slug')->unique();
            $table->unsignedSmallInteger('year_manufacture');
            $table->unsignedSmallInteger('year_model');
            $table->decimal('price', 12, 2);
            $table->decimal('cost_price', 12, 2)->nullable(); // Uso interno restrito
            $table->unsignedInteger('mileage')->default(0);
            $table->string('plate', 10)->unique(); // Uso interno + extração de final
            $table->string('fuel_type');
            $table->string('transmission');
            $table->string('color');
            $table->string('status')->default(VehicleStatus::Preparation->value)->index();
            $table->boolean('is_featured')->default(false)->index();
            $table->json('features')->nullable();
            $table->text('description')->nullable();
            $table->text('internal_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};