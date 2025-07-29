<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('listas', function (Blueprint $table) {
            $table->boolean('archived')->default(false)->after('parent_lista_id');
        });
    }

    public function down()
    {
        Schema::table('listas', function (Blueprint $table) {
            $table->dropColumn('archived');
        });
    }
};
