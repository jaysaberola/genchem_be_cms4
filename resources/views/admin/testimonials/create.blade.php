@extends('admin.layouts.app')

@section('pagetitle')
    Testimonial Management
@endsection

@section('content')
<div class="container pd-x-0">
    <div class="d-sm-flex align-items-center justify-content-between mg-b-20 mg-lg-b-25 mg-xl-b-30">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mg-b-10">
                    <li class="breadcrumb-item" aria-current="page"><a href="{{ route('dashboard') }}">CMS</a></li>
                    <li class="breadcrumb-item" aria-current="page"><a href="{{ route('testimonials.index') }}">Testimonials</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Add Testimonial</li>
                </ol>
            </nav>
            <h4 class="mg-b-0 tx-spacing--1">Add a Testimonial</h4>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <form action="{{ route('testimonials.store') }}" method="POST" enctype="multipart/form-data">
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
                    <label class="d-block">Company</label>
                    <input type="text" name="company" id="company" value="{{ old('company') }}" class="form-control @error('company') is-invalid @enderror" maxlength="100">
                    @error('company')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="d-block">Testimonial *</label>
                    <textarea name="testimony" id="testimony" rows="5" class="form-control @error('testimony') is-invalid @enderror" required>{{ old('testimony') }}</textarea>
                    @error('testimony')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="d-block">Thumbnail</label>
                    <div class="custom-file">
                        <input type="file" name="thumbnail" id="thumbnail" class="custom-file-input @error('thumbnail') is-invalid @enderror" accept="image/*">
                        <label class="custom-file-label" for="thumbnail">Choose image...</label>
                    </div>
                    @error('thumbnail')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                    <div id="thumbPreview" class="mg-t-10" style="display:none;">
                        <img id="previewImg" src="" alt="Preview" style="width:80px; height:80px; object-fit:cover; border-radius:50%; border:1px solid #dee2e6;">
                    </div>
                </div>

                <button class="btn btn-primary btn-sm btn-uppercase" type="submit">Add Testimonial</button>
                <a class="btn btn-outline-secondary btn-sm btn-uppercase" href="{{ route('testimonials.index') }}">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection

@section('customjs')
<script>
    $(function () {
        $('#thumbnail').on('change', function () {
            const file = this.files[0];
            if (file) {
                $(this).next('.custom-file-label').text(file.name);
                const reader = new FileReader();
                reader.onload = function (e) {
                    $('#previewImg').attr('src', e.target.result);
                    $('#thumbPreview').show();
                };
                reader.readAsDataURL(file);
            } else {
                $('#thumbPreview').hide();
                $(this).next('.custom-file-label').text('Choose image...');
            }
        });
    });
</script>
@endsection