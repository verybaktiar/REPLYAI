<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QaTestResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Controller untuk QA Testing Dashboard
 * 
 * Halaman ini memungkinkan admin melakukan testing sistematis
 * dan mencatat hasil test untuk memastikan kualitas aplikasi.
 */
class AdminQaController extends Controller
{
    /**
     * Tampilkan halaman QA Testing dengan semua test scenarios
     */
    public function index()
    {
        $testScenarios = $this->getTestScenarios();
        $testResults = QaTestResult::orderBy('tested_at', 'desc')->take(50)->get();
        
        return view('admin.qa.index', compact('testScenarios', 'testResults'));
    }

    /**
     * Simpan hasil test
     */
    public function saveResult(Request $request)
    {
        $request->validate([
            'scenario_id' => 'required|string',
            'status' => 'required|in:pass,fail,skip',
            'notes' => 'nullable|string|max:1000',
        ]);

        QaTestResult::create([
            'scenario_id' => $request->scenario_id,
            'status' => $request->status,
            'notes' => $request->notes,
            'tested_by' => Auth::guard('admin')->user()->name ?? 'Admin',
            'tested_at' => now(),
        ]);

        return back()->with('success', 'Hasil test berhasil disimpan!');
    }

    /**
     * Reset semua hasil test
     */
    public function resetResults(Request $request)
    {
        $category = $request->category;
        
        if ($category) {
            QaTestResult::where('scenario_id', 'like', $category . '%')->delete();
        } else {
            QaTestResult::truncate();
        }

        return back()->with('success', 'Hasil test berhasil direset!');
    }

    /**
     * Definisi semua test scenarios
     * Organize by category untuk kemudahan maintenance
     */
    private function getTestScenarios(): array
    {
        return [
            'payment_flow' => [
                'name' => 'Payment Flow',
                'description' => 'Test alur pembayaran dari pilih paket sampai aktivasi subscription',
                'tests' => [
                    [
                        'id' => 'payment_flow_001',
                        'name' => 'Pilih paket dan checkout',
                        'steps' => [
                            '1. Login sebagai user biasa',
                            '2. Klik "Pilih Paket" atau akses /pricing',
                            '3. Pilih paket Pro',
                            '4. Pastikan redirect ke halaman checkout',
                        ],
                        'expected' => 'Halaman checkout muncul dengan detail paket Pro',
                    ],
                    [
                        'id' => 'payment_flow_002',
                        'name' => 'Buat invoice pembayaran',
                        'steps' => [
                            '1. Di halaman checkout, pilih durasi (bulanan/tahunan)',
                            '2. Klik "Lanjutkan Pembayaran"',
                            '3. Pastikan redirect ke halaman invoice',
                        ],
                        'expected' => 'Halaman invoice muncul dengan nomor invoice dan total bayar',
                    ],
                    [
                        'id' => 'payment_flow_003',
                        'name' => 'Tombol kembali di invoice',
                        'steps' => [
                            '1. Di halaman invoice, klik tombol "Dashboard" di navbar',
                            '2. Pastikan redirect ke dashboard (bukan pricing)',
                        ],
                        'expected' => 'User kembali ke dashboard, bukan halaman pilih paket',
                    ],
                    [
                        'id' => 'payment_flow_004',
                        'name' => 'Banner pending invoice di pricing',
                        'steps' => [
                            '1. Dengan invoice pending, akses /pricing',
                            '2. Pastikan muncul banner kuning pending invoice',
                            '3. Klik "Lanjutkan Bayar"',
                        ],
                        'expected' => 'Banner muncul dan redirect ke halaman invoice',
                    ],
                    [
                        'id' => 'payment_flow_005',
                        'name' => 'Bayar via Midtrans',
                        'steps' => [
                            '1. Di halaman invoice, klik "Bayar Instan"',
                            '2. Pilih metode pembayaran di Midtrans popup',
                            '3. Selesaikan pembayaran (sandbox)',
                        ],
                        'expected' => 'Midtrans popup muncul dan bisa dipilih metode pembayaran',
                    ],
                    [
                        'id' => 'payment_flow_006',
                        'name' => 'Redirect setelah payment sukses',
                        'steps' => [
                            '1. Setelah payment di Midtrans selesai',
                            '2. Pastikan redirect ke halaman success',
                            '3. Cek animasi confetti muncul',
                        ],
                        'expected' => 'Halaman success dengan animasi confetti dan info subscription',
                    ],
                    [
                        'id' => 'payment_flow_007',
                        'name' => 'Subscription teraktivasi',
                        'steps' => [
                            '1. Setelah payment sukses, klik "Lihat Subscription"',
                            '2. Cek status subscription aktif',
                            '3. Cek paket yang benar (Pro)',
                        ],
                        'expected' => 'Halaman subscription menunjukkan paket Pro aktif',
                    ],
                ],
            ],
            'navigation' => [
                'name' => 'Navigation & UX',
                'description' => 'Test navigasi dan user experience',
                'tests' => [
                    [
                        'id' => 'navigation_001',
                        'name' => 'Dashboard accessible setelah login',
                        'steps' => [
                            '1. Login dengan user yang punya subscription aktif',
                            '2. Pastikan redirect ke /dashboard',
                        ],
                        'expected' => 'Dashboard muncul tanpa error',
                    ],
                    [
                        'id' => 'navigation_002',
                        'name' => 'Redirect ke pricing jika tidak ada subscription',
                        'steps' => [
                            '1. Login dengan user tanpa subscription',
                            '2. Coba akses /dashboard',
                        ],
                        'expected' => 'Redirect ke /pricing untuk pilih paket',
                    ],
                ],
            ],
            'admin' => [
                'name' => 'Admin Panel',
                'description' => 'Test fungsi admin panel',
                'tests' => [
                    [
                        'id' => 'admin_001',
                        'name' => 'Admin login',
                        'steps' => [
                            '1. Akses /admin/login',
                            '2. Login dengan kredensial admin',
                        ],
                        'expected' => 'Redirect ke admin dashboard',
                    ],
                    [
                        'id' => 'admin_002',
                        'name' => 'Approve payment manual',
                        'steps' => [
                            '1. Di admin, akses Payments',
                            '2. Cari payment pending dengan bukti transfer',
                            '3. Klik Approve',
                        ],
                        'expected' => 'Payment status berubah jadi paid, subscription user aktif',
                    ],
                ],
            ],
        ];
    }
}
