<?php

/*
 * This file is part of the Idlab Helper.
 *
 * (c) Idlab - Michael Vetterli (michael@idlab.ch)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Idlab\HelperBundle;

use PhpMyAdmin\SqlParser\Parser;
use PhpMyAdmin\SqlParser\Statements\DeleteStatement;
use PhpMyAdmin\SqlParser\Statements\DropStatement;
use PhpMyAdmin\SqlParser\Statements\InsertStatement;
use PhpMyAdmin\SqlParser\Statements\ReplaceStatement;
use PhpMyAdmin\SqlParser\Statements\SetStatement;
use PhpMyAdmin\SqlParser\Statements\UpdateStatement;

abstract class StaticHelper
{
    /**
     * Cleans swiss and frech phone numbers.
     */
    public static function cleanPhoneNumber(null|string $number, $spaces = true, $country = 'CH'): null|string
    {
        if (null === $number || empty($number)) {
            return null;
        }
        $number = trim($number);
        $number = preg_replace('/[oO]/', '0', $number);
        $number = preg_replace('/\D/', '', $number);
        $number = preg_replace('/^00/', '+', $number);
        $number = preg_replace('/^410/', '+41', $number);
        $number = preg_replace('/^41/', '+41', $number);
        $number = preg_replace('/^33/', '+33', $number);
        $number = preg_replace('/^34/', '+34', $number);
        // switzerland ONLY, might break
        switch ($country) {
            case 'CH':
                $number = preg_replace('/^0/', '+41', $number);
                $number = preg_replace('/^([1-9]\d)/', '+41\1', $number);
                break;
        }

        if ($spaces) {
            // +41 ## ### ## ##
            if (preg_match('/^\+41/', $number)) {
                return substr($number, 0, 3).' '.substr($number, 3, 2).' '.substr($number, 5, 3).' '.substr($number, 8, 2).' '.substr($number, 10, 2);
            }
            // +33 # ## ## ## ##
            if (preg_match('/^\+33/', $number)) {
                return substr($number, 0, 3).' '.substr($number, 3, 1).' '.substr($number, 4, 2).' '.substr($number, 6, 2).' '.substr($number, 8, 2).' '.substr($number, 10, 2);
            }
            // +34 ### ### ###
            if (preg_match('/^\+34/', $number)) {
                return substr($number, 0, 3).' '.substr($number, 3, 3).' '.substr($number, 6, 3).' '.substr($number, 9, 3);
            }
        }

        return $number;
    }

    /**
     * Checks if a swiss social secutity number is valid (it can fix it or anotate wrong numbers as well).
     */
    public static function cleanNAVS13(null|string|int $NAVS13, $makeValid = false, $anontateErrorPrefix = null): ?string
    {
        if (null === $NAVS13) {
            return null;
        }
        $CleanNAVS13 = preg_replace('/[^0-9]/', '', $NAVS13);
        $match = preg_match('/(^756)(\d{4})(\d{4})(\d)(\d)$/', $CleanNAVS13, $matches);
        if ($match) {
            if (6 === \count($matches) && $matches[5] == self::checkSumEAN13($matches[1].$matches[2].$matches[3].$matches[4])) {
                return $matches[1].'.'.$matches[2].'.'.$matches[3].'.'.$matches[4].$matches[5];
            } elseif (null == !$NAVS13 && $makeValid) {
                return $matches[1].'.'.$matches[2].'.'.$matches[3].'.'.$matches[4].self::checkSumEAN13($matches[1].$matches[2].$matches[3].$matches[4]);
            } elseif (null == !$NAVS13 && null == !$anontateErrorPrefix) {
                return $anontateErrorPrefix.$NAVS13;
            }
        }

        return null;
    }

    /**
     * EAN13 checksum calculator.
     */
    private static function checkSumEAN13(string $digits): string
    {
        // 1. Add the values of the digits in the even-numbered positions: 2, 4, 6, etc.
        $even_sum = (int) $digits[1] + (int) $digits[3] + (int) $digits[5] + (int) $digits[7] + (int) $digits[9] + (int) $digits[11];
        // 2. Multiply this result by 3.
        $even_sum_three = $even_sum * 3;
        // 3. Add the values of the digits in the odd-numbered positions: 1, 3, 5, etc.
        $odd_sum = (int) $digits[0] + (int) $digits[2] + (int) $digits[4] + (int) $digits[6] + (int) $digits[8] + (int) $digits[10];
        // 4. Sum the results of steps 2 and 3.
        $total_sum = $even_sum_three + $odd_sum;
        // 5. The check character is the smallest number which, when added to the result in step 4,  produces a multiple of 10.
        $next_ten = ceil($total_sum / 10) * 10;

        return $next_ten - $total_sum;
    }

    /**
     * Check whether numbers in a pair of string match exactly.
     *
     * @param $a
     * @param $b
     */
    public static function areNumbersInStringSame($a, $b): bool
    {
        if ($a === $b) {
            return true;
        }
        if (!empty($a) && !empty($b)) {
            $a = (int) preg_replace('/[^0-9]/', '', $a);
            $b = (int) preg_replace('/[^0-9]/', '', $b);

            return $a === $b;
        }

        return false;
    }

    /**
     * Extracts swiss and french IBAN bumbers and formats them properly.
     */
    public static function extractIban(null|string $input, $format = 1): null|string
    {
        if (null === $input) {
            return null;
        }
        $input = preg_replace('/[^\w]/', '', $input);
        if (1 === $format) {
            $match = preg_match('/(CH)(\d{2})(\w{5})(\w{12})/', $input, $matches);
            if ($match && 5 === \count($matches)) {
                return $matches[1].$matches[2].' '.$matches[3].' '.$matches[4];
            }
            $match = preg_match('/(FR)(\d{2})(\w{5})(\w{5})(\w{11})(\w{2})/', $input, $matches);
            if ($match && 7 === \count($matches)) {
                return $matches[1].$matches[2].' '.$matches[3].' '.$matches[4].' '.$matches[5].' '.$matches[6];
            }
        }
        if (2 === $format) {
            $match = preg_match('/(CH)(\d{2})(\w{4})(\w{4})(\w{4})(\w{4})(\w{1})/', $input, $matches);
            if ($match && 8 === \count($matches)) {
                return $matches[1].$matches[2].' '.$matches[3].' '.$matches[4].' '.$matches[5].' '.$matches[6].' '.$matches[7];
            }
            $match = preg_match('/(FR)(\d{2})(\w{4})(\w{4})(\w{4})(\w{4})(\w{4})(\w{3})/', $input, $matches);
            if ($match && 9 === \count($matches)) {
                return $matches[1].$matches[2].' '.$matches[3].' '.$matches[4].' '.$matches[5].' '.$matches[6].' '.$matches[7].' '.$matches[8];
            }
        }

        return null;
    }

    /**
     * Checks whether a SQL uery is a SELECT statements only and not an update, insert, alter, etc. query.
     */
    public static function isQuerySelect(string $query): bool
    {
        $parser = new Parser($query);
        foreach ($parser->statements as $statement) {
            if (
                $statement instanceof InsertStatement ||
                $statement instanceof UpdateStatement ||
                $statement instanceof DeleteStatement ||
                $statement instanceof ReplaceStatement ||
                $statement instanceof DropStatement ||
                $statement instanceof SetStatement
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Removes all accents and diactritics in latin alphabet.
     */
    public static function removeAccents(null|string|array $str, $charset = 'utf-8'): array|string|null
    {
        if (null === $str) {
            return null;
        }
        if (class_exists('\Normalizer', $autoload = false)) {
            $str = \Normalizer::normalize($str);
        }
        // @see http://stackoverflow.com/questions/3635511/remove-diacritics-from-a-string
        $str = preg_replace('/[\x{0300}-\x{036f}]+/u', '', $str);
        // or $str = preg_replace('/\pM+/u', '', $str);

        // converti en HTML entities à -> &agrave;
        $str = htmlentities($str, \ENT_NOQUOTES, $charset);
        // remplacement des entitie par la premières lettre agrave -> a
        $str = preg_replace('#&([A-za-z])(?:acute|cedil|caron|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);
        // pour les ligatures e.g. '&oelig;'
        $str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str);
        // supprime les autres caractères les & et les ;
        $str = preg_replace('#&[^;]+;#', '', $str);

        return $str;
    }

    /**
     * Generates a clean slug for a string be removing accents, non leter or number caracters an replacing spaces by "_".
     */
    public static function slugify(string $input, string $separator = '_'): string
    {
        $output = preg_replace('/[^A-Za-z\d_-]/', '_', preg_replace('/[\(\)\{\}\|\[\]\%\&\*\!\?\#\"\\\'\/\\\]/', '', strtolower(self::removeAccents($input))));
        $output = preg_replace('/_+/', $separator, $output);

        return $output;
    }

    /**
     * Converts a camelCase tsring to a snake_caste string.
     */
    public static function camelCaseToSnakeCaseConverter(string $input)
    {
        return strtolower(preg_replace('/[A-Z]/', '_\\0', lcfirst($input)));
    }

    /**
     * Converts a snake_caste string to a camelCase string.
     */
    public static function snakeCaseToCamelCaseToConverter(string $input, bool $upperCamelCase = false)
    {
        $camelCasedName = preg_replace_callback('/(^|_|\.)+(.)/', function ($match) {
            return ('.' === $match[1] ? '_' : '').strtoupper($match[2]);
        }, $input);

        if (!$upperCamelCase) {
            $camelCasedName = lcfirst($camelCasedName);
        }

        return $camelCasedName;
    }

    /**
     * Gamble with chances of winning in %.
     *
     * @see https://stackoverflow.com/a/27823456/8194488
     */
    public static function chance(int $percent): int
    {
        return mt_rand(1, 100) < $percent;
    }

    /**
     * Sanitizes a string by removing accents and non leter or number caracters.
     */
    public static function sanitizeString(string $string): string
    {
        return preg_replace('/[^a-z\d]+/', '-', self::removeAccents(strtolower($string)));
    }
}
