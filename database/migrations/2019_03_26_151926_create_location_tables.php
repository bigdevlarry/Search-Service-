<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLocationTables extends Migration
{
    public function up(): void
    {
        Schema::create('parking_spaces', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('lat', 8, 5);
            $table->decimal('lng', 8, 5);
            $table->string('space_details');
            $table->string('city');
            $table->string('street_name');
            $table->integer('no_of_spaces');
            $table->timestamps();

            $table->index(['lat', 'lng']);
        });

        Schema::create('park_and_rides', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->foreignId('user_id')->constrained()
                ->onDelete('cascade');
            $table->decimal('lat', 8, 5);
            $table->decimal('lng', 8, 5);
            $table->string('attraction_name');
            $table->string('location_description');
            $table->integer('minutes_to_destination');
            $table->timestamps();

            $table->index(['lat', 'lng']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listings');
        Schema::dropIfExists('park_and_rides');
    }
}
