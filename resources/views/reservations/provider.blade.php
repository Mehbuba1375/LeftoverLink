@extends('layouts.app')

@section('content')
<div class="space-y-8 pb-12">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-200/60 pb-4">
        <div>
            <h1 class="text-2xl font-heading font-extrabold text-[#222222]">Incoming Reservations</h1>
            <p class="text-xs text-[#666666]">Manage consumer reservations on your food listings and confirm pickups</p>
        </div>
        <a href="{{ route('provider.dashboard') }}" class="inline-flex items-center gap-2 px-5 py-3 bg-[#2E7D32] hover:bg-[#256928] text-white text-xs font-medium rounded-full shadow-md shadow-[#2E7D32]/20 hover:scale-105 transition-all shrink-0">
            <i class="fa-solid fa-arrow-left text-xs"></i> Back to Dashboard
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
                <p class="text-xs text-blue-600 font-medium">Pending Pickup</p>
            </div>
            <p class="text-2xl font-bold text-blue-600 mt-1">{{ $stats['active'] }}</p>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-xs border border-gray-100">
            <div class="flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-[#2E7D32]"></span>
                <p class="text-xs text-[#2E7D32] font-medium">Picked Up</p>
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
                <i class="fa-solid fa-clock mr-1"></i> Pending Pickup <span class="ml-1 opacity-70">({{ $stats['active'] }})</span>
            </button>
            <button @click="activeTab = 'completed'" :class="activeTab === 'completed' ? 'bg-[#2E7D32] text-white shadow-md shadow-[#2E7D32]/20' : 'bg-white text-[#222222] border border-gray-200 hover:bg-green-50'" class="px-4 py-2 rounded-full text-xs font-medium transition-all">
                <i class="fa-solid fa-circle-check mr-1"></i> Picked Up <span class="ml-1 opacity-70">({{ $stats['completed'] }})</span>
            </button>
            <button @click="activeTab = 'cancelled'" :class="activeTab === 'cancelled' ? 'bg-red-500 text-white shadow-md shadow-red-500/20' : 'bg-white text-[#222222] border border-gray-200 hover:bg-red-50'" class="px-4 py-2 rounded-full text-xs font-medium transition-all">
                <i class="fa-solid fa-circle-xmark mr-1"></i> Cancelled <span class="ml-1 opacity-70">({{ $stats['cancelled'] }})</span>
            </button>
        </div>

        {{-- Empty State --}}
        @if($reservations->isEmpty())
            <div class="bg-white rounded-xl border border-gray-100 p-12 text-center space-y-4">
                <div class="w-16 h-16 rounded-full bg-[#FFF9E8] text-[#2E7D32] flex items-center justify-center mx-auto text-2xl">
                    <i class="fa-solid fa-inbox"></i>
                </div>
                <div class="space-y-1">
                    <h3 class="text-lg font-bold text-[#222222]">No Reservations Yet</h3>
                    <p class="text-xs text-[#666666] max-w-sm mx-auto">When consumers reserve your food listings, they will appear here for you to manage.</p>
                </div>
            </div>
        @else
            {{-- Reservation Cards Table --}}
            <div class="bg-white rounded-xl shadow-xs border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead>
                            <tr class="bg-[#F5F5F5] text-left text-[#666666] font-semibold uppercase tracking-wider">
                                <th class="px-5 py-3">Food Item</th>
                                <th class="px-5 py-3">Consumer</th>
                                <th class="px-5 py-3">Qty</th>
                                <th class="px-5 py-3">Status</th>
                                <th class="px-5 py-3">Reserved At</th>
                                <th class="px-5 py-3">Notes</th>
                                <th class="px-5 py-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($reservations as $reservation)
                                <tr x-show="activeTab === 'all' || activeTab === '{{ $reservation->status }}'"
                                    x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0"
                                    x-transition:enter-end="opacity-100"
                                    class="hover:bg-[#FFF9E8]/50 transition-colors">

                                    {{-- Food Item --}}
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-lg bg-gray-100 overflow-hidden shrink-0">
                                                @if($reservation->food && $reservation->food->image)
                                                    <img src="{{ asset('storage/' . $reservation->food->image) }}" alt="{{ $reservation->food->food_name }}" class="w-full h-full object-cover">
                                                @else
                                                    <div class="w-full h-full flex items-center justify-center text-gray-400">
                                                        <i class="fa-solid fa-image text-xs"></i>
                                                    </div>
                                                @endif
                                            </div>
                                            <div>
                                                <p class="font-semibold text-[#222222] line-clamp-1">{{ $reservation->food ? $reservation->food->food_name : 'Unavailable' }}</p>
                                                <p class="text-[10px] text-[#666666]">
                                                    @if($reservation->food && $reservation->food->donation_status)
                                                        <span class="text-[#F59E0B] font-bold">FREE</span>
                                                    @else
                                                        ৳{{ $reservation->food ? number_format($reservation->food->price, 2) : '0.00' }}
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Consumer --}}
                                    <td class="px-5 py-4">
                                        <div>
                                            <p class="font-semibold text-[#222222]">{{ $reservation->user ? $reservation->user->name : 'Unknown' }}</p>
                                            <p class="text-[10px] text-[#666666]">{{ $reservation->user ? $reservation->user->phone : '' }}</p>
                                            @if($reservation->preferred_pickup_date)
                                                <div class="mt-1 text-[10px]">
                                                    <span class="text-[#666666]">Sched: <strong class="text-[#222222]">{{ $reservation->formatted_preferred_schedule }}</strong></span>
                                                    @if($reservation->isScheduleApproved())
                                                        <span class="ml-1 px-1.5 py-0.5 rounded text-[9px] font-bold bg-green-100 text-green-700">Approved</span>
                                                    @elseif($reservation->isScheduleAdjusted())
                                                        <span class="ml-1 px-1.5 py-0.5 rounded text-[9px] font-bold bg-blue-100 text-blue-700">Adjusted: {{ $reservation->formatted_approved_schedule }}</span>
                                                    @else
                                                        <span class="ml-1 px-1.5 py-0.5 rounded text-[9px] font-bold bg-amber-100 text-amber-700">Pending</span>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- Quantity --}}
                                    <td class="px-5 py-4">
                                        <span class="font-bold text-[#222222] text-sm">{{ $reservation->quantity }}</span>
                                    </td>

                                    {{-- Status --}}
                                    <td class="px-5 py-4">
                                        @if($reservation->isReserved())
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-100 text-blue-700">
                                                <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span> Pending Pickup
                                            </span>
                                        @elseif($reservation->isCompleted())
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-green-100 text-[#2E7D32]">
                                                <i class="fa-solid fa-circle-check"></i> Picked Up
                                            </span>
                                        @elseif($reservation->isCancelled())
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-red-100 text-red-600">
                                                <i class="fa-solid fa-circle-xmark"></i> Cancelled
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Reserved At --}}
                                    <td class="px-5 py-4 text-[#666666]">
                                        <p>{{ $reservation->reserved_at->format('M d, Y') }}</p>
                                        <p class="text-[10px]">{{ $reservation->reserved_at->format('h:i A') }}</p>
                                    </td>

                                    {{-- Notes --}}
                                    <td class="px-5 py-4 text-[#666666] max-w-[150px]">
                                        <p class="line-clamp-2 text-[11px]">{{ $reservation->notes ?? '—' }}</p>
                                    </td>

                                    {{-- Action --}}
                                    <td class="px-5 py-4 text-right">
                                        @if($reservation->isReserved())
                                            <div class="flex items-center justify-end gap-1.5">
                                                @if($reservation->preferred_pickup_date && $reservation->isSchedulePending())
                                                    <form action="{{ route('reservations.approve-schedule', $reservation->id) }}" method="POST" class="inline">
                                                        @csrf
                                                        <button type="submit" class="px-2.5 py-1.5 bg-green-50 hover:bg-green-600 text-green-700 hover:text-white text-[10px] font-semibold rounded-full transition-all" title="Approve Requested Schedule">
                                                            <i class="fa-solid fa-calendar-check mr-1"></i> Approve Sched
                                                        </button>
                                                    </form>
                                                @endif

                                                @if($reservation->preferred_pickup_date)
                                                    <div x-data="{ adjustModal: false }" class="inline text-left">
                                                        <button type="button" @click="adjustModal = true" class="px-2.5 py-1.5 bg-blue-50 hover:bg-blue-600 text-blue-700 hover:text-white text-[10px] font-semibold rounded-full transition-all" title="Adjust Pickup Schedule">
                                                            <i class="fa-solid fa-calendar-pen mr-1"></i> Adjust
                                                        </button>

                                                        <div x-show="adjustModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/40 backdrop-blur-xs flex items-center justify-center p-4">
                                                            <div @click.outside="adjustModal = false" class="bg-white max-w-sm w-full p-6 rounded-2xl shadow-2xl space-y-4">
                                                                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                                                                    <h3 class="text-base font-heading font-bold text-[#222222]">Adjust Pickup Schedule</h3>
                                                                    <button type="button" @click="adjustModal = false" class="text-[#666666] hover:text-[#222222]"><i class="fa-solid fa-xmark"></i></button>
                                                                </div>
                                                                <form action="{{ route('reservations.adjust-schedule', $reservation->id) }}" method="POST" class="space-y-4">
                                                                    @csrf
                                                                    <div>
                                                                        <label class="block text-xs font-semibold text-[#222222] mb-1">New Pickup Date</label>
                                                                        <input type="date" name="adjusted_pickup_date" min="{{ date('Y-m-d') }}" value="{{ $reservation->approved_pickup_date ? $reservation->approved_pickup_date->format('Y-m-d') : ($reservation->preferred_pickup_date ? $reservation->preferred_pickup_date->format('Y-m-d') : date('Y-m-d')) }}" required class="w-full px-3 py-2 bg-[#F5F5F5] border border-gray-200 rounded-xl text-xs text-[#222222]">
                                                                    </div>
                                                                    <div>
                                                                        <div class="flex justify-between items-center mb-1">
                                                                            <label class="block text-xs font-semibold text-[#222222]">New Pickup Time</label>
                                                                            <span class="text-[10px] text-[#2E7D32] font-medium">Window: {{ $reservation->food->pickup_window ?? 'Flexible' }}</span>
                                                                        </div>
                                                                        <input type="time" name="adjusted_pickup_time" value="{{ $reservation->approved_pickup_time ? \Carbon\Carbon::parse($reservation->approved_pickup_time)->format('H:i') : ($reservation->preferred_pickup_time ? \Carbon\Carbon::parse($reservation->preferred_pickup_time)->format('H:i') : '') }}" required class="w-full px-3 py-2 bg-[#F5F5F5] border border-gray-200 rounded-xl text-xs text-[#222222]">
                                                                    </div>
                                                                    <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                                                                        <button type="button" @click="adjustModal = false" class="px-4 py-2 bg-[#F5F5F5] text-[#222222] text-xs font-medium rounded-full">Cancel</button>
                                                                        <button type="submit" class="px-5 py-2 bg-[#2E7D32] hover:bg-[#256928] text-white text-xs font-medium rounded-full shadow-md">Save Schedule</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif

                                                <form action="{{ route('reservations.complete', $reservation->id) }}" method="POST" class="inline" onsubmit="return confirm('Confirm that this consumer has picked up the food?')">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="px-4 py-2 bg-[#2E7D32] hover:bg-[#256928] text-white text-[11px] font-medium rounded-full shadow-xs hover:shadow-md hover:scale-105 transition-all">
                                                        <i class="fa-solid fa-check mr-1"></i> Confirm Pickup
                                                    </button>
                                                </form>
                                            </div>
                                        @elseif($reservation->isCompleted())
                                            <span class="text-[11px] text-[#2E7D32] font-medium">
                                                <i class="fa-solid fa-check-double mr-1"></i>
                                                {{ $reservation->completed_at ? $reservation->completed_at->format('M d, h:i A') : 'Done' }}
                                            </span>
                                        @elseif($reservation->isCancelled())
                                            <span class="text-[11px] text-red-400 font-medium">
                                                {{ $reservation->cancelled_at ? $reservation->cancelled_at->format('M d, h:i A') : 'Cancelled' }}
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- Empty State for Filtered Tabs --}}
        @if($reservations->isNotEmpty())
            <div x-show="activeTab === 'reserved' && {{ $stats['active'] }} === 0" x-cloak class="bg-white rounded-xl border border-gray-100 p-8 text-center space-y-2">
                <i class="fa-solid fa-clock text-2xl text-blue-300"></i>
                <p class="text-sm font-medium text-[#222222]">No pending pickup reservations</p>
            </div>
            <div x-show="activeTab === 'completed' && {{ $stats['completed'] }} === 0" x-cloak class="bg-white rounded-xl border border-gray-100 p-8 text-center space-y-2">
                <i class="fa-solid fa-circle-check text-2xl text-green-300"></i>
                <p class="text-sm font-medium text-[#222222]">No completed pickups yet</p>
            </div>
            <div x-show="activeTab === 'cancelled' && {{ $stats['cancelled'] }} === 0" x-cloak class="bg-white rounded-xl border border-gray-100 p-8 text-center space-y-2">
                <i class="fa-solid fa-circle-xmark text-2xl text-red-300"></i>
                <p class="text-sm font-medium text-[#222222]">No cancelled reservations</p>
            </div>
        @endif
    </div>

</div>
@endsection
