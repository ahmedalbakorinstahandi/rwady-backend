<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\PhoneNumberFormat;

class PhoneValidation implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value)) {
            return;
        }

        try {
            $phoneUtil = PhoneNumberUtil::getInstance();
            
            // Try to parse the phone number
            $phoneNumber = $phoneUtil->parse($value, 'IQ'); // Default to Iraq
            
            // Check if the number is valid
            if (!$phoneUtil->isValidNumber($phoneNumber)) {
                $fail('The :attribute must be a valid phone number.');
                return;
            }

            // Check if the number is for Iraq (country code 964)
            if ($phoneNumber->getCountryCode() != 964) {
                $fail('The :attribute must be a valid Iraqi phone number.');
                return;
            }

            // Check if the number starts with 7 (Iraqi mobile numbers)
            $nationalNumber = $phoneNumber->getNationalNumber();
            if (!preg_match('/^7[0-9]{8}$/', $nationalNumber)) {
                $fail('The :attribute must be a valid Iraqi mobile number starting with 7.');
                return;
            }

        } catch (\Exception $e) {
            $fail('The :attribute must be a valid phone number.');
        }
    }
}
