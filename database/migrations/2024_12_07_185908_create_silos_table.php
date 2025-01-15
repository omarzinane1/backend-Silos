<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('silos', function (Blueprint $table) {
            $table->id();
            $table->string('numsilo', 255)->unique();
            $table->string('produit', 255);
            $table->float('stocki');
            $table->float('entre');
            $table->float('consumation');
            $table->float('stockf');
            $table->string('statut');
            $table->date('datevalidation');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('silos');
    }
};
