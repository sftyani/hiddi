@extends('layouts.pelanggan')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    /* [SEMUA CSS SAMA SEPERTI ASLINYA - TIDAK DIUBAH] */
    body { background-color: #f8f9fa; }
    .card-booking { border: none; border-radius: 20px; overflow: hidden; }
    .stepper-wrapper { display: flex; justify-content: space-between; margin-bottom: 3rem; position: relative; }
    .stepper-item { position: relative; display: flex; flex-direction: column; align-items: center; flex: 1; z-index: 2; }
    .stepper-item::before { position: absolute; content: ""; border-bottom: 2px solid #e9ecef; width: 100%; top: 20px; left: -50%; z-index: -1; }
    .stepper-item:first-child::before { content: none; }
    .step-counter { position: relative; display: flex; justify-content: center; align-items: center; width: 40px; height: 40px; border-radius: 50%; background: #e9ecef; color: #6c757d; font-weight: bold; margin-bottom: 6px; transition: 0.3s; }
    .stepper-item.active .step-counter { background-color: #D4AF37; color: #fff; box-shadow: 0 0 0 5px rgba(212, 175, 55, 0.2); }
    .stepper-item.completed .step-counter { background-color: #28a745; color: #fff; }
    .step-name { font-size: 0.85rem; color: #6c757d; font-weight: 500; }
    .active .step-name { color: #D4AF37; font-weight: bold; }
    .step-form { display: none; }
    .step-form.active { display: block; animation: slideIn 0.4s ease-out; }
    .form-control, .form-select { border-radius: 10px; padding: 0.75rem 1rem; border: 1px solid #dee2e6; }
    .form-control:focus { border-color: #D4AF37; box-shadow: 0 0 0 0.25rem rgba(212, 175, 55, 0.1); }
    .payment-summary-box { background: #fff9e6; border: 2px dashed #D4AF37; border-radius: 15px; padding: 20px; }
    .price-highlight { color: #B8962E; font-size: 1.5rem; font-weight: 800; }
    .btn-gold { background: linear-gradient(135deg, #D4AF37 0%, #B8962E 100%); color: white; border: none; padding: 12px 30px; border-radius: 10px; font-weight: 600; transition: 0.3s; }
    .btn-gold:hover { background: linear-gradient(135deg, #B8962E 0%, #9A7B24 100%); color: white; transform: translateY(-2px); }
    .btn-light { padding: 12px 30px; border-radius: 10px; font-weight: 600; }
    @keyframes slideIn { from { opacity: 0; transform: translateX(20px); } to { opacity: 1; transform: translateX(0); } }
    .package-badge { background: rgba(212, 175, 55, 0.1); color: #B8962E; padding: 5px 15px; border-radius: 50px; font-size: 0.9rem; font-weight: 600; }
    .flatpickr-day.booked { background: #ff4d4f !important; color: white !important; border-radius: 50%; position: relative; }
    .flatpickr-day.booked::after { content: ''; position: absolute; bottom: -12px; left: 50%; transform: translateX(-50%); font-size: 8px; color: red; }
</style>

<div class="container py-5 mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            
            <div class="card card-booking shadow-lg p-4 p-md-5 bg-white">
                <div class="text-center mb-5">
                    <span class="package-badge mb-2 d-inline-block">Booking Package</span>
                    <h2 class="fw-bold text-dark">{{ $package->name }}</h2>
                    <p class="text-muted">Lengkapi reservasi untuk paket seharga <strong>Rp {{ number_format($package->price, 0, ',', '.') }}</strong></p>
                </div>

                {{-- 🔥 ERROR MESSAGE BLOCK --}}
                @if($errors->any())
                <div class="alert alert-danger border-0 shadow-sm mb-4" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <strong>Periksa kembali form Anda:</strong>
                    <ul class="mb-0 mt-2 small">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                {{-- 🔥 SUCCESS MESSAGE --}}
                @if(session('success'))
                <div class="alert alert-success border-0 shadow-sm mb-4">
                    {{ session('success') }}
                </div>
                @endif

                <div class="stepper-wrapper">
                    <div class="stepper-item active" id="p-step-1">
                        <div class="step-counter">1</div>
                        <div class="step-name text-nowrap">Kontak</div>
                    </div>
                    <div class="stepper-item" id="p-step-2">
                        <div class="step-counter">2</div>
                        <div class="step-name text-nowrap">Jadwal Sesi</div>
                    </div>
                    <div class="stepper-item" id="p-step-3">
                        <div class="step-counter">3</div>
                        <div class="step-name text-nowrap">Konfirmasi</div>
                    </div>
                </div>

                @php $inc = $package->includes->pluck('type')->toArray(); @endphp

                <form action="{{ route('booking.store') }}" method="POST" id="multiStepForm">
                    @csrf
                    <input type="hidden" name="package_id" value="{{ $package->id }}">

                    {{-- STEP 1: INFORMASI PELANGGAN --}}
                    <div class="step-form active" id="step-1">
                        <div class="row g-4">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Nama Calon Pengantin</label>
                                <input type="text" name="customer_name" class="form-control" placeholder="Nama calon pengantin" 
                                       value="{{ old('customer_name') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nomor WhatsApp</label>
                                <input type="text" name="customer_phone" class="form-control" placeholder="0812xxxx" 
                                       value="{{ old('customer_phone') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Alamat Email</label>
                                <input type="email" name="customer_email" class="form-control" placeholder="nama@email.com" 
                                       value="{{ old('customer_email') }}" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Alamat Domisili</label>
                                <textarea name="customer_address" class="form-control" rows="2" placeholder="Tuliskan alamat lengkap..." required>{{ old('customer_address') }}</textarea>
                            </div>
                        </div>
                        <div class="text-end mt-5">
                            <button type="button" class="btn btn-gold px-5" onclick="nextStep(2)">Lanjut ke Jadwal <i class="fas fa-arrow-right ms-2"></i></button>
                        </div>
                    </div>

                    {{-- STEP 2: DETAIL JADWAL --}}
                    <div class="step-form" id="step-2">
                        <div class="row g-4">
                            @if(in_array('wedding', $inc))
                            <div class="col-12">
                                <div class="p-3 border rounded-4 bg-light mb-2">
                                    <h6 class="fw-bold mb-3 text-primary"><i class="fas fa-ring me-2"></i> Sesi Wedding</h6>
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="small fw-bold">Tanggal</label>
                                            <input type="text" id="wedding_date" name="wedding_date" class="form-control" placeholder="Pilih Tanggal" 
                                                   value="{{ old('wedding_date') }}" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="small fw-bold">Jam Mulai</label>
                                            <select id="wedding_time" name="wedding_time" class="form-select">
                                                <option value="">-- Pilih Jam --</option>
                                            </select>
                                            <input type="text" id="wedding_range" class="form-control mt-2" readonly>
                                            <input type="hidden" name="wedding_end_time" id="wedding_end_time">
                                        </div>
                                        <div class="col-md-5">
                                            <label class="small fw-bold">Lokasi Venue</label>
                                            <input type="text" name="wedding_location" class="form-control" placeholder="Nama Gedung/Rumah" 
                                                   value="{{ old('wedding_location') }}" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif

                            @if(in_array('prewedding', $inc))
                            <div class="col-12">
                                <div class="p-3 border rounded-4 bg-light">
                                    <h6 class="fw-bold mb-3 text-success"><i class="fas fa-camera me-2"></i> Sesi Prewedding</h6>
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="small fw-bold">Tanggal</label>
                                            <input type="text" id="prewedding_date" name="prewedding_date" class="form-control" placeholder="Pilih Tanggal"
                                                   value="{{ old('prewedding_date') }}">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="small fw-bold">Kategori Sesi</label>
                                            <select name="prewedding_category" class="form-select">
                                                <option value="indoor" {{ old('prewedding_category') == 'indoor' ? 'selected' : '' }}>Indoor (Studio)</option>
                                                <option value="outdoor" {{ old('prewedding_category') == 'outdoor' ? 'selected' : '' }}>Outdoor</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="small fw-bold">Jam Sesi</label>
                                            <select id="prewedding_time" name="prewedding_time" class="form-select">
                                                <option value="">-- Pilih Jam --</option>
                                            </select>
                                            <input type="text" id="prewedding_range" class="form-control mt-2" readonly>
                                            <input type="hidden" name="prewedding_end_time" id="prewedding_end_time">
                                        </div>
                                        <div class="col-12">
                                            <label class="small fw-bold">Rencana Lokasi</label>
                                            <input type="text" name="prewedding_location" class="form-control" placeholder="Contoh: Studio A / Pantai Melasti"
                                                   value="{{ old('prewedding_location') }}">
                                            <small id="prewed_note" class="text-muted mt-1 d-block"></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif

                            @if(in_array('engagement', $inc))
                            <div class="col-12">
                                <div class="p-3 border rounded-4 bg-light">
                                    <h6 class="fw-bold mb-3 text-warning"><i class="fas fa-heart me-2"></i> Sesi Engagement</h6>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="small fw-bold">Tanggal Acara</label>
                                            <input type="text" id="engagement_date" name="engagement_date" class="form-control" placeholder="Pilih Tanggal"
                                                   value="{{ old('engagement_date') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>

                        <div class="d-flex justify-content-between mt-5">
                            <button type="button" class="btn btn-light shadow-sm" onclick="nextStep(1)">Kembali</button>
                            <button type="button" class="btn btn-gold shadow-sm" onclick="nextStep(3)">Lanjut ke Pembayaran <i class="fas fa-arrow-right ms-2"></i></button>
                        </div>
                    </div>

                    {{-- STEP 3: PEMBAYARAN & KONFIRMASI --}}
                    <div class="step-form" id="step-3">
                        <div class="row g-4">
                            {{-- Sisi Kiri: Rincian Biaya --}}
                            <div class="col-lg-7">
                                <div class="p-4 border rounded-4 bg-white shadow-sm">
                                    <h6 class="fw-bold mb-4"><i class="fas fa-calculator me-2 text-gold"></i> Penyesuaian Biaya</h6>
                                    
                                    <div class="mb-3">
                                        <label class="small fw-bold text-muted">Biaya Transportasi (Sesuai kesepakatan Admin)</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0">Rp</span>
                                            <input type="number" id="input_transport" name="transport_fee" class="form-control border-start-0" placeholder="0" 
                                                   value="{{ old('transport_fee', 0) }}">
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label class="small fw-bold text-muted">Pilih Additional (Tambahan)</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0">
                                                <i class="fas fa-plus-circle"></i>
                                            </span>
                                            <select id="select_additional" class="form-select border-start-0">
                                                <option value="0" data-price="0" {{ old('additional_fee', 0) == 0 ? 'selected' : '' }}>-- Tanpa Tambahan --</option>
                                                @foreach($additionals as $item)
                                                    <option value="{{ $item->id }}" data-price="{{ $item->price }}" 
                                                            {{ old('additional_fee') == $item->price ? 'selected' : '' }}>
                                                        {{ $item->name }} (+Rp {{ number_format($item->price, 0, ',', '.') }})
                                                    </option>
                                                @endforeach
                                                <option value="custom">Input Manual...</option>
                                            </select>
                                        </div>
                                        <input type="number" id="custom_additional" class="form-control mt-2 d-none" placeholder="Masukkan biaya tambahan">
                                        <input type="hidden" name="additional_fee" id="final_additional" value="{{ old('additional_fee', 0) }}">
                                    </div>

                                    <div class="mb-0">
                                        <label class="form-label fw-bold">Pilih Tipe Pembayaran</label>
                                        <select name="payment_type" id="payment_type" class="form-select form-select-lg border-2">
                                            <option value="booking_fee" {{ old('payment_type') == 'booking_fee' ? 'selected' : '' }}>Booking Fee (Flat Rp 1.000.000)</option>
                                            <option value="dp" {{ old('payment_type') == 'dp' ? 'selected' : '' }}>Down Payment (50% dari Paket)</option>
                                            <option value="full" {{ old('payment_type') == 'full' ? 'selected' : '' }}>Pelunasan Langsung (100% Paket)</option>
                                        </select>
                                    </div>
                                </div>

                                {{-- Informasi Rekening --}}
                                <div class="mt-4 p-4 border rounded-4 bg-light">
                                    <h6 class="fw-bold mb-3"><i class="fas fa-university me-2 text-primary"></i> Informasi Rekening Transfer</h6>
                                    <div class="d-flex align-items-center p-3 bg-white rounded-3 shadow-sm mb-2">
                                        <img src="https://upload.wikimedia.org/wikipedia/commons/5/5c/Bank_Central_Asia.svg" alt="BCA" width="60" class="me-3">
                                        <div>
                                            <small class="text-muted d-block">Bank BCA</small>
                                            <strong class="fs-5">0551303321</strong>
                                            <small class="d-block text-uppercase">A/N DIKI WAHYUDIN</small>
                                        </div>
                                    </div>
                                    <small class="text-muted">*Mohon simpan bukti transfer untuk dikirimkan ke WhatsApp.</small>
                                </div>
                            </div>

                            {{-- Sisi Kanan: Summary Total --}}
                            <div class="col-lg-5">
                                <div class="payment-summary-box h-100 d-flex flex-column justify-content-center text-center">
                                    <small class="text-muted text-uppercase fw-bold">Total yang Harus Dibayar</small>
                                    <h2 class="price-highlight my-2" id="display_total_bayar">Rp 1.000.000</h2>
                                    <hr class="mx-4">
                                    <div class="px-4 text-start">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span>Harga Paket:</span>
                                            <span id="sum_paket">Rp {{ number_format($package->price, 0, ',', '.') }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-1">
                                            <span>Transport:</span>
                                            <span id="sum_transport">Rp 0</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-1">
                                            <span>Additional:</span>
                                            <span id="sum_additional">Rp 0</span>
                                        </div>
                                        <div class="alert alert-warning py-2 small mb-0">
                                            <i class="fas fa-info-circle me-1"></i> Klik konfirmasi untuk mengirim rincian ini ke WhatsApp.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-5">
                            <button type="button" class="btn btn-light shadow-sm" onclick="nextStep(2)">Kembali</button>
                            <button type="submit" id="btnConfirmBooking" class="btn btn-gold px-5 shadow-sm">Konfirmasi & Kirim WA</button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const additionalFromPackage = {{ 
    $package->includes
        ->filter(function($item) {
            return $item->type === 'additional' || $item->category_id == 7;
        })
        ->sum('price') ?? 0 
}};

document.addEventListener('DOMContentLoaded', function () {
    const weddingDates  = @json($weddingDates ?? []);
    const prewedDates   = @json($prewedDates ?? []);
    const prewedTimes   = @json($prewedTimes ?? []);
    const totalHargaPaket = {{ $package->price }};

    function formatDate(d) {
        return d.getFullYear() + '-' +
            String(d.getMonth() + 1).padStart(2, '0') + '-' +
            String(d.getDate()).padStart(2, '0');
    }

    // Flatpickr Wedding
    flatpickr("#wedding_date", {
        minDate: "today",
        dateFormat: "Y-m-d",
        disable: [...weddingDates, ...prewedDates],
        onDayCreate: function(dObj, dStr, fp, dayElem) {
            const date = formatDate(dayElem.dateObj);
            if (weddingDates.includes(date)) {
                dayElem.classList.add('booked');
            }
        }
    });

    // Flatpickr Prewedding
    flatpickr("#prewedding_date", {
        minDate: "today",
        dateFormat: "Y-m-d",
        onDayCreate: function(dObj, dStr, fp, dayElem) {
            const date = formatDate(dayElem.dateObj);
            if (prewedDates.includes(date)) {
                dayElem.style.background = "#ffe58f";
            }
        }
    });

    // Populate Jam Wedding
    const weddingDuration = @json($package->wedding_duration ?? 2);
    const wTime = document.getElementById('wedding_time');
    if (wTime) {
        for (let h = 7; h <= 21; h++) {
            let t = String(h).padStart(2, '0') + ":00";
            wTime.add(new Option(t, t));
        }
        wTime.addEventListener('change', function () {
            if (!this.value) return;
            let h = parseInt(this.value.split(':')[0]);
            let endH = h + weddingDuration;
            let endT = String(endH).padStart(2, '0') + ":00";
            document.getElementById('wedding_range').value = `${this.value} s/d ${endT}`;
            document.getElementById('wedding_end_time').value = endT;
        });
        wTime.dispatchEvent(new Event('change'));
    }

    // Populate Jam Prewedding
    const prewedDuration = 4;
    const pTime = document.getElementById('prewedding_time');
    const prewedDateInput = document.getElementById('prewedding_date');
    if (pTime) {
        for (let h = 7; h <= 18; h++) {
            let t = String(h).padStart(2, '0') + ":00";
            pTime.add(new Option(t, t));
        }
        pTime.addEventListener('change', function () {
            if (!this.value) return;
            let h = parseInt(this.value.split(':')[0]);
            let endT = String(h + prewedDuration).padStart(2, '0') + ":00";
            document.getElementById('prewedding_range').value = `${this.value} s/d ${endT}`;
            document.getElementById('prewedding_end_time').value = endT;
        });
    }

    function updatePrewedTimeOptions() {
        if (!pTime || !prewedDateInput) return;
        const selectedDate = prewedDateInput.value;
        const bookedTimes = prewedTimes[selectedDate] || [];
        Array.from(pTime.options).forEach(opt => {
            if (!opt.value) return;
            opt.disabled = false;
            opt.textContent = opt.value;
            opt.style.color = '';
        });
        bookedTimes.forEach(function (time) {
            const bookedStart = parseInt(time.split(':')[0]);
            const bookedEnd = bookedStart + prewedDuration;
            Array.from(pTime.options).forEach(opt => {
                if (!opt.value) return;
                const optHour = parseInt(opt.value.split(':')[0]);
                const wouldOverlap = (optHour + prewedDuration > bookedStart) && (optHour < bookedEnd);
                if (wouldOverlap) {
                    opt.disabled = true;
                    opt.textContent = opt.value + " (FULL)";
                    opt.style.color = 'red';
                }
            });
        });
        Array.from(pTime.options).forEach(opt => {
            if (!opt.value) return;
            const optHour = parseInt(opt.value.split(':')[0]);
            if (optHour + prewedDuration > 22) {
                opt.disabled = true;
                opt.textContent = opt.value + " (FULL)";
                opt.style.color = 'red';
            }
        });
    }

    if (prewedDateInput) {
        prewedDateInput.addEventListener('change', updatePrewedTimeOptions);
    }

    function formatRupiah(amount) {
        return "Rp " + new Intl.NumberFormat('id-ID').format(amount);
    }

    const inputTransport = document.getElementById('input_transport');
    const paymentTypeSelect = document.getElementById('payment_type');
    const displayTotalBayar = document.getElementById('display_total_bayar');

    function calculate() {
        const transport = parseInt(inputTransport.value) || 0;
        const additionalInput = document.getElementById('final_additional');
        const additional = additionalInput ? parseInt(additionalInput.value) || 0 : 0;
        const type = paymentTypeSelect.value;
        let base = 0;
        if (type === 'booking_fee') base = 1000000;
        else if (type === 'dp') base = Math.round(totalHargaPaket / 2);
        else base = totalHargaPaket;
        const total = base + transport + additional;
        displayTotalBayar.innerText = formatRupiah(total);
        document.getElementById('sum_paket').innerText = formatRupiah(base);
        document.getElementById('sum_transport').innerText = formatRupiah(transport);
        document.getElementById('sum_additional').innerText = formatRupiah(additional);
    }

    paymentTypeSelect.addEventListener('change', calculate);
    inputTransport.addEventListener('input', calculate);
    const additionalInput = document.getElementById('final_additional');
    if (additionalInput) {
        additionalInput.addEventListener('input', calculate);
    }
    document.addEventListener('DOMContentLoaded', calculate);

    // Navigasi Step
    function validateStep1() {
        const name = document.querySelector('[name="customer_name"]').value;
        const phone = document.querySelector('[name="customer_phone"]').value;
        const email = document.querySelector('[name="customer_email"]').value;
        if (!name || !phone || !email) {
            Swal.fire("Peringatan", "Isi semua data dulu", "warning");
            return false;
        }
        return true;
    }

    function validateStep2() {
        const weddingDate = document.getElementById('wedding_date')?.value;
        const weddingTime = document.getElementById('wedding_time')?.value;
        const weddingLoc = document.querySelector('[name="wedding_location"]')?.value;
        if (!weddingDate || !weddingTime || !weddingLoc) {
            Swal.fire("Peringatan", "Lengkapi data wedding dulu", "warning");
            return false;
        }
        return true;
    }

    window.nextStep = function (step) {
        if (step === 2 && !validateStep1()) return;
        if (step === 3 && !validateStep2()) return;
        document.querySelectorAll('.step-form').forEach(el => el.classList.remove('active'));
        document.getElementById('step-' + step).classList.add('active');
    };

    // Konfirmasi & Kirim WA
    function cleanText(text) {
        return (text || '').replace(/[*_~`]/g, '').trim();
    }

    document.getElementById('btnConfirmBooking').addEventListener('click', function (e) {
        e.preventDefault();
        
        // 🔥 VALIDASI CLIENT-SIDE SEBELUM BUKA WA
        let errors = [];
        
        // Step 1: Data Pelanggan
        const name  = document.querySelector('[name="customer_name"]')?.value.trim();
        const phone = document.querySelector('[name="customer_phone"]')?.value.trim();
        const email = document.querySelector('[name="customer_email"]')?.value.trim();
        const addr  = document.querySelector('[name="customer_address"]')?.value.trim();
        
        if (!name) errors.push('Nama lengkap wajib diisi');
        if (!phone || phone.length < 10) errors.push('Nomor WhatsApp tidak valid');
        if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) errors.push('Format email tidak valid');
        if (!addr || addr.length < 10) errors.push('Alamat domisili terlalu pendek');
        
        // Step 2: Wedding (jika ada di package)
        @if(in_array('wedding', $inc))
        const wDate = document.getElementById('wedding_date')?.value;
        const wTime = document.getElementById('wedding_time')?.value;
        const wLoc  = document.querySelector('[name="wedding_location"]')?.value.trim();
        if (!wDate) errors.push('Tanggal wedding wajib dipilih');
        if (!wTime) errors.push('Jam wedding wajib dipilih');
        if (!wLoc)  errors.push('Lokasi venue wedding wajib diisi');
        @endif
        
        // Step 2: Prewedding (jika ada di package)
        @if(in_array('prewedding', $inc))
        const pDate = document.getElementById('prewedding_date')?.value;
        const pCat  = document.querySelector('[name="prewedding_category"]')?.value;
        if (!pDate) errors.push('Tanggal prewedding wajib dipilih');
        if (!pCat)  errors.push('Kategori sesi prewedding wajib dipilih');
        @endif
        
        // Step 3: Pembayaran
        const payType = document.getElementById('payment_type')?.value;
        if (!payType) errors.push('Tipe pembayaran wajib dipilih');
        
        // 🔥 JIKA ADA ERROR, TAMPILKAN & STOP
        if (errors.length > 0) {
            Swal.fire({
                title: "Periksa Kembali Form",
                html: errors.map(e => `• ${e}`).join('<br>'),
                icon: "warning",
                confirmButtonText: "OK",
                confirmButtonColor: "#D4AF37"
            });
            return; // ⛔ JANGAN lanjut ke WhatsApp
        }
        
        // ✅ SEMUA VALIDASI LULUS - LANJUT KIRIM WA & SUBMIT
        const form = document.getElementById('multiStepForm');

        const nameClean  = cleanText(name);
        const phoneClean = cleanText(phone);
        const wDateClean = cleanText(document.getElementById('wedding_date')?.value || '-');
        const wRange     = cleanText(document.getElementById('wedding_range')?.value || '-');
        const wLocClean  = cleanText(document.querySelector('[name="wedding_location"]')?.value || '-');
        const packageName = cleanText("{{ $package->name }}");
        const preDate    = cleanText(document.getElementById('prewedding_date')?.value || '-');
        const preRange   = cleanText(document.getElementById('prewedding_range')?.value || '-');
        const preLoc     = cleanText(document.querySelector('[name="prewedding_location"]')?.value || '-');
        
        const paymentType  = document.getElementById('payment_type').value;
        const totalTagihan = document.getElementById('display_total_bayar').innerText;
        const transport    = parseInt(document.getElementById('input_transport')?.value)  || 0;
        const additional   = parseInt(document.getElementById('final_additional').value) || 0;
        
        let base = 0, paymentLabel = '';
        if (paymentType === 'booking_fee') { base = 1000000; paymentLabel = 'Booking Fee'; }
        else if (paymentType === 'dp') { base = Math.round(totalHargaPaket / 2); paymentLabel = 'DP 50%'; }
        else { base = totalHargaPaket; paymentLabel = 'Pelunasan'; }

        let msg = `Halo Hiddi, saya sudah melakukan pembayaran!\n\n`;
        msg += `*Detail Pesanan:*\n`;
        msg += `- Nama: ${nameClean}\n`;
        msg += `- Paket: ${packageName}\n`;
        msg += `\n*Rincian Pembayaran:*\n`;
        msg += `- ${paymentLabel}: ${formatRupiah(base)}\n`;
        msg += `- Transport: ${formatRupiah(transport)}\n`;
        msg += `- Additional: ${formatRupiah(additional)}\n`;
        msg += `- *Total Transfer:* ${totalTagihan}\n`;
        msg += `\n*Detail Wedding:*\n`;
        msg += `- Tanggal: ${wDateClean}\n`;
        msg += `- Jam: ${wRange}\n`;
        msg += `- Lokasi: ${wLocClean}\n`;
        if (preDate && preDate !== '-') {
            msg += `\n*Detail Prewedding:*\n`;
            msg += `- Tanggal: ${preDate}\n`;
            msg += `- Jam: ${preRange}\n`;
            msg += `- Lokasi: ${preLoc}\n`;
        }
        msg += `\n*Catatan:* Saya akan melampirkan bukti transfer setelah ini.`;

        const adminWA = "6282124591059";
        
        // 🔥 Buka WhatsApp DULU, baru submit form
        window.open(`https://wa.me/${adminWA}?text=${encodeURIComponent(msg)}`, '_blank');
        
        // Beri delay kecil agar WA terbuka dulu
        setTimeout(() => {
            form.submit();
        }, 500);
    });

    const selectAdditional = document.getElementById('select_additional');
    const customAdditional = document.getElementById('custom_additional');
    const finalAdditional = document.getElementById('final_additional');

    selectAdditional.addEventListener('change', function () {
        const selected = this.options[this.selectedIndex];
        const price = parseInt(selected.dataset.price) || 0;
        if (this.value === 'custom') {
            customAdditional.classList.remove('d-none');
            finalAdditional.value = 0;
        } else {
            customAdditional.classList.add('d-none');
            customAdditional.value = '';
            finalAdditional.value = price;
            calculate();
        }
    });

    customAdditional.addEventListener('input', function () {
        finalAdditional.value = parseInt(this.value) || 0;
        calculate();
    });

    // 🔥 Set old value untuk flatpickr setelah init
    const oldWeddingDate = "{{ old('wedding_date') }}";
    const oldPreweddingDate = "{{ old('prewedding_date') }}";
    if (oldWeddingDate && document.getElementById('wedding_date')) {
        flatpickr("#wedding_date").setDate(oldWeddingDate);
    }
    if (oldPreweddingDate && document.getElementById('prewedding_date')) {
        flatpickr("#prewedding_date").setDate(oldPreweddingDate);
    }
});
</script>
@endpush