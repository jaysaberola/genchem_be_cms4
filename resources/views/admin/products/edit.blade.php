@extends('admin.layouts.app')

@php use Illuminate\Support\Facades\Storage; @endphp

@section('pagetitle')
    Product Management
@endsection

@section('pagecss')
    <link href="{{ asset('lib/bselect/dist/css/bootstrap-select.css') }}" rel="stylesheet">
@endsection

@section('content')
<div class="container pd-x-0">
    <div class="d-sm-flex align-items-center justify-content-between mg-b-20 mg-lg-b-25 mg-xl-b-30">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mg-b-10">
                    <li class="breadcrumb-item" aria-current="page"><a href="{{ route('dashboard') }}">CMS</a></li>
                    <li class="breadcrumb-item" aria-current="page"><a href="{{ route('products.index') }}">Products</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit Product</li>
                </ol>
            </nav>
            <h4 class="mg-b-0 tx-spacing--1">Edit Product</h4>
        </div>
    </div>

    <div class="row row-sm">
        <div class="col-md-8">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <form action="{{ route('products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label class="d-block">Product Name *</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $product->name) }}" class="form-control @error('name') is-invalid @enderror" required maxlength="150">
                            @error('name')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group d-none">
                            <label class="d-block">Price *</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"></span>
                                </div>
                                <!-- <input type="number" name="price" id="price" value="{{ old('price', $product->price) }}" step="0.01" min="0" class="form-control @error('price') is-invalid @enderror" required> -->
                                <input type="number" name="price" id="price" value="1" step="0.01" min="0" class="form-control @error('price') is-invalid @enderror" required>
                            </div>
                            @error('price')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="d-block">Slug *</label>
                    <input type="text" name="slug" id="slug" value="{{ old('slug', $product->slug) }}" class="form-control @error('slug') is-invalid @enderror" required maxlength="150">
                    @error('slug')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="d-block">Brands</label>
                            <select name="category_id" class="selectpicker mg-b-5 @error('category_id') is-invalid @enderror" data-style="btn btn-outline-light btn-md btn-block tx-left" title="Select brand" data-width="100%">
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="d-block">Status *</label>
                            <select name="status" class="selectpicker mg-b-5 @error('status') is-invalid @enderror" data-style="btn btn-outline-light btn-md btn-block tx-left" data-width="100%" required>
                                <option value="active" {{ old('status', $product->status) == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $product->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="d-block">Product Image</label>
                    @if($product->image_url)
                        <div class="mg-b-10">
                            <img id="previewImg" src="{{ Storage::disk('public')->url($product->image_url) }}" alt="Current Image" style="max-height:120px; border-radius:4px; border:1px solid #dee2e6;">
                            <div class="tx-12 tx-gray-500 mg-t-5">Current image — upload a new one to replace it.</div>
                        </div>
                    @else
                        <div id="imagePreview" class="mg-b-10" style="display:none;">
                            <img id="previewImg" src="" alt="Preview" style="max-height:120px; border-radius:4px; border:1px solid #dee2e6;">
                        </div>
                    @endif
                    <div class="custom-file">
                        <input type="file" name="image" id="image" class="custom-file-input @error('image') is-invalid @enderror" accept="image/*">
                        <label class="custom-file-label" for="image">Choose image...</label>
                    </div>
                    @error('image')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="d-block">Description</label>
                    <textarea name="description" id="description" rows="5" class="form-control @error('description') is-invalid @enderror">{{ old('description', $product->description) }}</textarea>
                    @error('description')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <button class="btn btn-primary btn-sm btn-uppercase" type="submit">Update Product</button>
                <a class="btn btn-outline-secondary btn-sm btn-uppercase" href="{{ route('products.index') }}">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection

@section('pagejs')
    <script src="{{ asset('lib/bselect/dist/js/bootstrap-select.js') }}"></script>
    <script src="{{ asset('lib/bselect/dist/js/i18n/defaults-en_US.js') }}"></script>
@endsection

@section('customjs')
<script>
    $(function () {
        $('.selectpicker').selectpicker();

        $('#image').on('change', function () {
            const file = this.files[0];
            if (file) {
                $(this).next('.custom-file-label').text(file.name);
                const reader = new FileReader();
                reader.onload = function (e) {
                    $('#previewImg').attr('src', e.target.result);
                    $('#imagePreview').show();
                };
                reader.readAsDataURL(file);
            } else {
                $(this).next('.custom-file-label').text('Choose image...');
            }
        });
    });
</script>
@endsection