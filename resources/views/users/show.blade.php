@extends('layouts.karle')

@section('title', 'User Details')

@section('content')
    <div class="mx-auto max-w-2xl space-y-6">
        <div><a href="{{ route('users.index') }}" class="text-sm font-medium text-indigo-700 hover:text-indigo-800">&larr; Back to users</a><h1 class="page-title mt-2">User Details</h1></div>
        <section class="card space-y-5 p-6 shadow-md sm:p-8">
            <dl class="grid gap-5 sm:grid-cols-2">
                <div><dt class="text-sm font-medium text-slate-500">Name</dt><dd class="mt-1 text-slate-900">{{ $user->name }}</dd></div>
                <div><dt class="text-sm font-medium text-slate-500">Email</dt><dd class="mt-1 break-all text-slate-900">{{ $user->email }}</dd></div>
                <div><dt class="text-sm font-medium text-slate-500">Role</dt><dd class="mt-1 text-slate-900">{{ strtoupper($user->role) }}</dd></div>
                <div><dt class="text-sm font-medium text-slate-500">Registered</dt><dd class="mt-1 text-slate-900">{{ $user->created_at?->format('M j, Y') }}</dd></div>
            </dl>
            @if ($user->id !== auth()->id())
                <form method="POST" action="{{ route('users.update-role', $user) }}" class="flex flex-col gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:items-end">
                    @csrf
                    @method('PUT')
                    <div class="flex-1"><label for="role" class="label">Change role</label>
                        <select id="role" name="role" class="input">
                            <option value="user" @selected($user->role === \App\Models\User::ROLE_USER)>User</option>
                            <option value="admin" @selected($user->role === \App\Models\User::ROLE_ADMIN)>Admin</option>
                        </select>
                    </div>
                    <button class="btn btn-primary" type="submit">Save role</button>
                </form>
                <form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('Delete this user account?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-danger" type="submit">Delete user</button>
                </form>
            @else
                <p class="border-t border-slate-200 pt-5 text-sm text-slate-600">Use your profile page to manage your own account.</p>
            @endif
        </section>
    </div>
@endsection
