<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('facility_services', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_created_id');
            $table->unsignedBigInteger('user_updated_id')->nullable();
            $table->unsignedBigInteger('user_closed_id')->nullable();
            $table->string('doc_number');
            $table->string('flow_type');
            $table->string('room_type')->nullable();
            $table->string('WO_type')->nullable();
            $table->string('company');
            $table->string('location');
            $table->string('phone');
            $table->json('component_id');
            $table->text('request_description')->nullable();
            $table->text('contact_reason')->nullable();
            $table->text('report')->nullable();
            $table->string('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('facility_services');
    }
};
