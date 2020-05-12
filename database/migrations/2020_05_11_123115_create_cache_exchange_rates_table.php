<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCacheExchangeRatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cache_exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->char('currency_from', 3);
            $table->char('currency_to', 3);
            $table->decimal('rate', 18, 10)->unsigned();
            $table->timestamp('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cache_exchange_rates');
    }
}
