
    <h2>{{ __('Login') }}</h2>

    @if($errors->any())
        <div style="color: red;">{{ $errors->first() }}</div>
    @endif

    <form action="{{ route('admin.login.submit') }}" method="POST">
        @csrf
        <label>{{ __('Email:') }}</label><br>
        <input type="email" name="email" value="{{ old('email') }}" required><br><br>

        <label>{{ __('Password:') }}</label><br>
        <input type="password" name="password" required><br><br>

        <button type="submit">{{ __('Login') }}</button>
    </form>
