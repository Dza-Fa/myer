<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function __construct(
        private CategoryService $categoryService
    ) {}

    public function index(Request $request)
    {
        $type = $request->get('type');
        
        $categories = $request->user()->categories()
            ->with('parent')
            ->when($type, fn($q) => $q->where('type', $type))
            ->orderBy('type')
            ->orderBy('position')
            ->orderBy('name')
            ->get();

        if ($request->get('tree')) {
            $categories = $this->categoryService->getTree($request->user(), $type);
        }

        return response()->json(['success' => true, 'data' => $categories]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'type' => ['required', Rule::in(['income', 'expense'])],
            'parent_id' => ['nullable', 'exists:categories,id'],
            'icon' => ['nullable', 'string', 'max:50'],
            'color' => ['nullable', 'string', 'size:7'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $category = $this->categoryService->create($request->user(), $validated);

        return response()->json(['success' => true, 'data' => $category], 201);
    }

    public function show(Request $request, Category $category)
    {
        if ($category->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $category->load(['parent', 'children']);
        return response()->json(['success' => true, 'data' => $category]);
    }

    public function update(Request $request, Category $category)
    {
        if ($category->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:100'],
            'type' => ['sometimes', Rule::in(['income', 'expense'])],
            'parent_id' => ['nullable'],
            'icon' => ['nullable'],
            'color' => ['nullable'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        try {
            $category = $this->categoryService->update($category, $validated);
            return response()->json(['success' => true, 'data' => $category]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function destroy(Request $request, Category $category)
    {
        if ($category->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $this->categoryService->delete($category);
            return response()->json(['success' => true, 'message' => 'Category deleted']);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'categories' => ['required', 'array'],
        ]);

        $this->categoryService->reorder($request->user(), $request->categories);

        return response()->json(['success' => true, 'message' => 'Categories reordered']);
    }
}
