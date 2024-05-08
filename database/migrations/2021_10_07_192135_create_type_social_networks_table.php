<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTypeSocialNetworksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('con_type_social_networks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('typ_name')->comment('Nome da rede social');
            $table->string('typ_status')->default('A')->comment('Status da rede social.');
            $table->timestamps();
        });

        // Insert default values
        DB::table('con_type_social_networks')->insert(
            array(
                [
                    'typ_name' => 'Instagram',
                ],
                [
                    'typ_name' => 'Facebook',
                ],
                [
                    'typ_name' => 'Twitter',
                ],
            )
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('con_type_social_networks');
    }
}
