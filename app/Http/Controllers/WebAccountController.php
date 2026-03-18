<?php

namespace App\Http\Controllers;

use App\Services\AccountService;
use Illuminate\Http\Request;

class WebAccountController extends Controller
{
    public function __construct(
        private AccountService $accountService
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $accounts = $user->accounts()->with('category')->get();
        
        return view('accounts.index', compact('accounts'));
    }
}
