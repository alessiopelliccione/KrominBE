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
        Schema::dropIfExists('todos');
        Schema::create('todos', function (Blueprint $table) {
            $table->id();
            $table->string('content');
            $table->integer('position')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->enum('status', ['pending','done'])->default('pending');
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('todos');
    }
};
