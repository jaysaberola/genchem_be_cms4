
<div class="content-search content-company">
    <h3 class="tx-15 mg-b-0">{{ Setting::info()->company_name }}</h3>
</div>

<div class="dropdown dropdown-profile">
    @php
        $currentUser = Auth::user() ? Auth::user()->fresh() : null;
        $avatarUrl = \App\Helpers\Setting::resolve_user_avatar_url(optional($currentUser)->avatar) ?? asset('images/user.png');
        $avatarVersion = optional(optional($currentUser)->updated_at)->timestamp ?? time();
    @endphp
    <a href="" class="dropdown-link" data-toggle="dropdown" data-display="static">
        @if(optional($currentUser)->avatar == '')
            <div class="avatar avatar-sm"><img src="{{ asset('images/user.png') }}" class="rounded-circle" alt=""></div>
        @else
            <div class="avatar avatar-sm"><img src="{{ $avatarUrl }}?v={{ $avatarVersion }}" class="rounded-circle" alt="" onerror="this.onerror=null;this.src='{{ asset('images/user.png') }}';"></div>
        @endif
    </a>
    <!-- dropdown-link -->
    <div class="dropdown-menu dropdown-menu-right tx-13">
        <h6 class="tx-semibold mg-b-5">{{ optional($currentUser)->fullname }}</h6>
        <p class="tx-12 tx-color-03">{{ optional($currentUser)->role }}</p>
        <div class="dropdown-divider"></div>

        <a href="{{ route('account.edit', optional($currentUser)->id ) }}" class="dropdown-item"><i data-feather="edit-3"></i> Account Settings</a>
        <a href="{{ URL::asset('user-manual/TheVanguardAcademyUserGuideOct2021.pdf') }}" target="_blank" class="dropdown-item"><i data-feather="help-circle"></i> Help</a>
        <a href="{{route('logout')}}" class="dropdown-item" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i data-feather="log-out"></i>Log Out</a>

        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
            <input type="hidden" name="roleid" value="{{ optional($currentUser)->role_id }}">
        </form>
    </div>
    <!-- dropdown-menu -->
</div>
<!-- dropdown -->

