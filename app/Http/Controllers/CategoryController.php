<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
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
            $categories = $this->buildTree($categories);
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

        $category = $request->user()->categories()->create($validated);

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

        $category->update($validated);

        return response()->json(['success' => true, 'data' => $category]);
    }

    public function destroy(Request $request, Category $category)
    {
        if ($category->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        if ($category->transactions()->count() > 0) {
            $category->delete();
            return response()->json(['success' => true, 'message' => 'Category deleted']);
        }

        $category->forceDelete();
        return response()->json(['success' => true, 'message' => 'Category deleted']);
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'categories' => ['required', 'array'],
        ]);

        foreach ($request->categories as $cat) {
            Category::where('id', $cat['id'])
                ->where('user_id', $request->user()->id)
                ->update(['position' => $cat['position'] ?? 0]);
        }

        return response()->json(['success' => true, 'message' => 'Categories reordered']);
    }

    private function buildTree($categories)
    {
        $tree = [];
        $indexed = [];

        foreach ($categories as $cat) {
            $indexed[$cat->id] = $cat;
            $indexed[$cat->id]['children'] = [];
        }

        foreach ($indexed as $id => $cat) {
            if ($cat->parent_id && isset($indexed[$cat->parent_id])) {
                $indexed[$cat->parent_id]['children'][] = $cat;
            } else {
                $tree[] = $cat;
            }
        }

        return $tree;
    }
}
