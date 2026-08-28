@extends('layouts.admin')

@section('title')
    Dashboard
@endsection

@section('main')
    <div class="container">
        <h1>Dashboard</h1>
        <div class="stat-wrapper">
            <div class="stat">
                <h2 class="label">Builds</h2>
                <span class="value">{{ $buildCount }}</span>
            </div>
            <div class="stat">
                <h2 class="label">Active builds</h2>
                <span class="value">{{ $activeBuildCount }}</span>
            </div>
            <div class="stat">
                <h2 class="label">Competitions</h2>
                <span class="value">{{ $competitionCount }}</span>
            </div>
        </div>
    </div>
@endsection