<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class DocumentationController extends Controller
{
    /**
     * Display the documentation page.
     */
    public function index(): View
    {
        return view('pages.documentation.index');
    }
}
