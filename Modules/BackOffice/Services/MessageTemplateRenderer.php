<?php

namespace Modules\BackOffice\Services;

/**
 * Renders message templates. Placeholders ({name}) are replaced as text:
 * escaped in the email body, raw in the subject and SMS; template content is
 * never executed. Values are not re-expanded, so a value containing "{x}"
 * stays literal. Pure: no DB. PHP < 7.4: no arrow functions.
 */
class MessageTemplateRenderer
{
    const PLACEHOLDER = '/\{([A-Za-z0-9_]+)\}/';

    public static function fill($text, array $values, $escapeHtml, array &$unknown)
    {
        $lower = [];
        foreach ($values as $k => $v) {
            $lower[strtolower($k)] = $v;
        }
        return preg_replace_callback(self::PLACEHOLDER, function ($m) use ($lower, $escapeHtml, &$unknown) {
            $key = strtolower($m[1]);
            if (!array_key_exists($key, $lower)) {
                $unknown[$key] = true;
                return $m[0];
            }
            $value = (string) $lower[$key];
            return $escapeHtml ? htmlspecialchars($value, ENT_QUOTES, 'UTF-8') : $value;
        }, (string) $text);
    }

    public static function render($template, array $values)
    {
        $t = (object) $template;
        $unknown = [];
        $subject = isset($t->email_subject) && $t->email_subject !== '' ? $t->email_subject : null;
        $body    = isset($t->email_body) && $t->email_body !== '' ? $t->email_body : null;
        $sms     = isset($t->sms_body) && $t->sms_body !== '' ? $t->sms_body : null;

        $out = [
            'subject' => $subject === null ? null : trim(preg_replace('/\s*[\r\n]+\s*/', ' ', self::fill($subject, $values, false, $unknown))),
            'html'    => $body === null ? null : self::fill($body, $values, true, $unknown),
            'sms'     => $sms === null ? null : self::fill($sms, $values, false, $unknown),
        ];
        $keys = array_keys($unknown);
        sort($keys);
        $out['unknown'] = $keys;
        return $out;
    }

    public static function placeholdersIn($text)
    {
        preg_match_all(self::PLACEHOLDER, (string) $text, $m);
        $keys = array_values(array_unique(array_map('strtolower', $m[1])));
        sort($keys);
        return $keys;
    }

    public static function smsParts($text)
    {
        $text = (string) $text;
        $chars = function_exists('mb_strlen') ? mb_strlen($text, 'UTF-8') : strlen($text);
        $unicode = (bool) preg_match('/[^\x00-\x7F]/', $text);
        if ($chars === 0) {
            return ['chars' => 0, 'unicode' => $unicode, 'parts' => 0];
        }
        $single = $unicode ? 70 : 160;
        $multi  = $unicode ? 67 : 153;
        $parts = $chars <= $single ? 1 : (int) ceil($chars / $multi);
        return ['chars' => $chars, 'unicode' => $unicode, 'parts' => $parts];
    }

    /** Removes executable content from editor HTML. */
    public static function cleanHtml($html)
    {
        $html = (string) $html;
        $html = preg_replace('#<(script|style|iframe|object|embed)\b[^>]*>.*?</\1\s*>#is', '', $html);
        $html = preg_replace('#<(script|style|iframe|object|embed)\b[^>]*/?>#is', '', $html);
        $html = preg_replace('#\s+on[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)#i', '', $html);
        $html = preg_replace('#(href|src)\s*=\s*("|\')\s*javascript:[^"\']*\2#i', '$1="#"', $html);
        return $html;
    }
}
