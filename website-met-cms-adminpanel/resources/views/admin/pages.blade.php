@extends('layouts.admin')

@section('title')
    Pages
@endsection

@section('main')
    <div class="container">
        <h1>Pages</h1>
        <div class="pages-list">
            <table>
                <thead>
                    <tr>
                        <th>
                            Title
                        </th>
                        <th>
                            Slug
                        </th>
                        <th>
                            Action
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pages as $page)
                        <tr>
                            <td>
                                {{ $page->title }}
                            </td>
                            <td>
                                {{ $page->slug }}
                            </td>
                            <td>
                                <a class="btn" href="/admin/pages/{{ $page->id }}">Edit</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection