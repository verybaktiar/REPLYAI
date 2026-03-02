<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Class NotDisposableEmail
 * 
 * Validation rule untuk memblokir disposable/temporary email addresses.
 * Melindungi sistem dari spam dan abuse.
 */
class NotDisposableEmail implements ValidationRule
{
    /**
     * List domain disposable email yang diblokir
     * 
     * @var array<string>
     */
    private const DISPOSABLE_DOMAINS = [
        // TempMail providers
        'tempmail.com',
        'temp-mail.org',
        'temp-mail.ru',
        'tempmailaddress.com',
        'tempinbox.com',
        'tempail.com',
        'temp-mail.io',
        'tempm.com',
        'tmpmail.org',
        'tmpeml.com',
        'tempmails.com',
        
        // 10MinuteMail
        '10minutemail.com',
        '10minutemail.net',
        '10minutemail.info',
        '10minutemail.org',
        '10minutemail.co.uk',
        '10minutemailbox.com',
        '10minute-mail.com',
        '10minemail.com',
        
        // GuerrillaMail
        'guerrillamail.com',
        'guerrillamail.net',
        'guerrillamail.org',
        'guerrillamail.biz',
        'guerrillamail.de',
        'sharklasers.com',
        'spam4.me',
        
        // Mailinator & YopMail
        'mailinator.com',
        'mailinator.net',
        'mailinator.org',
        'mailinator.info',
        'mailinator.xyz',
        'yopmail.com',
        'yopmail.fr',
        'yopmail.net',
        'cool.fr.nf',
        'jetable.fr.nf',
        'nospam.ze.tc',
        
        // ThrowAwayMail
        'throwawaymail.com',
        'throwawaymail.org',
        'throwawaymail.net',
        
        // FakeEmail
        'fakeemail.com',
        'fakeemail.org',
        'fakeemail.net',
        
        // GetAirMail
        'getairmail.com',
        'getairmail.org',
        'getairmail.net',
        
        // FakeMailGenerator
        'fakemailgenerator.com',
        'fakemail.net',
        'fakemail.org',
        
        // Mohmal
        'mohmal.com',
        'mohmal.in',
        'mohmal.im',
        
        // Other popular disposable
        'mailnesia.com',
        'tempmailo.com',
        'tempmail.ninja',
        'tempmail.plus',
        'tempmail.ws',
        'tempmail.wiki',
        'tempmail.best',
        'tempmailpro.com',
        'tempmailid.com',
        'tempmaillol.com',
        'tempmailgen.com',
        'tempmailer.net',
        'tempmailusa.com',
        'tempmails.co',
        'tempmailworld.com',
        'tempr.email',
        'disposable-email.com',
        'disposableemail.org',
        'emailtemporanea.net',
        'emailtemporario.com.br',
        'burnermail.io',
        'temporary-mail.net',
        'temporarymail.org',
        'temporaryemail.net',
        'temporaryemail.org',
    ];

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $domain = strtolower(substr(strrchr($value, '@'), 1));
        
        if (in_array($domain, self::DISPOSABLE_DOMAINS, true)) {
            $fail('Email :attribute menggunakan domain temporary/disposable yang tidak diizinkan. Silakan gunakan email permanen seperti Gmail, Yahoo, atau email bisnis Anda.');
        }
    }
}
