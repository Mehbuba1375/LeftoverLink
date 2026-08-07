@extends('layouts.app')

@section('content')
<div class="space-y-6 pb-12">

    <!-- Page Title & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-gray-200/60 pb-3">
        <div>
            <h1 class="text-2xl font-heading font-extrabold text-[#222222]">Your Saved Favorite Listings</h1>
            <p class="text-xs text-[#666666]">Quick access to surplus meals and donations you have favorited</p>
        </div>

        <div class="flex items-center gap-2">
            <span class="text-xs text-[#666666]">Total Favorites: <strong class="text-[#222222]">{{ count($favoriteFoods) }}</strong></span>
        </div>
    </div>

    <!-- Favorite Food Cards Grid -->
    @if(count($favoriteFoods) > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($favoriteFoods as $food)
                <x-food-card :item="[
                    'id' => $food->id,
                    'food_name' => $food->food_name,
                    'category' => $food->category,
                    'quantity' => $food->quantity,
                    'price' => $food->price,
                    'expiration_time' => $food->expiration_time ? $food->expiration_time->format('M d, Y h:i A') : 'N/A',
                    'expiration_time_raw' => $food->expiration_time,
                    'pickup_window' => $food->pickup_window,
                    'donation_status' => $food->donation_status,
                    'image_url' => $food->image ? asset('storage/' . $food->image) : asset('images/default-food.png'),
                    'provider_name' => $food->user ? $food->user->name : 'Food Provider',
                    'average_rating' => $food->average_rating,
                    'reviews_count' => $food->reviews_count,
                    'is_favorited' => true,
                ]" />
            @endforeach
        </div>
    @else
        <!-- Friendly Empty State -->
        <div class="bg-white rounded-xl border border-gray-100 p-12 text-center space-y-4 max-w-xl mx-auto my-8 shadow-xs">
            <div class="w-16 h-16 rounded-full bg-red-50 text-[#EF4444] flex items-center justify-center mx-auto text-2xl">
                <i class="fa-solid fa-heart"></i>
            </div>
            <div class="space-y-1">
                <h3 class="text-lg font-bold text-[#222222]">No Favorite Listings Yet</h3>
                <p class="text-xs text-[#666666]">Start exploring food listings and save your favorite items for quick access.</p>
            </div>
            <div class="pt-2">
                <a href="{{ route('donations.index') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-[#2E7D32] hover:bg-[#256928] text-white text-xs font-medium rounded-full shadow-md shadow-[#2E7D32]/20 hover:scale-105 transition-all">
                    <i class="fa-solid fa-compass"></i> Browse Listings
                </a>
            </div>
        </div>
    @endif

</div>
@endsection
