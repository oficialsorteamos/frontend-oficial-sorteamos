<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('man_tags', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('type_tag_id')->comment('Tipo de tag (Contato, Agenda, etc)');
            $table->string('tag_name')->comment('Nome da tag');
            $table->string('tag_description')->comment('Descrição da tag');
            $table->string('tag_color')->comment('Cor da tag');
            $table->string('tag_status')->default('A')->comment('Status da tag.');
            $table->timestamps();

            $table->foreign('type_tag_id')
                ->references('id')
                ->on('man_type_tags')
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
        Schema::dropIfExists('man_tags');
    }
}
