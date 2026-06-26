@extends('admin.layouts.app')

@php use Illuminate\Support\Facades\Storage; @endphp

@section('pagetitle')
    Product Management
@endsection

@section('pagecss')
    <link href="{{ asset('lib/ion-rangeslider/css/ion.rangeSlider.min.css') }}" rel="stylesheet">
@endsection

@section('content')
    <div class="container pd-x-0">
        <div class="d-sm-flex align-items-center justify-content-between mg-b-20 mg-lg-b-25 mg-xl-b-30">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1 mg-b-5">
                        <li class="breadcrumb-item" aria-current="page">CMS</li>
                        <li class="breadcrumb-item active" aria-current="page">Products</li>
                    </ol>
                </nav>
                <h4 class="mg-b-0 tx-spacing--1">Manage Products</h4>
            </div>
        </div>

        <div class="row row-sm">

            <!-- Start Filters -->
            <div class="col-md-12">
                <div class="filter-buttons mg-b-10">
                    <div class="d-md-flex bd-highlight">
                        <div class="bd-highlight mg-r-10 mg-t-10">
                            <div class="dropdown d-inline mg-r-5">
                                <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    Filters
                                </button>
                                <div class="dropdown-menu" style="min-width: 260px;">
                                    <form id="filterForm" class="pd-20">
                                        <div class="form-group">
                                            <label>{{ __('common.sort_by') }}</label>
                                            <div class="custom-control custom-radio">
                                                <input type="radio" id="orderBy1" name="orderBy" class="custom-control-input" value="updated_at" @if ($filter->orderBy == 'updated_at') checked @endif>
                                                <label class="custom-control-label" for="orderBy1">{{ __('common.date_modified') }}</label>
                                            </div>
                                            <div class="custom-control custom-radio">
                                                <input type="radio" id="orderBy2" name="orderBy" class="custom-control-input" value="name" @if ($filter->orderBy == 'name') checked @endif>
                                                <label class="custom-control-label" for="orderBy2">{{ __('common.name') }}</label>
                                            </div>
                                            <div class="custom-control custom-radio">
                                                <input type="radio" id="orderBy3" name="orderBy" class="custom-control-input" value="price" @if ($filter->orderBy == 'price') checked @endif>
                                                <label class="custom-control-label" for="orderBy3">Price</label>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label>{{ __('common.sort_order') }}</label>
                                            <div class="custom-control custom-radio">
                                                <input type="radio" id="sortByAsc" name="sortBy" class="custom-control-input" value="asc" @if ($filter->sortBy == 'asc') checked @endif>
                                                <label class="custom-control-label" for="sortByAsc">{{ __('common.ascending') }}</label>
                                            </div>
                                            <div class="custom-control custom-radio">
                                                <input type="radio" id="sortByDesc" name="sortBy" class="custom-control-input" value="desc" @if ($filter->sortBy == 'desc') checked @endif>
                                                <label class="custom-control-label" for="sortByDesc">{{ __('common.descending') }}</label>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label>Status</label>
                                            <select name="status" class="form-control form-control-sm">
                                                <option value="">All</option>
                                                <option value="active" @if(isset($filter->status) && $filter->status == 'active') selected @endif>Active</option>
                                                <option value="inactive" @if(isset($filter->status) && $filter->status == 'inactive') selected @endif>Inactive</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" id="showDeleted" name="showDeleted" class="custom-control-input" @if ($filter->showDeleted) checked @endif>
                                                <label class="custom-control-label" for="showDeleted">Show Deleted</label>
                                            </div>
                                        </div>
                                        <div class="form-group mg-b-40">
                                            <label class="d-block">{{ __('common.item_displayed') }}</label>
                                            <input id="displaySize" type="text" class="js-range-slider" name="perPage" value="{{ $filter->perPage }}"/>
                                        </div>
                                        <button id="filter" type="button" class="btn btn-sm btn-primary">{{ __('common.apply_filters') }}</button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="ml-auto bd-highlight mg-t-10 mg-r-10">
                            <form class="form-inline" id="searchForm">
                                <div class="search-form mg-r-10">
                                    <input name="search" type="search" id="search" class="form-control" placeholder="Search by Name" value="{{ $filter->search }}">
                                    <button class="btn filter" type="button" id="btnSearch"><i data-feather="search"></i></button>
                                </div>
                            </form>
                        </div>
                        <div class="mg-t-10">
                            <a class="btn btn-primary btn-sm" href="{{ route('products.create') }}">Create Product</a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Filters -->

            <!-- Start Table -->
            <div class="col-md-12">
                <div class="table-list mg-b-10">
                    <div class="table-responsive-lg">
                        <table class="table mg-b-0 table-light table-hover" style="width:100%;">
                            <thead>
                                <tr>
                                    <th scope="col">Image</th>
                                    <th scope="col" width="25%">Name</th>
                                    <th scope="col">Brand</th>
                                    <!-- <th scope="col">Price</th> -->
                                    <th scope="col">Status</th>
                                    <th scope="col">Options</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($products as $product)
                                    <tr>
                                        <td>
                                            @if($product->image_url)
                                                <img src="{{ Storage::disk('public')->url($product->image_url) }}" alt="{{ $product->name }}" style="width:48px; height:48px; object-fit:cover; border-radius:4px;">
                                            @else
                                                <div style="width:48px; height:48px; background:#eee; border-radius:4px; display:flex; align-items:center; justify-content:center;">
                                                    <i data-feather="image" style="color:#aaa;"></i>
                                                </div>
                                            @endif
                                        </td>
                                        <th>
                                            <strong @if($product->deleted_at) style="text-decoration:line-through;" @endif>
                                                {{ $product->name }}
                                            </strong>
                                            <div class="tx-12 tx-gray-500">{{ $product->slug }}</div>
                                        </th>
                                        <td>
                                            @if($product->category)
                                                <span class="badge badge-info">{{ $product->category->name }}</span>
                                            @else
                                                <span class="tx-gray-400">—</span>
                                            @endif
                                        </td>
                                        <!-- <td>{{ number_format($product->price, 2) }}</td> -->
                                        <td>
                                            @if($product->status == 'active')
                                                <span class="badge badge-success">Active</span>
                                            @else
                                                <span class="badge badge-danger">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <nav class="nav table-options justify-content-start align-items-center flex-nowrap">
                                                @if(!$product->deleted_at)
                                                    <a class="nav-link" href="{{ route('products.edit', $product->id) }}" title="Edit Product">
                                                        <i data-feather="edit"></i>
                                                    </a>
                                                    <form action="{{ route('products.destroy', $product->id) }}" method="POST" style="display:inline; transform: translate(0px, 2px);" onsubmit="return confirm('Delete this product?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="nav-link btn btn-link d-flex flex-row align-items-center justify-content-center" title="Delete Product" style="padding: 0.25rem 0.5rem;">
                                                            <i data-feather="trash-2"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <form action="{{ route('products.restore', $product->id) }}" method="POST" style="display:inline;">
                                                        @csrf
                                                        <button type="submit" class="nav-link btn btn-link" title="Restore Product" style="padding: 0.25rem 0.5rem;">
                                                            <i data-feather="refresh-cw"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </nav>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" style="text-align:center;"><p class="text-danger">No products found.</p></td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- End Table -->

            <!-- Start Navigation -->
            <div class="col-md-6">
                <div class="mg-t-5">
                    @if ($products->firstItem() == null)
                        <p class="tx-gray-400 tx-12 d-inline">{{ __('common.showing_zero_items') }}</p>
                    @else
                        <p class="tx-gray-400 tx-12 d-inline">Showing {{ $products->firstItem() }} to {{ $products->lastItem() }} of {{ $products->total() }} products</p>
                    @endif
                </div>
            </div>
            <div class="col-md-6">
                <div class="text-md-right float-md-right mg-t-5">
                    {{ $products->appends((array) $filter)->links() }}
                </div>
            </div>
            <!-- End Navigation -->

        </div>
    </div>
@endsection

@section('pagejs')
    <script src="{{ asset('lib/ion-rangeslider/js/ion.rangeSlider.min.js') }}"></script>
    <script>
        let listingUrl = "{{ route('products.index') }}";
        let advanceListingUrl = "";
        let searchType = "{{ $searchType }}";
    </script>
    <script src="{{ asset('js/listing.js') }}"></script>
@endsection