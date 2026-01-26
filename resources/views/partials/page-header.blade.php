<div class="row mb-4">
    <div class="col-12 {{ isset($actions) ? 'd-flex justify-content-between align-items-center' : '' }}">
        <h1 class="h3 mb-0">{{ $title ?? 'Page' }}</h1>
        @if(isset($actions))
            <div>
                {!! $actions !!}
            </div>
        @endif
    </div>
</div>
