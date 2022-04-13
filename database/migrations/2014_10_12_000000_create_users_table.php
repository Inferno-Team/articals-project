<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('password');
            $table->foreignId('field_id')->nullable()->references('id')->on('fields');
            $table->enum('type', ['admin', 'doctor', 'master', 'normal'])->default('normal');
            $table->enum('approved', ['yes', 'no', 'waiting'])->default('waiting');
            $table->rememberToken();
            $table->timestamps();
        });
        DB::table('users')->insert([
            'first_name' => 'admin',
            'last_name' => 'admin',
            'email' => 'admin@artical.com',
            'password' => Hash::make('admin1234'),
            'approved' => 'yes',
            'type' => 'admin',
            'create_at' => now()
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};
