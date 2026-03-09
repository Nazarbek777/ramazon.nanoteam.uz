<?php

namespace Tests\Unit;

use Tests\TestCase;

class BroadcastLinkParsingTest extends TestCase
{
    /**
     * Test parsing of various Telegram link formats.
     */
    public function test_telegram_link_parsing()
    {
        $links = [
            'https://t.me/attestatsiya_jamoa/918' => ['@attestatsiya_jamoa', '918'],
            'https://t.me/attestatsiya_jamoa/918?start=123' => ['@attestatsiya_jamoa', '918'],
            'https://t.me/c/123456789/101' => ['-100123456789', '101'],
            't.me/username/55' => ['@username', '55'],
        ];

        foreach ($links as $link => $expected) {
            $cleanLink = preg_replace('/\?.*/', '', $link);
            $this->assertTrue((bool) preg_match('/t\.me\/(?:c\/)?([^\/]+)\/(\d+)/', $cleanLink, $matches), "Failed to match: $link");

            $fromChatIdRaw = $matches[1];
            $messageId = $matches[2];

            if (is_numeric($fromChatIdRaw)) {
                $fromChatId = (str_starts_with($fromChatIdRaw, '-100')) ? $fromChatIdRaw : ('-100' . $fromChatIdRaw);
            } else {
                $fromChatId = str_starts_with($fromChatIdRaw, '@') ? $fromChatIdRaw : ('@' . $fromChatIdRaw);
            }

            $this->assertEquals($expected[0], $fromChatId, "Chat ID mismatch for: $link");
            $this->assertEquals($expected[1], $messageId, "Message ID mismatch for: $link");
        }
    }
}
