<?php

declare(strict_types=1);

namespace Comfino\Api\Response;

class GetUserSettings extends Base
{
    /** @var array<string, array<string, mixed>> Flag attributes keyed by flag name. */
    public readonly array $flags;

    /**
     * @inheritDoc
     */
    protected function processResponseBody(array|string|bool|null|float|int $deserializedResponseBody): void
    {
        $this->checkResponseType($deserializedResponseBody, 'array');
        $this->checkResponseStructure($deserializedResponseBody, ['flags']);
        $this->checkResponseType($deserializedResponseBody['flags'], 'array', 'flags');

        $flags = [];

        foreach ($deserializedResponseBody['flags'] as $flag) {
            $this->checkResponseType($flag, 'array', 'flags[]');
            $this->checkResponseStructure($flag, ['name', 'attributes']);
            $this->checkResponseType($flag['name'], 'string', 'flags[][name]');
            $this->checkResponseType($flag['attributes'], 'array', 'flags[][attributes]');

            $flags[$flag['name']] = $flag['attributes'];
        }

        $this->flags = $flags;
    }

    public function hasFlag(string $flag): bool
    {
        return array_key_exists($flag, $this->flags);
    }

    /**
     * @return array<string, mixed>
     */
    public function getFlagAttributes(string $flag): array
    {
        return $this->flags[$flag] ?? [];
    }
}
