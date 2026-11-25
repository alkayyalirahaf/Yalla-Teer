@extends('super-admin.management-layout')

@section('admin-title', __('Airlines'))
@section('admin-content')
    <h1>{{ __('Airlines') }}</h1>
    @yield('airlines-content')
@endsection
