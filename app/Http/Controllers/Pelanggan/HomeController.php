<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\Portfolio;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Menampilkan Landing Page (Branding & Portfolio)
        */
public function index()
{
    // Ambil 6 portofolio terbaru
    $portfolios = Portfolio::latest()->take(6)->get();
    
    // Ambil semua paket aktif, pastikan relasi category dimuat (Eager Loading)
    // Lalu kita kelompokkan berdasarkan NAMA kategori
    $packages = Package::with('category')
        ->where('is_active', true)
        ->get()
        ->groupBy(function($item) {
            // Jika paket tidak punya kategori, beri nama 'Lainnya'
            return $item->category ? $item->category->name : 'Lainnya';
        });

    return view('pelanggan.home', compact('portfolios', 'packages'));
}
    /**
     * Detail Paket & Form Booking
     */
    public function showPackage($id)
    {
        $package = Package::with(['category', 'includes'])->findOrFail($id);
        
        return view('pelanggan.package_detail', compact('package'));
    }
}