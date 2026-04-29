<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Booking extends Model
{
    use HasFactory;

    /**
     * Mass assignable
     */
protected $fillable = [
    'user_id', 
    'package_id', 
    'customer_name',
    'customer_phone',
    'customer_email',
    'customer_address',
    'booking_date', 
    
    // Wedding
    'wedding_time',
    'wedding_end_time', // ✅ Baru
    'wedding_location',
    
    // Prewedding
    'prewedding_date',
    'prewedding_time',
    'prewedding_end_time', // ✅ Baru
    'prewedding_category',
    'prewedding_location',
    
    // Engagement
    'engagement_date', // ✅ Baru

    // Biaya
    'transport_fee',
    'additional_fee',
    
    // Pembayaran
    'payment_type',   // Sekarang string, bukan enum ketat
    'payment_method', // ✅ Baru
    'total_price', 
    'amount_paid', 
    'remaining', 
    
    'status', 
    'expired_at', 
    'notes',
];
    /**
     * Casting
     */
    protected $casts = [
        'booking_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'total_price' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'remaining' => 'decimal:2',
        'expired_at' => 'datetime',
    ];

    /**
     * RELATION
     */
    public function user(){
        return $this->belongsTo(User::class);
    }

    public function package(){
        return $this->belongsTo(Package::class);
    }

    public function transaction(){
        return $this->hasOne(Transaction::class);
    }

    public function histories(){
        return $this->hasMany(BookingStatusHistory::class);
    }

    /**
     * HELPER
     */

    // apakah sudah dibayar
    public function isPaid(){
        return in_array($this->status, ['dp_paid','paid']);
    }

    // apakah sudah lunas
    public function isFullyPaid(){
        return $this->status === 'paid';
    }

    // apakah masih pending
    public function isPending(){
        return $this->status === 'pending';
    }

    // apakah sudah expired
    public function isExpired(){
        return $this->expired_at && now()->gt($this->expired_at);
    }

    // hitung durasi jam
    public function getDurationHours(){
        if (!$this->start_time || !$this->end_time) return 0;

        return Carbon::parse($this->start_time)
            ->diffInHours(Carbon::parse($this->end_time));
    }
}