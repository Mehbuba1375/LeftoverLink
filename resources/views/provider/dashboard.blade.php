@extends('layouts.app')

@section('content')
<div x-data="{ 
    editModal: false, 
    editItem: { id: null, food_name: '', category: '', quantity: 1, price: 0, expiration_time: '', pickup_window: '', donation_status: false }
}" class="space-y-8 pb-12">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-200/60 pb-4">
        <div>
            <h1 class="text-2xl font-heading font-extrabold text-[#222222]">Add Food — Provider Listing Management</h1>
            <p class="text-xs text-[#666666]">Manage surplus food listings, update inventory, and create new food rescue items</p>
        </div>
        
        <!-- Create Listing Button (Navigates to Food Creation Form) -->
        <a href="{{ route('provider.listings.create') }}" class="inline-flex items-center gap-2 px-5 py-3 bg-[#2E7D32] hover:bg-[#256928] text-white text-xs font-medium rounded-full shadow-md shadow-[#2E7D32]/20 hover:scale-105 transition-all shrink-0">
            <i class="fa-solid fa-circle-plus text-xs"></i> Create Listing
        </a>
    </div>

    <!-- Quick Stat Badges -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-xl shadow-xs border border-gray-100">
            <p class="text-xs text-[#666666] font-medium">Total Listings</p>
            <p class="text-2xl font-bold text-[#222222] mt-1">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-xs border border-gray-100">
            <p class="text-xs text-[#2E7D32] font-medium">Active & Available</p>
            <p class="text-2xl font-bold text-[#2E7D32] mt-1">{{ $stats['active'] }}</p>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-xs border border-gray-100">
            <p class="text-xs text-amber-600 font-medium">Out of Stock</p>
            <p class="text-2xl font-bold text-amber-600 mt-1">{{ $stats['out_of_stock'] }}</p>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-xs border border-gray-100">
            <p class="text-xs text-red-600 font-medium">Expired Listings</p>
            <p class="text-2xl font-bold text-red-600 mt-1">{{ $stats['expired'] }}</p>
        </div>
    </div>

    <!-- Listings Grid (Cards View matching Figma Layout) -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-heading font-bold text-[#222222]">Your Food Listings</h2>
            <span class="text-xs text-[#666666]">Total: {{ $listings->count() }} listings</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            
            <!-- 1. "Add New Food" Dashboard Shortcut Card -->
            <a href="{{ route('provider.listings.create') }}" 
               class="bg-white border-2 border-dashed border-[#2E7D32]/40 rounded-xl p-8 flex flex-col items-center justify-center text-center gap-4 hover:border-[#2E7D32] hover:-translate-y-1 hover:shadow-md transition-all duration-300 group min-h-[320px]">
                
                <div class="w-16 h-16 rounded-full bg-[#2E7D32]/10 text-[#2E7D32] flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-plus text-2xl"></i>
                </div>

                <div class="space-y-1">
                    <h3 class="font-heading font-bold text-lg text-[#222222]">Create Listing</h3>
                    <p class="text-xs text-[#666666] max-w-xs">Publish surplus bakery items, meals, or groceries for food rescue</p>
                </div>

                <span class="px-5 py-2.5 bg-[#2E7D32] text-white text-xs font-medium rounded-full shadow-xs group-hover:bg-[#256928] transition-colors inline-flex items-center gap-2">
                    <i class="fa-solid fa-circle-plus"></i> Open Creation Form
                </span>
            </a>

            <!-- 2. Existing Food Listing Cards -->
            @foreach($listings as $item)
                @php
                    $isExpired = $item->expiration_time <= now();
                    $isOutOfStock = $item->quantity <= 0;
                @endphp
                <div class="bg-white rounded-xl shadow-xs border border-gray-100 overflow-hidden flex flex-col hover:-translate-y-1 hover:shadow-md transition-all duration-300">
                    
                    <!-- Image Banner (Cover fit, rounded top corners) -->
                    <div class="relative h-44 bg-gray-100 shrink-0 overflow-hidden">
                        @if($item->image)
                            <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->food_name }}" class="w-full h-full object-cover rounded-t-xl">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-gray-400 bg-[#F5F5F5]"><i class="fa-solid fa-image text-3xl"></i></div>
                        @endif

                        <!-- Status Badge -->
                        <div class="absolute top-3 left-3 flex gap-1.5">
                            @if($isExpired)
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-[#EF4444] text-white">Expired</span>
                            @elseif($isOutOfStock)
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-500 text-white">Out of Stock</span>
                            @endif

                            @if($item->donation_status)
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-500 text-white">Donation</span>
                            @endif
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="p-5 flex-1 flex flex-col justify-between space-y-4">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between gap-2">
                                <h3 class="font-heading font-bold text-base text-[#222222] truncate">{{ $item->food_name }}</h3>
                                @if($item->reviews_count > 0)
                                    <span class="flex items-center gap-1 text-amber-500 font-semibold text-xs shrink-0">
                                        <i class="fa-solid fa-star"></i> {{ number_format($item->average_rating, 1) }} <span class="text-gray-400 font-normal">({{ $item->reviews_count }})</span>
                                    </span>
                                @else
                                    <span class="text-[11px] text-gray-400 font-medium italic shrink-0">No reviews yet</span>
                                @endif
                            </div>
                            <span class="text-xs font-semibold px-2 py-0.5 rounded bg-[#F5F5F5] text-[#666666]">{{ $item->category }}</span>
                        </div>

                        <!-- Info details -->
                        <div class="space-y-1 text-xs text-[#666666] border-t border-gray-100 pt-2.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-[#2E7D32]">
                                    {{ $item->donation_status ? 'FREE Donation' : '৳' . number_format($item->price, 2) }}
                                </span>
                                <span class="font-medium text-[#222222]">{{ $item->quantity }} items remaining</span>
                            </div>
                            <p class="truncate"><i class="fa-solid fa-clock mr-1 text-[#666666]"></i> {{ $item->pickup_window }}</p>
                            <p class="truncate text-[11px] text-[#666666]">Expires: {{ $item->expiration_time ? $item->expiration_time->format('M d, Y h:i A') : 'N/A' }}</p>
                        </div>

                        <!-- Card Action Buttons: View, Edit, Delete -->
                        <div class="grid grid-cols-3 gap-2 pt-2 border-t border-gray-100">
                            <!-- View Button -->
                            <a href="{{ route('marketplace.index') }}" class="py-2 text-center bg-[#F5F5F5] hover:bg-gray-200 text-[#222222] text-xs font-medium rounded-lg transition-colors">
                                <i class="fa-solid fa-eye text-xs"></i> View
                            </a>

                            <!-- Edit Button -->
                            <button @click="
                                editItem = {
                                    id: {{ $item->id }},
                                    food_name: '{{ addslashes($item->food_name) }}',
                                    category: '{{ addslashes($item->category) }}',
                                    quantity: {{ $item->quantity }},
                                    price: {{ $item->price }},
                                    expiration_time: '{{ $item->expiration_time ? $item->expiration_time->format('Y-m-d\TH:i') : '' }}',
                                    pickup_window: '{{ addslashes($item->pickup_window) }}',
                                    donation_status: {{ $item->donation_status ? 'true' : 'false' }}
                                };
                                editModal = true;
                            " class="py-2 text-center bg-[#2E7D32]/10 hover:bg-[#2E7D32] text-[#2E7D32] hover:text-white text-xs font-medium rounded-lg transition-colors">
                                <i class="fa-solid fa-pen text-xs"></i> Edit
                            </button>

                            <!-- Delete Form -->
                            <form action="{{ route('provider.listings.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this listing?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full py-2 text-center bg-red-50 hover:bg-[#EF4444] text-[#EF4444] hover:text-white text-xs font-medium rounded-lg transition-colors">
                                    <i class="fa-solid fa-trash text-xs"></i> Delete
                                </button>
                            </form>
                        </div>

                    </div>
                </div>
            @endforeach

        </div>
    </div>

    <!-- Edit Listing Modal -->
    <div x-show="editModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/40 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.outside="editModal = false" class="bg-white max-w-xl w-full p-6 sm:p-8 rounded-xl shadow-2xl space-y-6">
            
            <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                <h3 class="text-xl font-heading font-bold text-[#222222]">Edit Food Listing</h3>
                <button @click="editModal = false" class="text-[#666666] hover:text-[#222222]"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>

            <form :action="'/provider/listings/' + editItem.id" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-[#222222] mb-1">Food Name *</label>
                        <input type="text" name="food_name" x-model="editItem.food_name" required class="w-full px-4 py-2.5 bg-[#F5F5F5] border border-gray-200 rounded-xl text-sm text-[#222222]">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#222222] mb-1">Category *</label>
                        <select name="category" x-model="editItem.category" required class="w-full px-4 py-2.5 bg-[#F5F5F5] border border-gray-200 rounded-xl text-sm text-[#222222]">
                            <option value="Bakery & Pastries">Bakery & Pastries</option>
                            <option value="Prepared Meals">Prepared Meals</option>
                            <option value="Fresh Produce">Fresh Produce</option>
                            <option value="Dairy & Eggs">Dairy & Eggs</option>
                            <option value="Beverages">Beverages</option>
                            <option value="Groceries & Snacks">Groceries & Snacks</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#222222] mb-1">Listing Type *</label>
                        <select name="donation_status" x-model="editItem.donation_status" required class="w-full px-4 py-2.5 bg-[#F5F5F5] border border-gray-200 rounded-xl text-sm text-[#222222]">
                            <option :value="false">Discounted Sale</option>
                            <option :value="true">Free Community Donation</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#222222] mb-1">Price (BDT ৳)</label>
                        <input type="number" step="0.01" min="0" name="price" x-model="editItem.price" class="w-full px-4 py-2.5 bg-[#F5F5F5] border border-gray-200 rounded-xl text-sm text-[#222222]">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#222222] mb-1">Quantity Available</label>
                        <input type="number" min="0" name="quantity" x-model="editItem.quantity" required class="w-full px-4 py-2.5 bg-[#F5F5F5] border border-gray-200 rounded-xl text-sm text-[#222222]">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#222222] mb-1">Pickup Window *</label>
                        <input type="text" name="pickup_window" x-model="editItem.pickup_window" required class="w-full px-4 py-2.5 bg-[#F5F5F5] border border-gray-200 rounded-xl text-sm text-[#222222]">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#222222] mb-1">Expiration Datetime *</label>
                        <input type="datetime-local" name="expiration_time" x-model="editItem.expiration_time" required class="w-full px-4 py-2.5 bg-[#F5F5F5] border border-gray-200 rounded-xl text-sm text-[#222222]">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-[#222222] mb-1">Replace Image (Optional)</label>
                        <input type="file" name="image" accept="image/*" class="block w-full text-xs text-[#666666] file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-[#2E7D32]/10 file:text-[#2E7D32]">
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                    <button type="button" @click="editModal = false" class="px-5 py-2.5 bg-[#F5F5F5] text-[#222222] text-xs font-medium rounded-full">Cancel</button>
                    <button type="submit" class="px-6 py-2.5 bg-[#2E7D32] text-white text-xs font-medium rounded-full shadow-md">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
