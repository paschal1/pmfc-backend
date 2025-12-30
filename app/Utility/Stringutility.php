<?php

namespace App\Utility;

class StringUtility
{
    /**
     * Sanitize string input
     * Removes special characters and trims whitespace
     */
    public static function sanitize(string $input): string
    {
        // Trim whitespace
        $input = trim($input);
        
        // Remove any HTML/PHP tags
        $input = strip_tags($input);
        
        // Remove extra whitespace
        $input = preg_replace('/\s+/', ' ', $input);
        
        return $input;
    }

    /**
     * Sanitize email
     */
    public static function sanitizeEmail(string $email): string
    {
        return strtolower(trim($email));
    }

    /**
     * Sanitize phone number
     * Keeps only digits, spaces, and common phone characters
     */
    public static function sanitizePhone(string $phone): string
    {
        // Remove all non-alphanumeric characters except +, -, (, ), and space
        return preg_replace('/[^0-9+\-() ]/', '', $phone);
    }

    /**
     * Sanitize address
     */
    public static function sanitizeAddress(string $address): string
    {
        // Trim and remove extra whitespace
        $address = trim($address);
        $address = preg_replace('/\s+/', ' ', $address);
        
        // Remove potentially dangerous characters but keep common address chars
        $address = preg_replace('/[^a-zA-Z0-9\s\-#,.]/', '', $address);
        
        return $address;
    }

    /**
     * Check if string is valid email
     */
    public static function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Truncate string to specified length
     */
    public static function truncate(string $string, int $length = 100, string $suffix = '...'): string
    {
        if (strlen($string) <= $length) {
            return $string;
        }
        
        return substr($string, 0, $length) . $suffix;
    }

    /**
     * Convert string to slug (for URLs)
     */
    public static function toSlug(string $string): string
    {
        // Convert to lowercase
        $string = strtolower($string);
        
        // Replace spaces with hyphens
        $string = preg_replace('/\s+/', '-', $string);
        
        // Remove all non-alphanumeric and hyphen characters
        $string = preg_replace('/[^a-z0-9\-]/', '', $string);
        
        // Remove multiple consecutive hyphens
        $string = preg_replace('/-+/', '-', $string);
        
        // Remove leading and trailing hyphens
        $string = trim($string, '-');
        
        return $string;
    }

    /**
     * Capitalize first letter of each word
     */
    public static function capitalize(string $string): string
    {
        return ucwords(strtolower($string));
    }

    /**
     * Remove all non-numeric characters
     */
    public static function numbersOnly(string $string): string
    {
        return preg_replace('/[^0-9]/', '', $string);
    }
}