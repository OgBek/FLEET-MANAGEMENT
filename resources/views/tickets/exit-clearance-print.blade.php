<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exit Clearance Ticket</title>
    <style>
        @page {
            size: A4;
            margin: 15mm;
        }
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .ticket {
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid #ddd;
            padding: 20px;
            position: relative;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #eee;
        }
        .header img {
            max-height: 80px;
            margin-bottom: 10px;
        }
        .header h1 {
            margin: 10px 0 0;
            color: #2c3e50;
            font-size: 24px;
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
        .section {
            margin-bottom: 25px;
        }
        .section h3 {
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
            margin-bottom: 15px;
            color: #2c3e50;
        }
        .detail-row {
            display: flex;
            margin-bottom: 8px;
        }
        .detail-label {
            font-weight: bold;
            width: 180px;
            color: #555;
        }
        .detail-value {
            flex: 1;
        }
        .barcode {
            text-align: center;
            margin: 30px 0;
            padding: 20px 0;
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
                margin: 15mm;
            }
            
            body {
                padding: 0 !important;
                margin: 0 !important;
                background: white !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            .ticket {
                border: none;
                padding: 0;
            }
            
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="ticket">
        <div class="header">
            <img src="http://localhost/fleet/favicon.ico" alt="Company Logo">
            <h1>Exit Clearance Ticket</h1>
            <div class="ticket-number">Ticket #{{ $ticket->ticket_number }}</div>
        </div>

        <div class="details">
            <div class="section">
                <h3>Driver Information</h3>
                <div class="detail-row">
                    <div class="detail-label">Driver Name:</div>
                    <div class="detail-value">{{ $ticket->driver->name ?? 'N/A' }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">License Number:</div>
                    <div class="detail-value">{{ $ticket->driver->license_number ?? 'N/A' }}</div>
                </div>
            </div>

            <div class="section">
                <h3>Vehicle Information</h3>
                <div class="detail-row">
                    <div class="detail-label">Vehicle:</div>
                    <div class="detail-value">
                        {{ $ticket->vehicle->make }} {{ $ticket->vehicle->model }} 
                        ({{ $ticket->vehicle->registration_number }})
                    </div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Type:</div>
                    <div class="detail-value">{{ $ticket->vehicle->type->name ?? 'N/A' }}</div>
                </div>
            </div>

            <div class="section">
                <h3>Trip Details</h3>
                <div class="detail-row">
                    <div class="detail-label">Purpose:</div>
                    <div class="detail-value">{{ $ticket->booking->purpose ?? 'N/A' }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Destination:</div>
                    <div class="detail-value">{{ $ticket->booking->destination ?? 'N/A' }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Departure:</div>
                    <div class="detail-value">
                        {{ $ticket->booking->start_time ? $ticket->booking->start_time->format('M d, Y H:i') : 'N/A' }}
                    </div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Return:</div>
                    <div class="detail-value">
                        {{ $ticket->booking->end_time ? $ticket->booking->end_time->format('M d, Y H:i') : 'N/A' }}
                    </div>
                </div>
            </div>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; margin: 20px 0; min-height: 200px;">
            <div style="flex: 1; text-align: center; padding: 20px;">
                <div id="qrcode" style="min-height: 150px; display: flex; justify-content: center; align-items: center; background: #f9f9f9; border: 1px dashed #ddd;">
                    <div>Loading QR code...</div>
                </div>
                <div id="qrcode-error" style="color: red; font-size: 12px; margin-top: 5px; display: none;"></div>
                <div style="font-size: 12px; margin-top: 5px; color: #666;">Scan to verify</div>
            </div>
            <div style="flex: 1; text-align: center;">
                <div style="font-family: 'Libre Barcode 128', cursive; font-size: 48px; letter-spacing: 5px;">
                    *{{ $ticket->ticket_number }}*
                </div>
                <div style="margin-top: 5px; font-family: monospace; letter-spacing: 8px;">
                    {{ $ticket->ticket_number }}
                </div>
            </div>
        </div>

        <div class="valid-until">
            Valid until: {{ $ticket->booking->end_time ? $ticket->booking->end_time->format('M d, Y H:i') : 'N/A' }}
        </div>

        <div class="footer">
            <p>This is an auto-generated exit clearance ticket. Please present this ticket when exiting the premises.</p>
            <p>Issued on: {{ now()->format('M d, Y H:i') }}</p>
        </div>

        <div class="no-print" style="margin-top: 30px; text-align: center; display: flex; justify-content: center; gap: 15px;">
            <button onclick="window.history.back()" style="padding: 10px 20px; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer; display: inline-flex; align-items: center;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16" style="margin-right: 5px;">
                    <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/>
                </svg>
                Back
            </button>
            <button onclick="window.print()" style="padding: 10px 20px; background: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer; display: inline-flex; align-items: center;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-printer" viewBox="0 0 16 16" style="margin-right: 5px;">
                    <path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/>
                    <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H6zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2H5zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1z"/>
                </svg>
                Print Ticket
            </button>
        </div>
    </div>

    <!-- QR Code Library -->
    <script src="https://cdn.jsdelivr.net/npm/davidshimjs-qrcodejs@0.0.2/qrcode.min.js"></script>
    
    <script>
        // Debug function to log errors
        function logError(message, error) {
            console.error(message, error);
            const errorDiv = document.getElementById('qrcode-error');
            if (errorDiv) {
                errorDiv.textContent = message + ' ' + (error?.message || '');
                errorDiv.style.display = 'block';
            }
        }

        // Generate QR Code with ticket information
        document.addEventListener('DOMContentLoaded', function() {
            try {
                // Create QR code data
                const ticketData = {
                    ticketNumber: '{{ $ticket->ticket_number }}',
                    driver: '{{ addslashes($ticket->driver->name ?? 'N/A') }}',
                    licenseNumber: '{{ $ticket->driver->license_number ?? 'N/A' }}',
                    vehicle: '{{ addslashes(($ticket->vehicle->make ?? '') . ' ' . ($ticket->vehicle->model ?? '')) }}',
                    registration: '{{ $ticket->vehicle->registration_number ?? 'N/A' }}',
                    purpose: '{{ addslashes($ticket->booking->purpose ?? 'N/A') }}',
                    destination: '{{ addslashes($ticket->booking->destination ?? 'N/A') }}',
                    departure: '{{ $ticket->booking->start_time ? $ticket->booking->start_time->format('M d, Y H:i') : 'N/A' }}',
                    return: '{{ $ticket->booking->end_time ? $ticket->booking->end_time->format('M d, Y H:i') : 'N/A' }}',
                    issuedAt: '{{ $ticket->created_at->format('M d, Y H:i') }}',
                    issuedBy: '{{ addslashes($ticket->issuer->name ?? 'System') }}'
                };

                // Convert to JSON string
                const qrData = JSON.stringify(ticketData, null, 2);
                
                // Generate QR Code
                const qrCodeElement = document.getElementById('qrcode');
                if (qrCodeElement) {
                    // Clear any existing QR code
                    qrCodeElement.innerHTML = '';
                    
                    // Generate new QR code
                    try {
                        new QRCode(qrCodeElement, {
                            text: qrData,
                            width: 150,
                            height: 150,
                            colorDark: '#000000',
                            colorLight: '#ffffff',
                            correctLevel: QRCode.CorrectLevel.H
                        });
                        
                        // Add a small border for better visibility
                        qrCodeElement.style.padding = '10px';
                        qrCodeElement.style.border = '1px solid #eee';
                        qrCodeElement.style.display = 'inline-block';
                        
                    } catch (e) {
                        logError('Failed to generate QR code:', e);
                        qrCodeElement.innerHTML = '<div style="color:red; padding:10px;">QR Code Error</div>';
                    }
                } else {
                    logError('QR code container not found');
                }
            } catch (e) {
                logError('Error in QR code generation:', e);
            }

            // Auto-print when the page loads (only in print view)
            if (window.self !== window.top) {
                window.print();
            }
        });
    </script>
</body>
</html>
