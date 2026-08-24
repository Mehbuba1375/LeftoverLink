<aside x-data="{ openMobile: false }" class="shrink-0">
    <!-- Desktop Sidebar (~250px) -->
    <div class="hidden lg:flex flex-col w-[250px] h-screen sticky top-0 bg-[#FFF9E8] border-r border-[#EFE5CD] p-6 justify-between overflow-y-auto z-40">
        
        <div class="space-y-8">
            <!-- Top Logo & Tagline -->
            <a href="{{ route('donations.index') }}" class="block group">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-[#2E7D32] to-[#66BB6A] p-0.5 shadow-md shadow-[#2E7D32]/20 group-hover:scale-105 transition-transform duration-300">
                        <div class="w-full h-full bg-white rounded-[14px] flex items-center justify-center">
                            <i class="fa-solid fa-leaf text-[#2E7D32] text-lg group-hover:rotate-12 transition-transform"></i>
                        </div>
                    </div>
                    <div>
                        <span class="font-heading font-extrabold text-xl tracking-tight text-[#222222]">Leftover<span class="text-[#2E7D32]">Link</span></span>
                        <span class="block text-[10px] font-medium text-[#666666] tracking-wide -mt-1">Food Rescue Marketplace</span>
                    </div>
                </div>
            </a>

            <!-- Navigation Links -->
            <nav class="space-y-1.5">
                <!-- Home -->
                <a href="{{ route('home') }}" 
                   class="flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('home') && !request()->routeIs('donations.index') && !request()->routeIs('marketplace.index') && !request()->routeIs('favorites.*') ? 'bg-[#2E7D32] text-white shadow-md shadow-[#2E7D32]/20 font-semibold' : 'text-[#222222] hover:bg-[#F5EED8] hover:text-[#2E7D32]' }}">
                    <i class="fa-solid fa-house w-5 text-base text-center"></i>
                    <span>Home</span>
                </a>

                <!-- Available Donations -->
                <a href="{{ route('donations.index') }}" 
                   class="flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('donations.*') ? 'bg-[#2E7D32] text-white shadow-md shadow-[#2E7D32]/20 font-semibold' : 'text-[#222222] hover:bg-[#F5EED8] hover:text-[#2E7D32]' }}">
                    <i class="fa-solid fa-utensils w-5 text-base text-center"></i>
                    <span>Available Donations</span>
                </a>

                <!-- Add Food (Opens Provider Dashboard for Providers) -->
                @auth
                    @if(auth()->user()->isProvider() || auth()->user()->isAdmin())
                        <a href="{{ route('provider.dashboard') }}" 
                           class="flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('provider.*') ? 'bg-[#2E7D32] text-white shadow-md shadow-[#2E7D32]/20 font-semibold' : 'text-[#222222] hover:bg-[#F5EED8] hover:text-[#2E7D32]' }}">
                            <i class="fa-solid fa-circle-plus w-5 text-base text-center"></i>
                            <span>Add Food</span>
                        </a>
                    @endif

                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" 
                           class="flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('admin.*') ? 'bg-[#2E7D32] text-white shadow-md shadow-[#2E7D32]/20 font-semibold' : 'text-[#222222] hover:bg-[#F5EED8] hover:text-[#2E7D32]' }}">
                            <i class="fa-solid fa-shield-halved w-5 text-base text-center"></i>
                            <span>Admin Portal</span>
                        </a>
                    @endif
                @endauth

                <!-- Cart -->
                <a href="#cart" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-medium text-[#222222] hover:bg-[#F5EED8] hover:text-[#2E7D32] transition-all duration-200">
                    <i class="fa-solid fa-cart-shopping w-5 text-base text-center"></i>
                    <span>Cart</span>
                </a>

                <!-- Favorites -->
                <a href="{{ route('favorites.index') }}" 
                   class="flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('favorites.*') ? 'bg-[#2E7D32] text-white shadow-md shadow-[#2E7D32]/20 font-semibold' : 'text-[#222222] hover:bg-[#F5EED8] hover:text-[#2E7D32]' }}">
                    <i class="fa-solid fa-heart w-5 text-base text-center"></i>
                    <span>Favorites</span>
                </a>

                <!-- NGO Requests -->
                @auth
                    @if(auth()->user()->isNgo() || auth()->user()->isAdmin())
                        <a href="{{ route('food-requests.index') }}" 
                           class="flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('food-requests.*') ? 'bg-[#2E7D32] text-white shadow-md shadow-[#2E7D32]/20 font-semibold' : 'text-[#222222] hover:bg-[#F5EED8] hover:text-[#2E7D32]' }}">
                            <i class="fa-solid fa-hand-holding-heart w-5 text-base text-center"></i>
                            <span>NGO Requests</span>
                        </a>
                    @endif
                @endauth

                <!-- Reservation History -->
                <a href="{{ route('reservations.index') }}" 
                   class="flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('reservations.*') ? 'bg-[#2E7D32] text-white shadow-md shadow-[#2E7D32]/20 font-semibold' : 'text-[#222222] hover:bg-[#F5EED8] hover:text-[#2E7D32]' }}">
                    <i class="fa-solid fa-clock-rotate-left w-5 text-base text-center"></i>
                    <span>Reservation History</span>
                </a>

                <!-- Sustainability Dashboard -->
                <a href="{{ route('sustainability.index') }}" 
                   class="flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('sustainability.*') ? 'bg-[#2E7D32] text-white shadow-md shadow-[#2E7D32]/20 font-semibold' : 'text-[#222222] hover:bg-[#F5EED8] hover:text-[#2E7D32]' }}">
                    <i class="fa-solid fa-leaf w-5 text-base text-center"></i>
                    <span>Sustainability</span>
                </a>
            </nav>
        </div>

        <!-- Bottom User Card (Sidebar Sign Out Removed) -->
        <div class="pt-6 border-t border-[#EFE5CD]">
            @guest
                <a href="{{ route('login') }}" class="flex items-center gap-3 px-4 py-2 text-xs font-semibold text-[#2E7D32] hover:underline">Log In</a>
                <a href="{{ route('register') }}" class="flex items-center justify-center gap-2 w-full py-2.5 bg-[#2E7D32] hover:bg-[#256928] text-white text-xs font-medium rounded-full shadow-md shadow-[#2E7D32]/20 transition-all mt-1">
                    <i class="fa-solid fa-user-plus text-[10px]"></i> Register
                </a>
            @else
                <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 p-2 rounded-2xl hover:bg-[#F5EED8] transition-colors group">
                    <div class="w-9 h-9 rounded-full bg-[#2E7D32] text-white flex items-center justify-center font-bold text-xs overflow-hidden shrink-0 shadow-xs">
                        @if(auth()->user()->profile_photo)
                            <img src="{{ asset('storage/' . auth()->user()->profile_photo) }}" alt="Avatar" class="w-full h-full object-cover">
                        @else
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        @endif
                    </div>
                    <div class="truncate">
                        <p class="text-xs font-bold text-[#222222] truncate group-hover:text-[#2E7D32]">{{ auth()->user()->name }}</p>
                        <span class="text-[10px] text-[#666666] uppercase font-semibold tracking-wider">{{ ucfirst(str_replace('_', ' ', auth()->user()->role)) }}</span>
                    </div>
                </a>
            @endguest
        </div>
    </div>

    <!-- Mobile Top Header -->
    <div class="lg:hidden flex items-center justify-between px-4 py-3 bg-[#FFF9E8] border-b border-[#EFE5CD] sticky top-0 z-50">
        <a href="{{ route('donations.index') }}" class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-[#2E7D32] to-[#66BB6A] p-0.5">
                <div class="w-full h-full bg-white rounded-[10px] flex items-center justify-center">
                    <i class="fa-solid fa-leaf text-[#2E7D32] text-sm"></i>
                </div>
            </div>
            <span class="font-heading font-extrabold text-lg text-[#222222]">Leftover<span class="text-[#2E7D32]">Link</span></span>
        </a>

        <button @click="openMobile = !openMobile" class="p-2 rounded-xl text-[#222222] hover:bg-[#F5EED8] focus:outline-none">
            <i class="fa-solid fa-bars text-lg"></i>
        </button>
    </div>

    <!-- Mobile Drawer -->
    <div x-show="openMobile" x-cloak class="lg:hidden fixed inset-0 z-50 bg-black/40 backdrop-blur-xs flex">
        <div @click.outside="openMobile = false" class="w-72 bg-[#FFF9E8] h-full p-6 flex flex-col justify-between overflow-y-auto shadow-2xl">
            <div class="space-y-6">
                <div class="flex items-center justify-between border-b border-[#EFE5CD] pb-4">
                    <span class="font-heading font-extrabold text-lg text-[#222222]">Navigation Menu</span>
                    <button @click="openMobile = false" class="text-[#666666] hover:text-[#222222]"><i class="fa-solid fa-xmark text-lg"></i></button>
                </div>

                <nav class="space-y-1.5">
                    <a href="{{ route('home') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-medium text-[#222222] hover:bg-[#F5EED8]">
                        <i class="fa-solid fa-house w-5 text-center"></i> Home
                    </a>
                    <a href="{{ route('donations.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-medium text-[#222222] hover:bg-[#F5EED8]">
                        <i class="fa-solid fa-utensils w-5 text-center"></i> Available Donations
                    </a>
                    @auth
                        @if(auth()->user()->isProvider() || auth()->user()->isAdmin())
                            <a href="{{ route('provider.dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-medium text-[#222222] hover:bg-[#F5EED8]">
                                <i class="fa-solid fa-circle-plus w-5 text-center"></i> Add Food
                            </a>
                        @endif
                    @endauth
                    <a href="#cart" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-medium text-[#222222] hover:bg-[#F5EED8]">
                        <i class="fa-solid fa-cart-shopping w-5 text-center"></i> Cart
                    </a>
                    <a href="{{ route('favorites.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-medium text-[#222222] hover:bg-[#F5EED8]">
                        <i class="fa-solid fa-heart w-5 text-center"></i> Favorites
                    </a>
                    @auth
                        @if(auth()->user()->isNgo() || auth()->user()->isAdmin())
                            <a href="{{ route('food-requests.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-medium text-[#222222] hover:bg-[#F5EED8]">
                                <i class="fa-solid fa-hand-holding-heart w-5 text-center"></i> NGO Requests
                            </a>
                        @endif
                    @endauth
                    <a href="{{ route('reservations.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-medium text-[#222222] hover:bg-[#F5EED8]">
                        <i class="fa-solid fa-clock-rotate-left w-5 text-center"></i> Reservation History
                    </a>
                    <a href="{{ route('sustainability.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-medium text-[#222222] hover:bg-[#F5EED8]">
                        <i class="fa-solid fa-leaf w-5 text-center"></i> Sustainability
                    </a>
                </nav>
            </div>
        </div>
    </div>
</aside>
