@extends('layouts.app')

@section('content')
<div class="space-y-8 pb-16" x-data="{ 
    activeTab: 'overview',
    searchQuery: '',
    statusFilter: 'all',
    selectedReceipt: null,
    showReceiptModal: false
}">

    {{-- Dashboard Banner & Header --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-[#1B4D3E] via-[#2E7D32] to-[#4CAF50] p-6 sm:p-8 text-white shadow-xl">
        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="absolute right-40 -top-10 w-40 h-40 bg-emerald-300/20 rounded-full blur-xl pointer-events-none"></div>
        
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-2 max-w-2xl">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 backdrop-blur-md text-emerald-100 text-xs font-semibold uppercase tracking-wider">
                    <i class="fa-solid fa-chart-line text-xs"></i> Consumer Workspace
                </div>
                <h1 class="text-3xl sm:text-4xl font-heading font-extrabold tracking-tight text-white">
                    Reservation History Dashboard
                </h1>
                <p class="text-emerald-100/90 text-sm sm:text-base leading-relaxed">
                    View your complete reservation histories, completed pickups, cancelled orders, and verified payment records in one central interface.
                </p>
            </div>

            <div class="flex items-center gap-3 shrink-0 flex-wrap">
                <a href="{{ route('marketplace.index') }}" class="inline-flex items-center gap-2 px-5 py-3 bg-white text-[#2E7D32] hover:bg-emerald-50 text-xs font-bold rounded-xl shadow-lg transition-all transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-store"></i> Browse Marketplace
                </a>
                <button type="button" @click="window.print()" class="inline-flex items-center gap-2 px-4 py-3 bg-emerald-900/40 hover:bg-emerald-900/60 border border-white/20 text-white text-xs font-semibold rounded-xl backdrop-blur-md transition-all">
                    <i class="fa-solid fa-print"></i> Print Records
                </button>
            </div>
        </div>
    </div>

    {{-- Key Performance Indicators (KPI Analytics Cards) --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
        {{-- Total Reservations --}}
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-emerald-900/5 flex flex-col justify-between hover:shadow-md transition-all">
            <div class="flex items-center justify-between text-gray-500 mb-2">
                <span class="text-xs font-semibold uppercase tracking-wider">Total Orders</span>
                <span class="p-2 rounded-xl bg-emerald-50 text-[#2E7D32]"><i class="fa-solid fa-receipt"></i></span>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
                <p class="text-[11px] text-gray-500 mt-0.5">Lifetime reservations</p>
            </div>
        </div>

        {{-- Completed Pickups --}}
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-emerald-900/5 flex flex-col justify-between hover:shadow-md transition-all">
            <div class="flex items-center justify-between text-emerald-600 mb-2">
                <span class="text-xs font-semibold uppercase tracking-wider">Completed</span>
                <span class="p-2 rounded-xl bg-emerald-100/60 text-emerald-700"><i class="fa-solid fa-circle-check"></i></span>
            </div>
            <div>
                <p class="text-2xl font-bold text-emerald-700">{{ $stats['completed'] }}</p>
                <p class="text-[11px] text-emerald-600/80 mt-0.5">Successful pickups</p>
            </div>
        </div>

        {{-- Cancelled --}}
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-emerald-900/5 flex flex-col justify-between hover:shadow-md transition-all">
            <div class="flex items-center justify-between text-rose-500 mb-2">
                <span class="text-xs font-semibold uppercase tracking-wider">Cancelled</span>
                <span class="p-2 rounded-xl bg-rose-50 text-rose-600"><i class="fa-solid fa-circle-xmark"></i></span>
            </div>
            <div>
                <p class="text-2xl font-bold text-rose-600">{{ $stats['cancelled'] }}</p>
                <p class="text-[11px] text-rose-500/80 mt-0.5">Voided reservations</p>
            </div>
        </div>

        {{-- Active --}}
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-emerald-900/5 flex flex-col justify-between hover:shadow-md transition-all">
            <div class="flex items-center justify-between text-sky-600 mb-2">
                <span class="text-xs font-semibold uppercase tracking-wider">Active</span>
                <span class="p-2 rounded-xl bg-sky-50 text-sky-600"><i class="fa-solid fa-clock animate-pulse"></i></span>
            </div>
            <div>
                <p class="text-2xl font-bold text-sky-600">{{ $stats['active'] }}</p>
                <p class="text-[11px] text-sky-600/80 mt-0.5">Pending pickup</p>
            </div>
        </div>

        {{-- Total Spend --}}
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-emerald-900/5 flex flex-col justify-between hover:shadow-md transition-all col-span-2 sm:col-span-1 lg:col-span-1">
            <div class="flex items-center justify-between text-amber-600 mb-2">
                <span class="text-xs font-semibold uppercase tracking-wider">Total Spent</span>
                <span class="p-2 rounded-xl bg-amber-50 text-amber-600"><i class="fa-solid fa-wallet"></i></span>
            </div>
            <div>
                <p class="text-2xl font-bold text-amber-700">৳{{ number_format($stats['total_spent'], 2) }}</p>
                <p class="text-[11px] text-amber-600 mt-0.5">Paid surplus food</p>
            </div>
        </div>

        {{-- Food Rescued --}}
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-emerald-900/5 flex flex-col justify-between hover:shadow-md transition-all col-span-2 sm:col-span-2 lg:col-span-1">
            <div class="flex items-center justify-between text-teal-600 mb-2">
                <span class="text-xs font-semibold uppercase tracking-wider">Items Rescued</span>
                <span class="p-2 rounded-xl bg-teal-50 text-teal-600"><i class="fa-solid fa-leaf"></i></span>
            </div>
            <div>
                <p class="text-2xl font-bold text-teal-700">{{ $stats['total_rescued_items'] }} <span class="text-xs font-normal">items</span></p>
                <p class="text-[11px] text-teal-600 mt-0.5">{{ $stats['free_pickups'] }} free donations</p>
            </div>
        </div>
    </div>

    {{-- Main Navigation Tabs & Search Controls --}}
    <div class="bg-white rounded-2xl p-4 shadow-sm border border-emerald-900/5 space-y-4">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-gray-100 pb-4">
            {{-- Navigation Tabs --}}
            <div class="flex items-center gap-1.5 p-1 bg-gray-100/80 rounded-xl overflow-x-auto shrink-0 scrollbar-none">
                <button type="button" @click="activeTab = 'overview'" :class="activeTab === 'overview' ? 'bg-white text-[#2E7D32] shadow-xs font-bold' : 'text-gray-600 hover:text-gray-900 font-medium'" class="px-4 py-2 rounded-lg text-xs transition-all whitespace-nowrap">
                    <i class="fa-solid fa-border-all mr-1.5"></i> Overview History
                </button>
                <button type="button" @click="activeTab = 'completed'" :class="activeTab === 'completed' ? 'bg-white text-[#2E7D32] shadow-xs font-bold' : 'text-gray-600 hover:text-gray-900 font-medium'" class="px-4 py-2 rounded-lg text-xs transition-all whitespace-nowrap">
                    <i class="fa-solid fa-box-check mr-1.5 text-emerald-600"></i> Completed Pickups
                </button>
                <button type="button" @click="activeTab = 'cancelled'" :class="activeTab === 'cancelled' ? 'bg-white text-rose-600 shadow-xs font-bold' : 'text-gray-600 hover:text-gray-900 font-medium'" class="px-4 py-2 rounded-lg text-xs transition-all whitespace-nowrap">
                    <i class="fa-solid fa-ban mr-1.5 text-rose-500"></i> Cancelled Pickups
                </button>
                <button type="button" @click="activeTab = 'payments'" :class="activeTab === 'payments' ? 'bg-white text-amber-700 shadow-xs font-bold' : 'text-gray-600 hover:text-gray-900 font-medium'" class="px-4 py-2 rounded-lg text-xs transition-all whitespace-nowrap">
                    <i class="fa-solid fa-file-invoice-dollar mr-1.5 text-amber-600"></i> Payment Records
                </button>
            </div>

            {{-- Search & Filter Input --}}
            <div class="flex items-center gap-3 w-full lg:w-auto">
                <div class="relative flex-1 lg:w-64">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input type="text" x-model="searchQuery" placeholder="Search by food name or provider..." class="w-full pl-9 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-[#2E7D32]/20 focus:border-[#2E7D32] transition-all">
                </div>
                
                <select x-model="statusFilter" class="py-2 px-3 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-700 font-medium focus:ring-2 focus:ring-[#2E7D32]/20 focus:border-[#2E7D32]">
                    <option value="all">Filter: All Statuses</option>
                    <option value="reserved">Active Only</option>
                    <option value="completed">Completed Only</option>
                    <option value="cancelled">Cancelled Only</option>
                </select>
            </div>
        </div>

        {{-- TAB 1: OVERVIEW & CARDS VIEW --}}
        <div x-show="activeTab === 'overview'" x-transition:enter="transition ease-out duration-200" class="space-y-6">
            @if($allReservations->isEmpty())
                <div class="bg-gray-50 rounded-2xl p-12 text-center space-y-4 border border-dashed border-gray-200">
                    <div class="w-16 h-16 rounded-2xl bg-emerald-100/60 text-[#2E7D32] flex items-center justify-center mx-auto text-2xl shadow-inner">
                        <i class="fa-solid fa-calendar-xmark"></i>
                    </div>
                    <div class="space-y-1">
                        <h3 class="text-lg font-bold text-gray-900">No Reservations Found</h3>
                        <p class="text-xs text-gray-500 max-w-sm mx-auto">You haven't made any food reservations yet. Explore local surplus food offerings on the marketplace!</p>
                    </div>
                    <a href="{{ route('marketplace.index') }}" class="inline-flex items-center gap-2 px-6 py-2.5 bg-[#2E7D32] hover:bg-[#256928] text-white text-xs font-bold rounded-xl shadow-md transition-all">
                        <i class="fa-solid fa-utensils"></i> Browse Food Marketplace
                    </a>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($allReservations as $res)
                        <div x-show="(statusFilter === 'all' || statusFilter === '{{ $res->status }}') && ('{{ strtolower(addslashes($res->food ? $res->food->food_name : '')) }}'.includes(searchQuery.toLowerCase()) || '{{ strtolower(addslashes($res->food && $res->food->user ? $res->food->user->name : '')) }}'.includes(searchQuery.toLowerCase()))"
                             class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col hover:-translate-y-1 hover:shadow-lg transition-all duration-300 group">
                            
                            {{-- Food Image Banner --}}
                            <div class="relative h-44 bg-gray-100 shrink-0 overflow-hidden">
                                @if($res->food && $res->food->image)
                                    <img src="{{ asset('storage/' . $res->food->image) }}" alt="{{ $res->food->food_name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-gray-400 bg-gray-50">
                                        <i class="fa-solid fa-utensils text-4xl"></i>
                                    </div>
                                @endif
                                <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-black/20"></div>

                                {{-- Status Badge --}}
                                <div class="absolute top-3 left-3">
                                    @if($res->isReserved())
                                        <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-sky-500 text-white shadow-sm flex items-center gap-1.5 backdrop-blur-md">
                                            <span class="w-1.5 h-1.5 rounded-full bg-white animate-ping"></span> Active Pickup
                                        </span>
                                    @elseif($res->isCompleted())
                                        <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-emerald-600 text-white shadow-sm flex items-center gap-1 backdrop-blur-md">
                                            <i class="fa-solid fa-circle-check text-xs"></i> Completed
                                        </span>
                                    @elseif($res->isCancelled())
                                        <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-rose-600 text-white shadow-sm flex items-center gap-1 backdrop-blur-md">
                                            <i class="fa-solid fa-circle-xmark text-xs"></i> Cancelled
                                        </span>
                                    @endif
                                </div>

                                {{-- Price / Donation Tag --}}
                                <div class="absolute top-3 right-3">
                                    @if($res->food && $res->food->donation_status)
                                        <span class="px-3 py-1 rounded-full text-[11px] font-extrabold bg-amber-500 text-white shadow-sm uppercase">
                                            FREE
                                        </span>
                                    @else
                                        <span class="px-3 py-1 rounded-full text-[11px] font-extrabold bg-white/90 text-gray-900 shadow-sm backdrop-blur-md">
                                            ৳{{ $res->food ? number_format($res->food->price, 2) : '0.00' }}
                                        </span>
                                    @endif
                                </div>

                                {{-- Title over overlay --}}
                                <div class="absolute bottom-3 left-3 right-3 text-white">
                                    <h3 class="font-heading font-bold text-base line-clamp-1 drop-shadow-md">
                                        {{ $res->food ? $res->food->food_name : 'Reserved Item' }}
                                    </h3>
                                    <p class="text-xs text-white/80 flex items-center gap-1.5">
                                        <i class="fa-solid fa-store text-emerald-400"></i>
                                        <span>{{ $res->food && $res->food->user ? $res->food->user->name : 'Food Provider' }}</span>
                                    </p>
                                </div>
                            </div>

                            {{-- Details Body --}}
                            <div class="p-5 flex-1 flex flex-col justify-between space-y-4">
                                <div class="space-y-2 text-xs text-gray-600">
                                    <div class="flex items-center justify-between border-b border-gray-100 pb-2">
                                        <span class="text-gray-500 font-medium">Quantity Reserved:</span>
                                        <span class="font-bold text-gray-900 bg-emerald-50 px-2.5 py-0.5 rounded-lg text-emerald-800">
                                            {{ $res->quantity }} {{ $res->quantity > 1 ? 'items' : 'item' }}
                                        </span>
                                    </div>

                                    <div class="flex items-center justify-between text-gray-500">
                                        <span>Reserved Date:</span>
                                        <span class="font-semibold text-gray-800">{{ $res->reserved_at->format('M d, Y • h:i A') }}</span>
                                    </div>

                                    @if($res->isCompleted() && $res->completed_at)
                                        <div class="flex items-center justify-between text-emerald-700 bg-emerald-50/70 p-2 rounded-xl border border-emerald-100">
                                            <span class="flex items-center gap-1.5"><i class="fa-solid fa-circle-check text-emerald-600"></i> Picked Up:</span>
                                            <span class="font-bold">{{ $res->completed_at->format('M d, Y h:i A') }}</span>
                                        </div>
                                    @endif

                                    @if($res->isCancelled() && $res->cancelled_at)
                                        <div class="flex items-center justify-between text-rose-700 bg-rose-50/70 p-2 rounded-xl border border-rose-100">
                                            <span class="flex items-center gap-1.5"><i class="fa-solid fa-ban text-rose-600"></i> Cancelled:</span>
                                            <span class="font-bold">{{ $res->cancelled_at->format('M d, Y h:i A') }}</span>
                                        </div>
                                    @endif

                                    @if($res->preferred_pickup_date)
                                        <div class="p-2.5 bg-gray-50 rounded-xl space-y-1 text-[11px]">
                                            <div class="flex items-center justify-between font-semibold text-gray-800">
                                                <span>Pickup Schedule:</span>
                                                @if($res->isScheduleApproved())
                                                    <span class="px-2 py-0.5 rounded text-[10px] bg-emerald-100 text-emerald-800 font-bold">Approved</span>
                                                @elseif($res->isScheduleAdjusted())
                                                    <span class="px-2 py-0.5 rounded text-[10px] bg-sky-100 text-sky-800 font-bold">Adjusted</span>
                                                @else
                                                    <span class="px-2 py-0.5 rounded text-[10px] bg-amber-100 text-amber-800 font-bold">Pending Approval</span>
                                                @endif
                                            </div>
                                            <p class="text-gray-600">Requested: <strong class="text-gray-800">{{ $res->formatted_preferred_schedule }}</strong></p>
                                        </div>
                                    @endif
                                </div>

                                {{-- Card Footer & Actions --}}
                                <div class="pt-2 border-t border-gray-100 flex flex-wrap items-center justify-between gap-2">
                                    <button type="button" @click="selectedReceipt = {{ json_encode($paymentRecords->firstWhere('id', $res->id)) }}; showReceiptModal = true" class="flex-1 py-2 px-3 bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs font-semibold rounded-xl transition-all text-center">
                                        <i class="fa-solid fa-file-invoice mr-1"></i> Receipt
                                    </button>

                                    @if($res->isReserved() && $res->food && !$res->food->donation_status && $res->payment_status !== 'paid')
                                        <form action="{{ route('payment.pay_reservation', $res->id) }}" method="POST" class="flex-1">
                                            @csrf
                                            <button type="submit" class="w-full py-2 px-3 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition-all text-center flex items-center justify-center gap-1">
                                                <i class="fa-solid fa-credit-card text-[11px]"></i> Pay Online
                                            </button>
                                        </form>
                                    @endif

                                    @if($res->isReserved())
                                        <form action="{{ route('reservations.cancel', $res->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel this reservation?')" class="flex-1">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="w-full py-2 px-3 bg-rose-50 hover:bg-rose-600 text-rose-600 hover:text-white text-xs font-semibold rounded-xl transition-all text-center">
                                                <i class="fa-solid fa-xmark mr-1"></i> Cancel
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>

                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- TAB 2: COMPLETED PICKUPS TAB --}}
        <div x-show="activeTab === 'completed'" x-transition:enter="transition ease-out duration-200" class="space-y-4">
            <div class="bg-emerald-50/60 border border-emerald-200/60 rounded-2xl p-4 flex items-center justify-between text-xs text-emerald-900">
                <div class="flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center text-lg shadow-sm"><i class="fa-solid fa-box-check"></i></span>
                    <div>
                        <h4 class="font-bold text-sm">Completed Pickup History</h4>
                        <p class="text-emerald-700">All surplus food items successfully claimed and verified by providers.</p>
                    </div>
                </div>
                <span class="px-3 py-1 bg-emerald-600 text-white font-bold rounded-full text-xs">{{ $stats['completed'] }} Total</span>
            </div>

            @php
                $completedList = $allReservations->where('status', \App\Models\Reservation::STATUS_COMPLETED);
            @endphp

            @if($completedList->isEmpty())
                <div class="bg-gray-50 rounded-2xl p-8 text-center text-xs text-gray-500 space-y-2">
                    <i class="fa-solid fa-box-open text-3xl text-gray-300"></i>
                    <p class="font-semibold text-gray-700">No completed pickups recorded yet.</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($completedList as $res)
                        <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:border-emerald-200 transition-all">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-lg shrink-0">
                                    <i class="fa-solid fa-circle-check"></i>
                                </div>
                                <div class="space-y-1">
                                    <h4 class="font-bold text-gray-900 text-sm">{{ $res->food->food_name ?? 'Surplus Food' }}</h4>
                                    <p class="text-xs text-gray-500 flex items-center gap-3">
                                        <span><i class="fa-solid fa-store text-emerald-600 mr-1"></i>{{ $res->food->user->name ?? 'Provider' }}</span>
                                        <span>•</span>
                                        <span>Qty: <strong>{{ $res->quantity }}</strong></span>
                                        <span>•</span>
                                        <span class="text-emerald-700 font-medium">Picked up {{ $res->completed_at ? $res->completed_at->format('M d, Y h:i A') : 'Completed' }}</span>
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 self-end sm:self-center">
                                <span class="font-bold text-sm text-gray-900">
                                    @if($res->food && $res->food->donation_status)
                                        <span class="text-amber-600 font-extrabold">FREE DONATION</span>
                                    @else
                                        ৳{{ number_format(($res->food->price ?? 0) * $res->quantity, 2) }}
                                    @endif
                                </span>
                                <button type="button" @click="selectedReceipt = {{ json_encode($paymentRecords->firstWhere('id', $res->id)) }}; showReceiptModal = true" class="px-4 py-2 bg-emerald-50 hover:bg-emerald-100 text-[#2E7D32] text-xs font-bold rounded-xl transition-all">
                                    View Receipt
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- TAB 3: CANCELLED PICKUPS TAB --}}
        <div x-show="activeTab === 'cancelled'" x-transition:enter="transition ease-out duration-200" class="space-y-4">
            <div class="bg-rose-50/60 border border-rose-200/60 rounded-2xl p-4 flex items-center justify-between text-xs text-rose-900">
                <div class="flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl bg-rose-600 text-white flex items-center justify-center text-lg shadow-sm"><i class="fa-solid fa-ban"></i></span>
                    <div>
                        <h4 class="font-bold text-sm">Cancelled Reservation History</h4>
                        <p class="text-rose-700">Reservations that were cancelled prior to pickup confirmation.</p>
                    </div>
                </div>
                <span class="px-3 py-1 bg-rose-600 text-white font-bold rounded-full text-xs">{{ $stats['cancelled'] }} Total</span>
            </div>

            @php
                $cancelledList = $allReservations->where('status', \App\Models\Reservation::STATUS_CANCELLED);
            @endphp

            @if($cancelledList->isEmpty())
                <div class="bg-gray-50 rounded-2xl p-8 text-center text-xs text-gray-500 space-y-2">
                    <i class="fa-solid fa-smile text-3xl text-gray-300"></i>
                    <p class="font-semibold text-gray-700">No cancelled reservations found. Clean track record!</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($cancelledList as $res)
                        <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:border-rose-200 transition-all">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center font-bold text-lg shrink-0">
                                    <i class="fa-solid fa-circle-xmark"></i>
                                </div>
                                <div class="space-y-1">
                                    <h4 class="font-bold text-gray-900 text-sm">{{ $res->food->food_name ?? 'Surplus Food' }}</h4>
                                    <p class="text-xs text-gray-500 flex items-center gap-3">
                                        <span>Provider: {{ $res->food->user->name ?? 'Provider' }}</span>
                                        <span>•</span>
                                        <span class="text-rose-600 font-medium">Cancelled on {{ $res->cancelled_at ? $res->cancelled_at->format('M d, Y h:i A') : 'Cancelled' }}</span>
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 self-end sm:self-center">
                                <span class="px-3 py-1 bg-rose-50 text-rose-600 font-bold rounded-lg text-xs">No Charge / Refunded</span>
                                <button type="button" @click="selectedReceipt = {{ json_encode($paymentRecords->firstWhere('id', $res->id)) }}; showReceiptModal = true" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs font-bold rounded-xl transition-all">
                                    View Audit Record
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- TAB 4: PAYMENT RECORDS LEDGER TAB --}}
        <div x-show="activeTab === 'payments'" x-transition:enter="transition ease-out duration-200" class="space-y-4">
            <div class="bg-amber-50/60 border border-amber-200/60 rounded-2xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4 text-xs text-amber-900">
                <div class="flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl bg-amber-600 text-white flex items-center justify-center text-lg shadow-sm"><i class="fa-solid fa-file-invoice-dollar"></i></span>
                    <div>
                        <h4 class="font-bold text-sm">Financial & Payment Statement Ledger</h4>
                        <p class="text-amber-800">Complete itemized financial transaction history for all food reservations.</p>
                    </div>
                </div>
                <div class="flex items-center gap-3 bg-white px-4 py-2 rounded-xl shadow-xs border border-amber-200/60">
                    <span class="text-gray-500 font-medium">Total Paid Spend:</span>
                    <span class="text-base font-extrabold text-amber-700">৳{{ number_format($stats['total_spent'], 2) }}</span>
                </div>
            </div>

            {{-- Payment Statement Table --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-gray-50/80 text-gray-600 uppercase font-bold tracking-wider text-[11px] border-b border-gray-100">
                                <th class="p-4">Transaction Ref</th>
                                <th class="p-4">Food Item</th>
                                <th class="p-4">Provider</th>
                                <th class="p-4 text-center">Qty</th>
                                <th class="p-4 text-right">Unit Price</th>
                                <th class="p-4 text-right">Total Amount</th>
                                <th class="p-4 text-center">Payment Status</th>
                                <th class="p-4 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-gray-700">
                            @foreach($paymentRecords as $rec)
                                <tr class="hover:bg-emerald-50/30 transition-colors">
                                    <td class="p-4 font-mono font-bold text-[#2E7D32]">
                                        {{ $rec['transaction_ref'] }}
                                        <div class="text-[10px] font-normal text-gray-400">{{ $rec['reservation_code'] }}</div>
                                    </td>
                                    <td class="p-4 font-semibold text-gray-900">
                                        {{ $rec['food_title'] }}
                                        <div class="text-[10px] font-normal text-gray-500">{{ $rec['category'] }}</div>
                                    </td>
                                    <td class="p-4 text-gray-600">{{ $rec['provider_name'] }}</td>
                                    <td class="p-4 text-center font-bold text-gray-900">{{ $rec['quantity'] }}</td>
                                    <td class="p-4 text-right font-medium">
                                        @if($rec['is_free'])
                                            <span class="text-amber-600 font-bold">FREE</span>
                                        @else
                                            ৳{{ number_format($rec['unit_price'], 2) }}
                                        @endif
                                    </td>
                                    <td class="p-4 text-right font-extrabold text-gray-900">
                                        @if($rec['is_free'])
                                            <span class="text-amber-600">৳0.00</span>
                                        @else
                                            ৳{{ number_format($rec['total_amount'], 2) }}
                                        @endif
                                    </td>
                                    <td class="p-4 text-center">
                                        @if($rec['payment_status'] === 'Paid via SSLCommerz')
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 flex items-center justify-center gap-1">
                                                <i class="fa-solid fa-[#003366] fa-shield-check"></i> Paid (SSLCommerz)
                                            </span>
                                        @elseif($rec['payment_status'] === 'Paid on Pickup')
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                                Paid on Pickup
                                            </span>
                                        @elseif($rec['payment_status'] === 'Free Donation')
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                                Free Donation
                                            </span>
                                        @elseif($rec['payment_status'] === 'Cancelled / No Charge')
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">
                                                No Charge
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-sky-100 text-sky-800">
                                                Pending
                                            </span>
                                        @endif
                                    </td>
                                    <td class="p-4 text-center space-y-1">
                                        <button type="button" @click="selectedReceipt = {{ json_encode($rec) }}; showReceiptModal = true" class="px-3 py-1.5 bg-gray-100 hover:bg-[#2E7D32] hover:text-white text-gray-700 text-[11px] font-bold rounded-lg transition-all">
                                            Receipt
                                        </button>
                                        @if($rec['payment_status'] !== 'Paid via SSLCommerz' && !$rec['is_free'] && $rec['reservation_status'] === 'reserved')
                                            <form action="{{ route('payment.pay_reservation', $rec['id']) }}" method="POST" class="inline-block">
                                                @csrf
                                                <button type="submit" class="px-2 py-1 bg-emerald-600 hover:bg-emerald-700 text-white text-[10px] font-bold rounded-md transition-all">
                                                    Pay Now
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Official Printable Payment Receipt Modal --}}
    <div x-show="showReceiptModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.outside="showReceiptModal = false" class="bg-white max-w-lg w-full p-6 sm:p-8 rounded-3xl shadow-2xl space-y-6 text-left relative overflow-hidden border border-gray-100">
            
            {{-- Top Modal Header --}}
            <div class="flex items-start justify-between border-b border-gray-100 pb-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="w-8 h-8 rounded-xl bg-[#2E7D32] text-white flex items-center justify-center font-bold text-xs"><i class="fa-solid fa-receipt"></i></span>
                        <h3 class="text-lg font-heading font-extrabold text-gray-900">Payment & Pickup Receipt</h3>
                    </div>
                    <p class="text-xs text-gray-500">Official LeftoverLink Surplus Food Transaction Record</p>
                </div>
                <button type="button" @click="showReceiptModal = false" class="text-gray-400 hover:text-gray-700 p-2 rounded-xl transition-colors">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            {{-- Receipt Content --}}
            <template x-if="selectedReceipt">
                <div class="space-y-5 text-xs text-gray-700">
                    <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100 space-y-2 font-mono">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Transaction Ref:</span>
                            <span class="font-bold text-[#2E7D32]" x-text="selectedReceipt.transaction_ref"></span>
                        </div>
                        <template x-if="selectedReceipt.val_id">
                            <div class="flex justify-between">
                                <span class="text-gray-500">SSL Validation ID:</span>
                                <span class="font-bold text-sky-700" x-text="selectedReceipt.val_id"></span>
                            </div>
                        </template>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Reservation ID:</span>
                            <span class="font-bold text-gray-900" x-text="selectedReceipt.reservation_code"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Payment Status:</span>
                            <span class="font-bold uppercase text-emerald-700" x-text="selectedReceipt.payment_status"></span>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div class="flex justify-between border-b border-gray-100 pb-2">
                            <span class="text-gray-500">Surplus Food Listing:</span>
                            <span class="font-bold text-gray-900" x-text="selectedReceipt.food_title"></span>
                        </div>
                        <div class="flex justify-between border-b border-gray-100 pb-2">
                            <span class="text-gray-500">Food Provider:</span>
                            <span class="font-semibold text-gray-800" x-text="selectedReceipt.provider_name"></span>
                        </div>
                        <div class="flex justify-between border-b border-gray-100 pb-2">
                            <span class="text-gray-500">Payment Gateway:</span>
                            <span class="font-bold text-emerald-800" x-text="selectedReceipt.gateway_card_type"></span>
                        </div>
                        <div class="flex justify-between border-b border-gray-100 pb-2">
                            <span class="text-gray-500">Quantity Claimed:</span>
                            <span class="font-bold text-gray-900" x-text="selectedReceipt.quantity + ' item(s)'"></span>
                        </div>
                        <div class="flex justify-between border-b border-gray-100 pb-2">
                            <span class="text-gray-500">Unit Price:</span>
                            <span class="font-semibold text-gray-800" x-text="selectedReceipt.is_free ? 'FREE DONATION' : '৳' + Number(selectedReceipt.unit_price).toFixed(2)"></span>
                        </div>
                    </div>

                    {{-- Total Summary Banner --}}
                    <div class="p-4 rounded-2xl bg-[#2E7D32]/10 border border-[#2E7D32]/20 flex items-center justify-between">
                        <span class="text-sm font-bold text-[#2E7D32]">Total Amount Paid</span>
                        <span class="text-xl font-extrabold text-[#2E7D32]" x-text="selectedReceipt.is_free ? '৳0.00 (FREE)' : '৳' + Number(selectedReceipt.total_amount).toFixed(2)"></span>
                    </div>
                </div>
            </template>

            {{-- Footer Buttons --}}
            <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-4">
                <button type="button" @click="showReceiptModal = false" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition-all">
                    Close
                </button>
                <button type="button" @click="window.print()" class="px-5 py-2.5 bg-[#2E7D32] hover:bg-[#256928] text-white text-xs font-bold rounded-xl shadow-md transition-all flex items-center gap-2">
                    <i class="fa-solid fa-print"></i> Print Receipt
                </button>
            </div>

        </div>
    </div>

</div>
@endsection
