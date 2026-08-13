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

    @if($user->isProvider())
        <!-- Provider Rating & Customer Reviews Card -->
        @php
            $providerReviews = $user->reviewsReceived()->with(['user', 'food'])->latest()->get();
        @endphp
        <div class="bg-white rounded-xl shadow-xs border border-gray-100 p-6 sm:p-8 space-y-6">
            <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                <div>
                    <h2 class="text-base font-bold text-[#222222] flex items-center gap-2">
                        <i class="fa-solid fa-star text-amber-500"></i> Provider Rating & Customer Reviews
                    </h2>
                    <p class="text-xs text-[#666666]">Feedback and ratings submitted by consumers for your food listings</p>
                </div>
                <div>
                    @if($user->reviews_count > 0)
                        <span class="px-3 py-1 bg-amber-50 text-amber-700 text-xs font-bold rounded-full border border-amber-200/60 flex items-center gap-1">
                            <i class="fa-solid fa-star text-amber-500"></i> {{ number_format($user->average_rating, 1) }} / 5.0
                        </span>
                    @else
                        <span class="text-xs text-gray-400 italic">No reviews yet</span>
                    @endif
                </div>
            </div>

            @if($providerReviews->count() > 0)
                <div class="space-y-4">
                    @foreach($providerReviews as $rev)
                        <div class="p-4 rounded-xl bg-[#F5F5F5] space-y-2 text-xs">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-[#222222]">{{ $rev->user->name ?? 'Consumer' }}</span>
                                <span class="text-amber-500 font-bold">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fa-{{ $i <= $rev->rating ? 'solid' : 'regular' }} fa-star"></i>
                                    @endfor
                                    ({{ $rev->rating }}/5)
                                </span>
                            </div>
                            <p class="text-[11px] text-[#666666]">Item: <strong>{{ $rev->food->food_name ?? 'Food' }}</strong> • {{ $rev->created_at->format('M d, Y') }}</p>
                            @if($rev->comment)
                                <p class="text-xs text-[#222222] italic">"{{ $rev->comment }}"</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="p-6 text-center text-xs text-[#666666]">
                    <i class="fa-regular fa-star text-2xl text-amber-300 mb-2"></i>
                    <p class="font-medium">No reviews received yet</p>
                    <p class="text-[11px] text-gray-400">Customer feedback will appear here after food pickups are completed.</p>
                </div>
            @endif
        </div>
    @endif

</div>
@endsection
