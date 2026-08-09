@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto py-12">
    <div class="bg-white rounded-xl shadow-xs border border-gray-100 p-8 sm:p-10 space-y-6">
        
        <!-- Header -->
        <div class="text-center space-y-2">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-[#2E7D32]/10 text-[#2E7D32] mb-1">
                <i class="fa-solid fa-right-to-bracket text-xl"></i>
            </div>
            <h2 class="text-2xl font-heading font-bold text-[#222222]">Welcome Back</h2>
            <p class="text-xs text-[#666666]">Sign in to your LeftoverLink account</p>
        </div>

        <form action="{{ route('login') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-semibold text-[#222222] mb-1">Email Address *</label>
                <input type="email" name="email" value="{{ old('email') }}" required placeholder="name@example.com"
                       class="w-full px-4 py-3 bg-[#F5F5F5] border border-gray-200 rounded-xl text-sm text-[#222222] focus:outline-none focus:border-[#2E7D32] focus:bg-white transition-colors">
            </div>

            <div>
                <label class="block text-xs font-semibold text-[#222222] mb-1">Password *</label>
                <input type="password" name="password" required placeholder="••••••••"
                       class="w-full px-4 py-3 bg-[#F5F5F5] border border-gray-200 rounded-xl text-sm text-[#222222] focus:outline-none focus:border-[#2E7D32] focus:bg-white transition-colors">
            </div>

            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="remember" class="w-4 h-4 rounded text-[#2E7D32] border-gray-300 focus:ring-[#2E7D32]">
                    <span class="text-xs text-[#666666]">Remember me</span>
                </label>
            </div>

            <button type="submit" class="w-full py-3.5 bg-[#2E7D32] hover:bg-[#256928] text-white font-medium text-xs rounded-full shadow-md shadow-[#2E7D32]/20 hover:scale-105 transition-all">
                Sign In
            </button>

            <p class="text-center text-xs text-[#666666] pt-2">
                Don't have an account? <a href="{{ route('register') }}" class="font-bold text-[#2E7D32] hover:underline">Register now</a>
            </p>
        </form>

    </div>
</div>
@endsection
