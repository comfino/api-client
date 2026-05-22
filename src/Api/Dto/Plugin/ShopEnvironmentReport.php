<?php

declare(strict_types=1);

namespace Comfino\Api\Dto\Plugin;

final class ShopEnvironmentReport
{
    /**
     * @param string $platform Platform identifier (e.g. 'magento', 'prestashop', 'woocommerce', 'shopware', 'shopify')
     * @param string $platformName Human-readable platform name (e.g. 'Magento', 'PrestaShop', 'WooCommerce')
     * @param string $platformVersion Exact platform version string (e.g. '2.4.8-p4')
     * @param string|null $platformEdition Edition when applicable (e.g. 'community', 'enterprise', 'cloud')
     * @param string $platformDomain Shop hostname
     * @param string $pluginVersion Comfino plugin / module version
     * @param ShopTheme $theme Theme metadata
     * @param string $language Shop default locale (e.g. 'pl', 'pl-PL')
     * @param string $currency Shop default currency (e.g. 'PLN')
     * @param array<string, bool> $capabilities Best-effort framework hints
     * @param string|null $testProductUrl Optional URL of an active product page the API may crawl for auto-selector detection
     * @param array<string, mixed> $meta Escape hatch for plugin- or platform-specific metadata
     */
    public function __construct(
        public string $platform,
        public string $platformName,
        public string $platformVersion,
        public ?string $platformEdition,
        public string $platformDomain,
        public string $pluginVersion,
        public ShopTheme $theme,
        public string $language,
        public string $currency,
        public array $capabilities = [],
        public ?string $testProductUrl = null,
        public array $meta = []
    ) {
    }
}
