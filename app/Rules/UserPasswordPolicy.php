<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UserPasswordPolicy implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Minimum 8 characters
        if (strlen($value) < 8) {
            $fail('Password harus minimal 8 karakter.');
            return;
        }
        
        // At least one uppercase letter
        if (!preg_match('/[A-Z]/', $value)) {
            $fail('Password harus mengandung huruf besar (A-Z).');
            return;
        }
        
        // At least one lowercase letter
        if (!preg_match('/[a-z]/', $value)) {
            $fail('Password harus mengandung huruf kecil (a-z).');
            return;
        }
        
        // At least one number
        if (!preg_match('/[0-9]/', $value)) {
            $fail('Password harus mengandung angka (0-9).');
            return;
        }
        
        // At least one special character
        if (!preg_match('/[^a-zA-Z0-9]/', $value)) {
            $fail('Password harus mengandung karakter spesial (!@#$%^&* dll).');
            return;
        }
        
        // Check if password is commonly used (optional)
        $commonPasswords = ['password', '12345678', 'qwerty123', 'password123', 'admin123'];
        if (in_array(strtolower($value), $commonPasswords)) {
            $fail('Password terlalu umum, gunakan password yang lebih kuat.');
            return;
        }
    }
    
    /**
     * Get password requirements description.
     */
    public static function requirements(): string
    {
        return 'Password harus: minimal 8 karakter, mengandung huruf besar (A-Z), huruf kecil (a-z), angka (0-9), dan karakter spesial.';
    }
    
    /**
     * Get password strength score (0-100).
     */
    public static function getStrengthScore(string $password): int
    {
        $score = 0;
        
        // Length
        $length = strlen($password);
        if ($length >= 8) $score += 20;
        if ($length >= 12) $score += 10;
        if ($length >= 16) $score += 10;
        
        // Character variety
        if (preg_match('/[a-z]/', $password)) $score += 15;
        if (preg_match('/[A-Z]/', $password)) $score += 15;
        if (preg_match('/[0-9]/', $password)) $score += 15;
        if (preg_match('/[^a-zA-Z0-9]/', $password)) $score += 15;
        
        return min(100, $score);
    }
    
    /**
     * Get strength label.
     */
    public static function getStrengthLabel(int $score): string
    {
        return match(true) {
            $score >= 80 => 'strong',
            $score >= 60 => 'good',
            $score >= 40 => 'medium',
            $score >= 20 => 'weak',
            default => 'very_weak',
        };
    }
}
