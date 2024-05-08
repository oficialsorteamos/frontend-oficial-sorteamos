<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCallsDataToVoiCallsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('voi_calls', function (Blueprint $table) {
            $table->string('cal_call_api_id')->nullable()->comment('Id da ligação na API')->after('id');
            $table->boolean('cal_has_record')->nullable()->comment('Marca se existe gravação associada a ligação na API')->after('cal_call_date');

            $table->text('cal_call_time')->nullable()->change();
            $table->text('cal_record_name')->nullable()->change();

        });
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('voi_calls', function (Blueprint $table) {
            $table->dropColumn('cal_call_api_id');
            $table->dropColumn('cal_has_record');

            $table->text('cal_call_time')->nullable(false)->change();
            $table->text('cal_record_name')->nullable(false)->change();
        });
    }
}
