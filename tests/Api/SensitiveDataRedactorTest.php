<?php

declare(strict_types=1);

namespace Comfino\Tests\Api;

use Comfino\Api\SensitiveDataRedactor;
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
}
