@extends('layouts.app')

@section('content')
<div x-init="$store.marketplace.fetchFilteredFoods()" class="space-y-6 pb-12">

    <!-- Page Title & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-gray-200/60 pb-3">
        <div>
            <h1 class="text-2xl font-heading font-extrabold text-[#222222]">
                {{ isset($isDonationPage) && $isDonationPage ? 'Available Community Donations' : 'Available Surplus Donations & Meals' }}
            </h1>
            <p class="text-xs text-[#666666]">
                {{ isset($isDonationPage) && $isDonationPage ? 'Browse free surplus food donations published by local restaurants and community partners' : 'Browse surplus food from local restaurants and bakeries at discounted prices or free donations' }}
            </p>
        </div>

        <div class="flex items-center gap-2">
            <span class="text-xs text-[#666666]">
                Showing <strong class="text-[#222222]" x-text="$store.marketplace.initialized ? $store.marketplace.itemsCount : {{ count($foods) }}">{{ count($foods) }}</strong> items
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
                @foreach($categories as $cat)
                    <option value="{{ $cat }}">{{ $cat }}</option>
                @endforeach
            </select>
        </div>

        <!-- Listing Type Filter -->
        <div>
            <label class="block font-semibold text-[#222222] mb-1">Listing Type</label>
            @if(isset($isDonationPage) && $isDonationPage)
                <select disabled class="w-full px-3 py-2.5 bg-gray-100 border border-gray-200 rounded-xl text-gray-500 font-semibold cursor-not-allowed">
                    <option value="donated" selected>Free Community Donations Only</option>
                </select>
            @else
                <select x-model="$store.marketplace.filters.type" @change="$store.marketplace.fetchFilteredFoods()" class="w-full px-3 py-2.5 bg-[#F5F5F5] border border-gray-200 rounded-xl text-[#222222]">
                    <option value="all">All Types</option>
                    <option value="discounted">Discounted Sale</option>
                    <option value="donated">Free Donation</option>
                </select>
            @endif
        </div>

        <!-- Provider Filter -->
        <div>
            <label class="block font-semibold text-[#222222] mb-1">Food Provider</label>
            <select x-model="$store.marketplace.filters.provider_id" @change="$store.marketplace.fetchFilteredFoods()" class="w-full px-3 py-2.5 bg-[#F5F5F5] border border-gray-200 rounded-xl text-[#222222]">
                <option value="all">All Food Providers</option>
                @foreach($providers as $p)
                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                @endforeach
            </select>
        </div>

        <!-- Price Range -->
        <div>
            <label class="block font-semibold text-[#222222] mb-1">Price Range (৳)</label>
            <div class="grid grid-cols-2 gap-2">
                <input type="number" x-model="$store.marketplace.filters.min_price" @input.debounce.400ms="$store.marketplace.fetchFilteredFoods()" placeholder="Min ৳" class="px-3 py-2.5 bg-[#F5F5F5] border border-gray-200 rounded-xl text-[#222222]">
                <input type="number" x-model="$store.marketplace.filters.max_price" @input.debounce.400ms="$store.marketplace.fetchFilteredFoods()" placeholder="Max ৳" class="px-3 py-2.5 bg-[#F5F5F5] border border-gray-200 rounded-xl text-[#222222]">
            </div>
        </div>

        <!-- Reset Button -->
        <div class="sm:col-span-2 lg:col-span-4 flex items-center justify-end gap-3 pt-1">
            <button @click="$store.marketplace.resetFilters()" class="px-5 py-2 bg-[#F5F5F5] hover:bg-gray-200 text-[#222222] font-medium rounded-full text-xs transition-colors">
                Reset All Filters
            </button>
        </div>
    </div>

    <!-- Loading Indicator -->
    <div x-show="$store.marketplace.loading" class="text-center py-12 space-y-3">
        <i class="fa-solid fa-circle-notch fa-spin text-2xl text-[#2E7D32]"></i>
        <p class="text-xs text-[#666666]">Fetching available food listings...</p>
    </div>

    <!-- Client-Side Reactive Food Cards Grid (When Alpine initialized) -->
    <template x-if="$store.marketplace.initialized">
        <div x-show="!$store.marketplace.loading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <template x-for="item in $store.marketplace.foods" :key="item.id">
                <div class="bg-white rounded-xl shadow-xs border border-gray-100 overflow-hidden flex flex-col hover:-translate-y-1 hover:shadow-md transition-all duration-300 group">
                    
                    <!-- Food Image Banner -->
                    <div class="relative h-48 bg-gray-100 overflow-hidden shrink-0">
                        <img :src="item.image_url" :alt="item.food_name" class="w-full h-full object-cover rounded-t-xl group-hover:scale-105 transition-transform duration-500">

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
                    <div class="p-5 flex-1 flex flex-col justify-between space-y-4">
                        
                        <div class="space-y-1.5">
                            <!-- Food Title -->
                            <h3 class="font-heading font-bold text-base text-[#222222] line-clamp-1 group-hover:text-[#2E7D32] transition-colors" x-text="item.food_name"></h3>

                            <!-- Provider Name & Rating -->
                            <div class="flex items-center justify-between text-xs text-[#666666]">
                                <span class="flex items-center gap-1.5 truncate max-w-[170px]">
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
                        <div class="space-y-2 text-xs text-[#666666] border-t border-gray-100 pt-3">
                            <div class="flex items-center justify-between">
                                <span class="text-base font-heading font-bold text-[#2E7D32]">
                                    <span x-show="!item.donation_status">৳<span x-text="item.price"></span></span>
                                    <span x-show="item.donation_status" class="text-[#F59E0B]">FREE</span>
                                </span>
                                <span class="text-[11px] font-medium px-2 py-0.5 rounded-md bg-[#F5F5F5] text-[#222222]" x-text="item.quantity + ' items left'"></span>
                            </div>

                            <div class="flex items-center gap-1.5 text-[11px]">
                                <i class="fa-solid fa-clock text-[#666666]"></i>
                                <span class="truncate">Expires: <strong class="text-[#222222] font-medium" x-text="item.expiration_time"></strong></span>
                            </div>

                            <div class="flex items-center gap-1.5 text-[11px]">
                                <i class="fa-solid fa-calendar-day text-[#666666]"></i>
                                <span class="truncate">Pickup: <strong class="text-[#222222] font-medium" x-text="item.pickup_window"></strong></span>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div x-data="{ reserveModal: false, reserveQty: 1, ngoModal: false, ngoQty: 1 }" class="pt-1 space-y-2">
                            <template x-if="item.quantity <= 0">
                                <button disabled class="w-full py-2.5 bg-[#F5F5F5] text-[#666666] text-xs font-medium rounded-full cursor-not-allowed text-center">
                                    Unavailable
                                </button>
                            </template>
                            <template x-if="item.quantity > 0">
                                <div>
                                    @guest
                                        <a href="{{ route('login') }}" class="block w-full py-2.5 bg-[#EF4444] hover:bg-[#DC2626] text-white text-xs font-medium rounded-full text-center shadow-md shadow-[#EF4444]/20 hover:scale-105 transition-all">
                                            Reserve Food
                                        </a>
                                    @else
                                        <!-- Reserve Food Button -->
                                        <template x-if="(item.user_id || item.provider_id) !== {{ auth()->id() }}">
                                            <button type="button" @click="reserveModal = true" class="block w-full py-2.5 bg-[#EF4444] hover:bg-[#DC2626] text-white text-xs font-medium rounded-full text-center shadow-md shadow-[#EF4444]/20 hover:scale-105 transition-all">
                                                Reserve Food
                                            </button>
                                        </template>
                                        <template x-if="(item.user_id || item.provider_id) === {{ auth()->id() }}">
                                            <button type="button" onclick="alert('You cannot reserve your own food listing.')" class="block w-full py-2.5 bg-[#EF4444] hover:bg-[#DC2626] text-white text-xs font-medium rounded-full text-center shadow-md shadow-[#EF4444]/20 hover:scale-105 transition-all">
                                                Reserve Food
                                            </button>
                                        </template>

                                        <!-- Reserve Quantity Modal -->
                                        <div x-show="reserveModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/40 backdrop-blur-xs flex items-center justify-center p-4">
                                            <div @click.outside="reserveModal = false" class="bg-white max-w-sm w-full p-6 rounded-2xl shadow-2xl space-y-4 text-left">
                                                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                                                    <h3 class="text-base font-heading font-bold text-[#222222]">Reserve <span x-text="item.food_name"></span></h3>
                                                    <button type="button" @click="reserveModal = false" class="text-[#666666] hover:text-[#222222]"><i class="fa-solid fa-xmark"></i></button>
                                                </div>
                                                <form :action="'/foods/' + item.id + '/reserve'" method="POST" class="space-y-4">
                                                    @csrf
                                                    <div>
                                                        <label class="block text-xs font-semibold text-[#222222] mb-1">Select Quantity (Available: <span x-text="item.quantity"></span>)</label>
                                                        <div class="flex items-center gap-3">
                                                            <button type="button" @click="if(reserveQty > 1) reserveQty--" class="w-9 h-9 rounded-lg bg-[#F5F5F5] hover:bg-gray-200 text-[#222222] font-bold">-</button>
                                                            <input type="number" name="quantity" x-model="reserveQty" min="1" :max="item.quantity" required class="w-20 text-center py-2 bg-[#F5F5F5] border border-gray-200 rounded-lg text-sm font-bold text-[#222222]">
                                                            <button type="button" @click="if(reserveQty < item.quantity) reserveQty++" class="w-9 h-9 rounded-lg bg-[#F5F5F5] hover:bg-gray-200 text-[#222222] font-bold">+</button>
                                                        </div>
                                                    </div>

                                                    <div>
                                                        <label class="block text-xs font-semibold text-[#222222] mb-1">Preferred Pickup Date</label>
                                                        <input type="date" name="preferred_pickup_date" min="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-2 bg-[#F5F5F5] border border-gray-200 rounded-xl text-xs text-[#222222]">
                                                    </div>

                                                    <div>
                                                        <div class="flex justify-between items-center mb-1">
                                                            <label class="block text-xs font-semibold text-[#222222]">Preferred Pickup Time</label>
                                                            <span class="text-[10px] text-[#2E7D32] font-medium" x-text="'Window: ' + (item.pickup_window || 'Flexible')"></span>
                                                        </div>
                                                        <input type="time" name="preferred_pickup_time" :min="item.pickup_start_time || '00:00'" :max="item.pickup_end_time || '23:59'" required class="w-full px-3 py-2 bg-[#F5F5F5] border border-gray-200 rounded-xl text-xs text-[#222222]">
                                                    </div>

                                                    <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                                                        <button type="button" @click="reserveModal = false" class="px-4 py-2 bg-[#F5F5F5] text-[#222222] text-xs font-medium rounded-full">Cancel</button>
                                                        <button type="submit" class="px-5 py-2 bg-[#EF4444] hover:bg-[#DC2626] text-white text-xs font-medium rounded-full shadow-md">Confirm Reservation</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>

                                        <!-- Additional Request Collection Button for NGOs on Donated Food -->
                                        @if(auth()->user()->isNgo())
                                            <template x-if="item.donation_status">
                                                <div>
                                                    <button type="button" @click="ngoModal = true" class="block w-full py-2 bg-[#2E7D32] hover:bg-[#256928] text-white text-xs font-medium rounded-full text-center shadow-xs transition-all mt-2">
                                                        <i class="fa-solid fa-hand-holding-heart mr-1"></i> Request Collection
                                                    </button>

                                                    <!-- NGO Request Quantity Modal -->
                                                    <div x-show="ngoModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/40 backdrop-blur-xs flex items-center justify-center p-4">
                                                        <div @click.outside="ngoModal = false" class="bg-white max-w-sm w-full p-6 rounded-2xl shadow-2xl space-y-4 text-left">
                                                            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                                                                <h3 class="text-base font-heading font-bold text-[#222222]">Request Donation: <span x-text="item.food_name"></span></h3>
                                                                <button type="button" @click="ngoModal = false" class="text-[#666666] hover:text-[#222222]"><i class="fa-solid fa-xmark"></i></button>
                                                            </div>
                                                            <form :action="'/foods/' + item.id + '/request'" method="POST" class="space-y-4">
                                                                @csrf
                                                                <div>
                                                                    <label class="block text-xs font-semibold text-[#222222] mb-1">Requested Quantity (Available: <span x-text="item.quantity"></span>)</label>
                                                                    <div class="flex items-center gap-3">
                                                                        <button type="button" @click="if(ngoQty > 1) ngoQty--" class="w-9 h-9 rounded-lg bg-[#F5F5F5] hover:bg-gray-200 text-[#222222] font-bold">-</button>
                                                                        <input type="number" name="quantity" x-model="ngoQty" min="1" :max="item.quantity" required class="w-20 text-center py-2 bg-[#F5F5F5] border border-gray-200 rounded-lg text-sm font-bold text-[#222222]">
                                                                        <button type="button" @click="if(ngoQty < item.quantity) ngoQty++" class="w-9 h-9 rounded-lg bg-[#F5F5F5] hover:bg-gray-200 text-[#222222] font-bold">+</button>
                                                                    </div>
                                                                </div>
                                                                <div>
                                                                    <label class="block text-xs font-semibold text-[#222222] mb-1">Organization Message / Notes (Optional)</label>
                                                                    <textarea name="notes" rows="2" placeholder="Describe distribution plan or notes for donor..." class="w-full px-3 py-2 bg-[#F5F5F5] border border-gray-200 rounded-xl text-xs text-[#222222]"></textarea>
                                                                </div>
                                                                <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                                                                    <button type="button" @click="ngoModal = false" class="px-4 py-2 bg-[#F5F5F5] text-[#222222] text-xs font-medium rounded-full">Cancel</button>
                                                                    <button type="submit" class="px-5 py-2 bg-[#2E7D32] hover:bg-[#256928] text-white text-xs font-medium rounded-full shadow-md">Submit Request</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>
                                        @endif
                                    @endguest
                                </div>
                            </template>
                        </div>

                    </div>
                </div>
            </template>
        </div>
    </template>

    <!-- Server-Side Fallback Render (Before Alpine JS initializes) -->
    <template x-if="!$store.marketplace.initialized">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($foods as $food)
                <x-food-card :item="[
                    'id' => $food->id,
                    'user_id' => $food->user_id,
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
                ]" />
            @empty
                <div class="col-span-3 bg-white rounded-xl border border-gray-100 p-12 text-center space-y-4">
                    <div class="w-16 h-16 rounded-full bg-[#FFF9E8] text-[#2E7D32] flex items-center justify-center mx-auto text-2xl">
                        <i class="fa-solid fa-utensils"></i>
                    </div>
                    <h3 class="text-lg font-bold text-[#222222]">No Food Listings Available</h3>
                </div>
            @endforelse
        </div>
    </template>

    <!-- Empty State -->
    <div x-show="$store.marketplace.initialized && !$store.marketplace.loading && $store.marketplace.foods.length === 0" x-cloak class="bg-white rounded-xl border border-gray-100 p-12 text-center space-y-4">
        <div class="w-16 h-16 rounded-full bg-[#FFF9E8] text-[#2E7D32] flex items-center justify-center mx-auto text-2xl">
            <i class="fa-solid fa-utensils"></i>
        </div>
        <div class="space-y-1">
            <h3 class="text-lg font-bold text-[#222222]">No Food Listings Match Your Search</h3>
            <p class="text-xs text-[#666666] max-w-sm mx-auto">Try resetting your active filters or searching for another keyword.</p>
        </div>
        <button @click="$store.marketplace.resetFilters()" class="px-5 py-2 bg-[#2E7D32] text-white text-xs font-medium rounded-full shadow-md">
            Reset Filters
        </button>
    </div>

</div>
@endsection
