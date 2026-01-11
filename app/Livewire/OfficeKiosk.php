<?php

namespace App\Livewire;

use Illuminate\Support\Facades\URL;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.plain')]
#[Title('Office Kiosk')]
class OfficeKiosk extends Component
{
    public string $qrCodeUrl = '';

    public int $nextRefreshInSeconds = 1800; // 30 minutes

    public function mount()
    {
        // Check if we have a cached QR code that's still valid
        $cachedQr = cache()->get('office_kiosk_qr');

        if ($cachedQr) {
            $this->qrCodeUrl = $cachedQr['url'];
            $this->nextRefreshInSeconds = now()->diffInSeconds($cachedQr['expires_at']);
        } else {
            $this->generateQrCode();
        }
    }

    public function generateQrCode()
    {
        // Force the URL generator to use the APP_URL from config (e.g., http://192.168.x.x:8000)
        // This ensures the QR code works for devices on the network even if generated from localhost
        URL::forceRootUrl(config('app.url'));

        $expiresAt = now()->addMinutes(30);

        // Generate a signed URL for token generation (valid for 30 minutes)
        $this->qrCodeUrl = URL::temporarySignedRoute(
            'auth.qr-token',
            $expiresAt
        );

        // Reset root URL to avoid affecting other links in the request lifecycle
        URL::forceRootUrl(null);

        $this->nextRefreshInSeconds = 1800;

        // Cache the QR code for 30 minutes
        cache()->put('office_kiosk_qr', [
            'url' => $this->qrCodeUrl,
            'expires_at' => $expiresAt,
        ], $expiresAt);
    }

    public function render()
    {
        return view('livewire.office-kiosk');
        // Note: Kiosk might need a standalone layout without sidebar later.
    }
}
