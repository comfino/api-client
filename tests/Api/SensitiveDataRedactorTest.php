<?php

declare(strict_types=1);

namespace Comfino\Tests\Api;

use Comfino\Api\SensitiveDataRedactor;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class SensitiveDataRedactorTest extends TestCase
{
    public function testRedactTextMasksEmail(): void
    {
        $result = SensitiveDataRedactor::redactText('Contact john.doe@example.com for details.');

        $this->assertStringNotContainsString('john.doe@example.com', $result);
        $this->assertStringContainsString('***REDACTED***', $result);
    }

    public function testRedactTextMasksPhoneWithSpaces(): void
    {
        $result = SensitiveDataRedactor::redactText('Call +48 600 100 200 now.');

        $this->assertStringNotContainsString('600 100 200', $result);
        $this->assertStringContainsString('***REDACTED***', $result);
    }

    public function testRedactTextMasksPhoneWithDashes(): void
    {
        $result = SensitiveDataRedactor::redactText('Number: 600-100-200.');

        $this->assertStringNotContainsString('600-100-200', $result);
        $this->assertStringContainsString('***REDACTED***', $result);
    }

    public function testRedactTextMasksIpv4(): void
    {
        $result = SensitiveDataRedactor::redactText('Client connected from 192.168.1.100 successfully.');

        $this->assertStringNotContainsString('192.168.1.100', $result);
        $this->assertStringContainsString('***REDACTED***', $result);
    }

    public function testRedactTextMasksIpv6(): void
    {
        $result = SensitiveDataRedactor::redactText('Client connected from 2001:0db8:85a3:0000:0000:8a2e:0370:7334.');

        $this->assertStringNotContainsString('2001:0db8:85a3:0000:0000:8a2e:0370:7334', $result);
        $this->assertStringContainsString('***REDACTED***', $result);
    }

    public function testRedactTextKeepsHtmlTagsAndMasksEmbeddedEmail(): void
    {
        $result = SensitiveDataRedactor::redactText('<p>Reach us at support@example.com</p>');

        $this->assertStringContainsString('<p>', $result);
        $this->assertStringContainsString('</p>', $result);
        $this->assertStringNotContainsString('support@example.com', $result);
    }

    public function testRedactTextEmptyString(): void
    {
        $this->assertSame('', SensitiveDataRedactor::redactText(''));
    }

    public function testRedactStructureShippingAndDescriptionSurvive(): void
    {
        $redacted = SensitiveDataRedactor::redactStructure([
            'shipping' => 'DHL',
            'description' => 'Order note',
            'recipient' => 'John',
        ]);

        $this->assertSame('DHL', $redacted['shipping']);
        $this->assertSame('Order note', $redacted['description']);
        $this->assertSame('John', $redacted['recipient']);
    }

    public function testRedactStructureMasksIpTokenVariants(): void
    {
        $redacted = SensitiveDataRedactor::redactStructure([
            'ip' => '1.2.3.4',
            'client_ip' => '1.2.3.4',
            'ipAddress' => '1.2.3.4',
        ]);

        $this->assertSame('***REDACTED***', $redacted['ip']);
        $this->assertSame('***REDACTED***', $redacted['client_ip']);
        $this->assertSame('***REDACTED***', $redacted['ipAddress']);
    }

    public function testRedactStructureMasksApartmentNumber(): void
    {
        /* The wire field name is 'apartmentNumber' (Api\Dto\Order\Customer\Address, Api\Request\CreateOrder);
           'flatNumber'/'flat_number' were already covered, but the actual key used elsewhere in this codebase was not. */
        $redacted = SensitiveDataRedactor::redactStructure([
            'apartmentNumber' => '12A',
            'apartment_number' => '12A',
        ]);

        $this->assertSame('***REDACTED***', $redacted['apartmentNumber']);
        $this->assertSame('***REDACTED***', $redacted['apartment_number']);
    }

    public function testRedactPayloadOnHtmlBodyKeepsTagsAndMasksEmail(): void
    {
        $html = '<html><body>Error for user@example.com</body></html>';
        $result = SensitiveDataRedactor::redactPayload($html);

        $this->assertStringContainsString('<html>', $result);
        $this->assertStringNotContainsString('user@example.com', $result);
    }

    public function testRedactPayloadOnJsonUsesStructuralRedaction(): void
    {
        $result = SensitiveDataRedactor::redactPayload(json_encode(['email' => 'a@b.com', 'city' => 'Warsaw']));

        $decoded = json_decode($result, true);

        $this->assertSame('***REDACTED***', $decoded['email']);
        $this->assertSame('***REDACTED***', $decoded['city']);
    }

    public function testTruncateNeverSplitsMultibyteCharacter(): void
    {
        // 'ę' is a 2-byte UTF-8 character (0xC4 0x99).
        $text = str_repeat('a', 9) . 'ę';

        $truncated = SensitiveDataRedactor::truncate($text, 10);

        $this->assertSame(str_repeat('a', 9), $truncated);
        $this->assertTrue(mb_check_encoding($truncated, 'UTF-8'));
    }

    public function testTruncateKeepsShortTextUnchanged(): void
    {
        $this->assertSame('short', SensitiveDataRedactor::truncate('short', 100));
    }

    public function testRedactTextMasksAuthorizationHeaderValueToTheEndOfTheLine(): void
    {
        $result = SensitiveDataRedactor::redactText("POST /x HTTP/1.1\nAuthorization: Bearer abc.def-ghi_123\nAccept: */*");

        $this->assertStringNotContainsString('abc.def-ghi_123', $result);
        $this->assertStringContainsString('Authorization: ***REDACTED***', $result);
        $this->assertStringContainsString('Accept: */*', $result);
    }

    public function testRedactTextMasksCookieHeaders(): void
    {
        $result = SensitiveDataRedactor::redactText('Cookie: sid=abc123; theme=dark');

        $this->assertStringNotContainsString('abc123', $result);
        $this->assertStringNotContainsString('theme=dark', $result);
    }

    public function testRedactTextMasksABareBearerTokenAndAJwt(): void
    {
        $this->assertStringNotContainsString('xyz789token', SensitiveDataRedactor::redactText('failed, sent Bearer xyz789token'));
        $this->assertStringNotContainsString(
            'eyJhbGciOi.eyJzdWIiOi.c2ln',
            SensitiveDataRedactor::redactText('token was eyJhbGciOi.eyJzdWIiOi.c2ln here')
        );
    }

    public function testRedactTextMasksSecretQueryParametersButKeepsOthers(): void
    {
        $result = SensitiveDataRedactor::redactText('GET /v3/paywall?auth=SECRETVALUE&loanAmount=1000&loggingToken=LOGTOKEN');

        $this->assertStringNotContainsString('SECRETVALUE', $result);
        $this->assertStringNotContainsString('LOGTOKEN', $result);
        $this->assertStringContainsString('loanAmount=1000', $result);
    }

    public function testShortSensitiveWordsMatchWholeWordsOfAKeyOnly(): void
    {
        $result = SensitiveDataRedactor::redactStructure([
            'capacity' => 'keep',
            'electricity' => 'keep',
            'snippet' => 'keep',
            'city' => 'Warszawa',
            'billing_city' => 'Warszawa',
            'billingCity' => 'Warszawa',
            'nip' => '1234567890',
            'company_nip' => '1234567890',
            'companyNip' => '1234567890',
        ]);

        foreach (['capacity', 'electricity', 'snippet'] as $key) {
            $this->assertSame('keep', $result[$key], $key);
        }

        foreach (['city', 'billing_city', 'billingCity', 'nip', 'company_nip', 'companyNip'] as $key) {
            $this->assertSame('***REDACTED***', $result[$key], $key);
        }
    }

    /**
     * @return array<string, array{string}>
     */
    public static function textKeptUnchangedProvider(): array
    {
        return [
            'report timestamp, ISO date + time' => ['Orders submitted: 3 since 2026-11-21 08:14:03 UTC'],
            'Polish date + time' => ['21-11-2026 08:14:03'],
            'Polish dotted date + time' => ['21.11.2026 08:14'],
            'ISO 8601 with milliseconds' => ['2026-11-21T08:14:03.123Z'],
            'ISO 8601 with offset' => ['2026-11-21T08:14:03+01:00'],
            'time alone' => ['12:30:00'],
            'time in a sentence' => ['started at 08:14:03, done'],
            'MAC address' => ['interface 00:1a:2b:3c:4d:5e'],
            'two colons, not an address' => ['a:b:c'],
        ];
    }

    #[DataProvider('textKeptUnchangedProvider')]
    public function testRedactTextKeepsDatesTimesAndNonAddresses(string $text): void
    {
        $this->assertSame($text, SensitiveDataRedactor::redactText($text));
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function ipv6Provider(): array
    {
        return [
            'compressed' => ['host 2001:db8::1 down', 'host ***REDACTED*** down'],
            'loopback' => ['bound to ::1.', 'bound to ***REDACTED***.'],
            'link-local' => ['fe80::1ff:fe23:4567:890a', '***REDACTED***'],
            'full eight groups' => ['2001:0db8:85a3:0000:0000:8a2e:0370:7334', '***REDACTED***'],
            'IPv4-mapped' => ['from ::ffff:192.0.2.1 x', 'from ***REDACTED*** x'],
        ];
    }

    #[DataProvider('ipv6Provider')]
    public function testRedactTextMasksWholeIpv6Addresses(string $text, string $expected): void
    {
        $this->assertSame($expected, SensitiveDataRedactor::redactText($text));
    }

    public function testRedactTextKeepsTheTimestampAndMasksPersonalDataInOneMessage(): void
    {
        $this->assertSame(
            'since 2026-11-21 08:14:03 UTC (***REDACTED***, ***REDACTED***)',
            SensitiveDataRedactor::redactText('since 2026-11-21 08:14:03 UTC (jan.kowalski@example.com, +48 600 700 800)')
        );
    }

    public function testRedactTextStillMasksAPhoneNumberAfterADate(): void
    {
        $this->assertSame('2026-11-21 ***REDACTED***', SensitiveDataRedactor::redactText('2026-11-21 600700800'));
    }

    public function testRedactTextMasksAPhoneShapedLikeAnInvalidDate(): void
    {
        $this->assertSame('call ***REDACTED*** now', SensitiveDataRedactor::redactText('call 12-34-5678 90 now'));
    }

    public function testRedactTextIsIdempotentOnTimestamps(): void
    {
        $text = 'since 2026-11-21 08:14:03 UTC, host 2001:db8::1';
        $once = SensitiveDataRedactor::redactText($text);

        $this->assertSame($once, SensitiveDataRedactor::redactText($once));
    }
}
