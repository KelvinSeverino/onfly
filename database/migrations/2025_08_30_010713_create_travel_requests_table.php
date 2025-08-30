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
        Schema::create('travel_requests', function (Blueprint $table) {
            $table->id();            
            $table->unsignedBigInteger('requester_id')->nullable();
            $table->unsignedBigInteger('travel_status_id')->nullable();

            $table->string('requester_name');
            $table->string('destination');
            $table->datetime('departure_date');
            $table->datetime('return_date');
            $table->timestamps();

            $table->foreign('requester_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('travel_status_id')->references('id')->on('travel_statuses')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('travel_requests');
    }
};
