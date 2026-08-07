@extends('layouts.app')

@section('content')
<div class="space-y-6 pb-12">

    <div class="flex items-center justify-between border-b border-gray-200/60 pb-4">
        <div>
            <h1 class="text-3xl font-heading font-extrabold text-[#222222]">Administrator Monitoring Portal</h1>
            <p class="text-xs text-[#666666]">Monitor overall platform operations, user account roles, and surplus listings</p>
        </div>
        <span class="px-3.5 py-1.5 rounded-full text-xs font-bold bg-[#2E7D32]/10 text-[#2E7D32] uppercase">
            Admin System
        </span>
    </div>

    <!-- Metric Stat Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-xl shadow-xs border border-gray-100 space-y-1">
            <span class="text-xs font-semibold text-[#666666] uppercase">Total Users</span>
            <p class="text-3xl font-heading font-bold text-[#222222]">{{ number_format($stats['total_users']) }}</p>
            <p class="text-[11px] text-[#2E7D32] font-medium">{{ $stats['consumers'] }} consumers • {{ $stats['providers'] }} providers</p>
        </div>

        <div class="bg-white p-5 rounded-xl shadow-xs border border-gray-100 space-y-1">
            <span class="text-xs font-semibold text-[#666666] uppercase">Total Food Listings</span>
            <p class="text-3xl font-heading font-bold text-[#222222]">{{ number_format($stats['total_listings']) }}</p>
            <p class="text-[11px] text-[#22C55E] font-medium">{{ $stats['active_listings'] }} active listings</p>
        </div>

        <div class="bg-white p-5 rounded-xl shadow-xs border border-gray-100 space-y-1">
            <span class="text-xs font-semibold text-[#666666] uppercase">Free Donations</span>
            <p class="text-3xl font-heading font-bold text-[#222222]">{{ number_format($stats['donations']) }}</p>
            <p class="text-[11px] text-[#666666]">Community items</p>
        </div>

        <div class="bg-white p-5 rounded-xl shadow-xs border border-gray-100 space-y-1">
            <span class="text-xs font-semibold text-[#666666] uppercase">Discounted Items</span>
            <p class="text-3xl font-heading font-bold text-[#222222]">{{ number_format($stats['discounted']) }}</p>
            <p class="text-[11px] text-[#666666]">Marketplace sales</p>
        </div>
    </div>

    <!-- Recent Tables Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Recent Users -->
        <div class="bg-white rounded-xl shadow-xs border border-gray-100 p-6 space-y-4">
            <h3 class="font-heading font-bold text-base text-[#222222]">Recent Registrations</h3>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-[#F5F5F5] text-[#666666] uppercase font-semibold">
                        <tr>
                            <th class="p-3 rounded-l-lg">User</th>
                            <th class="p-3">Role</th>
                            <th class="p-3 rounded-r-lg">Joined</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($recent_users as $u)
                            <tr>
                                <td class="p-3 font-medium text-[#222222]">
                                    <p class="font-bold">{{ $u->name }}</p>
                                    <p class="text-[10px] text-[#666666]">{{ $u->email }}</p>
                                </td>
                                <td class="p-3">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-[#2E7D32]/10 text-[#2E7D32]">
                                        {{ ucfirst(str_replace('_', ' ', $u->role)) }}
                                    </span>
                                </td>
                                <td class="p-3 text-[#666666]">{{ $u->created_at->diffForHumans() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Food Listings -->
        <div class="bg-white rounded-xl shadow-xs border border-gray-100 p-6 space-y-4">
            <h3 class="font-heading font-bold text-base text-[#222222]">Recent Surplus Listings</h3>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-[#F5F5F5] text-[#666666] uppercase font-semibold">
                        <tr>
                            <th class="p-3 rounded-l-lg">Item</th>
                            <th class="p-3">Provider</th>
                            <th class="p-3">Type</th>
                            <th class="p-3 rounded-r-lg">Stock</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($recent_listings as $item)
                            <tr>
                                <td class="p-3 font-bold text-[#222222]">{{ $item->food_name }}</td>
                                <td class="p-3 text-[#666666]">{{ $item->user ? $item->user->name : 'N/A' }}</td>
                                <td class="p-3">
                                    @if($item->donation_status)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-600">Donation</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#2E7D32]/10 text-[#2E7D32]">৳{{ number_format($item->price, 2) }}</span>
                                    @endif
                                </td>
                                <td class="p-3 font-bold text-[#222222]">{{ $item->quantity }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>
@endsection
