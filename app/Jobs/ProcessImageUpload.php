<?php

namespace App\Jobs;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class ProcessImageUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $event;
    public $imagePath;

    public function __construct(Event $event, $imagePath)
    {
        $this->event = $event;
        $this->imagePath = $imagePath;
    }

    public function handle()
    {
        try {
            // Check if file exists
            if (!Storage::disk('supabase')->exists($this->imagePath)) {
                throw new \Exception("Image file not found: {$this->imagePath}");
            }

            // Get the file
            $imageContent = Storage::disk('supabase')->get($this->imagePath);

            // Create intervention image instance
            $image = Image::make($imageContent);

            // Resize for different use cases
            $sizes = [
                'thumbnail' => [400, 300],
                'medium' => [800, 600],
                'large' => [1200, 900]
            ];

            $processedUrls = [];

            foreach ($sizes as $sizeName => $dimensions) {
                $resizedImage = clone $image;
                $resizedImage->resize($dimensions[0], $dimensions[1], function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });

                // Save resized image
                $resizedPath = 'events/' . $this->event->id . '/' . $sizeName . '_' . basename($this->imagePath);
                $resizedContent = $resizedImage->encode(pathinfo($this->imagePath, PATHINFO_EXTENSION));

                Storage::disk('supabase')->put($resizedPath, $resizedContent);
                $processedUrls[$sizeName] = $resizedPath;

                // Clean up
                $resizedImage->destroy();
            }

            // Update event with processed image URLs
            $this->event->update([
                'header_image_url' => $processedUrls['large'],
                'metadata' => array_merge($this->event->metadata ?? [], [
                    'processed_images' => $processedUrls
                ])
            ]);

            // Clean up original image if needed
            // Storage::disk('supabase')->delete($this->imagePath);

        } catch (\Exception $e) {
            // Log error but don't fail the job - event can still use original image
            Log::error('Error processing event image', [
                'event_id' => $this->event->id,
                'image_path' => $this->imagePath,
                'error' => $e->getMessage()
            ]);
        }
    }
}
