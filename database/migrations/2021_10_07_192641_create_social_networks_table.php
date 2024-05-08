<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSocialNetworksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('con_social_networks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('contact_id')->comment('Id do contato proprietário do endereço da rede social');
            $table->unsignedBigInteger('type_social_network_id')->comment('Id do tipo de rede social (Facebook, Instagram, etc.)');
            $table->string('soc_address')->comment('Link da rede social do contato');
            $table->string('soc_status')->default('A')->comment('Status da rede social do contato.');
            $table->timestamps();

            $table->foreign('contact_id')
                    ->references('id')
                    ->on('con_contacts')
                    ->onDelete('cascade');

            $table->foreign('type_social_network_id')
                    ->references('id')
                    ->on('con_type_social_networks')
                    ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('con_social_networks');
    }
}
