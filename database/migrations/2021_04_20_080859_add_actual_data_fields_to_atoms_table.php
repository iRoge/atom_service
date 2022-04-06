<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddActualDataFieldsToAtomsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('atoms', function (Blueprint $table) {
            $table->smallInteger('admin_id')->nullable(false);
            $table->integer('place_id')->nullable(false);
            $table->tinyInteger('place_type')->nullable(false);
            $table->unsignedSmallInteger('nds_bid')->nullable();
            $table->boolean('commission')->nullable(false);
            $table->timestamp('updated_at')->useCurrent()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('atoms', function (Blueprint $table) {
            $table->dropColumn('admin_id');
            $table->dropColumn('place_id');
            $table->dropColumn('place_type');
            $table->dropColumn('nds_bid');
            $table->dropColumn('commission');
            $table->dropColumn('updated_at');
        });
    }
}
