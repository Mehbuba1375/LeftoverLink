@extends('layouts.app')

@section('content')
<div class="space-y-8 pb-12">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-200/60 pb-4">
        <div>
            <h1 class="text-2xl font-heading font-extrabold text-[#222222]">My Reservations</h1>
            <p class="text-xs text-[#666666]">Track and manage all your food pickup reservations</p>
        </div>
        <a href="{{ route('marketplace.index') }}" class="inline-flex items-center gap-2 px-5 py-3 bg-[#2E7D32] hover:bg-[#256928] text-white text-xs font-medium rounded-full shadow-md shadow-[#2E7D32]/20 hover:scale-105 transition-all shrink-0">
            <i class="fa-solid fa-utensils text-xs"></i> Browse Food
        </a>
    </div>

    {{-- Quick Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-xl shadow-xs border border-gray-100">
            <p class="text-xs text-[#666666] font-medium">Total Reservations</p>
            <p class="text-2xl font-bold text-[#222222] mt-1">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-xs border border-gray-100">
            <div class="flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
                <p class="text-xs text-blue-600 font-medium">Active</p>
            </div>
            <p class="text-2xl font-bold text-blue-600 mt-1">{{ $stats['active'] }}</p>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-xs border border-gray-100">
            <div class="flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-[#2E7D32]"></span>
                <p class="text-xs text-[#2E7D32] font-medium">Completed</p>
            </div>
            <p class="text-2xl font-bold text-[#2E7D32] mt-1">{{ $stats['completed'] }}</p>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-xs border border-gray-100">
            <div class="flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-red-500"></span>
                <p class="text-xs text-red-600 font-medium">Cancelled</p>
            </div>
            <p class="text-2xl font-bold text-red-600 mt-1">{{ $stats['cancelled'] }}</p>
        </div>
    </div>

    {{-- Status Filter Tabs --}}
    <div x-data="{ activeTab: 'all' }" class="space-y-6">
        <div class="flex items-center gap-2 flex-wrap">
            <button @click="activeTab = 'all'" :class="activeTab === 'all' ? 'bg-[#2E7D32] text-white shadow-md shadow-[#2E7D32]/20' : 'bg-white text-[#222222] border border-gray-200 hover:bg-[#F5EED8]'" class="px-4 py-2 rounded-full text-xs font-medium transition-all">
                All <span class="ml-1 opacity-70">({{ $stats['total'] }})</span>
            </button>
            <button @click="activeTab = 'reserved'" :class="activeTab === 'reserved' ? 'bg-blue-600 text-white shadow-md shadow-blue-600/20' : 'bg-white text-[#222222] border border-gray-200 hover:bg-blue-50'" class="px-4 py-2 rounded-full text-xs font-medium transition-all">
                <i class="fa-solid fa-clock mr-1"></i> Reserved <span class="ml-1 opacity-70">({{ $stats['active'] }})</span>
            </button>
            <button @click="activeTab = 'completed'" :class="activeTab === 'completed' ? 'bg-[#2E7D32] text-white shadow-md shadow-[#2E7D32]/20' : 'bg-white text-[#222222] border border-gray-200 hover:bg-green-50'" class="px-4 py-2 rounded-full text-xs font-medium transition-all">
                <i class="fa-solid fa-circle-check mr-1"></i> Completed <span class="ml-1 opacity-70">({{ $stats['completed'] }})</span>
            </button>
            <button @click="activeTab = 'cancelled'" :class="activeTab === 'cancelled' ? 'bg-red-500 text-white shadow-md shadow-red-500/20' : 'bg-white text-[#222222] border border-gray-200 hover:bg-red-50'" class="px-4 py-2 rounded-full text-xs font-medium transition-all">
                <i class="fa-solid fa-circle-xmark mr-1"></i> Cancelled <span class="ml-1 opacity-70">({{ $stats['cancelled'] }})</span>
            </button>
        </div>

        {{-- Reservation Cards --}}
        @if($reservations->isEmpty())
            <div class="bg-white rounded-xl border border-gray-100 p-12 text-center space-y-4">
                <div class="w-16 h-16 rounded-full bg-[#FFF9E8] text-[#2E7D32] flex items-center justify-center mx-auto text-2xl">
                    <i class="fa-solid fa-calendar-check"></i>
                </div>
                <div class="space-y-1">
                    <h3 class="text-lg font-bold text-[#222222]">No Reservations Yet</h3>
                    <p class="text-xs text-[#666666] max-w-sm mx-auto">Browse the marketplace and reserve surplus food items before they expire!</p>
                </div>
                <a href="{{ route('marketplace.index') }}" class="inline-block px-5 py-2 bg-[#2E7D32] text-white text-xs font-medium rounded-full shadow-md">
                    Browse Available Food
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($reservations as $reservation)
                    <div x-show="activeTab === 'all' || activeTab === '{{ $reservation->status }}'"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         class="bg-white rounded-xl shadow-xs border border-gray-100 overflow-hidden flex flex-col hover:-translate-y-1 hover:shadow-md transition-all duration-300">

                        {{-- Food Image Banner --}}
                        <div class="relative h-40 bg-gray-100 shrink-0 overflow-hidden">
                            @if($reservation->food && $reservation->food->image)
                                <img src="{{ asset('storage/' . $reservation->food->image) }}" alt="{{ $reservation->food->food_name }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-400 bg-[#F5F5F5]">
                                    <i class="fa-solid fa-image text-3xl"></i>
                                </div>
                            @endif

                            {{-- Status Badge --}}
                            <div class="absolute top-3 left-3">
                                @if($reservation->isReserved())
                                    <span class="px-3 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-blue-500 text-white shadow-sm flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span> Reserved
                                    </span>
                                @elseif($reservation->isCompleted())
                                    <span class="px-3 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-[#2E7D32] text-white shadow-sm">
                                        <i class="fa-solid fa-circle-check mr-1"></i> Completed
                                    </span>
                                @elseif($reservation->isCancelled())
                                    <span class="px-3 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-red-500 text-white shadow-sm">
                                        <i class="fa-solid fa-circle-xmark mr-1"></i> Cancelled
                                    </span>
                                @endif
                            </div>

                            {{-- Quantity Badge --}}
                            <div class="absolute top-3 right-3">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-white/90 backdrop-blur-sm text-[#222222] shadow-sm">
                                    {{ $reservation->quantity }} {{ $reservation->quantity > 1 ? 'items' : 'item' }}
                                </span>
                            </div>
                        </div>

                        {{-- Card Body --}}
                        <div class="p-5 flex-1 flex flex-col justify-between space-y-4">
                            <div class="space-y-1.5">
                                <h3 class="font-heading font-bold text-base text-[#222222] line-clamp-1">
                                    {{ $reservation->food ? $reservation->food->food_name : 'Food item unavailable' }}
                                </h3>
                                <div class="flex items-center gap-1.5 text-xs text-[#666666]">
                                    <i class="fa-solid fa-store text-[#2E7D32]"></i>
                                    <span class="font-medium text-[#222222]">
                                        {{ $reservation->food && $reservation->food->user ? $reservation->food->user->name : 'Provider' }}
                                    </span>
                                </div>
                            </div>

                            {{-- Details --}}
                            <div class="space-y-2 text-xs text-[#666666] border-t border-gray-100 pt-3">
                                {{-- Price --}}
                                <div class="flex items-center justify-between">
                                    <span class="text-base font-heading font-bold text-[#2E7D32]">
                                        @if($reservation->food && $reservation->food->donation_status)
                                            <span class="text-[#F59E0B]">FREE</span>
                                        @else
                                            ৳{{ $reservation->food ? number_format($reservation->food->price, 2) : '0.00' }}
                                        @endif
                                    </span>
                                    @if($reservation->food)
                                        <span class="text-[11px] font-medium px-2 py-0.5 rounded-md bg-[#F5F5F5] text-[#222222]">
                                            {{ $reservation->food->category }}
                                        </span>
                                    @endif
                                </div>

                                {{-- Reserved At --}}
                                <div class="flex items-center gap-1.5 text-[11px]">
                                    <i class="fa-solid fa-calendar-plus text-blue-500"></i>
                                    <span>Reserved: <strong class="text-[#222222] font-medium">{{ $reservation->reserved_at->format('M d, Y h:i A') }}</strong></span>
                                </div>

                                {{-- Pickup Window --}}
                                @if($reservation->food)
                                    <div class="flex items-center gap-1.5 text-[11px]">
                                        <i class="fa-solid fa-clock text-[#666666]"></i>
                                        <span>Pickup: <strong class="text-[#222222] font-medium">{{ $reservation->food->pickup_window }}</strong></span>
                                    </div>
                                @endif

                                {{-- Completed/Cancelled At --}}
                                @if($reservation->isCompleted() && $reservation->completed_at)
                                    <div class="flex items-center gap-1.5 text-[11px]">
                                        <i class="fa-solid fa-circle-check text-[#2E7D32]"></i>
                                        <span>Picked up: <strong class="text-[#222222] font-medium">{{ $reservation->completed_at->format('M d, Y h:i A') }}</strong></span>
                                    </div>
                                @endif
                                @if($reservation->isCancelled() && $reservation->cancelled_at)
                                    <div class="flex items-center gap-1.5 text-[11px]">
                                        <i class="fa-solid fa-circle-xmark text-red-500"></i>
                                        <span>Cancelled: <strong class="text-[#222222] font-medium">{{ $reservation->cancelled_at->format('M d, Y h:i A') }}</strong></span>
                                    </div>
                                @endif

                                {{-- Notes --}}
                                @if($reservation->notes)
                                    <div class="flex items-start gap-1.5 text-[11px]">
                                        <i class="fa-solid fa-sticky-note text-amber-500 mt-0.5"></i>
                                        <span class="line-clamp-2">{{ $reservation->notes }}</span>
                                    </div>
                                @endif
                            </div>

                            {{-- Action Button --}}
                            @if($reservation->isReserved())
                                <div class="pt-1">
                                    <form action="{{ route('reservations.cancel', $reservation->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel this reservation? The food item will become available again.')">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="block w-full py-2.5 bg-red-50 hover:bg-[#EF4444] text-[#EF4444] hover:text-white text-xs font-medium rounded-full text-center shadow-xs hover:shadow-md transition-all">
                                            <i class="fa-solid fa-xmark mr-1"></i> Cancel Reservation
                                        </button>
                                    </form>
                                </div>
                            @elseif($reservation->isCompleted())
                                <div class="pt-1">
                                    <div class="w-full py-2.5 bg-[#2E7D32]/10 text-[#2E7D32] text-xs font-medium rounded-full text-center">
                                        <i class="fa-solid fa-circle-check mr-1"></i> Pickup Completed
                                    </div>
                                </div>
                            @elseif($reservation->isCancelled())
                                <div class="pt-1">
                                    <div class="w-full py-2.5 bg-red-50 text-red-400 text-xs font-medium rounded-full text-center">
                                        <i class="fa-solid fa-ban mr-1"></i> Reservation Cancelled
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Empty State for Filtered Tabs --}}
        @if($reservations->isNotEmpty())
            <div x-show="activeTab === 'reserved' && {{ $stats['active'] }} === 0" x-cloak class="bg-white rounded-xl border border-gray-100 p-8 text-center space-y-2">
                <i class="fa-solid fa-clock text-2xl text-blue-300"></i>
                <p class="text-sm font-medium text-[#222222]">No active reservations</p>
                <p class="text-xs text-[#666666]">Reserve food items from the marketplace to see them here.</p>
            </div>
            <div x-show="activeTab === 'completed' && {{ $stats['completed'] }} === 0" x-cloak class="bg-white rounded-xl border border-gray-100 p-8 text-center space-y-2">
                <i class="fa-solid fa-circle-check text-2xl text-green-300"></i>
                <p class="text-sm font-medium text-[#222222]">No completed reservations yet</p>
            </div>
            <div x-show="activeTab === 'cancelled' && {{ $stats['cancelled'] }} === 0" x-cloak class="bg-white rounded-xl border border-gray-100 p-8 text-center space-y-2">
                <i class="fa-solid fa-circle-xmark text-2xl text-red-300"></i>
                <p class="text-sm font-medium text-[#222222]">No cancelled reservations</p>
            </div>
        @endif
    </div>

</div>
@endsection
