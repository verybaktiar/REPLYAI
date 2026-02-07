<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AI Synonym Mapping (Per Industry)
    |--------------------------------------------------------------------------
    |
    | Mapping keyword user ke terminologi KB agar pencarian lebih akurat.
    | Dipisahkan berdasarkan tipe bisnis.
    |
    */

    'common' => [
        'lokasi' => ['alamat', 'map', 'tempat', 'cabang', 'kota'],
        'dimana' => ['alamat', 'map', 'tempat', 'cabang'],
        'buka'   => ['jam', 'jadwal', 'operasional'],
        'tutup'  => ['jam', 'jadwal', 'operasional'],
        'info'   => ['produk', 'menu', 'layanan', 'tentang', 'profil'],
        'biaya'  => ['harga', 'tarif', 'price'],
        'murah'  => ['harga', 'promo', 'diskon'],
        'mahal'  => ['harga', 'premium', 'kualitas'],
        'promo'  => ['diskon', 'sale', 'potongan', 'bonus', 'free', 'gratis', 'cashback', 'voucher'],
        'diskon' => ['promo', 'sale', 'potongan'],
        'wifi'      => ['fasilitas', 'internet', 'koneksi', 'password'],
        'parkir'    => ['fasilitas', 'lokasi', 'tempat', 'mobil', 'motor'],
        'bayar'     => ['pembayaran', 'payment', 'transfer', 'qris', 'cash', 'tunai', 'kartu', 'debit', 'kredit'],
    ],

    'fnb' => [
        'jualan' => ['menu', 'katalog', 'harga', 'makanan', 'minuman'],
        'jual'   => ['menu', 'katalog', 'harga'],
        'beli'   => ['pesan', 'order', 'pembayaran'],
        'order'  => ['pesan', 'pembayaran', 'rekening'],
        'pesan'  => ['pesan', 'pembayaran', 'booking', 'reservasi'],
        'booking'   => ['reservasi', 'pesan tempat', 'meja', 'acara', 'meeting'],
        'reservasi' => ['booking', 'pesan tempat', 'meja'],
        'antar'     => ['delivery', 'gojek', 'grab', 'shopee', 'ongkir', 'kirim'],
        'delivery'  => ['antar', 'gojek', 'grab', 'shopee', 'ongkir', 'kirim'],
        'member'    => ['loyalty', 'poin', 'reward', 'daftar'],
    ],

    'retail' => [
        'jualan' => ['produk', 'katalog', 'harga', 'barang', 'stok'],
        'jual'   => ['produk', 'katalog', 'harga'],
        'beli'   => ['cara pesan', 'order', 'checkout'],
        'ongkir' => ['pengiriman', 'ekspedisi', 'jne', 'jnt', 'sicepat'],
        'resi'   => ['pengiriman', 'status', 'lacak'],
        'retur'  => ['garansi', 'tukar', 'kembali', 'rusak'],
    ],

    'hospital' => [
        'jualan' => ['layanan', 'poli', 'dokter', 'paket', 'checkup'],
        'dokter' => ['spesialis', 'jadwal', 'praktek', 'dr'],
        'poli'   => ['spesialis', 'layanan', 'klinik'],
        'daftar' => ['registrasi', 'booking', 'janji temu'],
        'periksa' => ['konsultasi', 'layanan', 'dokter'],
        'sakit'  => ['keluhan', 'gejala', 'dokter', 'poli'],
        'obat'   => ['farmasi', 'apotek', 'resep'],
        'bpjs'   => ['asuransi', 'jaminan', 'kis'],
    ],

    'hospitality' => [
        'kamar' => ['room', 'tipe', 'suite', 'deluxe', 'fasilitas'],
        'checkin' => ['jam', 'masuk', 'syarat'],
        'checkout' => ['jam', 'keluar'],
        'booking' => ['reservasi', 'pesan', 'ketersediaan'],
        'sarapan' => ['breakfast', 'makan', 'restoran'],
        'kolam'   => ['fasilitas', 'renang', 'pool'],
    ],
    
    // Default fallback if industry not found
    'general' => [
        'jualan' => ['produk', 'layanan', 'harga'],
        'beli'   => ['cara pesan', 'order'],
    ],
];
