<header class="sticky top-0 z-30 bg-[#FFF9E8]/90 backdrop-blur-md border-b border-[#EFE5CD] px-4 sm:px-8 py-3.5 flex flex-wrap items-center justify-between gap-3">
    
    <!-- Left/Center: Search Bar + Filter + Sort -->
    <div class="flex-1 flex items-center gap-2 sm:gap-3 min-w-[280px]">
        
        <!-- Search Food Input -->
        <div class="relative flex-1">
            <span class="absolute left-4 top-3 text-[#666666]"><i class="fa-solid fa-magnifying-glass text-sm"></i></span>
            <input type="text" 
                   x-model="$store.marketplace.filters.search" 
                   @input.debounce.300ms="$store.marketplace.fetchFilteredFoods()"
                   @keydown.enter.prevent="$store.marketplace.fetchFilteredFoods()"
                   placeholder="Search food by name or provider..." 
                   class="w-full pl-11 pr-10 py-2.5 bg-white border border-[#EFE5CD] rounded-full text-sm text-[#222222] placeholder-[#666666] focus:outline-none focus:border-[#2E7D32] focus:ring-1 focus:ring-[#2E7D32] transition-colors shadow-xs">
            <button type="button" x-show="$store.marketplace.filters.search" @click="$store.marketplace.filters.search = ''; $store.marketplace.fetchFilteredFoods()" class="absolute right-3.5 top-2.5 text-[#666666] hover:text-[#222222]">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- Filter Button -->
        <button @click="$store.marketplace.showFilterDrawer = !$store.marketplace.showFilterDrawer" 
                :class="$store.marketplace.showFilterDrawer ? 'bg-[#2E7D32] text-white' : 'bg-white text-[#222222] border border-[#EFE5CD] hover:bg-[#F5EED8]'"
                class="px-4 py-2.5 rounded-full text-xs font-medium flex items-center gap-1.5 transition-colors shadow-xs shrink-0">
            <i class="fa-solid fa-sliders"></i>
            <span class="hidden sm:inline">Filter</span>
        </button>

        <!-- Sort Dropdown -->
        <select x-model="$store.marketplace.filters.sort" @change="$store.marketplace.fetchFilteredFoods()" class="px-3.5 py-2.5 bg-white border border-[#EFE5CD] rounded-full text-xs font-medium text-[#222222] focus:outline-none focus:border-[#2E7D32] shadow-xs shrink-0">
            <option value="latest">Sort: Newest First</option>
            <option value="oldest">Sort: Oldest First</option>
            <option value="price_low">Sort: Lowest Price</option>
            <option value="price_high">Sort: Highest Price</option>
            <option value="expiring_soon">Sort: Expiring Soon</option>
        </select>
    </div>

    <!-- Right: Notification Icon + User Profile Dropdown -->
    <div class="flex items-center gap-3 shrink-0">
        
        <!-- Notification Icon -->
        <button class="relative p-2.5 rounded-full bg-white border border-[#EFE5CD] text-[#222222] hover:text-[#2E7D32] hover:bg-[#F5EED8] transition-colors shadow-xs focus:outline-none">
            <i class="fa-solid fa-bell text-base"></i>
            <span class="absolute top-1 right-1 w-2.5 h-2.5 rounded-full bg-[#EF4444] border-2 border-white"></span>
        </button>

        <!-- User Dropdown -->
        @auth
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" class="flex items-center gap-2.5 p-1 bg-white border border-[#EFE5CD] rounded-full hover:border-[#2E7D32] transition-colors focus:outline-none shadow-xs">
                    <div class="w-9 h-9 rounded-full bg-[#2E7D32] text-white flex items-center justify-center font-bold text-sm overflow-hidden">
                        @if(auth()->user()->profile_photo)
                            <img src="{{ asset('storage/' . auth()->user()->profile_photo) }}" alt="Avatar" class="w-full h-full object-cover">
                        @else
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        @endif
                    </div>
                    <span class="hidden sm:block text-xs font-semibold text-[#222222] pr-2 max-w-[120px] truncate">{{ auth()->user()->name }}</span>
                    <i class="fa-solid fa-chevron-down text-xs text-[#666666] pr-2"></i>
                </button>

                <!-- Dropdown Card -->
                <div x-show="open" 
                     @click.outside="open = false"
                     x-cloak
                     class="absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-xl border border-gray-100 py-2 z-50">
                    <div class="px-4 py-2.5 border-b border-gray-100">
                        <p class="text-xs font-bold text-[#222222] truncate">{{ auth()->user()->name }}</p>
                        <p class="text-[10px] text-[#666666] truncate">{{ auth()->user()->email }}</p>
                    </div>

                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-4 py-2 text-xs font-medium text-[#222222] hover:bg-[#FFF9E8] hover:text-[#2E7D32]">
                        <i class="fa-solid fa-user-gear w-4 text-center"></i> Profile Settings
                    </a>

                    @if(auth()->user()->isProvider())
                        <a href="{{ route('provider.dashboard') }}" class="flex items-center gap-3 px-4 py-2 text-xs font-medium text-[#222222] hover:bg-[#FFF9E8] hover:text-[#2E7D32]">
                            <i class="fa-solid fa-circle-plus w-4 text-center"></i> Add Food (Dashboard)
                        </a>
                    @endif

                    <div class="border-t border-gray-100 my-1"></div>

                    <!-- Single Logout Option -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-3 px-4 py-2 text-xs font-medium text-red-600 hover:bg-red-50 text-left">
                            <i class="fa-solid fa-right-from-bracket w-4 text-center"></i> Sign Out
                        </button>
                    </form>
                </div>
            </div>
        @else
            <a href="{{ route('login') }}" class="px-4 py-2 bg-[#2E7D32] hover:bg-[#256928] text-white text-xs font-medium rounded-full shadow-md shadow-[#2E7D32]/20 hover:scale-105 transition-all">
                Log In
            </a>
        @endauth

    </div>
</header>
