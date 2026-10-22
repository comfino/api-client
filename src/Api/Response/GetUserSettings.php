<?php

declare(strict_types=1);

namespace Comfino\Api\Response;

class GetUserSettings extends Base
{
    /** @var array<string, mixed> Flag attributes keyed by flag name. */
    public readonly array $flags;

    /**
     * @inheritDoc
     */
    protected function processResponseBody(array|string|bool|null|float|int $deserializedResponseBody): void
    {
        $this->checkResponseType($deserializedResponseBody, 'array');
        $this->checkResponseStructure($deserializedResponseBody, ['flags']);
        $this->checkResponseType($deserializedResponseBody['flags'], 'array', 'flags');

        /* The structure of each flag belongs to the API and changes over time, so a flag value of any JSON type is
           accepted here; rejecting an unexpected shape would drop every other flag along with it. */
        $this->flags = $deserializedResponseBody['flags'];
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
        return is_array($this->flags[$flag] ?? null) ? $this->flags[$flag] : [];
    }
}
