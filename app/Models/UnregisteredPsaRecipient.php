<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnregisteredPsaRecipient extends Model
{
    protected $fillable = ['psa_id', 'email', 'full_name', 'first_name', 'middle_name', 'id_card_path', 'last_name', 'chapter', 'status', 'sent_at', 'error'];

    /** @return array{first_name: ?string, middle_name: ?string, last_name: ?string} */
    public static function splitName(?string $fullName): array
    {
        $name = trim((string) $fullName);
        $name = preg_replace('/,\s*(?:JR\.?|SR\.?|II|III|IV|V)\s*$/iu', '', $name) ?? $name;
        $parts = preg_split('/\s+/u', $name, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if ($parts === []) {
            return ['first_name' => null, 'middle_name' => null, 'last_name' => null];
        }

        $firstName = array_shift($parts);
        $lastName = array_pop($parts) ?? $firstName;

        return [
            'first_name' => $firstName,
            'middle_name' => $parts === [] ? null : implode(' ', $parts),
            'last_name' => mb_convert_case(mb_strtolower($lastName), MB_CASE_TITLE, 'UTF-8'),
        ];
    }

    protected function casts(): array
    {
        return ['sent_at' => 'datetime'];
    }
}
