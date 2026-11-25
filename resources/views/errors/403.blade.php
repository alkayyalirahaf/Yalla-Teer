@extends('layouts.app')

@section('title', __('Access denied'))

@section('content')
<section class="panel prose-page"><p class="eyebrow">{{ __('Error 403') }}</p><h1>{{ __('Access denied') }}</h1><p>{{ __('Your account does not have access to this page.') }}</p><a class="button" href="{{ route('home') }}">{{ __('Go to homepage') }}</a></section>
@endsection
