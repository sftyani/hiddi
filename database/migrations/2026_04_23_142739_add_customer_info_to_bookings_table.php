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
Schema::table('bookings', function (Blueprint $table) {
    $table->foreignId('user_id')->nullable()->change();

    // HANYA TAMBAH YANG BELUM ADA
    $table->text('customer_address')->after('customer_email')->comment('Alamat/Domisili');
});
}

public function down(): void
{
    Schema::table('bookings', function (Blueprint $table) {
        // Kembalikan ke asal jika migration di-rollback
        $table->foreignId('user_id')->nullable(false)->change();
        $table->dropColumn(['customer_name', 'customer_phone', 'customer_email', 'customer_address']);
    });
}
};
