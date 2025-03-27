<?php

namespace App\Traits;

trait FileSizeConverter
{
    /**
     * Convert bytes to human-readable file size format.
     *
     * @param int $sizeInBytes
     * @return string
     */
    public function convertFileSize($sizeInBytes)
    {
        $sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
        if ($sizeInBytes <= 0) {
            return '0 B';
        }
        
        $factor = floor((strlen($sizeInBytes) - 1) / 3);
        return round($sizeInBytes / pow(1024, $factor), 2) . ' ' . $sizes[$factor];
    }
}
