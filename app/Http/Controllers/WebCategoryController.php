<?php

namespace App\Http\Controllers;

use App\Services\CategoryService;
use Illuminate\Http\Request;

class WebCategoryController extends Controller
{
    public function __construct(
        private CategoryService $categoryService
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $categories = $user->categories()->with('children')->whereNull('parent_id')->get();
        
        return view('categories.index', compact('categories'));
    }
}
