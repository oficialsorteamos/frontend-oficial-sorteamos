<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContactsTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('con_contacts_tags', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('contact_id')->comment('Id do contato');
            $table->unsignedBigInteger('tag_id')->comment('Id da tag associada ao contato');
            $table->unsignedBigInteger('user_id')->comment('Id da do usuário fixou a tag no contato');
            $table->unsignedBigInteger('campaign_id')->nullable()->comment('Id da da campanha. Preenchido quando o contato é etiquetado durante uma campanha');
            $table->string('con_status')->default('A')->comment('Status da associação contato/tag');
            $table->timestamps();

            $table->foreign('contact_id')
                ->references('id')
                ->on('con_contacts')
                ->onDelete('cascade');
            
            $table->foreign('tag_id')
                ->references('id')
                ->on('man_tags')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            
            $table->foreign('campaign_id')
                ->references('id')
                ->on('cam_campaigns')
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
        Schema::dropIfExists('con_contacts_tags');
    }
}