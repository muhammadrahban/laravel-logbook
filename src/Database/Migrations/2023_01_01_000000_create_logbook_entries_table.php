<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('logbook_entries', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default('request'); // 'request' or 'event'
            $table->string('method')->nullable();
            $table->string('url', 1000)->nullable();
            $table->string('endpoint')->nullable();
            $table->integer('status_code')->nullable();
            $table->float('response_time')->nullable(); // in milliseconds
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('token_id')->nullable();
            $table->json('request_headers')->nullable();
            $table->json('response_headers')->nullable();
            $table->longText('request_body')->nullable();
            $table->longText('response_body')->nullable();
            $table->string('event_name')->nullable(); // for custom events
            $table->json('event_data')->nullable(); // for custom events
            $table->json('metadata')->nullable();
            $table->timestamp('created_at');
            $table->timestamp('updated_at')->nullable();

            $table->index(['type', 'created_at']);
            $table->index(['method', 'created_at']);
            $table->index(['status_code', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['endpoint', 'created_at']);
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('logbook_entries');
    }
};
