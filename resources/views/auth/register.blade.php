@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto py-8">
    <div class="bg-white rounded-xl shadow-xs border border-gray-100 p-8 sm:p-10 space-y-6">
        
        <!-- Header -->
        <div class="text-center space-y-2">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-[#2E7D32]/10 text-[#2E7D32] mb-1">
                <i class="fa-solid fa-user-plus text-xl"></i>
            </div>
            <h2 class="text-2xl font-heading font-bold text-[#222222]">Create LeftoverLink Account</h2>
            <p class="text-xs text-[#666666]">Join our food rescue marketplace to share or save surplus meals</p>
        </div>

        <form action="{{ route('register') }}" method="POST" class="space-y-5">
            @csrf

            <!-- Role Selection Cards -->
            <div x-data="{ selectedRole: '{{ old('role', 'consumer') }}' }">
                <label class="block text-xs font-semibold text-[#222222] uppercase tracking-wider mb-2">Account Role</label>
                <input type="hidden" name="role" :value="selectedRole">
                
                <div class="grid grid-cols-3 gap-3">
                    <button type="button" 
                            @click="selectedRole = 'consumer'"
                            :class="selectedRole === 'consumer' ? 'border-[#2E7D32] bg-[#2E7D32]/10 text-[#2E7D32] font-bold' : 'border-gray-200 bg-[#F5F5F5] text-[#666666]'"
                            class="p-3.5 rounded-xl border flex flex-col items-center gap-1.5 text-center transition-all">
                        <i class="fa-solid fa-basket-shopping text-lg"></i>
                        <span class="text-xs">Consumer</span>
                    </button>

                    <button type="button" 
                            @click="selectedRole = 'food_provider'"
                            :class="selectedRole === 'food_provider' ? 'border-[#2E7D32] bg-[#2E7D32]/10 text-[#2E7D32] font-bold' : 'border-gray-200 bg-[#F5F5F5] text-[#666666]'"
                            class="p-3.5 rounded-xl border flex flex-col items-center gap-1.5 text-center transition-all">
                        <i class="fa-solid fa-store text-lg"></i>
                        <span class="text-xs">Provider</span>
                    </button>

                    <button type="button" 
                            @click="selectedRole = 'ngo'"
                            :class="selectedRole === 'ngo' ? 'border-[#2E7D32] bg-[#2E7D32]/10 text-[#2E7D32] font-bold' : 'border-gray-200 bg-[#F5F5F5] text-[#666666]'"
                            class="p-3.5 rounded-xl border flex flex-col items-center gap-1.5 text-center transition-all">
                        <i class="fa-solid fa-hand-holding-heart text-lg"></i>
                        <span class="text-xs">NGO</span>
                    </button>
                </div>
            </div>

            <!-- Form Inputs -->
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-[#222222] mb-1">Full Name / Business Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required 
                           placeholder="e.g. Sultana Rahman or Green Bakery"
                           class="w-full px-4 py-3 bg-[#F5F5F5] border border-gray-200 rounded-xl text-sm text-[#222222] focus:outline-none focus:border-[#2E7D32] focus:bg-white transition-colors">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-[#222222] mb-1">Email Address *</label>
                        <input type="email" name="email" value="{{ old('email') }}" required 
                               placeholder="name@example.com"
                               class="w-full px-4 py-3 bg-[#F5F5F5] border border-gray-200 rounded-xl text-sm text-[#222222] focus:outline-none focus:border-[#2E7D32] focus:bg-white transition-colors">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#222222] mb-1">Phone Number *</label>
                        <input type="tel" name="phone" value="{{ old('phone') }}" required 
                               placeholder="+880 1700-000000"
                               class="w-full px-4 py-3 bg-[#F5F5F5] border border-gray-200 rounded-xl text-sm text-[#222222] focus:outline-none focus:border-[#2E7D32] focus:bg-white transition-colors">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#222222] mb-1">Address / Pickup Location</label>
                    <input type="text" name="address" value="{{ old('address') }}" 
                           placeholder="House/Road, Area, City"
                           class="w-full px-4 py-3 bg-[#F5F5F5] border border-gray-200 rounded-xl text-sm text-[#222222] focus:outline-none focus:border-[#2E7D32] focus:bg-white transition-colors">
                </div>

                <!-- Leaflet Location Picker -->
                <div x-data="{
                    lat: '{{ old("latitude") }}',
                    lng: '{{ old("longitude") }}',
                    map: null,
                    marker: null,
                    geoError: '',
                    initMap() {
                        this.$nextTick(() => {
                            const initialLat = this.lat ? parseFloat(this.lat) : 23.8103;
                            const initialLng = this.lng ? parseFloat(this.lng) : 90.4125;
                            
                            this.map = L.map('register-leaflet-map').setView([initialLat, initialLng], 12);
                            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                maxZoom: 19,
                                attribution: '&copy; OpenStreetMap'
                            }).addTo(this.map);

                            if (this.lat && this.lng) {
                                this.marker = L.marker([initialLat, initialLng]).addTo(this.map);
                            }

                            this.map.on('click', (e) => {
                                this.updateLocation(e.latlng.lat, e.latlng.lng);
                            });
                        });
                    },
                    updateLocation(latitude, longitude) {
                        this.lat = latitude.toFixed(6);
                        this.lng = longitude.toFixed(6);
                        if (this.marker) {
                            this.marker.setLatLng([latitude, longitude]);
                        } else {
                            this.marker = L.marker([latitude, longitude]).addTo(this.map);
                        }
                        this.map.setView([latitude, longitude], 14);
                    },
                    useCurrentLocation() {
                        this.geoError = '';
                        if ('geolocation' in navigator) {
                            navigator.geolocation.getCurrentPosition(
                                (pos) => {
                                    this.updateLocation(pos.coords.latitude, pos.coords.longitude);
                                },
                                (err) => {
                                    this.geoError = 'Geolocation permission denied or unavailable. Click on map to set location.';
                                }
                            );
                        } else {
                            this.geoError = 'Geolocation is not supported by your browser.';
                        }
                    }
                }" x-init="initMap()" class="space-y-2 border-t border-gray-100 pt-4">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-semibold text-[#222222]">
                            <i class="fa-solid fa-location-dot text-[#2E7D32] mr-1"></i> Select Location on Map
                        </label>
                        <button type="button" @click="useCurrentLocation()" class="px-3 py-1 bg-[#2E7D32]/10 hover:bg-[#2E7D32]/20 text-[#2E7D32] text-xs font-medium rounded-full transition-colors flex items-center gap-1">
                            <i class="fa-solid fa-location-arrow text-[10px]"></i> Use My Current Location
                        </button>
                    </div>
                    
                    <div id="register-leaflet-map" class="w-full h-48 rounded-xl border border-gray-200 z-0"></div>

                    <div class="grid grid-cols-2 gap-3 text-xs">
                        <div>
                            <label class="text-[10px] text-[#666666] font-semibold">Latitude</label>
                            <input type="text" name="latitude" x-model="lat" readonly placeholder="Click map to set" class="w-full px-3 py-1.5 bg-[#F5F5F5] border border-gray-200 rounded-lg text-xs text-[#222222]">
                        </div>
                        <div>
                            <label class="text-[10px] text-[#666666] font-semibold">Longitude</label>
                            <input type="text" name="longitude" x-model="lng" readonly placeholder="Click map to set" class="w-full px-3 py-1.5 bg-[#F5F5F5] border border-gray-200 rounded-lg text-xs text-[#222222]">
                        </div>
                    </div>
                    <template x-if="geoError">
                        <p class="text-[10px] text-amber-600 font-medium" x-text="geoError"></p>
                    </template>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-[#222222] mb-1">Password *</label>
                        <input type="password" name="password" required placeholder="••••••••"
                               class="w-full px-4 py-3 bg-[#F5F5F5] border border-gray-200 rounded-xl text-sm text-[#222222] focus:outline-none focus:border-[#2E7D32] focus:bg-white transition-colors">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#222222] mb-1">Confirm Password *</label>
                        <input type="password" name="password_confirmation" required placeholder="••••••••"
                               class="w-full px-4 py-3 bg-[#F5F5F5] border border-gray-200 rounded-xl text-sm text-[#222222] focus:outline-none focus:border-[#2E7D32] focus:bg-white transition-colors">
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <button type="submit" class="w-full py-3.5 bg-[#2E7D32] hover:bg-[#256928] text-white font-medium text-xs rounded-full shadow-md shadow-[#2E7D32]/20 hover:scale-105 transition-all">
                Register Account
            </button>

            <p class="text-center text-xs text-[#666666]">
                Already have an account? <a href="{{ route('login') }}" class="font-bold text-[#2E7D32] hover:underline">Log in here</a>
            </p>
        </form>

    </div>
</div>
@endsection
