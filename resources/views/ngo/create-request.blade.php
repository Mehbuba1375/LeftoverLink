@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto space-y-8 pb-12">

    {{-- Breadcrumb / Header --}}
    <div class="border-b border-gray-200/60 pb-4">
        <a href="{{ route('donations.index') }}" class="inline-flex items-center gap-1.5 text-xs text-[#2E7D32] hover:underline font-medium mb-2">
            <i class="fa-solid fa-chevron-left"></i> Back to Donations
        </a>
        <h1 class="text-2xl font-heading font-extrabold text-[#222222]">Request Donation Collection</h1>
        <p class="text-xs text-[#666666]">Submit a collection request to coordinate food distribution for your community</p>
    </div>

    {{-- Food Details Preview Card --}}
    <div class="bg-white rounded-xl border border-gray-100 p-5 shadow-xs flex flex-col sm:flex-row gap-5">
        <div class="w-full sm:w-40 h-28 bg-gray-100 rounded-lg overflow-hidden shrink-0">
            @if($food->image)
                <img src="{{ asset('storage/' . $food->image) }}" alt="{{ $food->food_name }}" class="w-full h-full object-cover">
            @else
                <div class="w-full h-full flex items-center justify-center text-gray-400 bg-[#F5F5F5]">
                    <i class="fa-solid fa-image text-2xl"></i>
                </div>
            @endif
        </div>
        <div class="space-y-2 flex-1 min-w-0">
            <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold bg-[#F59E0B]/10 text-[#F59E0B]">
                {{ $food->category }}
            </span>
            <h3 class="font-heading font-bold text-lg text-[#222222] truncate">{{ $food->food_name }}</h3>
            <p class="text-xs text-[#666666] line-clamp-2">{{ $food->description ?? 'No description provided.' }}</p>
            
            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-[#666666] pt-1">
                <span>Available Stock: <strong class="text-[#222222]">{{ $food->quantity }}</strong> items</span>
                <span class="w-1.5 h-1.5 rounded-full bg-gray-300"></span>
                <span>Expires: <strong class="text-[#222222]">{{ $food->expiration_time ? $food->expiration_time->format('M d, Y h:i A') : 'N/A' }}</strong></span>
            </div>
        </div>
    </div>

    {{-- Request Form --}}
    <div class="bg-white rounded-xl border border-gray-100 p-6 sm:p-8 shadow-xs">
        <form action="{{ route('ngo.requests.store', $food->id) }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                {{-- Quantity Requested --}}
                <div class="sm:col-span-1">
                    <label for="quantity_requested" class="block text-xs font-semibold text-[#222222] mb-1.5">Quantity to Request</label>
                    <div class="relative">
                        <input type="number" name="quantity_requested" id="quantity_requested" 
                               value="{{ old('quantity_requested', 1) }}" min="1" max="{{ $food->quantity }}" required
                               class="w-full px-3.5 py-3 bg-[#F5F5F5] border border-gray-200 focus:border-[#2E7D32] focus:ring-1 focus:ring-[#2E7D32] rounded-xl text-xs text-[#222222] outline-hidden transition-all">
                        <div class="absolute inset-y-0 right-3.5 flex items-center pointer-events-none text-[11px] text-[#666666]">
                            max {{ $food->quantity }}
                        </div>
                    </div>
                    @error('quantity_requested')
                        <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Proposed Pickup Time --}}
                <div class="sm:col-span-1">
                    <label for="pickup_time" class="block text-xs font-semibold text-[#222222] mb-1.5">Proposed Pickup Time</label>
                    <input type="text" name="pickup_time" id="pickup_time" 
                           placeholder="e.g. 4:00 PM - 5:00 PM Today" value="{{ old('pickup_time', $food->pickup_window) }}" required
                           class="w-full px-3.5 py-3 bg-[#F5F5F5] border border-gray-200 focus:border-[#2E7D32] focus:ring-1 focus:ring-[#2E7D32] rounded-xl text-xs text-[#222222] outline-hidden transition-all">
                    @error('pickup_time')
                        <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Contact Name --}}
                <div class="sm:col-span-1">
                    <label for="contact_name" class="block text-xs font-semibold text-[#222222] mb-1.5">Contact Person Name</label>
                    <input type="text" name="contact_name" id="contact_name" 
                           value="{{ old('contact_name', auth()->user()->name) }}" required
                           class="w-full px-3.5 py-3 bg-[#F5F5F5] border border-gray-200 focus:border-[#2E7D32] focus:ring-1 focus:ring-[#2E7D32] rounded-xl text-xs text-[#222222] outline-hidden transition-all">
                    @error('contact_name')
                        <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Contact Number --}}
                <div class="sm:col-span-1">
                    <label for="contact_no" class="block text-xs font-semibold text-[#222222] mb-1.5">Contact Number</label>
                    <input type="text" name="contact_no" id="contact_no" 
                           value="{{ old('contact_no', auth()->user()->phone) }}" required
                           class="w-full px-3.5 py-3 bg-[#F5F5F5] border border-gray-200 focus:border-[#2E7D32] focus:ring-1 focus:ring-[#2E7D32] rounded-xl text-xs text-[#222222] outline-hidden transition-all">
                    @error('contact_no')
                        <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Delivery / Collection Address --}}
                <div class="sm:col-span-2">
                    <label for="address" class="block text-xs font-semibold text-[#222222] mb-1.5">Collection Address / NGO Location</label>
                    <input type="text" name="address" id="address" 
                           value="{{ old('address', auth()->user()->address) }}" required
                           class="w-full px-3.5 py-3 bg-[#F5F5F5] border border-gray-200 focus:border-[#2E7D32] focus:ring-1 focus:ring-[#2E7D32] rounded-xl text-xs text-[#222222] outline-hidden transition-all">
                    @error('address')
                        <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Message --}}
                <div class="sm:col-span-2">
                    <label for="message" class="block text-xs font-semibold text-[#222222] mb-1.5">Message / Special Instructions (Optional)</label>
                    <textarea name="message" id="message" rows="4" 
                              placeholder="Describe your distribution program or specify special handling requirements..."
                              class="w-full px-3.5 py-3 bg-[#F5F5F5] border border-gray-200 focus:border-[#2E7D32] focus:ring-1 focus:ring-[#2E7D32] rounded-xl text-xs text-[#222222] outline-hidden transition-all resize-none">{{ old('message') }}</textarea>
                    @error('message')
                        <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Form Submit Buttons --}}
            <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-6">
                <a href="{{ route('donations.index') }}" 
                   class="px-5 py-2.5 bg-gray-50 hover:bg-gray-100 text-[#222222] text-xs font-medium rounded-full transition-colors">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-6 py-2.5 bg-[#F59E0B] hover:bg-[#D97706] text-white text-xs font-medium rounded-full shadow-md shadow-[#F59E0B]/20 transition-all hover:scale-105">
                    <i class="fa-solid fa-paper-plane mr-1.5"></i> Submit Collection Request
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
