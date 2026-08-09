@extends('layouts.app')

@section('content')
<div class="space-y-8 pb-12">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-200/60 pb-4">
        <div>
            <h1 class="text-2xl font-heading font-extrabold text-[#222222]">My Food Requests</h1>
            <p class="text-xs text-[#666666]">Track and manage your surplus food collection requests</p>
        </div>
        <a href="{{ route('donations.index') }}" class="inline-flex items-center gap-2 px-5 py-3 bg-[#F59E0B] hover:bg-[#D97706] text-white text-xs font-medium rounded-full shadow-md shadow-[#F59E0B]/20 hover:scale-105 transition-all shrink-0">
            <i class="fa-solid fa-hand-holding-heart text-xs"></i> Browse Donations
        </a>
    </div>

    {{-- Quick Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-xl shadow-xs border border-gray-100">
            <p class="text-xs text-[#666666] font-medium">Total Requests</p>
            <p class="text-2xl font-bold text-[#222222] mt-1">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-xs border border-gray-100">
            <div class="flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
                <p class="text-xs text-blue-600 font-medium">Pending Review</p>
            </div>
            <p class="text-2xl font-bold text-blue-600 mt-1">{{ $stats['pending'] }}</p>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-xs border border-gray-100">
            <div class="flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-[#2E7D32]"></span>
                <p class="text-xs text-[#2E7D32] font-medium">Approved</p>
            </div>
            <p class="text-2xl font-bold text-[#2E7D32] mt-1">{{ $stats['approved'] }}</p>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-xs border border-gray-100">
            <div class="flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-red-500"></span>
                <p class="text-xs text-red-600 font-medium">Rejected</p>
            </div>
            <p class="text-2xl font-bold text-red-600 mt-1">{{ $stats['rejected'] }}</p>
        </div>
    </div>

    {{-- Status Filter Tabs --}}
    <div x-data="{ activeTab: 'all' }" class="space-y-6">
        <div class="flex items-center gap-2 flex-wrap">
            <button @click="activeTab = 'all'" :class="activeTab === 'all' ? 'bg-[#2E7D32] text-white shadow-md shadow-[#2E7D32]/20' : 'bg-white text-[#222222] border border-gray-200 hover:bg-[#F5EED8]'" class="px-4 py-2 rounded-full text-xs font-medium transition-all">
                All <span class="ml-1 opacity-70">({{ $stats['total'] }})</span>
            </button>
            <button @click="activeTab = 'pending'" :class="activeTab === 'pending' ? 'bg-blue-600 text-white shadow-md shadow-blue-600/20' : 'bg-white text-[#222222] border border-gray-200 hover:bg-blue-50'" class="px-4 py-2 rounded-full text-xs font-medium transition-all">
                <i class="fa-solid fa-clock mr-1"></i> Pending <span class="ml-1 opacity-70">({{ $stats['pending'] }})</span>
            </button>
            <button @click="activeTab = 'approved'" :class="activeTab === 'approved' ? 'bg-[#2E7D32] text-white shadow-md shadow-[#2E7D32]/20' : 'bg-white text-[#222222] border border-gray-200 hover:bg-green-50'" class="px-4 py-2 rounded-full text-xs font-medium transition-all">
                <i class="fa-solid fa-circle-check mr-1"></i> Approved <span class="ml-1 opacity-70">({{ $stats['approved'] }})</span>
            </button>
            <button @click="activeTab = 'rejected'" :class="activeTab === 'rejected' ? 'bg-red-500 text-white shadow-md shadow-red-500/20' : 'bg-white text-[#222222] border border-gray-200 hover:bg-red-50'" class="px-4 py-2 rounded-full text-xs font-medium transition-all">
                <i class="fa-solid fa-circle-xmark mr-1"></i> Rejected <span class="ml-1 opacity-70">({{ $stats['rejected'] }})</span>
            </button>
        </div>

        {{-- Request Cards Grid --}}
        @if($requests->isEmpty())
            <div class="bg-white rounded-xl border border-gray-100 p-12 text-center space-y-4">
                <div class="w-16 h-16 rounded-full bg-[#FFF9E8] text-[#F59E0B] flex items-center justify-center mx-auto text-2xl">
                    <i class="fa-solid fa-hand-holding-heart"></i>
                </div>
                <div class="space-y-1">
                    <h3 class="text-lg font-bold text-[#222222]">No Requests Yet</h3>
                    <p class="text-xs text-[#666666] max-w-sm mx-auto">Browse the donations marketplace and request surplus food items for your organization!</p>
                </div>
                <a href="{{ route('donations.index') }}" class="inline-block px-5 py-2 bg-[#F59E0B] hover:bg-[#D97706] text-white text-xs font-medium rounded-full shadow-md">
                    Browse Donations
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($requests as $requestItem)
                    <div x-show="activeTab === 'all' || activeTab === '{{ $requestItem->status }}'"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         class="bg-white rounded-xl shadow-xs border border-gray-100 overflow-hidden flex flex-col hover:-translate-y-1 hover:shadow-md transition-all duration-300">

                        {{-- Food Image Banner --}}
                        <div class="relative h-40 bg-gray-100 shrink-0 overflow-hidden">
                            @if($requestItem->food && $requestItem->food->image)
                                <img src="{{ asset('storage/' . $requestItem->food->image) }}" alt="{{ $requestItem->food->food_name }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-400 bg-[#F5F5F5]">
                                    <i class="fa-solid fa-image text-3xl"></i>
                                </div>
                            @endif

                            {{-- Status Badge --}}
                            <div class="absolute top-3 left-3">
                                @if($requestItem->isPending())
                                    <span class="px-3 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-blue-500 text-white shadow-sm flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span> Pending Review
                                    </span>
                                @elseif($requestItem->isApproved())
                                    <span class="px-3 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-[#2E7D32] text-white shadow-sm">
                                        <i class="fa-solid fa-circle-check mr-1"></i> Approved
                                    </span>
                                @elseif($requestItem->isRejected())
                                    <span class="px-3 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-red-500 text-white shadow-sm">
                                        <i class="fa-solid fa-circle-xmark mr-1"></i> Rejected
                                    </span>
                                @endif
                            </div>

                            {{-- Quantity Requested --}}
                            <div class="absolute top-3 right-3">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-white/90 backdrop-blur-sm text-[#222222] shadow-sm">
                                    Requested: {{ $requestItem->quantity_requested }}
                                </span>
                            </div>
                        </div>

                        {{-- Card Body --}}
                        <div class="p-5 flex-1 flex flex-col justify-between space-y-4">
                            <div class="space-y-1.5">
                                <h3 class="font-heading font-bold text-base text-[#222222] line-clamp-1">
                                    {{ $requestItem->food ? $requestItem->food->food_name : 'Food item unavailable' }}
                                </h3>
                                <div class="flex items-center gap-1.5 text-xs text-[#666666]">
                                    <i class="fa-solid fa-store text-[#2E7D32]"></i>
                                    <span class="font-medium text-[#222222]">
                                        {{ $requestItem->food && $requestItem->food->user ? $requestItem->food->user->name : 'Provider' }}
                                    </span>
                                </div>
                            </div>

                            {{-- Details --}}
                            <div class="space-y-2 text-xs text-[#666666] border-t border-gray-100 pt-3">
                                {{-- Requested Date --}}
                                <div class="flex items-center gap-1.5 text-[11px]">
                                    <i class="fa-solid fa-calendar-plus text-blue-500"></i>
                                    <span>Requested: <strong class="text-[#222222] font-medium">{{ $requestItem->requested_at->format('M d, Y h:i A') }}</strong></span>
                                </div>

                                {{-- Pickup Time --}}
                                <div class="flex items-center gap-1.5 text-[11px]">
                                    <i class="fa-solid fa-clock text-[#F59E0B]"></i>
                                    <span>Proposed Pickup: <strong class="text-[#222222] font-medium">{{ $requestItem->pickup_time }}</strong></span>
                                </div>

                                {{-- Delivery / Pickup Address --}}
                                <div class="flex items-center gap-1.5 text-[11px] truncate">
                                    <i class="fa-solid fa-location-dot text-red-500"></i>
                                    <span class="truncate">Address: <strong class="text-[#222222] font-medium">{{ $requestItem->address }}</strong></span>
                                </div>

                                {{-- Message --}}
                                @if($requestItem->message)
                                    <div class="bg-[#F5F5F5] p-2.5 rounded-lg text-[11px] text-[#555555] italic border-l-2 border-[#F59E0B] mt-1 line-clamp-2">
                                        "{{ $requestItem->message }}"
                                    </div>
                                @endif

                                {{-- Rejection Reason / Notes --}}
                                @if($requestItem->isRejected() && $requestItem->admin_notes)
                                    <div class="bg-red-50 p-2.5 rounded-lg text-[11px] text-[#EF4444] font-medium border-l-2 border-red-500 mt-1">
                                        <strong>Reason:</strong> "{{ $requestItem->admin_notes }}"
                                    </div>
                                @endif
                            </div>

                            {{-- Action Button --}}
                            @if($requestItem->isPending())
                                <div class="pt-1">
                                    <form action="{{ route('ngo.requests.cancel', $requestItem->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel this request?')">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="block w-full py-2.5 bg-red-50 hover:bg-[#EF4444] text-[#EF4444] hover:text-white text-xs font-medium rounded-full text-center shadow-xs hover:shadow-md transition-all">
                                            <i class="fa-solid fa-xmark mr-1"></i> Cancel Request
                                        </button>
                                    </form>
                                </div>
                            @elseif($requestItem->isApproved())
                                <div class="pt-1">
                                    <div class="w-full py-2.5 bg-[#2E7D32]/10 text-[#2E7D32] text-xs font-semibold rounded-full text-center">
                                        <i class="fa-solid fa-circle-check mr-1"></i> Request Approved
                                    </div>
                                </div>
                            @elseif($requestItem->isRejected())
                                <div class="pt-1">
                                    <div class="w-full py-2.5 bg-red-50 text-red-400 text-xs font-semibold rounded-full text-center">
                                        <i class="fa-solid fa-ban mr-1"></i> Request Rejected
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Empty State for Filtered Tabs --}}
        @if($requests->isNotEmpty())
            <div x-show="activeTab === 'pending' && {{ $stats['pending'] }} === 0" x-cloak class="bg-white rounded-xl border border-gray-100 p-8 text-center space-y-2">
                <i class="fa-solid fa-clock text-2xl text-blue-300"></i>
                <p class="text-sm font-medium text-[#222222]">No pending requests</p>
            </div>
            <div x-show="activeTab === 'approved' && {{ $stats['approved'] }} === 0" x-cloak class="bg-white rounded-xl border border-gray-100 p-8 text-center space-y-2">
                <i class="fa-solid fa-circle-check text-2xl text-green-300"></i>
                <p class="text-sm font-medium text-[#222222]">No approved requests yet</p>
            </div>
            <div x-show="activeTab === 'rejected' && {{ $stats['rejected'] }} === 0" x-cloak class="bg-white rounded-xl border border-gray-100 p-8 text-center space-y-2">
                <i class="fa-solid fa-circle-xmark text-2xl text-red-300"></i>
                <p class="text-sm font-medium text-[#222222]">No rejected requests</p>
            </div>
        @endif
    </div>

</div>
@endsection
