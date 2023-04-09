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
        Schema::dropIfExists('listas');
        Schema::create('listas', function (Blueprint $table) {
            $table->id()->unique();
            $table->string('shortcode')->unique()->nullable();
            $table->enum('tipologia', ['pubblica','privata'])->default('privata');
            $table->foreignId('user_id')->constrained('users');
            $table->string('list_name')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('listas');
    }
};