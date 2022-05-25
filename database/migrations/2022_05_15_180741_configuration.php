<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Configuration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('configuration', function(Blueprint $table) {
            $table->increments('id');
            $table->string('website_name')->nullable();
            $table->string('modepro_admin')->nullable();
            $table->string('description_website')->nullable();
            $table->string('licencekey')->default(null)->nullable();
            $table->string('cg')->default("1");
            $table->string('linkcg')->nullable($value = true);
            $table->string('favicon')->nullable($value = true);
            $table->integer('sp_status')->default(1);
            $table->string('sp')->default('Coin');
            $table->string('sps')->default('Coins');
            $table->integer('captcha')->default(0);
            $table->string('maintenance_desc')->nullable();
            $table->text('maintenance_uri')->nullable();
            $table->text('tmpapi_pwd')->nullable();
            $table->integer('useractions_mode')->default(1);
            $table->engine = 'InnoDB';
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
        Schema::dropIfExists('configuration');
    }
}
