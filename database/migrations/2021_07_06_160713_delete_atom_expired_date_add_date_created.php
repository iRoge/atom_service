<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DeleteAtomExpiredDateAddDateCreated extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('atoms', function (Blueprint $table) {
            $table->timestamp('created_at')->nullable();
            $table->dropColumn('expired_at');
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
            $table->dropColumn('created_at');
            $table->timestamp('expired_at')->nullable();
        });
    }
}
