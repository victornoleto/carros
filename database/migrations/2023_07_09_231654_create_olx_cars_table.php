<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('olx_cars', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('olx_id')->unique();
            $table->string('brand');
            $table->string('model');
            $table->string('version')->nullable();
            $table->string('color')->nullable();
            //$table->string('title');
            $table->text('url');
            $table->bigInteger('odometer');
            $table->integer('year');
            $table->integer('price');
            $table->integer('old_price')->nullable();
            $table->string('state');
            $table->string('city');
            $table->timestamp('olx_updated_at');
            $table->boolean('active')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('olx_cars');
    }
};
