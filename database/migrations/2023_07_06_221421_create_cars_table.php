<?php

use App\Enums\CarProviderEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cars', function (Blueprint $table) {
            $table->id();
            $table->string('brand');
            $table->string('model');
            $table->string('version')->nullable();
            $table->integer('year');
            $table->integer('year_model')->nullable();
            $table->float('price');
            $table->bigInteger('odometer');
            $table->string('state', 2);
            $table->string('city');
            $table->enum('provider', CarProviderEnum::getValues());
            $table->string('provider_id');
            $table->timestamp('provider_updated_at');
            $table->string('provider_url')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cars');
    }
};
