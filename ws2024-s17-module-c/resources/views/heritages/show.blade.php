@extends('layouts.layout')

@section('content')
    <div class="heritage-show">
        <h1>{{ $contentPage->title }}</h1>
        <div class="heritage-content">
            {!! $contentPage->parsedContent !!}
        </div>
    </div>
@endsection