@extends('layouts.app')

@section('content')
<div class="space-y-8 pb-12">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-200/60 pb-4">
        <div>
            <h1 class="text-2xl font-heading font-extrabold text-[#222222]">NGO Food Collection Requests</h1>
            <p class="text-xs text-[#666666]">Track all food donation requests submitted by your organization</p>
        </div>
        <a href="{{ route('donations.index') }}" class="inline-flex items-center gap-2 px-5 py-3 bg-[#2E7D32] hover:bg-[#256928] text-white text-xs font-medium rounded-full shadow-md shadow-[#2E7D32]/20 hover:scale-105 transition-all shrink-0">
            <i class="fa-solid fa-hand-holding-heart text-xs"></i> Browse Donated Food
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
                <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                <p class="text-xs text-amber-600 font-medium">Pending</p>
            </div>
            <p class="text-2xl font-bold text-amber-600 mt-1">{{ $stats['pending'] }}</p>
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
            <button @click="activeTab = 'pending'" :class="activeTab === 'pending' ? 'bg-amber-500 text-white shadow-md shadow-amber-500/20' : 'bg-white text-[#222222] border border-gray-200 hover:bg-amber-50'" class="px-4 py-2 rounded-full text-xs font-medium transition-all">
                <i class="fa-solid fa-clock mr-1"></i> Pending <span class="ml-1 opacity-70">({{ $stats['pending'] }})</span>
            </button>
            <button @click="activeTab = 'approved'" :class="activeTab === 'approved' ? 'bg-[#2E7D32] text-white shadow-md shadow-[#2E7D32]/20' : 'bg-white text-[#222222] border border-gray-200 hover:bg-green-50'" class="px-4 py-2 rounded-full text-xs font-medium transition-all">
                <i class="fa-solid fa-circle-check mr-1"></i> Approved <span class="ml-1 opacity-70">({{ $stats['approved'] }})</span>
            </button>
            <button @click="activeTab = 'rejected'" :class="activeTab === 'rejected' ? 'bg-red-500 text-white shadow-md shadow-red-500/20' : 'bg-white text-[#222222] border border-gray-200 hover:bg-red-50'" class="px-4 py-2 rounded-full text-xs font-medium transition-all">
                <i class="fa-solid fa-circle-xmark mr-1"></i> Rejected <span class="ml-1 opacity-70">({{ $stats['rejected'] }})</span>
            </button>
        </div>

        {{-- Request Cards --}}
        @if($requests->isEmpty())
            <div class="bg-white rounded-xl border border-gray-100 p-12 text-center space-y-4">
                <div class="w-16 h-16 rounded-full bg-[#FFF9E8] text-[#2E7D32] flex items-center justify-center mx-auto text-2xl">
                    <i class="fa-solid fa-hand-holding-heart"></i>
                </div>
                <div class="space-y-1">
                    <h3 class="text-lg font-bold text-[#222222]">No Food Collection Requests Yet</h3>
                    <p class="text-xs text-[#666666] max-w-sm mx-auto">Browse available community donations and submit collection requests to food providers!</p>
                </div>
                <a href="{{ route('donations.index') }}" class="inline-block px-5 py-2 bg-[#2E7D32] text-white text-xs font-medium rounded-full shadow-md">
                    Browse Donated Food
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($requests as $req)
                    <div x-show="activeTab === 'all' || activeTab === '{{ $req->status }}'"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         class="bg-white rounded-xl shadow-xs border border-gray-100 overflow-hidden flex flex-col hover:-translate-y-1 hover:shadow-md transition-all duration-300">

                        {{-- Food Image Banner --}}
                        <div class="relative h-40 bg-gray-100 shrink-0 overflow-hidden">
                            @if($req->food && $req->food->image)
                                <img src="{{ asset('storage/' . $req->food->image) }}" alt="{{ $req->food->food_name }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-400 bg-[#F5F5F5]">
                                    <i class="fa-solid fa-image text-3xl"></i>
                                </div>
                            @endif

                            {{-- Status Badge --}}
                            <div class="absolute top-3 left-3">
                                @if($req->isPending())
                                    <span class="px-3 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-amber-500 text-white shadow-sm flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span> Pending Review
                                    </span>
                                @elseif($req->isApproved())
                                    <span class="px-3 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-[#2E7D32] text-white shadow-sm">
                                        <i class="fa-solid fa-circle-check mr-1"></i> Approved
                                    </span>
                                @elseif($req->isRejected())
                                    <span class="px-3 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-red-500 text-white shadow-sm">
                                        <i class="fa-solid fa-circle-xmark mr-1"></i> Rejected
                                    </span>
                                @endif
                            </div>

                            {{-- Quantity Badge --}}
                            <div class="absolute top-3 right-3">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-white/90 backdrop-blur-sm text-[#222222] shadow-sm">
                                    {{ $req->quantity }} {{ $req->quantity > 1 ? 'items' : 'item' }}
                                </span>
                            </div>
                        </div>

                        {{-- Card Body --}}
                        <div class="p-5 flex-1 flex flex-col justify-between space-y-4">
                            <div class="space-y-1.5">
                                <h3 class="font-heading font-bold text-base text-[#222222] line-clamp-1">
                                    {{ $req->food ? $req->food->food_name : 'Food item unavailable' }}
                                </h3>
                                <div class="flex items-center gap-1.5 text-xs text-[#666666]">
                                    <i class="fa-solid fa-store text-[#2E7D32]"></i>
                                    <span class="font-medium text-[#222222]">
                                        {{ $req->food && $req->food->user ? $req->food->user->name : 'Donor Provider' }}
                                    </span>
                                </div>
                            </div>

                            {{-- Details --}}
                            <div class="space-y-2 text-xs text-[#666666] border-t border-gray-100 pt-3">
                                {{-- Free Donation Badge --}}
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-[#F59E0B] uppercase tracking-wider">
                                        <i class="fa-solid fa-heart mr-1"></i> Free Donation
                                    </span>
                                    @if($req->food)
                                        <span class="text-[11px] font-medium px-2 py-0.5 rounded-md bg-[#F5F5F5] text-[#222222]">
                                            {{ $req->food->category }}
                                        </span>
                                    @endif
                                </div>

                                {{-- Requested Date --}}
                                <div class="flex items-center gap-1.5 text-[11px]">
                                    <i class="fa-solid fa-calendar-plus text-amber-500"></i>
                                    <span>Requested: <strong class="text-[#222222] font-medium">{{ $req->created_at->format('M d, Y h:i A') }}</strong></span>
                                </div>

                                {{-- Pickup Window --}}
                                @if($req->food)
                                    <div class="flex items-center gap-1.5 text-[11px]">
                                        <i class="fa-solid fa-clock text-[#666666]"></i>
                                        <span>Pickup: <strong class="text-[#222222] font-medium">{{ $req->food->pickup_window }}</strong></span>
                                    </div>
                                @endif

                                {{-- Processed Timestamps --}}
                                @if($req->isApproved() && $req->approved_at)
                                    <div class="flex items-center gap-1.5 text-[11px]">
                                        <i class="fa-solid fa-circle-check text-[#2E7D32]"></i>
                                        <span>Approved: <strong class="text-[#222222] font-medium">{{ $req->approved_at->format('M d, Y h:i A') }}</strong></span>
                                    </div>
                                @endif
                                @if($req->isRejected() && $req->rejected_at)
                                    <div class="flex items-center gap-1.5 text-[11px]">
                                        <i class="fa-solid fa-circle-xmark text-red-500"></i>
                                        <span>Rejected: <strong class="text-[#222222] font-medium">{{ $req->rejected_at->format('M d, Y h:i A') }}</strong></span>
                                    </div>
                                @endif

                                {{-- Notes --}}
                                @if($req->notes)
                                    <div class="flex items-start gap-1.5 text-[11px]">
                                        <i class="fa-solid fa-sticky-note text-amber-500 mt-0.5"></i>
                                        <span class="line-clamp-2">{{ $req->notes }}</span>
                                    </div>
                                @endif
                            </div>

                            {{-- Status Banner --}}
                            <div class="pt-1">
                                @if($req->isPending())
                                    <div class="w-full py-2 bg-amber-50 text-amber-600 text-xs font-medium rounded-full text-center border border-amber-200">
                                        <i class="fa-solid fa-clock mr-1"></i> Awaiting Provider Approval
                                    </div>
                                @elseif($req->isApproved())
                                    <div class="w-full py-2 bg-[#2E7D32]/10 text-[#2E7D32] text-xs font-medium rounded-full text-center">
                                        <i class="fa-solid fa-circle-check mr-1"></i> Request Approved for Pickup
                                    </div>
                                @elseif($req->isRejected())
                                    <div class="w-full py-2 bg-red-50 text-red-500 text-xs font-medium rounded-full text-center">
                                        <i class="fa-solid fa-ban mr-1"></i> Request Declined
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

    </div>

</div>
@endsection
