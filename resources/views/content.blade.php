@extends('veltrix::layouts.master')

@section('content')
    <div class="vx-page-header">
        <div>
            <h1 class="vx-page-title">{{ $header }}</h1>
            @if(!empty($description))
                <p class="vx-page-desc">{{ $description }}</p>
            @endif
        </div>

        @if(!empty($breadcrumb) && config('veltrix.enable_default_breadcrumb', true))
            <nav class="vx-breadcrumb">
                <a href="{{ veltrix_base_path() }}">Home</a>
                @foreach($breadcrumb as $item)
                    <span>/</span>
                    @if(isset($item['url']))
                        <a href="{{ $item['url'] }}">{{ $item['text'] }}</a>
                    @else
                        <span>{{ $item['text'] ?? $item }}</span>
                    @endif
                @endforeach
            </nav>
        @endif
    </div>

    <div class="vx-page-body">
        {!! $_content_ !!}

        @if(isset($_view_) && $_view_ && isset($_view_['view']))
            @include($_view_['view'], $_view_['data'])
        @endif
    </div>
@endsection
