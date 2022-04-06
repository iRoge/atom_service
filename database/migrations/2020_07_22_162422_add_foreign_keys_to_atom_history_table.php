<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToAtomHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('atom_history', function (Blueprint $table) {
            $table->foreign('parent_id', 'atom_history_atom_history_id_fk')->references('id')->on('atom_history')->onUpdate('NO ACTION')->onDelete('NO ACTION');
            $table->foreign('atom_id', 'atom_history_atoms_id_fk')->references('id')->on('atoms')->onUpdate('NO ACTION')->onDelete('NO ACTION');
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
            $table->dropForeign('atom_history_atom_history_id_fk');
            $table->dropForeign('atom_history_atoms_id_fk');
        });
    }
}
