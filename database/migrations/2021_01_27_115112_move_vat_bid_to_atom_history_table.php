<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class MoveVatBidToAtomHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::table('atoms', function (Blueprint $table) {
			$table->dropColumn('nds_bid');
		});
		Schema::table('atom_history', function (Blueprint $table) {
			  $table->unsignedSmallInteger('nds_bid')->nullable();
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
