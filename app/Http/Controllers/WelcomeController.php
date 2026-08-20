<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contract;

class WelcomeController extends Controller
{
    public function __invoke()
    {
        return view('welcome', [
            'contrats' => Contract::get(),
        ]);
    }
}