<aside class="aside aside-fixed">
    @php
        $currentUser = Auth::user() ? Auth::user()->fresh() : null;
        $avatarUrl = \App\Helpers\Setting::resolve_user_avatar_url(optional($currentUser)->avatar) ?? asset('images/user.png');
        $avatarVersion = optional(optional($currentUser)->updated_at)->timestamp ?? time();
    @endphp
    <div class="aside-header">
        <a href="{{ route('dashboard') }}" class="aside-logo">Admin <span>Portal</span></a>
        <a href="" class="aside-menu-link">
            <i data-feather="menu"></i>
            <i data-feather="x"></i>
        </a>
    </div>
    <div class="aside-body">
        <div class="aside-loggedin">
            @if(optional($currentUser)->avatar == '')
                <div class="d-flex justify-content-center">
                    <a href="{{ route('account.edit') }}" class="avatar wd-100"><img src="{{ asset('images/user.png') }}" class="rounded-circle" alt=""></a>
                </div>
                <div class="aside-loggedin-user tx-center">
                    <h6 class="tx-semibold mg-b-0">{{ optional($currentUser)->fullname }}</h6>
                    <p class="tx-color-03 tx-11 mg-b-0 tx-uppercase">{{ App\Models\User::userRole(optional($currentUser)->role_id) }}</p>
                </div>
            @else
                <div class="d-flex justify-content-center">
                    <a href="{{ route('account.edit') }}" class="avatar wd-100"><img src="{{ $avatarUrl }}?v={{ $avatarVersion }}" class="rounded-circle" alt="" onerror="this.onerror=null;this.src='{{ asset('images/user.png') }}';"></a>
                </div>
                <div class="aside-loggedin-user tx-center">
                    <h6 class="tx-semibold mg-b-0">{{ optional($currentUser)->fullname }}</h6>
                    <p class="tx-color-03 tx-11 mg-b-0 tx-uppercase">{{ App\Models\User::userRole(optional($currentUser)->role_id) }}</p>
                </div>
            @endif
        </div>
        <!-- aside-loggedin -->
        @include('admin.layouts.sidebar-menu')
    </div>
</aside>