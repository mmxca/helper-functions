<?php

namespace HelperFunctions;

/**
 * HelperFunctions\HelperFunctions.
 *
 * Provides repeatable use functions that should have been part of PHP but weren't
 *
 * @author  David Hoffman
 *
 * @version 0.0.1
 */
class HelperFunctions
{
    /**
     * Turns a multiword string into a single word.
     *
     * @param string $string The String you want to slugify
     * @param string $joiner (optional) The string to concatinate with, default is -
     */
    public static function slugify($string, $joiner = '-')
    {
        return strtolower(
            str_replace(' ', $joiner, str_replace('  ', ' ', str_replace(',', '', str_replace('.', '', str_replace('&', '', ucwords(strtolower(str_replace('_', ' ', $string))))))))
        );
    }

    /**
     * Turns a multiword string into a single word hashtag.
     *
     * @param string $string The String you want to hashtagify
     */
    public static function hashtagify($string)
    {
        return '#'.str_replace(' ', '', str_replace(',', '', str_replace('.', '', str_replace('&', '', ucwords(strtolower(str_replace('_', ' ', $string)))))));
    }

    public static function buildHashtagArray($array_in)
    {
        $outArray = [];
        foreach ($array_in as $string) {
            if ('' != trim($string)) {
                $outArray[] = HelperFunctions::hashtagify($string);
            }
        }

        $outArray = array_unique($outArray);
        sort($outArray);

        return implode(' ', $outArray);
    }

    public static function calculateDiscountPrice($retailPrice, $discount, $discount_type)
    {
        $discounted_price = 0;

        if ('%' == $discount_type) {
            $discounted_price = $retailPrice * ((100 - $discount) / 100);
        } else {
            $discounted_price = $retailPrice - $discount;
        }

        return HelperFunctions::format_money('%.2n', $discounted_price);
    }

    public static function formatDiscount($discount, $discount_type)
    {
        $formatted_discount = '';

        if ('%' == $discount_type) {
            $formatted_discount = ($discount + 0).'%';
        } else {
            $formatted_discount = '$'.$discount;
        }

        return $formatted_discount;
    }

    public static function format_money($format, $number)
    {
        if (function_exists('money_format')) {
            return money_format($format, $number);
        } else {
            $regex = '/%((?:[\^!\-]|\+|\(|\=.)*)([0-9]+)?'.
                '(?:#([0-9]+))?(?:\.([0-9]+))?([in%])/';
            if ('C' == setlocale(LC_MONETARY, 0)) {
                setlocale(LC_MONETARY, '');
            }
            $locale = localeconv();
            preg_match_all($regex, $format, $matches, PREG_SET_ORDER);
            foreach ($matches as $fmatch) {
                $value = floatval($number);
                $flags = array(
                    'fillchar' => preg_match('/\=(.)/', $fmatch[1], $match) ?
                        $match[1] : ' ',
                    'nogroup' => preg_match('/\^/', $fmatch[1]) > 0,
                    'usesignal' => preg_match('/\+|\(/', $fmatch[1], $match) ?
                        $match[0] : '+',
                    'nosimbol' => preg_match('/\!/', $fmatch[1]) > 0,
                    'isleft' => preg_match('/\-/', $fmatch[1]) > 0,
                );
                $width = trim($fmatch[2]) ? (int) $fmatch[2] : 0;
                $left = trim($fmatch[3]) ? (int) $fmatch[3] : 0;
                $right = trim($fmatch[4]) ? (int) $fmatch[4] : $locale['int_frac_digits'];
                $conversion = $fmatch[5];

                $positive = true;
                if ($value < 0) {
                    $positive = false;
                    $value *= -1;
                }
                $letter = $positive ? 'p' : 'n';

                $prefix = $suffix = $cprefix = $csuffix = $signal = '';

                $signal = $positive ? $locale['positive_sign'] : $locale['negative_sign'];
                switch (true) {
                    case 1 == $locale["{$letter}_sign_posn"] && '+' == $flags['usesignal']:
                        $prefix = $signal;
                        break;
                    case 2 == $locale["{$letter}_sign_posn"] && '+' == $flags['usesignal']:
                        $suffix = $signal;
                        break;
                    case 3 == $locale["{$letter}_sign_posn"] && '+' == $flags['usesignal']:
                        $cprefix = $signal;
                        break;
                    case 4 == $locale["{$letter}_sign_posn"] && '+' == $flags['usesignal']:
                        $csuffix = $signal;
                        break;
                    case '(' == $flags['usesignal']:
                    case 0 == $locale["{$letter}_sign_posn"]:
                        $prefix = '(';
                        $suffix = ')';
                        break;
                }
                if (!$flags['nosimbol']) {
                    $currency = $cprefix.
                        ('i' == $conversion ? $locale['int_curr_symbol'] : $locale['currency_symbol']).
                        $csuffix;
                } else {
                    $currency = '';
                }
                $space = $locale["{$letter}_sep_by_space"] ? ' ' : '';

                $value = number_format(
                    $value,

                    $right,

                    $locale['mon_decimal_point'],
                    $flags['nogroup'] ? '' : $locale['mon_thousands_sep']
                );
                $value = @explode($locale['mon_decimal_point'], $value);

                $n = strlen($prefix) + strlen($currency) + strlen($value[0]);
                if ($left > 0 && $left > $n) {
                    $value[0] = str_repeat($flags['fillchar'], $left - $n).$value[0];
                }
                $value = implode($locale['mon_decimal_point'], $value);
                if ($locale["{$letter}_cs_precedes"]) {
                    $value = $prefix.$currency.$space.$value.$suffix;
                } else {
                    $value = $prefix.$value.$space.$currency.$suffix;
                }
                if ($width > 0) {
                    $value = str_pad($value, $width, $flags['fillchar'], $flags['isleft'] ?
                        STR_PAD_RIGHT : STR_PAD_LEFT);
                }

                $format = str_replace($fmatch[0], $value, $format);
            }

            return $format;
        }
    }
}
