<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateComprobantes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('comprobantes')){
            Schema::create('comprobantes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->dateTime('fechaEmision');
                $table->string('nameEmisor');
                $table->string('rucEmisor');
                $table->string('nameReceptor');
                $table->string('rucReceptor');
                $table->float('ventaTotal', 10, 3);
                $table->float('ventaTotalImpuesto', 10, 3);
                $table->float('otrosPagos', 10, 3);
                $table->float('importeTotal', 10, 3);
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
        Schema::dropIfExists('comprobantes');
    }
}
