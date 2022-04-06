<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddComissionFlagToAtomHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('atom_history', function (Blueprint $table) {
            $table->boolean('commission')->nullable(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('atom_history', function (Blueprint $table) {
            $table->dropColumn('commission');
        });
    }
}
