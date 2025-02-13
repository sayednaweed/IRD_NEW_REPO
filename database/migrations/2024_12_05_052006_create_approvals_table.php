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
        Schema::create('approvals', function (Blueprint $table) {
            $table->id();
            $table->string("request_comment")->nullable();
            $table->string("request_date");
            $table->string("respond_comment")->nullable();
            $table->string("respond_date")->nullable();
            $table->boolean("approved")->default(false);
            $table->unsignedBigInteger('requester_id')->comment("Person ID who sends the request");
            $table->string('requester_type')->comment("The Requester Model class name");
            $table->unsignedBigInteger('responder_id')->comment("Person ID who responds the request")->nullable();
            $table->string('responder_type')->comment("The responder Model class name")->nullable();
            $table->unsignedBigInteger('request_type_id');
            $table->foreign('request_type_id')->references('id')->on('request_types')
                ->onUpdate('cascade')
                ->onDelete('no action');
            $table->index(
                [
                    'requester_id',
                    'requester_type',
                    'request_type_id',
                ],
                'approvable_approve_idx'
            );
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approvals');
    }
};
