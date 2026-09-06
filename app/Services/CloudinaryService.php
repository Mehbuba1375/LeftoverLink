<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CloudinaryService
{
    protected string $cloudName;
    protected string $apiKey;
    protected string $apiSecret;
    protected string $uploadPreset;
    protected bool $enabled;

    public function __construct()
    {
        $this->cloudName = (string) (config('services.cloudinary.cloud_name') ?: env('CLOUDINARY_CLOUD_NAME', ''));
        $this->apiKey = (string) (config('services.cloudinary.api_key') ?: env('CLOUDINARY_API_KEY', ''));
        $this->apiSecret = (string) (config('services.cloudinary.api_secret') ?: env('CLOUDINARY_API_SECRET', ''));
        $this->uploadPreset = (string) (config('services.cloudinary.upload_preset') ?: env('CLOUDINARY_UPLOAD_PRESET', 'leftoverlink'));

        $this->enabled = !empty($this->cloudName);
    }

    /**
     * Check if Cloudinary is configured and enabled.
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Upload an image file to Cloudinary.
     * Returns the secure HTTPS URL on success, or null on failure/unconfigured.
     */
    public function upload(UploadedFile $file, string $folder = 'food-images'): ?string
    {
        if (!$this->enabled) {
            return null;
        }

        try {
            $timestamp = time();

            // 1. Unsigned Upload (Preset based)
            if (!empty($this->uploadPreset) && empty($this->apiKey)) {
                $response = Http::attach(
                    'file', file_get_contents($file->getRealPath()), $file->getClientOriginalName()
                )->post("https://api.cloudinary.com/v1_1/{$this->cloudName}/image/upload", [
                    'upload_preset' => $this->uploadPreset,
                    'folder' => $folder,
                ]);
            } else {
                // 2. Signed Upload (API Key & Secret)
                $params = [
                    'folder' => $folder,
                    'timestamp' => $timestamp,
                ];
                ksort($params);

                $stringToSign = '';
                foreach ($params as $key => $value) {
                    $stringToSign .= "{$key}={$value}&";
                }
                $stringToSign = rtrim($stringToSign, '&') . $this->apiSecret;
                $signature = sha1($stringToSign);

                $response = Http::attach(
                    'file', file_get_contents($file->getRealPath()), $file->getClientOriginalName()
                )->post("https://api.cloudinary.com/v1_1/{$this->cloudName}/image/upload", [
                    'api_key' => $this->apiKey,
                    'timestamp' => $timestamp,
                    'signature' => $signature,
                    'folder' => $folder,
                ]);
            }

            if ($response->successful()) {
                $data = $response->json();
                return $data['secure_url'] ?? $data['url'] ?? null;
            }

            Log::error('CloudinaryService upload failed:', ['status' => $response->status(), 'body' => $response->body()]);
            return null;
        } catch (\Throwable $e) {
            Log::error('CloudinaryService upload exception:', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Delete an image from Cloudinary using public_id extracted from URL.
     */
    public function deleteByUrl(string $url): bool
    {
        if (!$this->enabled || empty($this->apiKey) || empty($this->apiSecret)) {
            return false;
        }

        try {
            if (preg_match('/\/upload\/(?:v\d+\/)?(.+?)\.[a-z]+$/i', $url, $matches)) {
                $publicId = $matches[1];
                $timestamp = time();
                $stringToSign = "public_id={$publicId}&timestamp={$timestamp}" . $this->apiSecret;
                $signature = sha1($stringToSign);

                $response = Http::post("https://api.cloudinary.com/v1_1/{$this->cloudName}/image/destroy", [
                    'public_id' => $publicId,
                    'api_key' => $this->apiKey,
                    'timestamp' => $timestamp,
                    'signature' => $signature,
                ]);

                return $response->successful();
            }
        } catch (\Throwable $e) {
            Log::error('CloudinaryService delete exception:', ['error' => $e->getMessage()]);
        }

        return false;
    }
}
