<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-[#FFF9E8]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'LeftoverLink') }} - Rescue Surplus Food</title>

    <!-- Google Fonts: Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Poppins', 'sans-serif'],
                        heading: ['Poppins', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            green: '#2E7D32',
                            lightGreen: '#66BB6A',
                            cream: '#FFF9E8',
                            card: '#FFFFFF',
                            gray: '#F5F5F5',
                            text: '#222222',
                            muted: '#666666',
                        }
                    }
                }
            }
        }
    </script>

    <!-- FontAwesome & Alpine JS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('marketplace', {
                loading: false,
                showFilterDrawer: false,
                itemsCount: 0,
                foods: [],
                initialized: false,
                filters: {
                    search: '{{ request("search") }}',
                    category: '{{ request("category", "all") }}',
                    type: '{{ request()->is("donations*") ? "donated" : request("type", "all") }}',
                    provider_id: '{{ request("provider_id", "all") }}',
                    min_price: '{{ request("min_price", "") }}',
                    max_price: '{{ request("max_price", "") }}',
                    sort: '{{ request("sort", "latest") }}'
                },
                async fetchFilteredFoods() {
                    const path = window.location.pathname;
                    if (path !== '/' && path !== '/marketplace' && path !== '/donations') {
                        window.location.href = '/donations?search=' + encodeURIComponent(this.filters.search);
                        return;
                    }

                    this.loading = true;
                    const params = new URLSearchParams();

                    if (path === '/donations' || this.filters.type === 'donated') {
                        params.append('is_donation_page', '1');
                        params.append('type', 'donated');
                    } else if (this.filters.type && this.filters.type !== 'all') {
                        params.append('type', this.filters.type);
                    }

                    if (this.filters.search) params.append('search', this.filters.search);
                    if (this.filters.category && this.filters.category !== 'all') params.append('category', this.filters.category);
                    if (this.filters.provider_id && this.filters.provider_id !== 'all') params.append('provider_id', this.filters.provider_id);
                    if (this.filters.min_price) params.append('min_price', this.filters.min_price);
                    if (this.filters.max_price) params.append('max_price', this.filters.max_price);
                    if (this.filters.sort) params.append('sort', this.filters.sort);

                    try {
                        const res = await fetch(`/marketplace/api/search?${params.toString()}`);
                        const json = await res.json();
                        this.foods = json.data || [];
                        this.itemsCount = json.count || 0;
                        this.initialized = true;
                    } catch (e) {
                        console.error("Search error:", e);
                    } finally {
                        this.loading = false;
                    }
                },
                resetFilters() {
                    const isDonationPage = window.location.pathname === '/donations';
                    this.filters = {
                        search: '',
                        category: 'all',
                        type: isDonationPage ? 'donated' : 'all',
                        provider_id: 'all',
                        min_price: '',
                        max_price: '',
                        sort: 'latest'
                    };
                    this.fetchFilteredFoods();
                }
            });
        });
    </script>
    
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Poppins', sans-serif; }
    </style>
</head>
<body class="h-full bg-[#FFF9E8] text-[#222222] antialiased selection:bg-[#2E7D32] selection:text-white">

    <div x-data class="flex min-h-screen">
        <!-- Permanent Left Sidebar Component (~250px) -->
        <x-sidebar />

        <!-- Main Workspace (Topbar + Page Content) -->
        <div class="flex-1 flex flex-col min-w-0 bg-[#FFF9E8]">
            
            <!-- Fixed Top Horizontal Navigation Bar -->
            <x-topbar />

            <!-- Flash Notifications -->
            <div class="max-w-7xl mx-auto px-4 sm:px-8 mt-4 w-full">
                @if (session('success'))
                    <div x-data="{ show: true }" x-show="show" class="p-4 rounded-xl bg-[#22C55E]/10 border border-[#22C55E]/20 text-[#2E7D32] flex items-center justify-between shadow-xs">
                        <div class="flex items-center gap-3 text-sm font-medium">
                            <i class="fa-solid fa-circle-check text-base"></i>
                            <span>{{ session('success') }}</span>
                        </div>
                        <button @click="show = false" class="text-[#2E7D32]/60 hover:text-[#2E7D32]"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                @endif

                @if (session('error'))
                    <div x-data="{ show: true }" x-show="show" class="p-4 rounded-xl bg-[#EF4444]/10 border border-[#EF4444]/20 text-[#EF4444] flex items-center justify-between shadow-xs">
                        <div class="flex items-center gap-3 text-sm font-medium">
                            <i class="fa-solid fa-circle-exclamation text-base"></i>
                            <span>{{ session('error') }}</span>
                        </div>
                        <button @click="show = false" class="text-[#EF4444]/60 hover:text-[#EF4444]"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                @endif
            </div>

            <!-- Page Main Content View -->
            <main class="flex-1 p-4 sm:p-8">
                @yield('content')
            </main>
        </div>
    </div>

</body>
</html>
