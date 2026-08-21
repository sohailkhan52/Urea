<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories.
     */
    public function index(): View
    {
        $this->authorize('categories.view');

        $categories = Category::orderBy('name')
            ->paginate(15);

        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new category.
     */
    public function create(): View
    {
        $this->authorize('categories.create');

        return view('admin.categories.create');
    }

    /**
     * Store a newly created category in storage.
     */
    public function store(StoreCategoryRequest $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $this->authorize('categories.create');

        $category = Category::create($request->validated());

        // Return JSON if it's an AJAX request
        if ($request->wantsJson()) {
            return response()->json($category);
        }

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Category created successfully.');
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit(Category $category): View
    {
        $this->authorize('categories.update');

        return view('admin.categories.edit', compact('category'));
    }

    /**
     * Update the specified category in storage.
     */
    public function update(Category $category, UpdateCategoryRequest $request): RedirectResponse
    {
        $this->authorize('categories.update');

        $category->update($request->validated());

        return back()->with('success', 'Category updated successfully.');
    }

    /**
     * Activate the specified category.
     */
    public function activate(Category $category): RedirectResponse
    {
        $this->authorize('categories.update');

        $category->update(['status' => Category::STATUS_ACTIVE]);

        return back()->with('success', 'Category activated successfully.');
    }

    /**
     * Deactivate the specified category.
     */
    public function deactivate(Category $category): RedirectResponse
    {
        $this->authorize('categories.update');

        $category->update(['status' => Category::STATUS_INACTIVE]);

        return back()->with('success', 'Category deactivated successfully.');
    }

    /**
     * Delete the specified category.
     */
    public function destroy(Category $category): RedirectResponse
    {
        $this->authorize('categories.delete');

        if (!$category->canBeDeleted()) {
            return back()->with('error', 'Cannot delete category with products.');
        }

        $category->delete();

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Category deleted successfully.');
    }
}
