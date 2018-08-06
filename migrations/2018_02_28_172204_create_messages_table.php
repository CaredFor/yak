<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->uuid('id')->primary();

            if (Schema::getColumnType('users', 'id') === 'string') {
                $table->uuid('author_id');
            } else {
                $table->unsignedInteger('author_id');
            }

            $table->text('body');
            $table->uuid('conversation_id');
            $table->text('message_type')->default('default');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->foreign('author_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('conversation_id')->references('id')->on('conversations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign('messages_author_id_foreign');
            $table->dropForeign('messages_conversation_id_foreign');
        });

        Schema::dropIfExists('messages');
    }
}
