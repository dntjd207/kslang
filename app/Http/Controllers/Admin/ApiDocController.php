<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ApiDocController extends Controller
{
    public function index()
    {
        $baseUrl = url('/api/v1');
        return view('admin.api-docs.index', compact('baseUrl'));
    }
}

