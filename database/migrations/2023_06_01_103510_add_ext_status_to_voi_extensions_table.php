<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExtStatusToVoiExtensionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('voi_extensions', function (Blueprint $table) {
            $table->string('ext_status')->default('A')->comment('Status do ramal no ChatX')->after('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('voi_extensions', function (Blueprint $table) {
            $table->dropColumn('ext_status');
        });
    }
}
