<?php

namespace App\Services;

use App\Models\Category;
use App\Models\User;

class CategoryService
{
    /**
     * Create a new category.
     */
    public function create(User $user, array $data): Category
    {
        return $user->categories()->create([
            'name' => $data['name'],
            'type' => $data['type'],
            'parent_id' => $data['parent_id'] ?? null,
            'icon' => $data['icon'] ?? null,
            'color' => $data['color'] ?? null,
            'position' => $data['position'] ?? 0,
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    /**
     * Update a category.
     */
    public function update(Category $category, array $data): Category
    {
        // Prevent circular reference
        if (!empty($data['parent_id']) && $data['parent_id'] === $category->id) {
            throw new \InvalidArgumentException('Category cannot be its own parent');
        }

        $category->update($data);
        return $category->fresh();
    }

    /**
     * Delete a category.
     */
    public function delete(Category $category): bool
    {
        if ($category->transactions()->count() > 0) {
            $category->delete();
            return true;
        }

        if ($category->children()->count() > 0) {
            throw new \InvalidArgumentException('Cannot delete category with subcategories');
        }

        $category->forceDelete();
        return true;
    }

    /**
     * Reorder categories.
     */
    public function reorder(User $user, array $categories): void
    {
        foreach ($categories as $cat) {
            Category::where('id', $cat['id'])
                ->where('user_id', $user->id)
                ->update(['position' => $cat['position'] ?? 0]);
        }
    }

    /**
     * Get categories with tree structure.
     */
    public function getTree(User $user, ?string $type = null)
    {
        $query = $user->categories()
            ->with('parent')
            ->orderBy('type')
            ->orderBy('position')
            ->orderBy('name');

        if ($type) {
            $query->where('type', $type);
        }

        $categories = $query->get();
        return $this->buildTree($categories);
    }

    /**
     * Build tree structure from flat categories.
     */
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
