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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->constrained('cylinder_brands');
            $table->foreignId('cylinder_type_id')->constrained('cylinder_types');
            $table->morphs('source');
            $table->foreignId('customer_id')->nullable()->constrained('customers');
            // Set on delivery rows: which booking this stock went out for
            $table->foreignId('booking_id')->nullable()->constrained('bookings');
            $table->string('movement_type');
            $table->string('condition', 10)->default('full')->comment('full, empty, damaged');
            $table->integer('quantity');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['brand_id', 'cylinder_type_id', 'condition']);
            $table->index('movement_type');
            $table->index(['source_id', 'source_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
