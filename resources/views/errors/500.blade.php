@extends('layouts.app')

@section('title', __('Something went wrong'))

@section('content')
<section class="panel prose-page"><p class="eyebrow">{{ __('Error 500') }}</p><h1>{{ __('Something went wrong') }}</h1><p>{{ __('Try again later or return to the homepage.') }}</p><a class="button" href="{{ route('home') }}">{{ __('Go to homepage') }}</a></section>
@endsection
