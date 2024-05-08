<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDocumentIdToSetWhiteLabelTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('set_white_label', function (Blueprint $table) {
            $table->string('whi_document_number')->nullable()->comment('Número do documento da empresa ou pessoa física (CNPJ ou CPF)')->after('whi_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('set_white_label', function (Blueprint $table) {
            $table->dropColumn('whi_document_number');
        });
    }
}
