@extends('layouts.app')

@section('title', __('Page not found'))

@section('content')
<section class="panel prose-page"><p class="eyebrow">{{ __('Error 404') }}</p><h1>{{ __('Page not found') }}</h1><p>{{ __('Check the address or search for a flight.') }}</p><a class="button" href="{{ route('home') }}">{{ __('Go to homepage') }}</a></section>
<div class="section"><x-travel-search /></div>
@endsection
