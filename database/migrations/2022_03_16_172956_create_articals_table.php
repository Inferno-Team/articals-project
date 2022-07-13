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
        Schema::create('articals', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('field_id')->references('id')->on('fields');
            $table->foreignId('doctor_id')->nullable()->references('id')->on('users');
            $table->enum('type', ['artical', 'research']);
            $table->string('university_name');
            $table->foreignId('writer_id')->references('id')->on('users');
            $table->string('file_url')->nullable();
          
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
        Schema::dropIfExists('articals');
    }
};
