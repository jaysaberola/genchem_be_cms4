@extends('admin.layouts.app')

@php use Illuminate\Support\Facades\Storage; @endphp

@section('pagetitle')
    Testimonial Management
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
                        <li class="breadcrumb-item active" aria-current="page">Testimonials</li>
                    </ol>
                </nav>
                <h4 class="mg-b-0 tx-spacing--1">Manage Testimonials</h4>
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
                                    <input name="search" type="search" id="search" class="form-control" placeholder="Search by Name or Company" value="{{ $filter->search }}">
                                    <button class="btn filter" type="button" id="btnSearch"><i data-feather="search"></i></button>
                                </div>
                            </form>
                        </div>
                        <div class="mg-t-10">
                            <a class="btn btn-primary btn-sm" href="{{ route('testimonials.create') }}">Add Testimonial</a>
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
                                    <th scope="col">Thumbnail</th>
                                    <th scope="col" width="20%">Name</th>
                                    <th scope="col">Company</th>
                                    <th scope="col" width="40%">Testimony</th>
                                    <th scope="col">Options</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($testimonials as $testimonial)
                                    <tr>
                                        <td>
                                            @if($testimonial->thumbnail)
                                                <img src="{{ Storage::disk('public')->url($testimonial->thumbnail) }}" alt="{{ $testimonial->name }}" style="width:48px; height:48px; object-fit:cover; border-radius:50%;">
                                            @else
                                                <div style="width:48px; height:48px; background:#eee; border-radius:50%; display:flex; align-items:center; justify-content:center;">
                                                    <i data-feather="user" style="color:#aaa;"></i>
                                                </div>
                                            @endif
                                        </td>
                                        <th><strong>{{ $testimonial->name }}</strong></th>
                                        <td>{{ $testimonial->company ?? '—' }}</td>
                                        <td>
                                            <span class="tx-12 tx-gray-600">{{ Str::limit($testimonial->testimony, 100) }}</span>
                                        </td>
                                        <td>
                                            <nav class="nav table-options justify-content-start flex-nowrap">
                                                <a class="nav-link" href="{{ route('testimonials.edit', $testimonial->id) }}" title="Edit Testimonial">
                                                    <i data-feather="edit"></i>
                                                </a>
                                                <form action="{{ route('testimonials.destroy', $testimonial->id) }}" method="POST" style="display:inline; transform: translate(0px, -2px);" onsubmit="return confirm('Delete this testimonial?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="nav-link btn btn-link d-flex flex-row align-items-center justify-content-center" title="Delete Testimonial" style="padding: 0.25rem 0.5rem;">
                                                        <i data-feather="trash-2"></i>
                                                    </button>
                                                </form>
                                            </nav>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" style="text-align:center;"><p class="text-danger">No testimonials found.</p></td>
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
                    @if ($testimonials->firstItem() == null)
                        <p class="tx-gray-400 tx-12 d-inline">{{ __('common.showing_zero_items') }}</p>
                    @else
                        <p class="tx-gray-400 tx-12 d-inline">Showing {{ $testimonials->firstItem() }} to {{ $testimonials->lastItem() }} of {{ $testimonials->total() }} testimonials</p>
                    @endif
                </div>
            </div>
            <div class="col-md-6">
                <div class="text-md-right float-md-right mg-t-5">
                    {{ $testimonials->appends((array) $filter)->links() }}
                </div>
            </div>
            <!-- End Navigation -->

        </div>
    </div>
@endsection

@section('pagejs')
    <script src="{{ asset('lib/ion-rangeslider/js/ion.rangeSlider.min.js') }}"></script>
    <script>
        let listingUrl = "{{ route('testimonials.index') }}";
        let advanceListingUrl = "";
        let searchType = "{{ $searchType }}";
    </script>
    <script src="{{ asset('js/listing.js') }}"></script>
@endsection