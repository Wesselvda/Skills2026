@extends('layouts.layout')

@section('content')
    <div class="heritage-list">
        @foreach ($contentPages as $contentPage)
            <li class="heritage-item">
                @if ($contentPage instanceof \App\Models\ContentPage)
                    <a class="heritage-link" href="{{ route('heritage.show', ['slug' => $contentPage->fileName]) }}">
                        {{ $contentPage->title }}
                    </a>
                    <span class="heritage-summary">{{ $contentPage->summary }}</span>
                @else
                    <a class="heritage-link" href="{{ route('heritage.show', ['slug' => ltrim($contentPage->filePath, '/')]) }}">
                        {{ $contentPage->fileName }}
                    </a>
                @endif
            </li>
        @endforeach
    </div>
@endsection
