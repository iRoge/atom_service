<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAtomHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('atom_history', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('parent_id')->nullable()->index('atom_history_atom_history_id_fk');
            $table->integer('atom_id')->index('atom_history_atoms_id_fk');
            $table->smallInteger('admin_id');
            $table->integer('place_id');
            $table->tinyInteger('place_type')->nullable();
            $table->timestamp('created_at')->useCurrent()->nullable();
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
        Schema::dropIfExists('atom_history');
    }
}
