@extends('admin.layouts.app')

@section('pagetitle')
    Product Category Management
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
                        <li class="breadcrumb-item active" aria-current="page">Product Brands</li>
                    </ol>
                </nav>
                <h4 class="mg-b-0 tx-spacing--1">Manage Product Brands</h4>
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
                                <div class="dropdown-menu">
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
                            <a class="btn btn-primary btn-sm" href="{{ route('product-categories.create') }}">Create Brand</a>
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
                                    <th scope="col" width="35%">Name</th>
                                    <th scope="col">Slug</th>
                                    <th scope="col">Products</th>
                                    <th scope="col">Created</th>
                                    <th scope="col">Options</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($categories as $category)
                                    <tr>
                                        <th>
                                            <strong @if($category->deleted_at) style="text-decoration:line-through;" @endif>
                                                {{ $category->name }}
                                            </strong>
                                        </th>
                                        <td>{{ $category->slug }}</td>
                                        <td><span class="badge badge-secondary">{{ $category->products_count }}</span></td>
                                        <td>{{ $category->created_at->format('d M Y') }}</td>
                                        <td>
                                            <nav class="nav table-options justify-content-start flex-nowrap">
                                                @if(!$category->deleted_at)
                                                    <a class="nav-link" href="{{ route('product-categories.edit', $category->id) }}" title="Edit Category">
                                                        <i data-feather="edit"></i>
                                                    </a>
                                                    <form action="{{ route('product-categories.destroy', $category->id) }}" method="POST" style="display:inline; transform: translate(0px, -2px);" onsubmit="return confirm('Delete this category?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="nav-link btn btn-link d-flex flex-row align-items-center justify-content-center" title="Delete Category" style="padding: 0.25rem 0.5rem;">
                                                            <i data-feather="trash-2"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <form action="{{ route('product-categories.restore', $category->id) }}" method="POST" style="display:inline;">
                                                        @csrf
                                                        <button type="submit" class="nav-link btn btn-link" title="Restore Category" style="padding: 0.25rem 0.5rem;">
                                                            <i data-feather="refresh-cw"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </nav>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" style="text-align:center;"><p class="text-danger">No categories found.</p></td>
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
                    @if ($categories->firstItem() == null)
                        <p class="tx-gray-400 tx-12 d-inline">{{ __('common.showing_zero_items') }}</p>
                    @else
                        <p class="tx-gray-400 tx-12 d-inline">Showing {{ $categories->firstItem() }} to {{ $categories->lastItem() }} of {{ $categories->total() }} categories</p>
                    @endif
                </div>
            </div>
            <div class="col-md-6">
                <div class="text-md-right float-md-right mg-t-5">
                    {{ $categories->appends((array) $filter)->links() }}
                </div>
            </div>
            <!-- End Navigation -->

        </div>
    </div>
@endsection

@section('pagejs')
    <script src="{{ asset('lib/ion-rangeslider/js/ion.rangeSlider.min.js') }}"></script>
    <script>
        let listingUrl = "{{ route('product-categories.index') }}";
        let advanceListingUrl = "";
        let searchType = "{{ $searchType }}";
    </script>
    <script src="{{ asset('js/listing.js') }}"></script>
@endsection