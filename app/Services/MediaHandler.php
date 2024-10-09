<?php

namespace App\Services;

use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class MediaHandler
{
    public function handleMediaFiles($files)
    {
        $mediaLinks = [];

        try {
            if (is_array($files)) {
                foreach ($files as $file) {
                    $uploadedFileUrl = cloudinary()->upload($file->getRealPath(), [
                        'resource_type' => 'auto',
                        'folder' => 'media'
                    ]);

                    $mediaLinks[] = $uploadedFileUrl->getSecurePath();
                }
            }

            return $mediaLinks;
        } catch (\Exception $e) {
            \Log::error('Error uploading media files: ' . $e->getMessage());
            return false;
        }
    }
}
