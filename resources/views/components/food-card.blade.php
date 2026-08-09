@props(['item'])

@php
    $isExpired = isset($item['expiration_time_raw']) && \Carbon\Carbon::parse($item['expiration_time_raw'])->isPast();
    $isOutOfStock = isset($item['quantity']) && $item['quantity'] <= 0;
    $reviewsCount = $item['reviews_count'] ?? 0;
    $avgRating = $item['average_rating'] ?? 0.0;
    $foodId = $item['id'] ?? 0;
    $isFavorited = !empty($item['is_favorited']) || (auth()->check() && $foodId && auth()->user()->favoriteFoods->contains($foodId));
@endphp

<div x-data="{ favorited: {{ $isFavorited ? 'true' : 'false' }} }" class="bg-white rounded-xl shadow-xs border border-gray-100/90 overflow-hidden flex flex-col hover:-translate-y-1 hover:shadow-md transition-all duration-300 group">
    
    <!-- Food Image Banner -->
    <div class="relative h-44 bg-gray-100 overflow-hidden shrink-0">
        <img src="{{ $item['image_url'] ?? asset('images/default-food.png') }}" 
             alt="{{ $item['food_name'] ?? 'Food item' }}" 
             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">

        <!-- Top Badges -->
        <div class="absolute top-3 left-3 flex flex-wrap gap-1.5">
            @if(!empty($item['donation_status']))
                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-[#F59E0B] text-white shadow-xs">
                    <i class="fa-solid fa-hand-holding-heart mr-1"></i> Donation
                </span>
            @else
                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-[#2E7D32] text-white shadow-xs">
                    <i class="fa-solid fa-tags mr-1"></i> Discounted
                </span>
            @endif

            @if($isExpired)
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#EF4444] text-white">Expired</span>
            @elseif($isOutOfStock)
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500 text-white">Out of Stock</span>
            @endif
        </div>

        <!-- Favorite Heart Icon Button -->
        @auth
            <form action="{{ route('favorites.toggle', $foodId) }}" method="POST" @submit.prevent="
                favorited = !favorited;
                $store.marketplace.toggleFavorite({{ $foodId }});
            " class="inline">
                @csrf
                <button type="submit" 
                        title="Toggle Favorite"
                        class="absolute top-3 right-3 w-8 h-8 rounded-full bg-white/90 backdrop-blur-xs flex items-center justify-center transition-all duration-300 hover:scale-110 shadow-xs z-10">
                    <i :class="favorited ? 'fa-solid fa-heart text-[#EF4444] scale-110' : 'fa-regular fa-heart text-[#666666] hover:text-[#EF4444]'" class="text-sm transition-transform"></i>
                </button>
            </form>
        @else
            <a href="{{ route('login') }}" 
               title="Log in to save favorites"
               class="absolute top-3 right-3 w-8 h-8 rounded-full bg-white/90 backdrop-blur-xs flex items-center justify-center text-[#666666] hover:text-[#EF4444] transition-all duration-300 hover:scale-110 shadow-xs z-10">
                <i class="fa-regular fa-heart text-sm"></i>
            </a>
        @endauth

        <!-- Category Badge -->
        <div class="absolute bottom-2 left-3">
            <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold bg-black/60 text-white backdrop-blur-xs">
                {{ $item['category'] ?? 'General' }}
            </span>
        </div>
    </div>

    <!-- Card Details -->
    <div class="p-4 flex-1 flex flex-col justify-between space-y-3">
        
        <div class="space-y-1.5">
            <!-- Food Title -->
            <h3 class="font-heading font-bold text-base text-[#222222] line-clamp-1 group-hover:text-[#2E7D32] transition-colors">
                {{ $item['food_name'] ?? 'Food Listing' }}
            </h3>

            <!-- Provider Name & Dynamic Rating -->
            <div class="flex items-center justify-between text-xs text-[#666666]">
                <span class="flex items-center gap-1.5 truncate max-w-[160px]">
                    <i class="fa-solid fa-store text-[#2E7D32]"></i>
                    <span class="truncate font-medium text-[#222222]">{{ $item['provider_name'] ?? 'Food Provider' }}</span>
                </span>

                <!-- Rating -->
                @if($reviewsCount > 0)
                    <span class="flex items-center gap-1 text-amber-500 font-semibold text-[11px]">
                        <i class="fa-solid fa-star"></i> {{ number_format($avgRating, 1) }} <span class="text-gray-400 font-normal">({{ $reviewsCount }})</span>
                    </span>
                @else
                    <span class="text-[11px] text-gray-400 font-medium italic">No reviews yet</span>
                @endif
            </div>
        </div>

        <!-- Details Grid -->
        <div class="space-y-1.5 text-xs text-[#666666] border-t border-gray-100 pt-2.5">
            <!-- Price & Quantity -->
            <div class="flex items-center justify-between">
                <span class="text-sm font-heading font-bold text-[#2E7D32]">
                    @if(!empty($item['donation_status']))
                        <span class="text-[#F59E0B]">FREE</span>
                    @else
                        ৳{{ number_format($item['price'] ?? 0, 2) }}
                    @endif
                </span>
                <span class="text-[11px] font-medium px-2 py-0.5 rounded-md bg-[#F5F5F5] text-[#222222]">
                    {{ $item['quantity'] ?? 0 }} items left
                </span>
            </div>

            <!-- Expiration Time -->
            <div class="flex items-center gap-1.5 text-[11px]">
                <i class="fa-solid fa-clock text-[#666666]"></i>
                <span class="truncate">Expires: <strong class="text-[#222222] font-medium">{{ $item['expiration_time'] ?? 'N/A' }}</strong></span>
            </div>

            <!-- Pickup Window -->
            <div class="flex items-center gap-1.5 text-[11px]">
                <i class="fa-solid fa-calendar-day text-[#666666]"></i>
                <span class="truncate">Pickup: <strong class="text-[#222222] font-medium">{{ $item['pickup_window'] ?? 'Flexible' }}</strong></span>
            </div>
        </div>

        <!-- Action Button -->
        <div class="pt-1">
            @if($isExpired || $isOutOfStock)
                <button disabled class="w-full py-2.5 bg-[#F5F5F5] text-[#666666] text-xs font-medium rounded-full cursor-not-allowed text-center">
                    Unavailable
                </button>
            @else
                <a href="{{ route('login') }}" class="block w-full py-2.5 bg-[#2E7D32] hover:bg-[#256928] text-white text-xs font-medium rounded-full text-center shadow-md shadow-[#2E7D32]/20 hover:scale-105 transition-all">
                    Reserve Food
                </a>
            @endif
        </div>

    </div>
</div>
