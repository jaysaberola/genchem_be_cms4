@extends('admin.layouts.app')

@section('pagetitle')
    Product Category Management
@endsection

@section('content')
<div class="container pd-x-0">
    <div class="d-sm-flex align-items-center justify-content-between mg-b-20 mg-lg-b-25 mg-xl-b-30">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mg-b-10">
                    <li class="breadcrumb-item" aria-current="page"><a href="{{ route('dashboard') }}">CMS</a></li>
                    <li class="breadcrumb-item" aria-current="page"><a href="{{ route('product-categories.index') }}">Product Categories</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Create Brand</li>
                </ol>
            </nav>
            <h4 class="mg-b-0 tx-spacing--1">Create a Product Brand</h4>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <form action="{{ route('product-categories.store') }}" method="POST">
                @csrf
                @method('POST')

                <div class="form-group">
                    <label class="d-block">Name *</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" required maxlength="100">
                    @error('name')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="d-block">Slug *</label>
                    <input type="text" name="slug" id="slug" value="{{ old('slug') }}" class="form-control @error('slug') is-invalid @enderror" required maxlength="100" placeholder="auto-generated from name">
                    @error('slug')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <button class="btn btn-primary btn-sm btn-uppercase" type="submit">Create Brand</button>
                <a class="btn btn-outline-secondary btn-sm btn-uppercase" href="{{ route('product-categories.index') }}">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection

@section('customjs')
<script>
    $(function () {
        $('#name').on('input', function () {
            const slug = $(this).val()
                .toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .trim()
                .replace(/\s+/g, '-');
            $('#slug').val(slug);
        });
    });
</script>
@endsection