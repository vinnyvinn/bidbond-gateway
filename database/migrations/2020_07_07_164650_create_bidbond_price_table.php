<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBidbondPriceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bidbond_prices', function (Blueprint $table) {
            $table->increments('id');
            $table->decimal('lower_bound', 18, 2);
            $table->decimal('upper_bound', 18, 2);
            $table->decimal('cost', 18, 2);
            $table->integer('duration')->nullable();
            $table->string('charge_type');
            $table->float('discount',8,4)->default(0);
            $table->string('discount_type');
            $table->unsignedInteger('group_id');
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
        Schema::dropIfExists('bidbond_prices');
    }
}
