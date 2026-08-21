@extends('layouts.app')

@section('content')
<div class="space-y-10 pb-12">

    {{-- ── Page Header ─────────────────────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 border-b border-gray-200/60 pb-6">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-[#2E7D32] to-[#66BB6A] flex items-center justify-center shadow-md shadow-[#2E7D32]/20">
                    <i class="fa-solid fa-leaf text-white text-lg"></i>
                </div>
                <span class="text-xs font-semibold text-[#2E7D32] uppercase tracking-widest">Our Impact</span>
            </div>
            <h1 class="text-3xl font-heading font-extrabold text-[#222222] leading-tight">
                Sustainability Dashboard
            </h1>
            <p class="text-sm text-[#666666] mt-1 max-w-xl">
                Every rescued meal matters. Here's how the LeftoverLink community is reducing food waste and building a greener future — together.
            </p>
        </div>
        <div class="text-xs text-[#666666] shrink-0">
            <i class="fa-solid fa-clock mr-1"></i> Live platform statistics
        </div>
    </div>

    {{-- ── Hero Impact Numbers ──────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">

        {{-- Meals Rescued --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 flex flex-col gap-4 hover:shadow-md transition-shadow group">
            <div class="flex items-center justify-between">
                <div class="w-12 h-12 rounded-2xl bg-[#2E7D32]/10 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-bowl-food text-[#2E7D32] text-xl"></i>
                </div>
                <span class="text-xs font-semibold px-3 py-1 bg-[#2E7D32]/10 text-[#2E7D32] rounded-full">Meals Rescued</span>
            </div>
            <div>
                <p class="text-5xl font-extrabold text-[#2E7D32] tabular-nums" id="stat-meals">{{ number_format($totalMealsRescued) }}</p>
                <p class="text-xs text-[#666666] mt-1">Total meal units rescued from waste</p>
            </div>
            <div class="grid grid-cols-2 gap-3 pt-3 border-t border-gray-100">
                <div>
                    <p class="text-lg font-bold text-[#222222]">{{ number_format($mealsRescuedViaReservations) }}</p>
                    <p class="text-[10px] text-[#666666]">Via consumer pickups</p>
                </div>
                <div>
                    <p class="text-lg font-bold text-[#222222]">{{ number_format($mealsRescuedViaNgo) }}</p>
                    <p class="text-[10px] text-[#666666]">Via NGO collections</p>
                </div>
            </div>
        </div>

        {{-- Food Waste Reduced --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 flex flex-col gap-4 hover:shadow-md transition-shadow group">
            <div class="flex items-center justify-between">
                <div class="w-12 h-12 rounded-2xl bg-amber-50 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-trash-can-arrow-up text-amber-600 text-xl"></i>
                </div>
                <span class="text-xs font-semibold px-3 py-1 bg-amber-50 text-amber-600 rounded-full">Waste Avoided</span>
            </div>
            <div>
                <p class="text-5xl font-extrabold text-amber-600 tabular-nums">{{ number_format($estimatedKgSaved, 1) }} <span class="text-2xl font-bold">kg</span></p>
                <p class="text-xs text-[#666666] mt-1">Estimated food weight diverted from landfill</p>
            </div>
            <div class="pt-3 border-t border-gray-100">
                <p class="text-xs text-[#666666]">Based on an average of <span class="font-semibold text-[#222222]">0.5 kg</span> per rescued meal unit.</p>
            </div>
        </div>

        {{-- CO2 Saved --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 flex flex-col gap-4 hover:shadow-md transition-shadow group">
            <div class="flex items-center justify-between">
                <div class="w-12 h-12 rounded-2xl bg-sky-50 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-cloud-sun text-sky-500 text-xl"></i>
                </div>
                <span class="text-xs font-semibold px-3 py-1 bg-sky-50 text-sky-500 rounded-full">CO₂ Saved</span>
            </div>
            <div>
                <p class="text-5xl font-extrabold text-sky-500 tabular-nums">{{ number_format($estimatedCo2Saved, 1) }} <span class="text-2xl font-bold">kg</span></p>
                <p class="text-xs text-[#666666] mt-1">Estimated greenhouse gas emissions avoided</p>
            </div>
            <div class="pt-3 border-t border-gray-100">
                <p class="text-xs text-[#666666]">Based on <span class="font-semibold text-[#222222]">2.5 kg CO₂</span> equivalent per kg of food waste prevented.</p>
            </div>
        </div>

    </div>

    {{-- ── Community Stats Row ──────────────────────────────────────────────── --}}
    <div>
        <h2 class="text-xl font-heading font-bold text-[#222222] mb-4">Community Overview</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">

            <div class="bg-white rounded-xl border border-gray-100 shadow-xs p-4 text-center hover:shadow-sm transition-shadow">
                <div class="w-9 h-9 rounded-full bg-[#2E7D32]/10 flex items-center justify-center mx-auto mb-2">
                    <i class="fa-solid fa-store text-[#2E7D32] text-sm"></i>
                </div>
                <p class="text-2xl font-extrabold text-[#222222]">{{ $activeProviders }}</p>
                <p class="text-[10px] text-[#666666] mt-0.5">Active Providers</p>
            </div>

            <div class="bg-white rounded-xl border border-gray-100 shadow-xs p-4 text-center hover:shadow-sm transition-shadow">
                <div class="w-9 h-9 rounded-full bg-purple-50 flex items-center justify-center mx-auto mb-2">
                    <i class="fa-solid fa-hand-holding-heart text-purple-500 text-sm"></i>
                </div>
                <p class="text-2xl font-extrabold text-[#222222]">{{ $registeredNgos }}</p>
                <p class="text-[10px] text-[#666666] mt-0.5">Registered NGOs</p>
            </div>

            <div class="bg-white rounded-xl border border-gray-100 shadow-xs p-4 text-center hover:shadow-sm transition-shadow">
                <div class="w-9 h-9 rounded-full bg-amber-50 flex items-center justify-center mx-auto mb-2">
                    <i class="fa-solid fa-list-check text-amber-500 text-sm"></i>
                </div>
                <p class="text-2xl font-extrabold text-[#222222]">{{ $totalListingsCreated }}</p>
                <p class="text-[10px] text-[#666666] mt-0.5">Food Listings Created</p>
            </div>

            <div class="bg-white rounded-xl border border-gray-100 shadow-xs p-4 text-center hover:shadow-sm transition-shadow">
                <div class="w-9 h-9 rounded-full bg-[#2E7D32]/10 flex items-center justify-center mx-auto mb-2">
                    <i class="fa-solid fa-circle-check text-[#2E7D32] text-sm"></i>
                </div>
                <p class="text-2xl font-extrabold text-[#222222]">{{ $totalCompletedPickups }}</p>
                <p class="text-[10px] text-[#666666] mt-0.5">Completed Pickups</p>
            </div>

            <div class="bg-white rounded-xl border border-gray-100 shadow-xs p-4 text-center hover:shadow-sm transition-shadow">
                <div class="w-9 h-9 rounded-full bg-sky-50 flex items-center justify-center mx-auto mb-2">
                    <i class="fa-solid fa-boxes-stacked text-sky-500 text-sm"></i>
                </div>
                <p class="text-2xl font-extrabold text-[#222222]">{{ $listingsRescued }}</p>
                <p class="text-[10px] text-[#666666] mt-0.5">Distinct Items Rescued</p>
            </div>

        </div>
    </div>

    {{-- ── NGO Impact & SMS Banner ──────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- NGO Impact Card --}}
        <div class="bg-gradient-to-br from-[#2E7D32] to-[#66BB6A] rounded-2xl p-6 text-white shadow-md shadow-[#2E7D32]/20">
            <div class="flex items-start gap-4">
                <div class="w-14 h-14 rounded-2xl bg-white/20 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-people-carry-box text-2xl text-white"></i>
                </div>
                <div>
                    <h3 class="font-heading font-bold text-lg">NGO Community Impact</h3>
                    <p class="text-sm text-white/80 mt-1">
                        {{ $ngoRequestsApproved }} collection request{{ $ngoRequestsApproved !== 1 ? 's' : '' }} approved, delivering
                        <span class="font-bold text-white">{{ number_format($mealsRescuedViaNgo) }} meals</span>
                        to communities in need.
                    </p>
                    <div class="mt-4 flex items-center gap-2">
                        <div class="flex-1 bg-white/20 rounded-full h-2">
                            @php $ngoPercent = $totalMealsRescued > 0 ? round(($mealsRescuedViaNgo / $totalMealsRescued) * 100) : 0; @endphp
                            <div class="bg-white rounded-full h-2 transition-all" style="width: {{ $ngoPercent }}%"></div>
                        </div>
                        <span class="text-xs font-semibold text-white/90">{{ $ngoPercent }}% via NGOs</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- SMS Notifications Banner --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <div class="flex items-start gap-4">
                <div class="w-14 h-14 rounded-2xl bg-blue-50 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-mobile-screen-button text-blue-500 text-2xl"></i>
                </div>
                <div>
                    <h3 class="font-heading font-bold text-lg text-[#222222]">SMS Notifications Active</h3>
                    <p class="text-sm text-[#666666] mt-1">
                        LeftoverLink automatically sends SMS pickup reminders and status updates to consumers and NGOs via the Twilio API, keeping everyone informed at every step.
                    </p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <span class="text-[10px] font-semibold px-2.5 py-1 bg-[#2E7D32]/10 text-[#2E7D32] rounded-full"><i class="fa-solid fa-check mr-1"></i>Reservation Confirmation</span>
                        <span class="text-[10px] font-semibold px-2.5 py-1 bg-[#2E7D32]/10 text-[#2E7D32] rounded-full"><i class="fa-solid fa-check mr-1"></i>Pickup Reminders</span>
                        <span class="text-[10px] font-semibold px-2.5 py-1 bg-[#2E7D32]/10 text-[#2E7D32] rounded-full"><i class="fa-solid fa-check mr-1"></i>Status Updates</span>
                        <span class="text-[10px] font-semibold px-2.5 py-1 bg-[#2E7D32]/10 text-[#2E7D32] rounded-full"><i class="fa-solid fa-check mr-1"></i>NGO Alerts</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Recent Rescues Feed ───────────────────────────────────────────────── --}}
    @if($recentRescues->isNotEmpty())
    <div>
        <h2 class="text-xl font-heading font-bold text-[#222222] mb-4">
            <i class="fa-solid fa-fire-flame-curved text-amber-500 mr-2"></i>Recent Rescues
        </h2>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="divide-y divide-gray-50">
                @foreach($recentRescues as $rescue)
                <div class="flex items-center gap-4 px-6 py-4 hover:bg-[#FFF9E8] transition-colors">
                    <div class="w-9 h-9 rounded-full bg-[#2E7D32]/10 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-circle-check text-[#2E7D32] text-sm"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-[#222222] truncate">{{ $rescue->food->food_name ?? 'Food item' }}</p>
                        <p class="text-xs text-[#666666]">{{ $rescue->quantity }} unit{{ $rescue->quantity !== 1 ? 's' : '' }} rescued &bull; by {{ $rescue->food->user->name ?? 'Provider' }}</p>
                    </div>
                    <div class="text-right shrink-0">
                        <p class="text-xs font-semibold text-[#2E7D32]">+{{ $rescue->quantity }} meals</p>
                        <p class="text-[10px] text-[#666666]">{{ $rescue->completed_at ? $rescue->completed_at->diffForHumans() : 'Completed' }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- ── How It Works ─────────────────────────────────────────────────────── --}}
    <div class="bg-gradient-to-r from-[#FFF9E8] to-white rounded-2xl border border-[#EFE5CD] p-8">
        <h2 class="text-xl font-heading font-bold text-[#222222] mb-6 text-center">How LeftoverLink Calculates Impact</h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 text-center">
            <div class="flex flex-col items-center gap-3">
                <div class="w-12 h-12 rounded-full bg-[#2E7D32]/10 flex items-center justify-center">
                    <i class="fa-solid fa-bowl-food text-[#2E7D32]"></i>
                </div>
                <div>
                    <p class="text-sm font-bold text-[#222222]">Count Rescued Meals</p>
                    <p class="text-xs text-[#666666] mt-1">Sum of quantities from all completed consumer reservations and approved NGO collection requests.</p>
                </div>
            </div>
            <div class="flex flex-col items-center gap-3">
                <div class="w-12 h-12 rounded-full bg-amber-50 flex items-center justify-center">
                    <i class="fa-solid fa-weight-scale text-amber-500"></i>
                </div>
                <div>
                    <p class="text-sm font-bold text-[#222222]">Estimate Weight Saved</p>
                    <p class="text-xs text-[#666666] mt-1">Multiply rescued meals × 0.5 kg average serving weight to estimate kilograms of food diverted from waste.</p>
                </div>
            </div>
            <div class="flex flex-col items-center gap-3">
                <div class="w-12 h-12 rounded-full bg-sky-50 flex items-center justify-center">
                    <i class="fa-solid fa-cloud text-sky-500"></i>
                </div>
                <div>
                    <p class="text-sm font-bold text-[#222222]">Calculate CO₂ Avoided</p>
                    <p class="text-xs text-[#666666] mt-1">Multiply estimated food weight × 2.5 kg CO₂ equivalent per kg — the standard IPCC food waste emission factor.</p>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
