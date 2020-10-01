<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAgentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('agents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->unique();
            $table->string('physical_address');
            $table->string('postal_address');
            $table->unsignedInteger('postal_code_id')->nullable();
            $table->string('agent_type');
            $table->string('crp')->nullable();
            $table->string('secret')->unique();
            $table->decimal('limit', 13, 2)->default(0.00);
            $table->decimal('balance', 13, 2)->default(0.00);
            $table->unsignedInteger('group_id')->nullable();
            $table->foreign('group_id')->references('id')->on('groups');
            $table->string('customerid')->nullable();
            $table->string('account')->nullable();
            $table->boolean('active')->default(1);
            $table->unsignedBigInteger('created_by')->index()->nullable();
            $table->foreign('created_by')->references('id')->on('users');
            $table->softDeletes();
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
        Schema::dropIfExists('agents');
    }
}
