<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StrongPassword implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $config = config('admin.password_policy');
        
        // Minimum length
        if (strlen($value) < $config['min_length']) {
            $fail("Password harus minimal {$config['min_length']} karakter.");
            return;
        }
        
        // Require uppercase
        if ($config['require_uppercase'] && !preg_match('/[A-Z]/', $value)) {
            $fail('Password harus mengandung huruf besar (A-Z).');
            return;
        }
        
        // Require lowercase
        if ($config['require_lowercase'] && !preg_match('/[a-z]/', $value)) {
            $fail('Password harus mengandung huruf kecil (a-z).');
            return;
        }
        
        // Require numbers
        if ($config['require_numbers'] && !preg_match('/[0-9]/', $value)) {
            $fail('Password harus mengandung angka (0-9).');
            return;
        }
        
        // Require special characters
        if ($config['require_special'] && !preg_match('/[^a-zA-Z0-9]/', $value)) {
            $fail('Password harus mengandung karakter spesial (!@#$%^&* dll).');
            return;
        }
    }
    
    /**
     * Get password requirements description.
     */
    public static function requirements(): string
    {
        $config = config('admin.password_policy');
        $requirements = [];
        
        $requirements[] = "Minimal {$config['min_length']} karakter";
        
        if ($config['require_uppercase']) {
            $requirements[] = 'huruf besar (A-Z)';
        }
        if ($config['require_lowercase']) {
            $requirements[] = 'huruf kecil (a-z)';
        }
        if ($config['require_numbers']) {
            $requirements[] = 'angka (0-9)';
        }
        if ($config['require_special']) {
            $requirements[] = 'karakter spesial (!@#$%^&*)';
        }
        
        return 'Password harus mengandung: ' . implode(', ', $requirements);
    }
}
