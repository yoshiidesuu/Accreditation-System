<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;

class SecurityHelper
{
    /**
     * Sanitize user input to prevent XSS attacks
     *
     * @param string|null $input
     * @param bool $allowHtml
     * @return string
     */
    public static function sanitizeInput(?string $input, bool $allowHtml = false): string
    {
        if ($input === null) {
            return '';
        }

        // Remove null bytes
        $input = str_replace(chr(0), '', $input);
        
        // Trim whitespace
        $input = trim($input);
        
        if (!$allowHtml) {
            // Strip all HTML tags
            $input = strip_tags($input);
        } else {
            // Allow only safe HTML tags
            $allowedTags = '<p><br><strong><em><u><ol><ul><li><h1><h2><h3><h4><h5><h6><blockquote><code><pre>';
            $input = strip_tags($input, $allowedTags);
            
            // Remove dangerous attributes
            $input = preg_replace('/(<[^>]+)\s*(on\w+|javascript:|vbscript:|data:)[^>]*>/i', '$1>', $input);
        }
        
        return $input;
    }
    
    /**
     * Encode output for safe display in HTML
     *
     * @param string|null $output
     * @param bool $doubleEncode
     * @return string
     */
    public static function encodeOutput(?string $output, bool $doubleEncode = false): string
    {
        if ($output === null) {
            return '';
        }
        
        return htmlspecialchars($output, ENT_QUOTES | ENT_HTML5, 'UTF-8', $doubleEncode);
    }
    
    /**
     * Create safe HTML string (already encoded)
     *
     * @param string $html
     * @return HtmlString
     */
    public static function safeHtml(string $html): HtmlString
    {
        return new HtmlString($html);
    }
    
    /**
     * Validate and sanitize file upload
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param array $allowedExtensions
     * @param array $allowedMimeTypes
     * @param int $maxSize Maximum size in bytes
     * @return bool
     */
    public static function validateFileUpload($file, array $allowedExtensions = [], array $allowedMimeTypes = [], int $maxSize = 10485760): bool
    {
        if (!$file || !$file->isValid()) {
            return false;
        }
        
        // Check file size
        if ($file->getSize() > $maxSize) {
            Log::warning('File upload rejected: size too large', [
                'filename' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'max_size' => $maxSize
            ]);
            return false;
        }
        
        // Check file extension
        if (!empty($allowedExtensions)) {
            $extension = strtolower($file->getClientOriginalExtension());
            if (!in_array($extension, $allowedExtensions)) {
                Log::warning('File upload rejected: invalid extension', [
                    'filename' => $file->getClientOriginalName(),
                    'extension' => $extension,
                    'allowed' => $allowedExtensions
                ]);
                return false;
            }
        }
        
        // Check MIME type
        if (!empty($allowedMimeTypes)) {
            $mimeType = $file->getMimeType();
            if (!in_array($mimeType, $allowedMimeTypes)) {
                Log::warning('File upload rejected: invalid MIME type', [
                    'filename' => $file->getClientOriginalName(),
                    'mime_type' => $mimeType,
                    'allowed' => $allowedMimeTypes
                ]);
                return false;
            }
        }
        
        // Check for executable files
        $dangerousExtensions = ['php', 'phtml', 'php3', 'php4', 'php5', 'pht', 'phar', 'exe', 'bat', 'cmd', 'com', 'scr', 'vbs', 'js', 'jar'];
        $extension = strtolower($file->getClientOriginalExtension());
        
        if (in_array($extension, $dangerousExtensions)) {
            Log::warning('File upload rejected: dangerous extension', [
                'filename' => $file->getClientOriginalName(),
                'extension' => $extension
            ]);
            return false;
        }
        
        return true;
    }
    
    /**
     * Generate secure filename
     *
     * @param string $originalName
     * @return string
     */
    public static function generateSecureFilename(string $originalName): string
    {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $basename = pathinfo($originalName, PATHINFO_FILENAME);
        
        // Sanitize basename
        $basename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $basename);
        $basename = trim($basename, '._-');
        
        // Ensure basename is not empty
        if (empty($basename)) {
            $basename = 'file';
        }
        
        // Add timestamp and random string
        $timestamp = time();
        $random = bin2hex(random_bytes(4));
        
        return $basename . '_' . $timestamp . '_' . $random . ($extension ? '.' . $extension : '');
    }
    
    /**
     * Check if string contains potential XSS
     *
     * @param string $input
     * @return bool
     */
    public static function containsXSS(string $input): bool
    {
        $xssPatterns = [
            '/<script[^>]*>.*?<\/script>/is',
            '/javascript:/i',
            '/vbscript:/i',
            '/on\w+\s*=/i',
            '/<iframe[^>]*>.*?<\/iframe>/is',
            '/<object[^>]*>.*?<\/object>/is',
            '/<embed[^>]*>/i',
            '/<form[^>]*>.*?<\/form>/is',
        ];
        
        foreach ($xssPatterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }
        
        return false;
    }
}