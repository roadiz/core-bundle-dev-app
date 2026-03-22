<?php

declare(strict_types=1);

namespace RZ\Roadiz\Random;

class PasswordGenerator extends RandomGenerator implements PasswordGeneratorInterface
{
    /**
     * Generates a strong password of N length containing at least one lower case letter,
     * one uppercase letter, one digit, and one special character. The remaining characters
     * in the password are chosen at random from those four sets.
     *
     * The available characters in each set are user-friendly - there are no ambiguous
     * characters such as i, l, 1, o, 0, etc.
     *
     * @see https://gist.github.com/tylerhall/521810
     */
    #[\Override]
    public function generatePassword(int $length = 16): string
    {
        $sets = [];
        $sets[] = 'abcdefghjkmnpqrstuvwxyz';
        $sets[] = 'ABCDEFGHJKMNPQRSTUVWXYZ';
        $sets[] = '23456789';
        $sets[] = '!@#$%&*?-';

        $all = '';
        $password = '';
        foreach ($sets as $set) {
            $chars = \mb_str_split($set);
            $password .= $chars[random_int(0, count($chars) - 1)];
            $all .= $set;
        }

        $all = \mb_str_split($all);
        for ($i = 0; $i < $length - count($sets); ++$i) {
            $password .= $all[random_int(0, count($all) - 1)];
        }

        // Fisher-Yates shuffle using CSPRNG — str_shuffle() uses Mersenne Twister
        $chars = \mb_str_split($password);
        for ($i = count($chars) - 1; $i > 0; --$i) {
            $j = random_int(0, $i);
            [$chars[$i], $chars[$j]] = [$chars[$j], $chars[$i]];
        }

        return implode('', $chars);
    }
}
