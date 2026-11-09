<x-guest-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Choose a new password</h1>
        <p class="mt-1 text-sm text-slate-500">Enter and confirm your new password below.</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <label for="email" class="label">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email', $request->email) }}"
                   required autofocus autocomplete="username"
                   class="input @error('email') input-invalid @enderror">
            @error('email')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="label">New password</label>
            <input id="password" name="password" type="password"
                   required autocomplete="new-password"
                   class="input @error('password') input-invalid @enderror">
            @error('password')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password_confirmation" class="label">Confirm password</label>
            <input id="password_confirmation" name="password_confirmation" type="password"
                   required autocomplete="new-password"
                   class="input @error('password_confirmation') input-invalid @enderror">
            @error('password_confirmation')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary w-full">Reset password</button>
    </form>
</x-guest-layout>