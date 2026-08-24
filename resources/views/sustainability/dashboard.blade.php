@extends('layouts.app')

@section('content')
<div class="space-y-8 pb-12">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-200/60 pb-4">
        <div>
            <h1 class="text-2xl font-heading font-extrabold text-[#222222]">Sustainability Dashboard</h1>
            <p class="text-xs text-[#666666]">Monitor our collective environmental impact and food rescue statistics</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-3.5 py-1.5 rounded-full text-xs font-bold bg-[#2E7D32]/10 text-[#2E7D32] flex items-center gap-1.5">
                <i class="fa-solid fa-leaf"></i> Green Platform
            </span>
        </div>
    </div>

    {{-- Core Sustainability Impact Metrics --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        
        <!-- Meals Rescued -->
        <div class="bg-white p-6 rounded-2xl shadow-xs border border-[#EFE5CD] flex flex-col justify-between hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-[#666666] uppercase tracking-wider">Meals Rescued</span>
                <div class="w-10 h-10 rounded-xl bg-[#2E7D32]/10 flex items-center justify-center text-[#2E7D32]">
                    <i class="fa-solid fa-bowl-food text-lg"></i>
                </div>
            </div>
            <div class="mt-4">
                <h3 class="text-3xl font-heading font-extrabold text-[#222222]">{{ number_format($stats['total_meals_rescued']) }}</h3>
                <p class="text-xs text-[#2E7D32] font-medium mt-1">
                    <i class="fa-solid fa-circle-check mr-1"></i> Completed pickups & donations
                </p>
            </div>
        </div>

        <!-- Food Waste Saved -->
        <div class="bg-white p-6 rounded-2xl shadow-xs border border-[#EFE5CD] flex flex-col justify-between hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-[#666666] uppercase tracking-wider">Waste Reduced</span>
                <div class="w-10 h-10 rounded-xl bg-amber-500/10 flex items-center justify-center text-amber-600">
                    <i class="fa-solid fa-trash-can-slash text-lg"></i>
                </div>
            </div>
            <div class="mt-4">
                <h3 class="text-3xl font-heading font-extrabold text-[#222222]">{{ number_format($stats['food_waste_reduced_kg'], 1) }} <span class="text-lg font-medium text-[#666666]">kg</span></h3>
                <p class="text-xs text-amber-600 font-medium mt-1">
                    <i class="fa-solid fa-scale-balanced mr-1"></i> Diverted from landfills
                </p>
            </div>
        </div>

        <!-- CO2 Prevented -->
        <div class="bg-white p-6 rounded-2xl shadow-xs border border-[#EFE5CD] flex flex-col justify-between hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-[#666666] uppercase tracking-wider">CO₂ Prevented</span>
                <div class="w-10 h-10 rounded-xl bg-blue-500/10 flex items-center justify-center text-blue-600">
                    <i class="fa-solid fa-cloud-arrow-down text-lg"></i>
                </div>
            </div>
            <div class="mt-4">
                <h3 class="text-3xl font-heading font-extrabold text-[#222222]">{{ number_format($stats['co2_prevented'], 1) }} <span class="text-lg font-medium text-[#666666]">kg</span></h3>
                <p class="text-xs text-blue-600 font-medium mt-1">
                    <i class="fa-solid fa-wind mr-1"></i> Greenhouse gas reduction
                </p>
            </div>
        </div>

        <!-- Water Saved -->
        <div class="bg-white p-6 rounded-2xl shadow-xs border border-[#EFE5CD] flex flex-col justify-between hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-[#666666] uppercase tracking-wider">Water Saved</span>
                <div class="w-10 h-10 rounded-xl bg-cyan-500/10 flex items-center justify-center text-cyan-600">
                    <i class="fa-solid fa-droplet text-lg"></i>
                </div>
            </div>
            <div class="mt-4">
                <h3 class="text-3xl font-heading font-extrabold text-[#222222]">{{ number_format($stats['water_saved']) }} <span class="text-lg font-medium text-[#666666]">L</span></h3>
                <p class="text-xs text-cyan-600 font-medium mt-1">
                    <i class="fa-solid fa-water mr-1"></i> Freshwater footprint saved
                </p>
            </div>
        </div>
    </div>

    {{-- Detailed Info Section --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left: Transaction Split and Info -->
        <div class="bg-white p-6 rounded-2xl shadow-xs border border-[#EFE5CD] space-y-6 lg:col-span-1">
            <h3 class="font-heading font-bold text-base text-[#222222] border-b border-gray-100 pb-3">Rescued Share Breakdown</h3>
            
            <div class="space-y-4">
                <div class="flex justify-between items-center text-xs">
                    <span class="text-[#666666]">Total Transactions</span>
                    <span class="font-bold text-[#222222]">{{ number_format($stats['total_transactions']) }}</span>
                </div>
                
                <div class="space-y-2">
                    <div class="flex justify-between text-xs font-medium">
                        <span class="text-[#2E7D32]">Completed Consumer Pickups</span>
                        <span>{{ number_format($stats['completed_pickups']) }}</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2">
                        @php
                            $pickupPercentage = $stats['total_transactions'] > 0 ? ($stats['completed_pickups'] / $stats['total_transactions']) * 100 : 0;
                            $ngoPercentage = $stats['total_transactions'] > 0 ? ($stats['ngo_donations'] / $stats['total_transactions']) * 100 : 0;
                        @endphp
                        <div class="bg-[#2E7D32] h-2 rounded-full" style="width: {{ $pickupPercentage }}%"></div>
                    </div>
                </div>

                <div class="space-y-2 pt-2">
                    <div class="flex justify-between text-xs font-medium">
                        <span class="text-amber-600">NGO Charity Donations</span>
                        <span>{{ number_format($stats['ngo_donations']) }}</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2">
                        <div class="bg-amber-500 h-2 rounded-full" style="width: {{ $ngoPercentage }}%"></div>
                    </div>
                </div>
            </div>


        </div>

        <!-- Right: Monthly Rescue Trend Chart -->
        <div class="bg-white p-6 rounded-2xl shadow-xs border border-[#EFE5CD] space-y-6 lg:col-span-2">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <h3 class="font-heading font-bold text-base text-[#222222]">Monthly Food Rescue Activity</h3>
                <span class="text-[10px] font-semibold text-[#666666] uppercase">Rescued Meals Quantity</span>
            </div>

            <!-- Custom CSS Bar Chart -->
            <div class="h-64 flex flex-col justify-end pt-4">
                <div class="flex-1 flex items-end justify-between gap-2 sm:gap-6 px-2">
                    @foreach($monthlyData as $data)
                        @php
                            $heightPercent = $maxMonthlyMeals > 0 ? ($data['meals'] / $maxMonthlyMeals) * 100 : 0;
                            // Ensure there is at least a 2% height if meals count is greater than 0 so it displays visually
                            if ($data['meals'] > 0 && $heightPercent < 4) {
                                $heightPercent = 4;
                            }
                        @endphp
                        <div class="flex-1 flex flex-col items-center group relative">
                            <!-- Tooltip -->
                            <div class="absolute -top-10 scale-0 group-hover:scale-100 transition-transform bg-[#222222] text-white text-[10px] px-2 py-1.5 rounded-lg shadow-md font-medium whitespace-nowrap z-10">
                                {{ number_format($data['meals']) }} meals
                            </div>
                            <!-- Bar -->
                            <div class="w-full bg-gradient-to-t from-[#2E7D32]/80 to-[#2E7D32] rounded-t-lg group-hover:opacity-90 transition-all duration-300 shadow-sm"
                                 style="height: {{ $heightPercent }}%">
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <!-- X-Axis Labels -->
                <div class="flex justify-between border-t border-gray-100 pt-3 px-2 mt-2">
                    @foreach($monthlyData as $data)
                        <div class="flex-1 text-center">
                            <span class="hidden sm:inline text-[10px] font-bold text-[#666666]">{{ $data['label'] }}</span>
                            <span class="sm:hidden text-[10px] font-bold text-[#666666]">{{ $data['short_label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Platform Community Statistics --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <!-- Served Members -->
        <div class="bg-white p-6 rounded-2xl shadow-xs border border-[#EFE5CD] flex items-center gap-4 hover:shadow-md transition-shadow">
            <div class="w-12 h-12 rounded-2xl bg-[#2E7D32]/10 flex items-center justify-center text-[#2E7D32]">
                <i class="fa-solid fa-users text-xl"></i>
            </div>
            <div>
                <span class="text-xs font-semibold text-[#666666] uppercase tracking-wider">Community Members Served</span>
                <h3 class="text-2xl font-heading font-extrabold text-[#222222] mt-0.5">{{ number_format($stats['community_members']) }}</h3>
                <p class="text-[10px] text-[#666666] mt-0.5">Consumers and NGOs actively saving surplus food</p>
            </div>
        </div>

        <!-- Active Food Providers -->
        <div class="bg-white p-6 rounded-2xl shadow-xs border border-[#EFE5CD] flex items-center gap-4 hover:shadow-md transition-shadow">
            <div class="w-12 h-12 rounded-2xl bg-amber-500/10 flex items-center justify-center text-amber-600">
                <i class="fa-solid fa-store text-xl"></i>
            </div>
            <div>
                <span class="text-xs font-semibold text-[#666666] uppercase tracking-wider">Active Food Providers</span>
                <h3 class="text-2xl font-heading font-extrabold text-[#222222] mt-0.5">{{ number_format($stats['active_providers']) }}</h3>
                <p class="text-[10px] text-[#666666] mt-0.5">Restaurants and bakeries providing surplus items</p>
            </div>
        </div>
    </div>

</div>
@endsection
