<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateComprobanteItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('comprobante_items')){
            Schema::create('comprobante_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('comprobante_id');
                $table->foreign('comprobante_id')->references('id')->on('comprobantes')->onDelete('cascade');
                $table->string('productoName');
                $table->float('productoPrecio', 10, 3);
                $table->timestamps();
            });
        }
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('comprobante_items');
    }
}
