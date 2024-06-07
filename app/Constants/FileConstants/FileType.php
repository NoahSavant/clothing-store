<?php

namespace App\Constants\FileConstants;

class FileType
{
    const IMAGE = 0;

    const VIDEO = 1;

    const PDF = 2;

    const DOC = 3;

    const GIF = 4;

    const CSV = 5;

    const SVG = 6;

    public static function getFileType($mime) {
        $fileType = null;
        switch ($mime) {
            case 'image/jpeg':
            case 'image/png':
            case 'image/gif':
            case 'image/webp':
                $fileType = self::IMAGE;
                break;
            case 'video/mp4':
            case 'video/quicktime':
                $fileType = self::VIDEO;
                break;
            case 'application/pdf':
                $fileType = self::PDF;
                break;
            case 'application/msword':
                $fileType = self::DOC;
                break;
            case 'text/csv':
                $fileType = self::CSV;
                break;
            case 'image/svg+xml':
                $fileType = self::SVG;
                break;
            default:
                // Unsupported file type
                return "Unknown";
        }

        return $fileType;
    }
}
