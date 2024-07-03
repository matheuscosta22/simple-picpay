<?php

namespace App\Modules\Document\Services;

use Illuminate\Support\Str;

class ValidateDocumentService
{
    public function validate(string $documentNumber): bool
    {
        $documentNumber = numbersOnly($documentNumber);
        if (Str::length($documentNumber) == 11) {
            return $this->validateCpf($documentNumber);
        }

        if (Str::length($documentNumber) == 14) {
            return $this->validateCnpj($documentNumber);
        }

        return false;
    }

    public function validateCnpj(string $cnpj): bool
    {
        if (empty($cnpj)) {
            return false;
        }

        $cnpj = str_pad($cnpj, 14, 0, STR_PAD_LEFT);

        if (strlen($cnpj) !== 14) {
            return false;
        }

        $invalid_values = [
            '11111111111111',
            '22222222222222',
            '33333333333333',
            '44444444444444',
            '55555555555555',
            '66666666666666',
            '77777777777777',
            '88888888888888',
            '99999999999999',
        ];

        if (in_array($cnpj, $invalid_values)) {
            return false;
        }

        $j = 5;
        $k = 6;
        $sum_1 = 0;
        $sum_2 = 0;

        for ($i = 0; $i < 13; $i++) {
            $j = $j == 1 ? 9 : $j;
            $k = $k == 1 ? 9 : $k;

            $sum_2 += ($cnpj[$i] * $k);

            if ($i < 12) {
                $sum_1 += ($cnpj[$i] * $j);
            }

            $k--;
            $j--;
        }

        $first_digit = $sum_1 % 11 < 2 ? 0 : 11 - $sum_1 % 11;
        $second_digit = $sum_2 % 11 < 2 ? 0 : 11 - $sum_2 % 11;

        return (($cnpj[12] == $first_digit) and ($cnpj[13] == $second_digit));
    }

    public function validateCpf(string $cpf): bool
    {
        $length = strlen($cpf);

        if ($length !== 11) {
            return false;
        }

        $invalid_values = [
            '00000000000',
            '11111111111',
            '22222222222',
            '33333333333',
            '44444444444',
            '55555555555',
            '66666666666',
            '77777777777',
            '88888888888',
            '99999999999',
        ];

        if (in_array($cpf, $invalid_values)) {
            return false;
        }

        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;

            if ($cpf[$c] != $d) {
                return false;
            }
        }

        return true;
    }
}
