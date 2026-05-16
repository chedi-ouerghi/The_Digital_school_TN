<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTasksTable2 extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id(); // Ajout de la clé primaire
            $table->string('name')->nullable(); // Ajout de la colonne "name"
            $table->boolean('isComplited')->default(false); // Ajout de la colonne "isComplited"
            $table->text('description')->nullable(); // Ajout de la colonne "description"
            $table->timestamps(); // Ajout des champs "updated_at" et "created_at"
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('tasks');
    }
}
