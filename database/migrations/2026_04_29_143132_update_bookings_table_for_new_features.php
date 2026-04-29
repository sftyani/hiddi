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
            // 1. PERBAIKI PAYMENT_TYPE: Ubah ENUM atau VARCHAR agar muat 'booking_fee'
            // Jika sebelumnya ENUM('dp','full'), kita ubah jadi VARCHAR atau ENUM yang lebih luas
            // Cara paling aman di Laravel jika sudah ada data: Drop enum lalu add string, atau modify enum.
            // Di sini kita asumsikan kita ubah jadi VARCHAR(20) agar fleksibel.
            $table->string('payment_type', 20)->change(); 

            // 2. TAMBAHKAN KOLOM YANG HILANG DI CONTROLLER KAMU
            $table->time('wedding_end_time')->nullable()->after('wedding_time');
            $table->time('prewedding_end_time')->nullable()->after('prewedding_time');
            $table->date('engagement_date')->nullable()->after('wedding_location');
            
            // 3. TAMBAHKAN PAYMENT_METHOD (untuk QRIS/Transfer)
            $table->string('payment_method', 20)->nullable()->after('payment_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->enum('payment_type', ['dp', 'full'])->change(); // Kembalikan ke semula jika rollback
            
            $table->dropColumn([
                'wedding_end_time',
                'prewedding_end_time',
                'engagement_date',
                'payment_method'
            ]);
        });
    }
};