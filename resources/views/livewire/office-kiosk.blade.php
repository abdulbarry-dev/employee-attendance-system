<div class="flex h-screen w-full items-center justify-center bg-white">
    <div class="relative flex items-center justify-center p-8">
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=500x500&data={{ urlencode($qrCodeUrl) }}" 
             alt="Attendance QR Code" 
             class="h-[80vh] w-[80vh] max-h-[800px] max-w-[800px] object-contain" />
    </div>
    
    <div wire:poll.1800s="generateQrCode"></div>
</div>
