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
        Schema::create('river_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('station_id')->constrained('stations')->onDelete('cascade');
            $table->decimal('nivel', 8, 3)->nullable()->comment('Nível do rio em metros');
            $table->decimal('vazao', 10, 3)->nullable()->comment('Vazão em m³/s');
            $table->decimal('chuva', 8, 2)->nullable()->comment('Precipitação em mm');
            $table->timestamp('data_medicao');
            $table->timestamps();

            $table->index(['station_id', 'data_medicao']);
            $table->index('data_medicao');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('river_data');
    }
};
