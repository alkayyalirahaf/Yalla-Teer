@extends('super-admin.management-layout')
@section('admin-title', __('Edit airline admin'))
@section('admin-content')
    <h1>{{ __('Edit airline admin') }}</h1>
    <form method="POST" action="{{ route('admin.airline-admins.update', $airlineAdmin) }}">@csrf @method('PUT')
        @include('super-admin.airline-admins.form')
    </form>
@endsection
