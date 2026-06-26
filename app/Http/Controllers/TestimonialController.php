<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TestimonialController extends Controller
{
    public function __construct()
    {
        Permission::module_init($this, 'testimonials');
    }

    public function index(Request $request)
    {
        $filter = (object) [
            'search'  => $request->get('search', ''),
            'orderBy' => $request->get('orderBy', 'updated_at'),
            'sortBy'  => $request->get('sortBy', 'desc'),
            'perPage' => $request->get('perPage', 15),
        ];

        $searchType = 'basic';

        $query = Testimonial::query();

        if ($filter->search) {
            $query->where(function ($q) use ($filter) {
                $q->where('name', 'like', '%' . $filter->search . '%')
                  ->orWhere('company', 'like', '%' . $filter->search . '%');
            });
        }

        $testimonials = $query
            ->orderBy($filter->orderBy, $filter->sortBy)
            ->paginate($filter->perPage);

        return view('admin.testimonials.index', compact('testimonials', 'filter', 'searchType'));
    }

    public function create()
    {
        return view('admin.testimonials.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:100',
            'company'   => 'nullable|string|max:100',
            'testimony' => 'required|string',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if ($request->hasFile('thumbnail')) {
            $validated['thumbnail'] = $request->file('thumbnail')->store('testimonials', 'public');
        }

        Testimonial::create($validated);

        return redirect()->route('testimonials.index')
            ->with('success', 'Testimonial added successfully.');
    }

    public function edit(Testimonial $testimonial)
    {
        return view('admin.testimonials.edit', compact('testimonial'));
    }

    public function update(Request $request, Testimonial $testimonial)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:100',
            'company'   => 'nullable|string|max:100',
            'testimony' => 'required|string',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if ($request->hasFile('thumbnail')) {
            // Delete old thumbnail if stored locally
            if ($testimonial->thumbnail && Storage::disk('public')->exists($testimonial->thumbnail)) {
                Storage::disk('public')->delete($testimonial->thumbnail);
            }
            $validated['thumbnail'] = $request->file('thumbnail')->store('testimonials', 'public');
        } else {
            // Keep existing thumbnail — don't overwrite with null
            unset($validated['thumbnail']);
        }

        $testimonial->update($validated);

        return redirect()->route('testimonials.index')
            ->with('success', 'Testimonial updated successfully.');
    }

    public function destroy(Testimonial $testimonial)
    {
        $testimonial->delete();

        return redirect()->route('testimonials.index')
            ->with('success', 'Testimonial deleted.');
    }

    public function fetch_testimonials(Request $request)
    {
        $query = Testimonial::query();

        if ($request->search) {
            $query->where('name', 'like', '%'.$request->search.'%')
                ->orWhere('company', 'like', '%'.$request->search.'%')
                ->orWhere('testimony', 'like', '%'.$request->search.'%');
        }

        $perPage = $request->get('per_page', 10);

        $testimonials = $query
            ->latest()
            ->paginate($perPage);

        return response()->json($testimonials);
    }
}