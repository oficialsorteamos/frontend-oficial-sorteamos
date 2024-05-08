<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateExtensionsColumnToVoiExtensionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('voi_extensions', function (Blueprint $table) {
            $table->unsignedBigInteger('voip_id')->nullable()->change();
            $table->text('host')->nullable()->change();
            $table->text('type')->nullable()->change();

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
            $table->text('voip_id')->nullable(false)->change();
            $table->text('host')->nullable(false)->change();
            $table->text('type')->nullable(false)->change();
        });
    }
}
