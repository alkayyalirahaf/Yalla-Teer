@extends('layouts.app', ['area' => 'my'])
@section('title', __('Traveler profile'))
@section('content')
<h1 class="traveler-profile-title">{{ __('Traveler profile') }}</h1>
<div class="traveler-profile">
    <section class="panel" aria-labelledby="profile-account-title">
        <h2 id="profile-account-title">{{ __('Account overview') }}</h2>
        <dl class="traveler-profile-facts">
            <div><dt>{{ __('Email') }}</dt><dd>{{ $user->email }}</dd></div>
            @if($user->created_at)
                <div><dt>{{ __('Member since') }}</dt><dd>{{ $user->created_at->format('M Y') }}</dd></div>
            @endif
            <div><dt>{{ __('Credits balance') }}</dt><dd>JOD {{ $user->customer->credit_balance }}</dd></div>
        </dl>
        <a class="button secondary" href="{{ route('my.credits') }}">{{ __('View credits wallet') }}</a>
    </section>
    <form method="POST" class="panel stack" action="{{ route('my.profile.update') }}" aria-labelledby="profile-contact-title">
        @csrf
        @method('PATCH')
        <h2 id="profile-contact-title">{{ __('Personal details') }}</h2>
        <x-travel-field name="name" label="Full name" :value="$user->name" maxlength="255" autocomplete="name" required />
        <x-travel-field name="phone" label="Phone (optional)" type="tel" :value="$user->customer->phone" maxlength="30" autocomplete="tel" />
        <div><button class="button" type="submit">{{ __('Save profile') }}</button></div>
    </form>
</div>
@endsection
