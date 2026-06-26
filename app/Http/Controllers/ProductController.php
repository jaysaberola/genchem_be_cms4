<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $filter = (object) [
            'search'      => $request->get('search', ''),
            'orderBy'     => $request->get('orderBy', 'updated_at'),
            'sortBy'      => $request->get('sortBy', 'desc'),
            'perPage'     => $request->get('perPage', 15),
            'showDeleted' => $request->boolean('showDeleted'),
            'status'      => $request->get('status', ''),
        ];

        $searchType = 'basic';

        $query = Product::with('category');

        if ($filter->showDeleted) {
            $query->withTrashed();
        }

        if ($filter->search) {
            $query->where('name', 'like', '%' . $filter->search . '%');
        }

        if ($filter->status) {
            $query->where('status', $filter->status);
        }

        $products = $query
            ->orderBy($filter->orderBy, $filter->sortBy)
            ->paginate($filter->perPage);

        return view('admin.products.index', compact('products', 'filter', 'searchType'));
    }

    public function create()
    {
        $categories = ProductCategory::orderBy('name')->get();

        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:150',
            'slug'        => 'required|string|max:150|unique:products,slug',
            'price'       => 'required|numeric|min:0',
            'category_id' => 'nullable|exists:product_categories,id',
            'status'      => 'required|in:active,inactive',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'description' => 'nullable|string',
        ]);

        $validated['slug'] = Str::slug($validated['slug']);

        if ($request->hasFile('image')) {
            $validated['image_url'] = $request->file('image')->store('products', 'public');
        }

        unset($validated['image']);

        $validated['user_id'] = Auth::user()->id;

        Product::create($validated);

        return redirect()->route('products.index')
            ->with('success', 'Product created successfully.');
    }

    public function edit(Product $product)
    {
        $categories = ProductCategory::orderBy('name')->get();

        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:150',
            'slug'        => 'required|string|max:150|unique:products,slug,' . $product->id,
            'price'       => 'required|numeric|min:0',
            'category_id' => 'nullable|exists:product_categories,id',
            'status'      => 'required|in:active,inactive',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'description' => 'nullable|string',
        ]);

        $validated['slug'] = Str::slug($validated['slug']);

        if ($request->hasFile('image')) {
            if ($product->image_url && Storage::disk('public')->exists($product->image_url)) {
                Storage::disk('public')->delete($product->image_url);
            }
            $validated['image_url'] = $request->file('image')->store('products', 'public');
        }

        unset($validated['image']);

        $product->update($validated);

        return redirect()->route('products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Product deleted.');
    }

    public function restore(int $id)
    {
        $product = Product::withTrashed()->findOrFail($id);
        $product->restore();

        return redirect()->route('products.index')
            ->with('success', 'Product restored.');
    }

    public function showProduct(Product $product)
    {
        $product->load('category:id,name,slug');

        return response()->json([
            'data' => $product,
        ]);
    }

    public function fetch_products(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 100);
        $perPage = min($perPage, 1000); // hard cap

        $products = Product::with('category')
            ->where('status', 'active')
            ->orderBy('name')
            ->paginate($perPage);

        $items = $products->getCollection()->map(fn (Product $p) => [
            'id'          => $p->id,
            'slug'        => $p->slug,
            'name'        => $p->name,
            'description' => $p->description,
            'price'       => $p->price,
            'image_url'   => $p->image_url
                                ? asset('storage/' . $p->image_url)
                                : null,
            'category_id' => $p->category_id,
            'category'    => $p->category ? [
                'id'   => $p->category->id,
                'name' => $p->category->name,
                'slug' => $p->category->slug,
            ] : null,
        ]);

        return response()->json([
            'data' => $items,
            'meta' => [
                'total'        => $products->total(),
                'per_page'     => $products->perPage(),
                'current_page' => $products->currentPage(),
                'last_page'    => $products->lastPage(),
            ],
        ]);
    }

    public function showProducts(Request $request)
    {
        $perPage = $request->integer('per_page', 10);

        $query = Product::query()->with('category:id,name,slug');

        // trashed params acceptance (same style as pages/articles)
        $onlyTrashed = $request->boolean('only_trashed')
            || $request->boolean('onlyDeleted')
            || $request->boolean('trashed')
            || $request->boolean('show_deleted');

        $withTrashed = $request->boolean('with_trashed')
            || $request->boolean('withDeleted')
            || $request->boolean('include_deleted');

        if ($onlyTrashed) {
            $query = $query->onlyTrashed();
        } elseif ($withTrashed) {
            $query = $query->withTrashed();
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $query->when($request->search, function ($q) use ($request) {
            $term = $request->search;
            $q->where(function ($qq) use ($term) {
                $qq->where('name', 'like', '%' . $term . '%')
                    ->orWhere('description', 'like', '%' . $term . '%')
                    ->orWhere('slug', 'like', '%' . $term . '%');
            });
        });

        $products = $query->latest('updated_at')->paginate($perPage);

        // Normalize image_url to a full URL (optional, but convenient)
        $products->getCollection()->transform(function ($p) {
            $arr = $p->toArray();
            if (!empty($p->image_url)) {
                $arr['image_url_full'] = url(Storage::url($p->image_url));
            }
            return $arr;
        });

        return response()->json($products);
    }
}