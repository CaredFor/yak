<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConversationStatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('conversation_states', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('conversation_id');

            if (Schema::getColumnType('users', 'id') === 'string') {
                $table->uuid('user_id');
            } else {
                $table->unsignedInteger('user_id');
            }

            $table->boolean('read')->default(true);
            $table->timestamp('last_read_at')->nullable();
            $table->timestamps();

            $table->foreign('conversation_id')->references('id')->on('conversations')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('conversation_states');
    }
}
