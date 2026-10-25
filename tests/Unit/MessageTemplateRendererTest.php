<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Modules\BackOffice\Services\MessageTemplateRenderer as R;

class MessageTemplateRendererTest extends TestCase
{
    public function test_fills_placeholders_and_reports_unknown()
    {
        $t = (object) ['email_subject' => "Rent due for {unit}\n", 'email_body' => '<p>Dear {tenant_name}, pay {amount}.</p>', 'sms_body' => 'Dear {tenant_name}: {nope}'];
        $r = R::render($t, ['tenant_name' => 'Ali', 'unit' => 'S 03']);
        $this->assertSame('Rent due for S 03', $r['subject']);
        $this->assertSame('<p>Dear Ali, pay {amount}.</p>', $r['html']);
        $this->assertSame('Dear Ali: {nope}', $r['sms']);
        $this->assertSame(['amount', 'nope'], $r['unknown']);
    }

    public function test_values_are_escaped_in_html_and_never_re_expanded()
    {
        $t = (object) ['email_subject' => '{tenant_name}', 'email_body' => '<b>{tenant_name}</b>', 'sms_body' => '{tenant_name}'];
        $r = R::render($t, ['tenant_name' => 'A & B <x> {rent}', 'rent' => '999']);
        $this->assertSame('<b>A &amp; B &lt;x&gt; {rent}</b>', $r['html']);
        $this->assertSame('A & B <x> {rent}', $r['sms']);
        $this->assertSame('A & B <x> {rent}', $r['subject']);
        $this->assertSame([], $r['unknown']);
    }

    public function test_missing_channels_render_as_null()
    {
        $r = R::render((object) ['email_subject' => null, 'email_body' => null, 'sms_body' => 'Hi'], []);
        $this->assertNull($r['subject']);
        $this->assertNull($r['html']);
        $this->assertSame('Hi', $r['sms']);
    }

    public function test_placeholder_names_are_case_insensitive()
    {
        $unknown = [];
        $this->assertSame('Hi Ali', R::fill('Hi {Tenant_Name}', ['tenant_name' => 'Ali'], false, $unknown));
    }

    public function test_sms_parts_gsm_and_unicode()
    {
        $this->assertSame(['chars' => 0, 'unicode' => false, 'parts' => 0], R::smsParts(''));
        $this->assertSame(1, R::smsParts(str_repeat('a', 160))['parts']);
        $this->assertSame(2, R::smsParts(str_repeat('a', 161))['parts']);
        $this->assertSame(3, R::smsParts(str_repeat('a', 307))['parts']);
        $ar = R::smsParts(str_repeat('ب', 70));
        $this->assertSame(['chars' => 70, 'unicode' => true, 'parts' => 1], $ar);
        $this->assertSame(2, R::smsParts(str_repeat('ب', 71))['parts']);
    }

    public function test_clean_html_strips_scripts_and_handlers()
    {
        $dirty = '<p onclick="x()">Hi<script>alert(1)</script></p><a href="javascript:bad()">l</a><iframe src="x"></iframe><style>p{}</style><img src="a.png" onerror="y()">';
        $clean = R::cleanHtml($dirty);
        $this->assertSame('<p>Hi</p><a href="#">l</a><img src="a.png">', $clean);
    }

    /** Inputs that got past the first regex cleaner (final review I1). */
    public function test_clean_html_resists_bypasses()
    {
        $cases = [
            '<scr<script>ipt>alert(1)</scr<script>ipt>',
            '<svg/onload=alert(1)>',
            '<img src="x"onerror="alert(1)">',
            '<a href=javascript:alert(1)>x</a>',
            "<a href=\"java\tscript:alert(1)\">x</a>",
            '<a href="jav&#x61;script:alert(1)">x</a>',
            "<a href='javascript:alert(\"x\")'>x</a>",
            '<form><button formaction="javascript:alert(1)">x</button></form>',
            '<meta http-equiv="refresh" content="0;url=javascript:alert(1)">',
            '<IMG SRC=x ONERROR=alert(1)>',
            '<p style="background:url(javascript:alert(1))">x</p>',
        ];
        foreach ($cases as $dirty) {
            $clean = R::cleanHtml($dirty);
            $this->assertDoesNotMatchRegularExpression('/<script|<svg|<form|<button|<meta|\son[a-z]+\s*=|javascript:|formaction/i', $clean, $dirty . ' => ' . $clean);
        }
    }

    public function test_clean_html_keeps_normal_content()
    {
        $this->assertSame('<p>Pay online = easy</p>', R::cleanHtml('<p>Pay online = easy</p>'));
        $this->assertSame('<p dir="rtl">مرحبا {tenant_name}</p>', R::cleanHtml('<p dir="rtl">مرحبا {tenant_name}</p>'));
        $this->assertSame('<p><a href="https://alhabib.om/pay?a=1&amp;b=2" target="_blank">Pay</a> <a href="{link}">here</a></p>',
            R::cleanHtml('<p><a href="https://alhabib.om/pay?a=1&amp;b=2" target="_blank">Pay</a> <a href="{link}">here</a></p>'));
        $this->assertSame('<table border="1"><tbody><tr><td style="color: red;"><strong>A</strong></td></tr></tbody></table>',
            R::cleanHtml('<table border="1"><tbody><tr><td style="color: red;"><strong>A</strong></td></tr></tbody></table>'));
        $this->assertSame('', R::cleanHtml(''));
    }

    public function test_blank_html()
    {
        foreach (['', '   ', '<p></p>', '<p><br></p>', '<p>&nbsp;</p>', "<p>\xC2\xA0 </p>", null] as $blank) {
            $this->assertTrue(R::isBlankHtml($blank), var_export($blank, true));
        }
        $this->assertFalse(R::isBlankHtml('<p>Hi</p>'));
        $this->assertFalse(R::isBlankHtml('<p><img src="logo.png"></p>'));
    }

    public function test_blocks_are_raw_in_body_and_empty_elsewhere()
    {
        $t = (object) ['email_subject' => 'Report {details}', 'email_body' => '<p>{tenant_name}</p>{details}', 'sms_body' => 'See {details}email'];
        $r = R::render($t, ['tenant_name' => 'A<b>'], ['details' => '<table><tr><td>{rent}</td></tr></table>']);
        $this->assertSame('<p>A&lt;b&gt;</p><table><tr><td>{rent}</td></tr></table>', $r['html']);
        $this->assertSame('Report', $r['subject']);
        $this->assertSame('See email', $r['sms']);
        $this->assertSame([], $r['unknown']); // {rent} inside a block is neither expanded nor reported
    }

    public function test_placeholders_in_text()
    {
        $this->assertSame(['rent', 'tenant_name'], R::placeholdersIn('{tenant_name} owes {rent}; {tenant_name}'));
    }
}
