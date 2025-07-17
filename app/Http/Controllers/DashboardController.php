<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Exibe a view do dashboard usando o componente Livewire
     */
    public function index()
    {
        return view('admin.dashboard-livewire');
    }
} 