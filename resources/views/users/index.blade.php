@extends('layouts.karle')

@section('title', 'Users')

@section('content')
    <div class="space-y-6">
        <div><h1 class="page-title">Users</h1><p class="mt-1 text-sm text-slate-600">Review accounts and administer roles. Authentication credentials are not displayed.</p></div>
        <section class="card overflow-hidden shadow-md">
            @if ($users->isEmpty())
                <p class="px-5 py-10 text-center text-sm text-slate-600">No users found.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-slate-50"><tr><th class="th">Name</th><th class="th">Email</th><th class="th">Role</th><th class="th">Registered</th><th class="th text-right">Details</th></tr></thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach ($users as $user)
                                <tr>
                                    <td class="td font-medium text-slate-900">{{ $user->name }}</td>
                                    <td class="td">{{ $user->email }}</td>
                                    <td class="td"><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $user->isAdmin() ? 'bg-indigo-50 text-indigo-700' : 'bg-slate-100 text-slate-700' }}">{{ strtoupper($user->role) }}</span></td>
                                    <td class="td whitespace-nowrap">{{ $user->created_at?->format('M j, Y') }}</td>
                                    <td class="td text-right"><a href="{{ route('users.show', $user) }}" class="btn btn-secondary btn-sm">View</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-slate-200 px-5 py-4">{{ $users->links() }}</div>
            @endif
        </section>
    </div>
@endsection
