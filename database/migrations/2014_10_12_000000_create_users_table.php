<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('firstname')->nullable();
            $table->string('middlename')->nullable();
            $table->string('lastname')->nullable();
            $table->string('email')->unique();
            $table->string('phone_number')->nullable();
            $table->string('id_number')->unique()->nullable();
            $table->string('kra_pin')->unique()->nullable();
            $table->string('user_unique_id')->unique()->nullable();
            $table->string('password')->nullable();
            $table->string('signup_platform')->default('Web');
            $table->boolean('verified_otp')->default(0);
            $table->boolean('verified_phone')->default(0);
            $table->timestamp('email_verified_at')->nullable();
            $table->boolean('active')->default(1);
            $table->boolean('create_by_admin')->default(0);
            $table->string('referral_code')->nullable();
            $table->bigInteger('parent')->unsigned()->nullable();
            $table->foreign('parent')->references('id')->on('users');
            $table->boolean('requires_password_change')->default(0);
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
