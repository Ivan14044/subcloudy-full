<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\Option;

class OptionController extends Controller
{
    public function index()
    {
        return response()->json(Option::pluck('value', 'name')->toArray());
    }
}
