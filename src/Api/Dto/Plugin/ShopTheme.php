<?php

declare(strict_types=1);

namespace Comfino\Api\Dto\Plugin;

final class ShopTheme
{
    /**
     * @param string $code Raw platform theme identifier (e.g. 'Hyva_Theme')
     * @param string $family Normalized SDK profile ('hyva'|'luma'|'blank'|'classic'|'storefront'|'custom')
     * @param string[] $parents Parent theme inheritance chain, root-most first
     * @param bool|null $isPwa Whether the theme is a Progressive Web App; null when unknown
     */
    public function __construct(
        public readonly string $code,
        public readonly string $family,
        public readonly array $parents = [],
        public readonly ?bool $isPwa = null
    ) {
    }
}
