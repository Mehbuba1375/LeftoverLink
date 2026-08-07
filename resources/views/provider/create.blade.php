@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto py-6 space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-heading font-bold text-[#222222]">Add New Food Listing</h1>
            <p class="text-xs text-[#666666]">Publish surplus food to reduce waste and help your local community</p>
        </div>
        <a href="{{ route('provider.dashboard') }}" class="px-4 py-2 bg-[#F5F5F5] hover:bg-gray-200 text-[#222222] text-xs font-medium rounded-full transition-colors">
            <i class="fa-solid fa-arrow-left mr-1.5"></i> Back to Dashboard
        </a>
    </div>

    <!-- Centered Form Card -->
    <div class="bg-white rounded-xl shadow-xs border border-gray-100 p-6 sm:p-8 space-y-6">
        
        <form action="{{ route('provider.listings.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6" x-data="foodImageUpload()">
            @csrf

            <!-- Form Validation Summary -->
            @if ($errors->any())
                <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs space-y-1">
                    <p class="font-bold flex items-center gap-1.5"><i class="fa-solid fa-circle-exclamation text-sm"></i> Please fix the following errors before saving:</p>
                    <ul class="list-disc list-inside space-y-0.5 text-[11px]">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <!-- Food Name -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-[#222222] mb-1.5">Food Name <span class="text-red-500">*</span></label>
                    <input type="text" name="food_name" value="{{ old('food_name') }}" required 
                           placeholder="e.g. Fresh Artisan Sourdough Bread" 
                           class="w-full px-4 py-3 bg-[#F5F5F5] border border-gray-200 rounded-xl text-sm text-[#222222] focus:outline-none focus:border-[#2E7D32] focus:bg-white transition-colors">
                    @error('food_name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Description -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-[#222222] mb-1.5">Food Description / Notes</label>
                    <textarea name="description" rows="3" 
                              placeholder="Provide details about food ingredients, packaging, dietary information, or pickup notes..." 
                              class="w-full px-4 py-3 bg-[#F5F5F5] border border-gray-200 rounded-xl text-sm text-[#222222] focus:outline-none focus:border-[#2E7D32] focus:bg-white transition-colors">{{ old('description') }}</textarea>
                    @error('description') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Category -->
                <div>
                    <label class="block text-xs font-semibold text-[#222222] mb-1.5">Category <span class="text-red-500">*</span></label>
                    <select name="category" required class="w-full px-4 py-3 bg-[#F5F5F5] border border-gray-200 rounded-xl text-sm text-[#222222] focus:outline-none focus:border-[#2E7D32] focus:bg-white transition-colors">
                        <option value="Bakery & Pastries" {{ old('category') == 'Bakery & Pastries' ? 'selected' : '' }}>Bakery & Pastries</option>
                        <option value="Prepared Meals" {{ old('category') == 'Prepared Meals' ? 'selected' : '' }}>Prepared Meals</option>
                        <option value="Fresh Produce" {{ old('category') == 'Fresh Produce' ? 'selected' : '' }}>Fresh Produce</option>
                        <option value="Dairy & Eggs" {{ old('category') == 'Dairy & Eggs' ? 'selected' : '' }}>Dairy & Eggs</option>
                        <option value="Beverages" {{ old('category') == 'Beverages' ? 'selected' : '' }}>Beverages</option>
                        <option value="Groceries & Snacks" {{ old('category') == 'Groceries & Snacks' ? 'selected' : '' }}>Groceries & Snacks</option>
                        <option value="Other" {{ old('category') == 'Other' ? 'selected' : '' }}>Other</option>
                    </select>
                    @error('category') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Donation Status Dropdown -->
                <div>
                    <label class="block text-xs font-semibold text-[#222222] mb-1.5">Listing Type <span class="text-red-500">*</span></label>
                    <select name="donation_status" x-model="donationType" required class="w-full px-4 py-3 bg-[#F5F5F5] border border-gray-200 rounded-xl text-sm text-[#222222] focus:outline-none focus:border-[#2E7D32] focus:bg-white transition-colors">
                        <option value="0">Discounted Sale (Paid)</option>
                        <option value="1">Free Community Donation (Free)</option>
                    </select>
                </div>

                <!-- Price -->
                <div x-show="donationType == '0'">
                    <label class="block text-xs font-semibold text-[#222222] mb-1.5">Selling Price (BDT ৳) <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" min="0" name="price" value="{{ old('price', '50.00') }}" 
                           class="w-full px-4 py-3 bg-[#F5F5F5] border border-gray-200 rounded-xl text-sm text-[#222222] focus:outline-none focus:border-[#2E7D32] focus:bg-white transition-colors">
                    @error('price') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Quantity -->
                <div :class="donationType == '1' ? 'sm:col-span-2' : ''">
                    <label class="block text-xs font-semibold text-[#222222] mb-1.5">Quantity Available <span class="text-red-500">*</span></label>
                    <input type="number" min="1" name="quantity" value="{{ old('quantity', 5) }}" required 
                           class="w-full px-4 py-3 bg-[#F5F5F5] border border-gray-200 rounded-xl text-sm text-[#222222] focus:outline-none focus:border-[#2E7D32] focus:bg-white transition-colors">
                    @error('quantity') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Pickup Window -->
                <div>
                    <label class="block text-xs font-semibold text-[#222222] mb-1.5">Pickup Window <span class="text-red-500">*</span></label>
                    <input type="text" name="pickup_window" value="{{ old('pickup_window') }}" required 
                           placeholder="e.g. 5:00 PM - 8:00 PM Today" 
                           class="w-full px-4 py-3 bg-[#F5F5F5] border border-gray-200 rounded-xl text-sm text-[#222222] focus:outline-none focus:border-[#2E7D32] focus:bg-white transition-colors">
                    @error('pickup_window') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Expiration Date & Time -->
                <div>
                    <label class="block text-xs font-semibold text-[#222222] mb-1.5">Expiration Date & Time <span class="text-red-500">*</span></label>
                    <input type="datetime-local" name="expiration_time" value="{{ old('expiration_time') }}" required 
                           class="w-full px-4 py-3 bg-[#F5F5F5] border border-gray-200 rounded-xl text-sm text-[#222222] focus:outline-none focus:border-[#2E7D32] focus:bg-white transition-colors">
                    @error('expiration_time') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Drag & Drop Image Uploader Component with Live Preview -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-[#222222] mb-1.5">Food Photo</label>
                    
                    <div class="relative border-2 border-dashed border-gray-300 hover:border-[#2E7D32] rounded-xl p-6 text-center transition-colors bg-[#F5F5F5] group"
                         @dragover.prevent="dragover = true"
                         @dragleave.prevent="dragover = false"
                         @drop.prevent="handleDrop($event)">

                        <input type="file" 
                               id="image" 
                               name="image" 
                               accept="image/*" 
                               @change="handleFileSelect($event)" 
                               class="hidden">

                        <!-- Dropzone Empty State -->
                        <div x-show="!imagePreview" class="space-y-2">
                            <div class="w-12 h-12 rounded-full bg-[#2E7D32]/10 text-[#2E7D32] flex items-center justify-center mx-auto text-xl">
                                <i class="fa-solid fa-cloud-arrow-up"></i>
                            </div>
                            <div class="space-y-1">
                                <p class="text-xs font-semibold text-[#222222]">Drag & drop food image here, or browse</p>
                                <p class="text-[11px] text-[#666666]">Supports JPG, PNG, WEBP up to 5MB</p>
                            </div>
                            <label for="image" class="inline-block px-4 py-2 bg-[#2E7D32] text-white text-xs font-medium rounded-full cursor-pointer hover:bg-[#256928] shadow-xs">
                                Upload Photo
                            </label>
                        </div>

                        <!-- Image Preview State -->
                        <div x-show="imagePreview" x-cloak class="space-y-3">
                            <div class="relative w-48 h-36 mx-auto rounded-lg overflow-hidden border border-gray-200 shadow-sm">
                                <img :src="imagePreview" class="w-full h-full object-cover rounded-lg">
                            </div>
                            <div class="flex items-center justify-center gap-3">
                                <label for="image" class="px-3 py-1.5 bg-white border border-gray-200 text-[#222222] text-xs font-medium rounded-full cursor-pointer hover:bg-gray-100">
                                    <i class="fa-solid fa-arrows-rotate mr-1"></i> Replace Image
                                </label>
                                <button type="button" @click="removeImage()" class="px-3 py-1.5 bg-red-50 text-red-600 text-xs font-medium rounded-full hover:bg-red-100">
                                    <i class="fa-solid fa-trash mr-1"></i> Remove Image
                                </button>
                            </div>
                        </div>
                    </div>
                    @error('image') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-100">
                <a href="{{ route('provider.dashboard') }}" class="px-6 py-3 bg-[#F5F5F5] hover:bg-gray-200 text-[#222222] font-medium text-xs rounded-full transition-colors">
                    Cancel
                </a>
                <button type="submit" class="px-8 py-3 bg-[#2E7D32] hover:bg-[#256928] text-white font-medium text-xs rounded-full shadow-md shadow-[#2E7D32]/20 hover:scale-105 transition-all">
                    Save Food Listing
                </button>
            </div>
        </form>

    </div>
</div>

<script>
    function foodImageUpload() {
        return {
            donationType: '{{ old('donation_status', '0') }}',
            dragover: false,
            imagePreview: null,
            handleFileSelect(e) {
                const file = e.target.files[0];
                if (file) {
                    this.previewFile(file);
                }
            },
            handleDrop(e) {
                this.dragover = false;
                const file = e.dataTransfer.files[0];
                if (file) {
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    document.getElementById('image').files = dataTransfer.files;
                    this.previewFile(file);
                }
            },
            previewFile(file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.imagePreview = e.target.result;
                };
                reader.readAsDataURL(file);
            },
            removeImage() {
                this.imagePreview = null;
                document.getElementById('image').value = '';
            }
        }
    }
</script>
@endsection
