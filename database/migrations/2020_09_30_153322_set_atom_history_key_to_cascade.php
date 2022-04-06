<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SetAtomHistoryKeyToCascade extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('
            alter table atom_history drop foreign key atom_history_atom_history_id_fk
        ');
        DB::statement('
            alter table atom_history
            add constraint atom_history_atom_history_id_fk
            foreign key (parent_id) references atom_history (id)
            on update cascade on delete cascade;
        ');


        DB::statement('
            alter table atom_history drop foreign key atom_history_atoms_id_fk;
        ');
        DB::statement('
            alter table atom_history
	        add constraint atom_history_atoms_id_fk
		    foreign key (atom_id) references atoms (id)
			on update cascade on delete cascade;
        ');

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cascade', function (Blueprint $table) {
            //
        });
    }
}
