<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Category;
use App\Models\Package;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->package_id) {
            return redirect('/')->with('error', 'Pilih paket dulu');
        }

        $package = Package::with('includes')->findOrFail($request->package_id);
        $categoryAdditional = Category::whereRaw('LOWER(TRIM(name)) IN (?, ?)', ['addition', 'additional'])->first();

        $additionals = collect(); // Default kosong
        
        if ($categoryAdditional) {
            $additionals = Package::where('category_id', $categoryAdditional->id)->get();
        } else {
            Log::warning('Kategori "Additional" tidak ditemukan di database. Cek tabel categories.');
        }

        $weddingDates = Booking::whereNotIn('status', ['cancelled','rejected'])
            ->whereNotNull('booking_date')
            ->pluck('booking_date')
            ->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))
            ->toArray();

        $prewedDates = Booking::whereNotIn('status', ['cancelled','rejected'])
            ->whereNotNull('prewedding_date')
            ->pluck('prewedding_date')
            ->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))
            ->toArray();

        $prewedTimes = Booking::whereNotIn('status', ['cancelled','rejected'])
            ->whereNotNull('prewedding_date')
            ->get()
            ->groupBy(fn($b) => Carbon::parse($b->prewedding_date)->format('Y-m-d'))
            ->map(fn($items) => $items->pluck('prewedding_time')
                ->map(fn($time) => substr($time, 0, 5))
                ->toArray()
            );

        return view('pelanggan.booking', compact(
            'package', 'additionals', 'weddingDates', 'prewedDates', 'prewedTimes'
        ));
    }

    public function store(Request $request)
    {
        $package = Package::findOrFail($request->package_id);
        $inc = $package->includes->pluck('type')->toArray();

        // ================= VALIDATION =================
        $rules = [
            'package_id'       => 'required|exists:packages,id',
            'customer_name'    => 'required|string|max:255',
            'customer_phone'   => 'required|numeric|digits_between:10,15',
            'customer_email'   => 'required|email',
            'customer_address' => 'required|string|min:10',
            'payment_type'     => 'required|in:booking_fee,dp,full',
            'payment_method'   => 'nullable|in:transfer,qris',
            'transport_fee'    => 'nullable|numeric|min:0',
            'additional_fee'   => 'nullable|numeric|min:0',
        ];

        if (in_array('prewedding', $inc)) {
            $rules['prewedding_date'] = 'required|date';
            if ($request->filled('wedding_date')) {
                $rules['prewedding_date'] .= '|before_or_equal:wedding_date';
            }
            $rules['prewedding_category'] = 'required|in:indoor,outdoor';
            $rules['prewedding_time'] = 'nullable|date_format:H:i';
            $rules['prewedding_location'] = 'nullable|string|max:255';
        }

        if (in_array('wedding', $inc)) {
            $rules['wedding_date'] = 'required|date|after:today';
            $rules['wedding_time'] = 'nullable|date_format:H:i';
            $rules['wedding_location'] = 'required|string|max:255';
        }

        if (in_array('engagement', $inc)) {
            $rules['engagement_date'] = 'required|date|after:today';
        }

        $messages = [
            'customer_name.required'    => 'Nama lengkap wajib diisi.',
            'customer_phone.required'   => 'Nomor WhatsApp wajib diisi.',
            'customer_phone.numeric'    => 'Nomor WhatsApp harus berupa angka.',
            'customer_email.required'   => 'Email wajib diisi.',
            'customer_email.email'      => 'Format email tidak valid.',
            'customer_address.required' => 'Alamat domisili wajib diisi.',
            'customer_address.min'      => 'Alamat domisili terlalu singkat.',
            'payment_type.required'     => 'Tipe pembayaran wajib dipilih.',
            'wedding_date.required'     => 'Tanggal wedding wajib dipilih.',
            'wedding_date.after'        => 'Tanggal wedding harus setelah hari ini.',
            'wedding_location.required' => 'Lokasi venue wajib diisi.',
            'prewedding_date.required'  => 'Tanggal prewedding wajib dipilih.',
            'prewedding_date.before_or_equal' => 'Tanggal prewedding tidak boleh setelah tanggal wedding.',
            'prewedding_category.required' => 'Kategori sesi prewedding wajib dipilih.',
        ];

        $validated = $request->validate($rules, $messages);

        // ================= TANGGAL UTAMA =================
        $mainDate = $request->wedding_date 
            ?? $request->engagement_date 
            ?? $request->prewedding_date;

        // ================= HITUNG BIAYA =================
        $transport = (int) ($request->transport_fee ?? 0);
        $additional = (int) ($request->additional_fee ?? 0);
        $totalPrice = $package->price + $transport + $additional;

        if ($request->payment_type === 'booking_fee') {
            $amountPaid = 1000000;
        } elseif ($request->payment_type === 'dp') {
            $amountPaid = round($totalPrice * 0.5);
        } else {
            $amountPaid = $totalPrice;
        }

        $remaining = $totalPrice - $amountPaid;
        $status = $request->payment_method === 'qris' ? 'waiting_confirmation' : 'pending';

        // ================= SIMPAN =================
        $booking = Booking::create([
            'user_id'            => auth()->check() ? auth()->id() : null,
            'package_id'         => $package->id,
            'customer_name'      => $request->customer_name,
            'customer_phone'     => $request->customer_phone,
            'customer_email'     => $request->customer_email,
            'customer_address'   => $request->customer_address,
            'booking_date'       => $mainDate,

            // prewedding
            'prewedding_date'     => $request->prewedding_date,
            'prewedding_category' => $request->prewedding_category,
            'prewedding_time'     => $request->prewedding_time,
            'prewedding_end_time' => $request->prewedding_end_time, // Pastikan kolom ini sudah ada di DB
            'prewedding_location' => $request->prewedding_location,

            // wedding
            'wedding_time'     => $request->wedding_time,
            'wedding_end_time' => $request->wedding_end_time, // Pastikan kolom ini sudah ada di DB
            'wedding_location' => $request->wedding_location,

            // engagement
            'engagement_date'  => $request->engagement_date, // Pastikan kolom ini sudah ada di DB

            // biaya
            'transport_fee'  => $transport,
            'additional_fee' => $additional,

            // pembayaran
            'payment_type'   => $request->payment_type,
            'payment_method' => $request->payment_method, // Pastikan kolom ini sudah ada di DB
            
            'total_price'  => $totalPrice,
            'amount_paid'  => $amountPaid,
            'remaining'    => $remaining,
            'status'       => $status,
            'expired_at'   => now()->addHours(24),
        ]);

        return redirect('/')
            ->with('success', 'Booking berhasil! Admin akan menghubungi via WhatsApp.');
    }
}