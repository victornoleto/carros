<?php

use App\Models\OlxCar;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('olx_car_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(OlxCar::class)->constrained();
            $table->float('price');
            $table->float('old_price');
            $table->float('diff');
            $table->timestamp('olx_updated_at');
            $table->timestamp('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('olx_car_prices');
    }
};
