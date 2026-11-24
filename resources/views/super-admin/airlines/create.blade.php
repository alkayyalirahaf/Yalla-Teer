@extends('super-admin.airlines.layout')
@section('airlines-content')
    <h2>{{ __('Add airline') }}</h2>
    <form method="POST" action="{{ route('admin.airlines.store') }}" enctype="multipart/form-data">
        @include('super-admin.airlines.form')
    </form>
@endsection
