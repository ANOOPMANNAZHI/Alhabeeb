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

    /** Tags kept as they are (with allowed attributes). */
    const ALLOWED_TAGS = ['p', 'br', 'b', 'strong', 'i', 'em', 'u', 's', 'strike', 'span', 'div', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'ul', 'ol', 'li', 'a', 'img', 'table', 'thead', 'tbody', 'tfoot', 'tr', 'td', 'th', 'caption', 'blockquote', 'hr',
        'sub', 'sup', 'font', 'center', 'pre', 'code', 'small', 'big'];
    /** Tags removed together with everything inside them; any other tag is unwrapped (its text kept). */
    const DROPPED_TAGS = ['script', 'style', 'iframe', 'frame', 'frameset', 'object', 'embed', 'applet', 'form', 'input', 'button',
        'textarea', 'select', 'option', 'meta', 'link', 'base', 'svg', 'math', 'noscript', 'template', 'title', 'head'];
    const ALLOWED_ATTRS = ['href', 'src', 'alt', 'title', 'width', 'height', 'style', 'align', 'valign', 'colspan', 'rowspan',
        'target', 'border', 'cellpadding', 'cellspacing', 'color', 'face', 'size', 'bgcolor', 'dir', 'lang'];

    /**
     * Allow-list cleaner for editor HTML: unknown tags are unwrapped, dangerous
     * ones dropped with their content, attributes outside the list (all on*)
     * removed, and href/src kept only for http(s), mailto, tel, cid, relative
     * links, placeholders and (img only) data:image.
     */
    public static function cleanHtml($html)
    {
        $html = (string) $html;
        if (trim($html) === '') {
            return '';
        }
        $doc = new \DOMDocument();
        $prev = libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="utf-8"?><div id="mt-root">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($prev);

        $root = null;
        foreach ($doc->getElementsByTagName('div') as $div) {
            if ($div->getAttribute('id') === 'mt-root') { $root = $div; break; }
        }
        if (!$root) {
            return '';
        }
        self::cleanChildren($root);

        $out = '';
        foreach ($root->childNodes as $child) {
            $out .= $doc->saveHTML($child);
        }
        // libxml URL-encodes braces inside href/src; keep placeholders readable.
        return preg_replace('/%7B([A-Za-z0-9_]+)%7D/i', '{$1}', $out);
    }

    private static function cleanChildren(\DOMNode $parent)
    {
        $children = [];
        foreach ($parent->childNodes as $c) {
            $children[] = $c;
        }
        foreach ($children as $node) {
            if ($node instanceof \DOMComment || $node instanceof \DOMProcessingInstruction) {
                $parent->removeChild($node);
                continue;
            }
            if (!($node instanceof \DOMElement)) {
                continue;
            }
            $tag = strtolower($node->nodeName);
            if (in_array($tag, self::DROPPED_TAGS, true)) {
                $parent->removeChild($node);
                continue;
            }
            self::cleanChildren($node);
            if (!in_array($tag, self::ALLOWED_TAGS, true)) {
                while ($node->firstChild) {
                    $parent->insertBefore($node->firstChild, $node);
                }
                $parent->removeChild($node);
                continue;
            }
            $names = [];
            foreach ($node->attributes as $attr) {
                $names[] = $attr->nodeName;
            }
            foreach ($names as $name) {
                $lower = strtolower($name);
                $value = $node->getAttribute($name);
                if (!in_array($lower, self::ALLOWED_ATTRS, true)) {
                    $node->removeAttribute($name);
                } elseif (($lower === 'href' || $lower === 'src') && !self::safeUrl($value, $tag === 'img' && $lower === 'src')) {
                    $node->removeAttribute($name);
                    if ($lower === 'href') {
                        $node->setAttribute('href', '#');
                    }
                } elseif ($lower === 'style' && preg_match('/expression|url\s*\(|javascript|behavior|@import|-moz-binding/i', html_entity_decode($value))) {
                    $node->removeAttribute($name);
                }
            }
        }
    }

    private static function safeUrl($url, $allowDataImage)
    {
        $u = html_entity_decode((string) $url, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $u = strtolower(preg_replace('/[\x00-\x20\x7F]+/', '', $u));
        if (!preg_match('/^([a-z][a-z0-9+.\-]*):/', $u, $m)) {
            return true; // relative, #anchor or {placeholder}
        }
        if (in_array($m[1], ['http', 'https', 'mailto', 'tel', 'cid'], true)) {
            return true;
        }
        return $allowDataImage && preg_match('#^data:image/(png|jpe?g|gif|webp);#', $u);
    }

    /** True when editor HTML has no visible text and no image (e.g. "<p>&nbsp;</p>"). */
    public static function isBlankHtml($html)
    {
        $html = (string) $html;
        if (stripos($html, '<img') !== false) {
            return false;
        }
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = str_replace("\xC2\xA0", ' ', $text);
        return trim($text) === '';
    }
}
