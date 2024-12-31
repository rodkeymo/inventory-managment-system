<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('stock_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id'); // Reference to the product
            $table->string('product_name');          // Store product name for easy access
            $table->integer('current_quantity');     // Current stock quantity
            $table->integer('alert_threshold');      // Alert threshold
            $table->boolean('read')->default(false); // Track read/unread status
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('stock_notifications');
    }
};
