@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto py-6 space-y-6">

    <div class="flex items-center justify-between border-b border-gray-200/60 pb-4">
        <div>
            <h1 class="text-2xl font-heading font-bold text-[#222222]">Profile Settings</h1>
            <p class="text-xs text-[#666666]">Update personal details, upload profile picture, and change password</p>
        </div>
        <span class="px-3 py-1 rounded-full text-xs font-bold bg-[#2E7D32]/10 text-[#2E7D32] uppercase">
            {{ ucfirst(str_replace('_', ' ', $user->role)) }}
        </span>
    </div>

    <!-- Info Form -->
    <div class="bg-white rounded-xl shadow-xs border border-gray-100 p-6 sm:p-8 space-y-6">
        <h2 class="text-base font-bold text-[#222222] flex items-center gap-2">
            <i class="fa-solid fa-id-card text-[#2E7D32]"></i> Personal Information
        </h2>

        <form action="{{ route('profile.updateInfo') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- Avatar -->
            <div class="flex items-center gap-5 p-4 rounded-xl bg-[#F5F5F5]">
                <div class="w-16 h-16 rounded-full bg-[#2E7D32] text-white flex items-center justify-center font-bold text-xl overflow-hidden shrink-0">
                    @if($user->profile_photo)
                        <img src="{{ asset('storage/' . $user->profile_photo) }}" alt="Avatar" class="w-full h-full object-cover">
                    @else
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    @endif
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-semibold text-[#222222]">Upload Profile Picture</label>
                    <input type="file" name="profile_photo" accept="image/*" class="block w-full text-xs text-[#666666] file:mr-4 file:py-1.5 file:px-3 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-[#2E7D32]/10 file:text-[#2E7D32]">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-[#222222] mb-1">Full Name</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full px-4 py-2.5 bg-[#F5F5F5] border border-gray-200 rounded-xl text-sm text-[#222222]">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#222222] mb-1">Email Address</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full px-4 py-2.5 bg-[#F5F5F5] border border-gray-200 rounded-xl text-sm text-[#222222]">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#222222] mb-1">Phone Number</label>
                    <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}" required class="w-full px-4 py-2.5 bg-[#F5F5F5] border border-gray-200 rounded-xl text-sm text-[#222222]">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#222222] mb-1">Address / Location</label>
                    <input type="text" name="address" value="{{ old('address', $user->address) }}" class="w-full px-4 py-2.5 bg-[#F5F5F5] border border-gray-200 rounded-xl text-sm text-[#222222]">
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-6 py-2.5 bg-[#2E7D32] hover:bg-[#256928] text-white text-xs font-medium rounded-full shadow-md">
                    Save Profile Changes
                </button>
            </div>
        </form>
    </div>

    <!-- Password Form -->
    <div class="bg-white rounded-xl shadow-xs border border-gray-100 p-6 sm:p-8 space-y-6">
        <h2 class="text-base font-bold text-[#222222] flex items-center gap-2">
            <i class="fa-solid fa-lock text-[#2E7D32]"></i> Change Password
        </h2>

        <form action="{{ route('profile.updatePassword') }}" method="POST" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-[#222222] mb-1">Current Password</label>
                    <input type="password" name="current_password" required placeholder="••••••••" class="w-full px-4 py-2.5 bg-[#F5F5F5] border border-gray-200 rounded-xl text-sm text-[#222222]">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#222222] mb-1">New Password</label>
                    <input type="password" name="password" required placeholder="••••••••" class="w-full px-4 py-2.5 bg-[#F5F5F5] border border-gray-200 rounded-xl text-sm text-[#222222]">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#222222] mb-1">Confirm Password</label>
                    <input type="password" name="password_confirmation" required placeholder="••••••••" class="w-full px-4 py-2.5 bg-[#F5F5F5] border border-gray-200 rounded-xl text-sm text-[#222222]">
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-6 py-2.5 bg-[#F5F5F5] hover:bg-gray-200 text-[#222222] text-xs font-medium rounded-full">
                    Update Password
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
