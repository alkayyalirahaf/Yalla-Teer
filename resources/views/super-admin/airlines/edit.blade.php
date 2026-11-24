@extends('super-admin.airlines.layout')
@section('airlines-content')
    <h2>{{ __('Edit') }} {{ $airline->name }}</h2>
    <form method="POST" action="{{ route('admin.airlines.update', $airline->id) }}" enctype="multipart/form-data">
        @method('PUT')
        @include('super-admin.airlines.form')
    </form>
@endsection
