@extends('super-admin.countries.layout')

@section('countries-content')
    <h1>Edit destination: {{ $country->name }}</h1>
    <form class="panel p-3 p-md-4" method="POST" action="{{ route('admin.countries.update', $country) }}">
        @method('PUT')
        @include('super-admin.countries.form')
    </form>
@endsection
