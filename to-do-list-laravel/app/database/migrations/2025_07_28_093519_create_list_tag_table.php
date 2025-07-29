<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('list_tag', function (Blueprint $table) {
            $table->foreignId('lista_id')->constrained('listas')->onDelete('cascade');
            $table->foreignId('tag_id')->constrained('tags')->onDelete('cascade');
            $table->primary(['lista_id', 'tag_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('list_tag');
    }
};
