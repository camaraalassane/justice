<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jugements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('procedure_id')->constrained('procedures')->cascadeOnDelete();
            $table->date('date_jugement');
            $table->string('juridiction');
            $table->string('numero_jugement')->unique();
            $table->enum('verdict', ['Condamnation', 'Acquittement', 'Non-lieu', 'Classé sans suite']);
            $table->text('peine_principale')->nullable();
            $table->text('peines_complementaires')->nullable();
            $table->integer('duree_peine_jours')->nullable();
            $table->boolean('est_definitif')->default(false);
            $table->date('date_appel')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jugements');
    }
};