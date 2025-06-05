@extends('layouts.dashboard')

@section('header', 'Exit Clearance Ticket')

@push('styles')
<style>
        @page {
            size: A4;
            margin: 0;
        }
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .ticket {
            max-width: 800px;
            margin: 0 auto;
            border: 2px solid #ddd;
            padding: 20px;
            position: relative;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #eee;
        }
        .header h1 {
            margin: 0;
            color: #2c3e50;
        }
        .ticket-number {
            position: absolute;
            top: 20px;
            right: 20px;
            background: #f8f9fa;
            padding: 5px 10px;
            border-radius: 4px;
            font-weight: bold;
        }
        .details {
            margin-bottom: 20px;
        }
        .detail-row {
            display: flex;
            margin-bottom: 10px;
        }
        .detail-label {
            font-weight: bold;
            width: 200px;
            color: #555;
        }
        .detail-value {
            flex: 1;
        }
        .barcode {
            text-align: center;
            margin: 20px 0;
            padding: 15px;
            border-top: 1px dashed #ddd;
            border-bottom: 1px dashed #ddd;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 0.9em;
            color: #777;
            border-top: 1px solid #eee;
            padding-top: 15px;
        }
        .valid-until {
            text-align: center;
            font-weight: bold;
            margin: 15px 0;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 4px;
        }
        @media print {
            @page {
                margin: 0;
                size: auto;
            }
            
            body {
                padding: 0 !important;
                margin: 0 !important;
                background: white !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            .no-print,
            .sidebar,
            .header,
            .sidebar-toggle,
            .navbar,
            .print-hide {
                display: none !important;
            }
            
            .ticket {
                margin: 0 !important;
                padding: 1.5rem !important;
                border: none !important;
                box-shadow: none !important;
                max-width: 100% !important;
            }
            
            .main-content {
                margin: 0 !important;
                padding: 0 !important;
            }
            
            body * {
                visibility: hidden;
            }
            
            .ticket, 
            .ticket * {
                visibility: visible;
            }
            
            .ticket {
                position: relative;
                left: 0;
                top: 0;
                width: 100%;
            }
        }
    </style>
    @endpush

    @section('content')
    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-semibold text-gray-800">Exit Clearance Ticket</h2>
                        <button onclick="window.print()" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                            </svg>
                            Print Ticket
                        </button>
                    </div>
                    
                    <div class="ticket bg-white rounded-lg shadow-md p-8 border border-gray-200">
        <div class="header text-center mb-8">
            <div class="mb-2">
                <img src="{{ asset('images/logo.png') }}" alt="Company Logo" class="h-16 mx-auto mb-4">
            </div>
            <h1 class="text-2xl font-bold text-gray-800 uppercase tracking-wider mb-2">Vehicle Exit Clearance Ticket</h1>
            <div class="text-sm text-gray-600">
                <span class="font-semibold">Ticket #{{ $ticket->ticket_number }}</span> • 
                <span>Issued: {{ $ticket->created_at->format('M d, Y h:i A') }}</span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="bg-gray-50 p-6 rounded-lg">
                <h3 class="text-lg font-semibold text-gray-800 border-b pb-2 mb-4">Driver Information</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Driver Name:</span>
                        <span class="font-medium text-gray-800">{{ $ticket->driver->name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium">License Number:</span>
                        <span>{{ $ticket->driver->license_number ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>
            
            <div class="bg-gray-50 p-6 rounded-lg">
                <h3 class="text-lg font-semibold text-gray-800 border-b pb-2 mb-4">Vehicle Information</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Vehicle:</span>
                        <span class="font-medium text-gray-800">{{ $ticket->vehicle->make }} {{ $ticket->vehicle->model }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Registration:</span>
                        <span class="font-medium text-gray-800">{{ $ticket->vehicle->registration_number }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Booking Ref:</span>
                        <span class="font-medium text-gray-800">{{ $ticket->booking->reference_number }}</span>
                    </div>
                </div>
            </div>
            
            <div class="bg-gray-50 p-6 rounded-lg">
                <h3 class="text-lg font-semibold text-gray-800 border-b pb-2 mb-4">Ticket Details</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Valid Until:</span>
                        <span class="font-medium text-gray-800">{{ $ticket->valid_until->format('M d, Y h:i A') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Issued By:</span>
                        <span class="font-medium text-gray-800">{{ $ticket->issuer->name ?? 'System' }}</span>
                    </div>
                    @if($ticket->gate_number)
                    <div class="flex justify-between">
                        <span class="text-gray-600">Gate Number:</span>
                        <span class="font-medium text-gray-800">{{ $ticket->gate_number }}</span>
                    </div>
                    @endif
                </div>
            </div>
            
            @if($ticket->remarks)
            <div class="bg-gray-50 p-6 rounded-lg">
                <h3 class="text-lg font-semibold text-gray-800 border-b pb-2 mb-4">Additional Notes</h3>
                <p class="text-gray-700">{{ $ticket->remarks }}</p>
            </div>
            @endif
        </div>

        <div class="valid-until">
            VALID UNTIL: {{ $ticket->valid_until->format('M d, Y h:i A') }}
        </div>

        <div class="barcode">
            <!-- Simple barcode representation - consider using a barcode generator library in production -->
            <div style="font-family: 'Libre Barcode 128', cursive; font-size: 48px; letter-spacing: 5px;">
                *{{ $ticket->ticket_number }}*
            </div>
            <div class="mt-8 pt-6 border-t border-gray-200">
            <div class="text-center">
                <div class="inline-block p-4 bg-gray-50 rounded-lg">
                    <div class="text-sm font-medium text-gray-500 mb-1">Ticket Barcode</div>
                    <div class="text-2xl font-mono tracking-wider">{{ $ticket->ticket_number }}</div>
                    <div class="mt-2 h-12 bg-gray-200 flex items-center justify-center overflow-hidden">
                        <!-- Barcode representation -->
                        @php
                            $barcode = str_split(preg_replace('/[^0-9]/', '', $ticket->ticket_number));
                            $barcodePattern = '';
                            foreach($barcode as $digit) {
                                $barcodePattern .= str_repeat('1', $digit * 2) . '0';
                            }
                        @endphp
                        <div class="flex h-full">
                            @foreach(str_split($barcodePattern) as $bar)
                                <div class="h-full w-1 mx-px bg-gray-800" style="width: {{ $bar === '1' ? '3px' : '1px' }};"></div>
                            @endforeach
                        </div>
                    </div>
                    <p class="mt-4 text-sm text-gray-500">Present this ticket when exiting the premises</p>
                </div>
            </div>
            
            <div class="mt-8 text-center text-sm text-gray-500">
                <p>Generated on {{ now()->format('M d, Y h:i A') }}</p>
                <p class="mt-1">{{ config('app.name') }} • {{ config('app.url') }}</p>
            </div>
        </div>
                </div>
            </div>
        </div>
    </div>
    @endsection

    @push('scripts')
    <script>
        // Auto-print when the page loads (optional)
        window.onload = function() {
            // Uncomment the line below to auto-print the ticket
            // window.print();
        };
    </script>
    @endpush
