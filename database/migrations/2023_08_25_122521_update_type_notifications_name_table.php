<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTypeNotificationsNameTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('migrations', function (Blueprint $table) {

            DB::table('migrations')
                ->where('migration','2023_08_25_122646_create_type_notifications_table')
                ->update([
                    "migration" => "2023_08_25_122646_create_adm_type_notifications_table"
            ]);
        });
    }
}
