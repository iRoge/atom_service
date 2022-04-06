<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsSellingPriceAndPurchaseInvoiceItemId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('atoms', function (Blueprint $table) {
            $table->bigInteger('purchase_invoice_item_id')->nullable();
            $table->float('selling_price')->nullable();
            $table->integer('event_id')->nullable(false);
        });
        Schema::table('atom_history', function (Blueprint $table) {
            $table->float('selling_price')->nullable();
            $table->integer('event_id')->nullable(false);
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
            $table->dropColumn('purchase_invoice_item_id');
            $table->dropColumn('selling_price');
            $table->dropColumn('event_id');
        });
        Schema::table('atom_history', function (Blueprint $table) {
            $table->dropColumn('selling_price');
            $table->dropColumn('event_id');
        });
    }
}
