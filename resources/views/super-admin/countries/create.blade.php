@extends('super-admin.countries.layout')

@section('countries-content')
    <h1>Create destination</h1>
    <form class="panel p-3 p-md-4" method="POST" action="{{ route('admin.countries.store') }}">
        @include('super-admin.countries.form')
    </form>
@endsection
