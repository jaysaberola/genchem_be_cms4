<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductCategoryController extends Controller
{
    public function index(Request $request)
    {
        $filter = (object) [
            'search'      => $request->get('search', ''),
            'orderBy'     => $request->get('orderBy', 'updated_at'),
            'sortBy'      => $request->get('sortBy', 'desc'),
            'perPage'     => $request->get('perPage', 15),
            'showDeleted' => $request->boolean('showDeleted'),
        ];

        $searchType = 'basic';

        $query = ProductCategory::withCount('products');

        if ($filter->showDeleted) {
            $query->withTrashed();
        }

        if ($filter->search) {
            $query->where('name', 'like', '%' . $filter->search . '%');
        }

        $categories = $query
            ->orderBy($filter->orderBy, $filter->sortBy)
            ->paginate($filter->perPage);

        return view('admin.products.index_category', compact('categories', 'filter', 'searchType'));
    }

    public function create()
    {
        return view('admin.products.create_category');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:product_categories,name',
            'slug' => 'required|string|max:100|unique:product_categories,slug',
        ]);

        $validated['slug'] = Str::slug($validated['slug']);
        $validated['user_id'] = auth()->id();

        ProductCategory::create($validated);

        return redirect()->route('product-categories.index')
            ->with('success', 'Category created successfully.');
    }

    public function edit(ProductCategory $productCategory)
    {
        return view('admin.products.edit_category', ['category' => $productCategory]);
    }

    public function update(Request $request, ProductCategory $productCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:product_categories,name,' . $productCategory->id,
            'slug' => 'required|string|max:100|unique:product_categories,slug,' . $productCategory->id,
        ]);

        $validated['slug'] = Str::slug($validated['slug']);

        $productCategory->update($validated);

        return redirect()->route('product-categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(ProductCategory $productCategory)
    {
        $productCategory->delete();

        return redirect()->route('product-categories.index')
            ->with('success', 'Category deleted.');
    }

    public function restore(int $id)
    {
        $category = ProductCategory::withTrashed()->findOrFail($id);
        $category->restore();

        return redirect()->route('product-categories.index')
            ->with('success', 'Category restored.');
    }

    public function fetch_categories(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 100);
        $perPage = min($perPage, 1000);

        $categories = ProductCategory::orderBy('name')
            ->paginate($perPage);

        $items = $categories->getCollection()->map(fn (ProductCategory $c) => [
            'id'   => $c->id,
            'name' => $c->name,
            'slug' => $c->slug,
        ]);

        return response()->json([
            'data' => $items,
            'meta' => [
                'total'        => $categories->total(),
                'per_page'     => $categories->perPage(),
                'current_page' => $categories->currentPage(),
                'last_page'    => $categories->lastPage(),
            ],
        ]);
    }
}