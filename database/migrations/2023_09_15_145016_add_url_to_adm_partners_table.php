<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUrlToAdmPartnersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('adm_partners', function (Blueprint $table) {
            $table->string('par_url')->nullable()->comment('URL do ambiente do parceiro')->after('par_corporate_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('adm_partners', function (Blueprint $table) {
            $table->dropColumn('par_url');
        });
    }
}
