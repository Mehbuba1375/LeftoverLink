@extends('layouts.app')

@section('content')
<div class="space-y-8 pb-12">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-200/60 pb-4">
        <div>
            <h1 class="text-2xl font-heading font-extrabold text-[#222222]">Incoming NGO Requests</h1>
            <p class="text-xs text-[#666666]">Approve or reject donation collection requests from registered charitable organizations</p>
        </div>
        <a href="{{ route('provider.dashboard') }}" class="inline-flex items-center gap-2 px-5 py-3 bg-[#2E7D32] hover:bg-[#256928] text-white text-xs font-medium rounded-full shadow-md shadow-[#2E7D32]/20 hover:scale-105 transition-all shrink-0">
            <i class="fa-solid fa-gauge text-xs"></i> Provider Dashboard
        </a>
    </div>

    {{-- Quick Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-xl shadow-xs border border-gray-100">
            <p class="text-xs text-[#666666] font-medium">Total Requests Received</p>
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

        {{-- Request Cards List --}}
        @if($requests->isEmpty())
            <div class="bg-white rounded-xl border border-gray-100 p-12 text-center space-y-4">
                <div class="w-16 h-16 rounded-full bg-[#FFF9E8] text-[#2E7D32] flex items-center justify-center mx-auto text-2xl">
                    <i class="fa-solid fa-envelope-open-text"></i>
                </div>
                <h3 class="text-lg font-bold text-[#222222]">No Requests Received</h3>
                <p class="text-xs text-[#666666] max-w-sm mx-auto">When registered NGOs request your surplus food donations, they will appear here for review.</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach($requests as $requestItem)
                    <div x-data="{ showRejectInput: false }" 
                         x-show="activeTab === 'all' || activeTab === '{{ $requestItem->status }}'"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         class="bg-white rounded-xl shadow-xs border border-gray-100 p-5 flex flex-col justify-between space-y-4 hover:shadow-md transition-shadow">

                        <div class="space-y-3.5">
                            {{-- Header: NGO & Food Info --}}
                            <div class="flex items-start justify-between gap-4">
                                <div class="space-y-1">
                                    <span class="text-[10px] uppercase font-bold tracking-wider text-[#F59E0B]">
                                        NGO Request
                                    </span>
                                    <h3 class="font-heading font-extrabold text-base text-[#222222]">
                                        {{ $requestItem->ngo ? $requestItem->ngo->name : 'Registered NGO' }}
                                    </h3>
                                    <p class="text-xs text-[#666666] font-medium flex items-center gap-1">
                                        <i class="fa-solid fa-circle-info text-blue-500"></i>
                                        Requested: <strong class="text-[#2E7D32]">{{ $requestItem->quantity_requested }}x</strong> {{ $requestItem->food ? $requestItem->food->food_name : 'Food item' }}
                                    </p>
                                </div>

                                {{-- Status Badge --}}
                                <div>
                                    @if($requestItem->isPending())
                                        <span class="px-2.5 py-1 rounded-full text-[9px] font-bold uppercase tracking-wider bg-blue-100 text-blue-700">Pending</span>
                                    @elseif($requestItem->isApproved())
                                        <span class="px-2.5 py-1 rounded-full text-[9px] font-bold uppercase tracking-wider bg-green-100 text-green-700">Approved</span>
                                    @elseif($requestItem->isRejected())
                                        <span class="px-2.5 py-1 rounded-full text-[9px] font-bold uppercase tracking-wider bg-red-100 text-red-700">Rejected</span>
                                    @endif
                                </div>
                            </div>

                            {{-- Details Grid --}}
                            <div class="grid grid-cols-2 gap-3 text-xs text-[#666666] bg-[#FFF9E8]/30 border border-[#EFE5CD]/30 p-3 rounded-lg">
                                <div>
                                    <p class="font-semibold text-[#222222]">Contact Name</p>
                                    <p class="mt-0.5">{{ $requestItem->contact_name ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="font-semibold text-[#222222]">Contact No</p>
                                    <p class="mt-0.5">{{ $requestItem->contact_no ?? 'N/A' }}</p>
                                </div>
                                <div class="col-span-2">
                                    <p class="font-semibold text-[#222222]">Proposed Pickup Time</p>
                                    <p class="mt-0.5 text-blue-600 font-semibold">{{ $requestItem->pickup_time ?? 'Flexible' }}</p>
                                </div>
                                <div class="col-span-2">
                                    <p class="font-semibold text-[#222222]">Collection Location</p>
                                    <p class="mt-0.5 flex items-center gap-1">
                                        <i class="fa-solid fa-location-dot text-red-500"></i>
                                        <span>{{ $requestItem->address ?? 'N/A' }}</span>
                                    </p>
                                </div>
                            </div>

                            {{-- NGO Message --}}
                            @if($requestItem->message)
                                <div class="bg-[#F5F5F5] p-3 rounded-lg text-xs text-[#555555] italic border-l-2 border-[#2E7D32]">
                                    "{{ $requestItem->message }}"
                                </div>
                            @endif

                            {{-- Rejection Note if Rejected --}}
                            @if($requestItem->isRejected() && $requestItem->admin_notes)
                                <div class="bg-red-50 p-3 rounded-lg text-xs text-red-700 border-l-2 border-red-500">
                                    <strong>Rejection Reason:</strong> "{{ $requestItem->admin_notes }}"
                                </div>
                            @endif
                        </div>

                        {{-- Action Buttons --}}
                        @if($requestItem->isPending())
                            <div class="border-t border-gray-100 pt-4 flex flex-col gap-2.5">
                                <div x-show="!showRejectInput" class="flex gap-2.5">
                                    {{-- Reject Form toggler --}}
                                    <button @click="showRejectInput = true" class="flex-1 py-2 border border-red-200 hover:bg-red-50 text-red-600 text-xs font-semibold rounded-full transition-all">
                                        <i class="fa-solid fa-xmark mr-1"></i> Reject
                                    </button>

                                    {{-- Approve Form --}}
                                    <form action="{{ route('provider.ngo-requests.approve', $requestItem->id) }}" method="POST" class="flex-1">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="w-full py-2 bg-[#2E7D32] hover:bg-[#256928] text-white text-xs font-semibold rounded-full shadow-md shadow-[#2E7D32]/10 transition-all">
                                            <i class="fa-solid fa-check mr-1"></i> Approve
                                        </button>
                                    </form>
                                </div>

                                {{-- Rejection Textarea Form --}}
                                <div x-show="showRejectInput" x-cloak class="space-y-2.5">
                                    <form action="{{ route('provider.ngo-requests.reject', $requestItem->id) }}" method="POST" class="space-y-2">
                                        @csrf
                                        @method('PATCH')
                                        <label for="reason-{{ $requestItem->id }}" class="block text-[11px] font-semibold text-[#222222]">Reason for Rejection (Optional)</label>
                                        <textarea name="reason" id="reason-{{ $requestItem->id }}" rows="2" placeholder="e.g. Quantity requested exceeds available stock" 
                                                  class="w-full px-3 py-2 bg-[#F5F5F5] border border-gray-200 focus:border-red-500 focus:ring-1 focus:ring-red-500 rounded-lg text-xs text-[#222222] outline-hidden transition-all resize-none"></textarea>
                                        <div class="flex gap-2">
                                            <button type="button" @click="showRejectInput = false" class="flex-1 py-1.5 bg-gray-50 hover:bg-gray-100 text-[#222222] text-xs font-medium rounded-full">Cancel</button>
                                            <button type="submit" class="flex-1 py-1.5 bg-red-500 hover:bg-red-600 text-white text-xs font-semibold rounded-full shadow-md shadow-red-500/10">Confirm Reject</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @elseif($requestItem->isApproved())
                            <div class="border-t border-gray-100 pt-3 flex items-center justify-between text-xs text-gray-500">
                                <span>Approved on: <strong>{{ $requestItem->responded_at->format('M d, Y h:i A') }}</strong></span>
                                <span class="text-[#2E7D32] font-semibold"><i class="fa-solid fa-circle-check"></i> Processed</span>
                            </div>
                        @elseif($requestItem->isRejected())
                            <div class="border-t border-gray-100 pt-3 flex items-center justify-between text-xs text-gray-500">
                                <span>Rejected on: <strong>{{ $requestItem->responded_at->format('M d, Y h:i A') }}</strong></span>
                                <span class="text-red-500 font-semibold"><i class="fa-solid fa-circle-xmark"></i> Rejected</span>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Empty States for Filtered Tabs --}}
        @if($requests->isNotEmpty())
            <div x-show="activeTab === 'pending' && {{ $stats['pending'] }} === 0" x-cloak class="bg-white rounded-xl border border-gray-100 p-8 text-center space-y-2">
                <i class="fa-solid fa-clock text-2xl text-blue-300"></i>
                <p class="text-sm font-medium text-[#222222]">No pending requests</p>
            </div>
            <div x-show="activeTab === 'approved' && {{ $stats['approved'] }} === 0" x-cloak class="bg-white rounded-xl border border-gray-100 p-8 text-center space-y-2">
                <i class="fa-solid fa-circle-check text-2xl text-green-300"></i>
                <p class="text-sm font-medium text-[#222222]">No approved requests</p>
            </div>
            <div x-show="activeTab === 'rejected' && {{ $stats['rejected'] }} === 0" x-cloak class="bg-white rounded-xl border border-gray-100 p-8 text-center space-y-2">
                <i class="fa-solid fa-circle-xmark text-2xl text-red-300"></i>
                <p class="text-sm font-medium text-[#222222]">No rejected requests</p>
            </div>
        @endif
    </div>

</div>
@endsection
