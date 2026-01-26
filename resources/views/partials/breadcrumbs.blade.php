@if(isset($breadcrumbs) && is_array($breadcrumbs) && count($breadcrumbs) > 0)
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="{{ Auth::user()->isAdmin() ? route('admin.dashboard') : (Auth::user()->isTeacher() ? route('teacher.dashboard') : route('student.dashboard')) }}">
                <i class="fas fa-home me-1"></i>Tableau de bord
            </a>
        </li>
        @foreach($breadcrumbs as $breadcrumb)
        @if($loop->last)
            <li class="breadcrumb-item active" aria-current="page">{{ $breadcrumb['title'] }}</li>
        @else
            <li class="breadcrumb-item">
                @if(isset($breadcrumb['url']))
                    <a href="{{ $breadcrumb['url'] }}">{{ $breadcrumb['title'] }}</a>
                @else
                    {{ $breadcrumb['title'] }}
                @endif
            </li>
        @endif
        @endforeach
    </ol>
</nav>
@endif
