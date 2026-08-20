@extends('layouts.app')

@section('content')
<div x-init="$store.marketplace.fetchFilteredFoods()" class="space-y-6 pb-12">

    <!-- Page Title & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-gray-200/60 pb-3">
        <div>
            <h1 class="text-2xl font-heading font-extrabold text-[#222222]">Your Saved Favorite Listings</h1>
            <p class="text-xs text-[#666666]">Quick access to surplus meals and donations you have favorited</p>
        </div>

        <div class="flex items-center gap-2">
            <span class="text-xs text-[#666666]">
                Total Favorites: <strong class="text-[#222222]" x-text="$store.marketplace.initialized ? $store.marketplace.itemsCount : {{ count($favoriteFoods) }}">{{ count($favoriteFoods) }}</strong>
            </span>
        </div>
    </div>

    <!-- Filter Drawer Panel (Toggled via Topbar Filter Button) -->
    <div x-show="$store.marketplace.showFilterDrawer" x-cloak class="bg-white rounded-xl shadow-xs border border-gray-100 p-4 sm:p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
        <!-- Category Filter -->
        <div>
            <label class="block font-semibold text-[#222222] mb-1">Category</label>
            <select x-model="$store.marketplace.filters.category" @change="$store.marketplace.fetchFilteredFoods()" class="w-full px-3 py-2.5 bg-[#F5F5F5] border border-gray-200 rounded-xl text-[#222222]">
                <option value="all">All Categories</option>
                @foreach(['Prepared Meals', 'Bakery & Pastries', 'Fresh Produce', 'Dairy & Eggs', 'Beverages', 'Groceries & Snacks', 'Other'] as $cat)
                    <option value="{{ $cat }}">{{ $cat }}</option>
                @endforeach
            </select>
        </div>

        <!-- Listing Type Filter -->
        <div>
            <label class="block font-semibold text-[#222222] mb-1">Listing Type</label>
            <select x-model="$store.marketplace.filters.type" @change="$store.marketplace.fetchFilteredFoods()" class="w-full px-3 py-2.5 bg-[#F5F5F5] border border-gray-200 rounded-xl text-[#222222]">
                <option value="all">All Types</option>
                <option value="discounted">Discounted Sale</option>
                <option value="donated">Free Donation</option>
            </select>
        </div>

        <!-- Provider Filter -->
        <div>
            <label class="block font-semibold text-[#222222] mb-1">Food Provider</label>
            <select x-model="$store.marketplace.filters.provider_id" @change="$store.marketplace.fetchFilteredFoods()" class="w-full px-3 py-2.5 bg-[#F5F5F5] border border-gray-200 rounded-xl text-[#222222]">
                <option value="all">All Providers</option>
                @foreach(\App\Models\User::where('role', 'food_provider')->select('id', 'name')->get() as $prov)
                    <option value="{{ $prov->id }}">{{ $prov->name }}</option>
                @endforeach
            </select>
        </div>

        <!-- Price Range Filter -->
        <div>
            <label class="block font-semibold text-[#222222] mb-1">Price Range (BDT ৳)</label>
            <div class="flex items-center gap-2">
                <input type="number" x-model="$store.marketplace.filters.min_price" @input.debounce.400ms="$store.marketplace.fetchFilteredFoods()" placeholder="Min" class="w-full px-3 py-2.5 bg-[#F5F5F5] border border-gray-200 rounded-xl text-[#222222]">
                <span class="text-gray-400">-</span>
                <input type="number" x-model="$store.marketplace.filters.max_price" @input.debounce.400ms="$store.marketplace.fetchFilteredFoods()" placeholder="Max" class="w-full px-3 py-2.5 bg-[#F5F5F5] border border-gray-200 rounded-xl text-[#222222]">
            </div>
        </div>

        <div class="sm:col-span-2 lg:col-span-4 flex justify-end">
            <button @click="$store.marketplace.resetFilters()" class="px-5 py-2 bg-[#F5F5F5] hover:bg-gray-200 text-[#222222] font-medium rounded-full text-xs transition-colors">
                Reset All Filters
            </button>
        </div>
    </div>

    <!-- Loading Indicator -->
    <div x-show="$store.marketplace.loading" class="text-center py-12 space-y-3">
        <i class="fa-solid fa-circle-notch fa-spin text-2xl text-[#2E7D32]"></i>
        <p class="text-xs text-[#666666]">Fetching favorite food listings...</p>
    </div>

    <!-- Client-Side Reactive Food Cards Grid (When Alpine initialized) -->
    <template x-if="$store.marketplace.initialized">
        <div>
            <div x-show="!$store.marketplace.loading && $store.marketplace.foods.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <template x-for="item in $store.marketplace.foods" :key="item.id">
                    <div class="bg-white rounded-xl shadow-xs border border-gray-100 overflow-hidden flex flex-col hover:-translate-y-1 hover:shadow-md transition-all duration-300 group">
                        
                        <!-- Food Image Banner -->
                        <div class="relative h-44 bg-gray-100 overflow-hidden shrink-0">
                            <img :src="item.image_url" :alt="item.food_name" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">

                            <!-- Badges -->
                            <div class="absolute top-3 left-3 flex flex-wrap gap-1.5">
                                <span x-show="item.donation_status" class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-[#F59E0B] text-white shadow-xs">
                                    <i class="fa-solid fa-hand-holding-heart mr-1"></i> Donation
                                </span>
                                <span x-show="!item.donation_status" class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-[#2E7D32] text-white shadow-xs">
                                    <i class="fa-solid fa-tags mr-1"></i> Discounted
                                </span>
                            </div>

                            <!-- Favorite Button -->
                            <button @click="$store.marketplace.toggleFavorite(item.id)" 
                                    :title="item.is_favorited ? 'Remove from Favorites' : 'Add to Favorites'"
                                    class="absolute top-3 right-3 w-8 h-8 rounded-full bg-white/90 backdrop-blur-xs flex items-center justify-center transition-all duration-300 hover:scale-110 shadow-xs z-10">
                                <i :class="item.is_favorited ? 'fa-solid fa-heart text-[#EF4444] scale-110' : 'fa-regular fa-heart text-[#666666] hover:text-[#EF4444]'" class="text-sm transition-transform"></i>
                            </button>

                            <!-- Category Badge -->
                            <div class="absolute bottom-2 left-3">
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold bg-black/60 text-white backdrop-blur-xs" x-text="item.category"></span>
                            </div>
                        </div>

                        <!-- Card Details -->
                        <div class="p-4 flex-1 flex flex-col justify-between space-y-3">
                            <div class="space-y-1.5">
                                <!-- Food Title -->
                                <h3 class="font-heading font-bold text-base text-[#222222] line-clamp-1 group-hover:text-[#2E7D32] transition-colors" x-text="item.food_name"></h3>

                                <!-- Provider Name & Rating -->
                                <div class="flex items-center justify-between text-xs text-[#666666]">
                                    <span class="flex items-center gap-1.5 truncate max-w-[160px]">
                                        <i class="fa-solid fa-store text-[#2E7D32]"></i>
                                        <span class="truncate font-medium text-[#222222]" x-text="item.provider_name"></span>
                                    </span>

                                    <!-- Dynamic Rating -->
                                    <template x-if="item.reviews_count > 0">
                                        <span class="flex items-center gap-1 text-amber-500 font-semibold text-[11px]">
                                            <i class="fa-solid fa-star"></i>
                                            <span x-text="Number(item.average_rating).toFixed(1)"></span>
                                            <span class="text-gray-400 font-normal" x-text="'(' + item.reviews_count + ')'"></span>
                                        </span>
                                    </template>
                                    <template x-if="!item.reviews_count || item.reviews_count === 0">
                                        <span class="text-[11px] text-gray-400 font-medium italic">No reviews yet</span>
                                    </template>
                                </div>
                            </div>

                            <!-- Details List -->
                            <div class="space-y-1.5 text-xs text-[#666666] border-t border-gray-100 pt-2.5">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-heading font-bold text-[#2E7D32]">
                                        <span x-show="!item.donation_status">৳<span x-text="item.price"></span></span>
                                        <span x-show="item.donation_status" class="text-[#F59E0B]">FREE</span>
                                    </span>
                                    <span class="text-[11px] font-medium px-2 py-0.5 rounded-md bg-[#F5F5F5] text-[#222222]" x-text="item.quantity + ' items left'"></span>
                                </div>
                                <div class="flex items-center gap-1.5 text-[11px]">
                                    <i class="fa-solid fa-clock text-[#666666]"></i>
                                    <span class="truncate">Expires: <strong class="text-[#222222] font-medium" x-text="item.expiration_time || 'N/A'"></strong></span>
                                </div>
                                <div class="flex items-center gap-1.5 text-[11px]">
                                    <i class="fa-solid fa-calendar-day text-[#666666]"></i>
                                    <span class="truncate">Pickup: <strong class="text-[#222222] font-medium" x-text="item.pickup_window || 'Flexible'"></strong></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- No Filter Matches Empty State -->
            <div x-show="!$store.marketplace.loading && $store.marketplace.foods.length === 0" class="bg-white rounded-xl border border-gray-100 p-12 text-center space-y-4 max-w-xl mx-auto my-8 shadow-xs">
                <div class="w-16 h-16 rounded-full bg-red-50 text-[#EF4444] flex items-center justify-center mx-auto text-2xl">
                    <i class="fa-solid fa-heart"></i>
                </div>
                <div class="space-y-1">
                    <h3 class="text-lg font-bold text-[#222222]">No Matching Favorite Listings</h3>
                    <p class="text-xs text-[#666666]">No favorite food items match your current search query or filter selection.</p>
                </div>
                <div class="pt-2">
                    <button type="button" @click="$store.marketplace.resetFilters()" class="inline-flex items-center gap-2 px-6 py-2.5 bg-[#F5F5F5] hover:bg-gray-200 text-[#222222] text-xs font-medium rounded-full transition-colors">
                        <i class="fa-solid fa-rotate-left"></i> Reset Filters
                    </button>
                </div>
            </div>
        </div>
    </template>

    <!-- Server-Side Fallback Grid (Before Alpine initializes) -->
    <div x-show="!$store.marketplace.initialized" class="space-y-6">
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

</div>
@endsection
