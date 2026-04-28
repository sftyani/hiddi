<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
Schema::table('portfolios', function (Blueprint $table) {

    // ubah image_path jadi nullable (ini aman)
    $table->string('image_path')->nullable()->change();

    // TAMBAH CUMA KALO BELUM ADA
    if (!Schema::hasColumn('portfolios', 'video_path')) {
        $table->string('video_path')->nullable()->after('image_path');
    }

    if (!Schema::hasColumn('portfolios', 'type')) {
        $table->enum('type', ['image','video'])->default('image')->after('video_path');
    }

});
    }

    public function down()
    {
        Schema::table('portfolios', function (Blueprint $table) {
            $table->dropColumn('video_path');
            $table->dropColumn('type');

            $table->string('image_path')->nullable(false)->change();
        });
    }
};
