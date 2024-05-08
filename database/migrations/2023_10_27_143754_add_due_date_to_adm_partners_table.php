<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDueDateToAdmPartnersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('adm_partners', function (Blueprint $table) {
            $table->string('par_due_date')->nullable()->comment('Dia de vencimento da fatura de um parceiro')->after('par_country');
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
            $table->dropColumn('par_due_date');
        });
    }
}
