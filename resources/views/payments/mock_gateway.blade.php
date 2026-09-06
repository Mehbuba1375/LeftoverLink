@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto py-12 px-4">
    <div class="bg-white rounded-3xl shadow-2xl overflow-hidden border border-emerald-900/10">
        {{-- Header Banner --}}
        <div class="bg-gradient-to-r from-[#003366] via-[#00509d] to-[#00296b] p-6 text-white text-center relative overflow-hidden">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md text-sky-200 text-xs font-semibold uppercase tracking-wider mb-2">
                <i class="fa-solid fa-shield-halved"></i> SSLCommerz Secure Gateway (Sandbox)
            </div>
            <h1 class="text-2xl font-bold font-heading">SSLCommerz Payment Gateway</h1>
            <p class="text-xs text-sky-100/90 mt-1">Official Payment Processing for LeftoverLink Surplus Food</p>
        </div>

        {{-- Transaction Summary Box --}}
        <div class="p-6 bg-slate-50 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <span class="text-xs text-slate-500 font-medium">Merchant: LeftoverLink Ltd.</span>
                <h3 class="font-bold text-slate-900 text-base">{{ $payment->food ? $payment->food->food_name : 'Surplus Food Item' }}</h3>
                <p class="text-xs font-mono text-emerald-700 font-semibold">Transaction ID: {{ $payment->tran_id }}</p>
            </div>
            <div class="text-right">
                <span class="text-xs text-slate-500 font-medium">Total Amount Due</span>
                <p class="text-2xl font-extrabold text-[#003366]">৳{{ number_format($payment->amount, 2) }} <span class="text-xs font-normal text-slate-500">BDT</span></p>
            </div>
        </div>

        {{-- Payment Method Selection (Online Payment System) --}}
        <div class="p-6 space-y-6"
             x-data="{
                selected: 'bkash',
                methods: {
                    bkash:      { card_type: 'SSLCOMMERZ-BKASH',            card_no: '017****8899',  prefix: 'BKASH' },
                    nagad:      { card_type: 'SSLCOMMERZ-NAGAD',            card_no: '018****4477',  prefix: 'NAGAD' },
                    visa:       { card_type: 'SSLCOMMERZ-VISA',             card_no: '4111****1111', prefix: 'VISA' },
                    mastercard: { card_type: 'SSLCOMMERZ-MASTERCARD',       card_no: '5555****4444', prefix: 'MC' },
                    ibanking:   { card_type: 'SSLCOMMERZ-INTERNET-BANKING', card_no: 'IB****2210',   prefix: 'IB' }
                },
                get current() { return this.methods[this.selected]; }
             }">
            <div class="space-y-2">
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500">Select Payment Gateway Option</h4>

                <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                    <button type="button" @click="selected = 'bkash'"
                            :class="selected === 'bkash' ? 'border-2 border-emerald-600 bg-emerald-50/50 shadow-xs' : 'border border-slate-200 hover:border-slate-300'"
                            class="p-3 rounded-2xl text-center flex flex-col items-center justify-center cursor-pointer transition-all">
                        <span class="font-black text-pink-600 text-sm tracking-tight">bKash</span>
                        <span class="text-[10px] font-semibold text-slate-600 mt-1">Mobile Banking</span>
                    </button>
                    <button type="button" @click="selected = 'nagad'"
                            :class="selected === 'nagad' ? 'border-2 border-emerald-600 bg-emerald-50/50 shadow-xs' : 'border border-slate-200 hover:border-slate-300'"
                            class="p-3 rounded-2xl text-center flex flex-col items-center justify-center cursor-pointer transition-all">
                        <span class="font-black text-orange-600 text-sm tracking-tight">Nagad</span>
                        <span class="text-[10px] font-semibold text-slate-600 mt-1">Mobile Banking</span>
                    </button>
                    <button type="button" @click="selected = 'visa'"
                            :class="selected === 'visa' ? 'border-2 border-emerald-600 bg-emerald-50/50 shadow-xs' : 'border border-slate-200 hover:border-slate-300'"
                            class="p-3 rounded-2xl text-center flex flex-col items-center justify-center cursor-pointer transition-all">
                        <span class="font-bold text-blue-700 text-sm">VISA</span>
                        <span class="text-[10px] font-semibold text-slate-600 mt-1">Credit / Debit</span>
                    </button>
                    <button type="button" @click="selected = 'mastercard'"
                            :class="selected === 'mastercard' ? 'border-2 border-emerald-600 bg-emerald-50/50 shadow-xs' : 'border border-slate-200 hover:border-slate-300'"
                            class="p-3 rounded-2xl text-center flex flex-col items-center justify-center cursor-pointer transition-all">
                        <span class="font-bold text-red-600 text-sm">Mastercard</span>
                        <span class="text-[10px] font-semibold text-slate-600 mt-1">Credit / Debit</span>
                    </button>
                    <button type="button" @click="selected = 'ibanking'"
                            :class="selected === 'ibanking' ? 'border-2 border-emerald-600 bg-emerald-50/50 shadow-xs' : 'border border-slate-200 hover:border-slate-300'"
                            class="p-3 rounded-2xl text-center flex flex-col items-center justify-center cursor-pointer transition-all">
                        <span class="font-bold text-indigo-700 text-sm">City Bank</span>
                        <span class="text-[10px] font-semibold text-slate-600 mt-1">Internet Banking</span>
                    </button>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="space-y-3 pt-4 border-t border-slate-100">
                <form action="{{ route('payment.success') }}" method="POST">
                    @csrf
                    <input type="hidden" name="tran_id" value="{{ $payment->tran_id }}">
                    <input type="hidden" name="value_a" value="{{ $callbackToken }}">
                    <input type="hidden" name="val_id" value="VAL_MOCK_{{ strtoupper(substr(md5(time()), 0, 10)) }}">
                    <input type="hidden" name="amount" value="{{ $payment->amount }}">
                    <input type="hidden" name="card_type" :value="current.card_type">
                    <input type="hidden" name="card_no" :value="current.card_no">
                    <input type="hidden" name="bank_tran_id" :value="current.prefix + '_{{ strtoupper(substr(md5(time()), 0, 8)) }}'">

                    <button type="submit" class="w-full py-3.5 px-6 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm rounded-xl shadow-lg transition-all flex items-center justify-center gap-2">
                        <i class="fa-solid fa-lock"></i> Pay ৳{{ number_format($payment->amount, 2) }} Securely via SSLCommerz
                    </button>
                </form>

                <div class="grid grid-cols-2 gap-3">
                    <form action="{{ route('payment.fail') }}" method="POST">
                        @csrf
                        <input type="hidden" name="tran_id" value="{{ $payment->tran_id }}">
                        <input type="hidden" name="value_a" value="{{ $callbackToken }}">
                        <button type="submit" class="w-full py-2.5 px-4 bg-slate-100 hover:bg-rose-50 text-rose-600 font-semibold text-xs rounded-xl border border-slate-200 transition-all text-center">
                            Simulate Failed Payment
                        </button>
                    </form>

                    <form action="{{ route('payment.cancel') }}" method="POST">
                        @csrf
                        <input type="hidden" name="tran_id" value="{{ $payment->tran_id }}">
                        <input type="hidden" name="value_a" value="{{ $callbackToken }}">
                        <button type="submit" class="w-full py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl border border-slate-200 transition-all text-center">
                            Cancel & Return
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Footer Trust Notice --}}
        <div class="p-4 bg-slate-100 text-center text-[11px] text-slate-500 border-t border-slate-200">
            <i class="fa-solid fa-circle-check text-emerald-600 mr-1"></i> Protected by 256-bit SSL Commerz Encryption Standard
        </div>
    </div>
</div>
@endsection
