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
        Schema::create('pending_task_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pending_task_id');
            $table->foreign('pending_task_id')->references('id')->on('pending_tasks')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->unsignedBigInteger('check_list_id')->nullable();
            $table->foreign('check_list_id')->references('id')->on('check_lists')
                ->onUpdate('cascade')
                ->onDelete('no action');
            $table->string('size', 32);
            $table->string('path', 256);
            $table->string('actual_name', 64);
            $table->string('extension', 32);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pending_task_documents');
    }
};
