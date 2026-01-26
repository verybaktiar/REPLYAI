<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\SaaSMasterService;

class AdminRevenueController extends Controller
{
    protected $saasService;

    public function __construct(SaaSMasterService $saasService)
    {
        $this->saasService = $saasService;
    }

    public function index()
    {
        $metrics = [
            'mrr' => $this->saasService->getMRR(),
            'churn' => $this->saasService->getChurnRate(),
            'ltv' => $this->saasService->getLTV(),
            'arpu' => $this->saasService->getARPU(),
            'growth' => $this->saasService->getRevenueGrowth(),
        ];

        return view('admin.revenue.index', compact('metrics'));
    }
}
