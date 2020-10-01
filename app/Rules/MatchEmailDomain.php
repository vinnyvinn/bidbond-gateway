<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class MatchEmailDomain implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $allowedEmailDomains = explode(',', config('domain.email_domains'));
        return in_array(explode('@', $value)[1], $allowedEmailDomains);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute does not match allowed domains ' . config('domain.email_domains');
    }
}
