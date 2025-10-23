<?php

namespace Comfino\Api;

use Comfino\Api\Dto\Payment\LoanQueryCriteria;
use Comfino\Api\Dto\Payment\LoanTypeEnum;
use Comfino\Api\Exception\AccessDenied;
use Comfino\Api\Exception\AuthorizationError;
use Comfino\Api\Exception\RequestValidationError;
use Comfino\Api\Exception\ResponseValidationError;
use Comfino\Api\Exception\ServiceUnavailable;
use Comfino\Api\Request\CancelOrder as CancelOrderRequest;
use Comfino\Api\Request\CreateOrder as CreateOrderRequest;
use Comfino\Api\Request\GetFinancialProductDetails as GetFinancialProductDetailsRequest;
use Comfino\Api\Request\GetFinancialProducts as GetFinancialProductsRequest;
use Comfino\Api\Request\GetOrder as GetOrderRequest;
use Comfino\Api\Request\GetPaywall as GetPaywallRequest;
use Comfino\Api\Request\GetPaywallItemDetails as GetPaywallItemDetailsRequest;
use Comfino\Api\Request\GetProductTypes as GetProductTypesRequest;
use Comfino\Api\Request\GetWidgetKey as GetWidgetKeyRequest;
use Comfino\Api\Request\GetWidgetTypes as GetWidgetTypesRequest;
use Comfino\Api\Request\IsShopAccountActive as IsShopAccountActiveRequest;
use Comfino\Api\Response\Base as BaseApiResponse;
use Comfino\Api\Response\CreateOrder as CreateOrderResponse;
use Comfino\Api\Response\GetFinancialProductDetails as GetFinancialProductDetailsResponse;
use Comfino\Api\Response\GetFinancialProducts as GetFinancialProductsResponse;
use Comfino\Api\Response\GetOrder as GetOrderResponse;
use Comfino\Api\Response\GetPaywall as GetPaywallResponse;
use Comfino\Api\Response\GetPaywallItemDetails as GetPaywallItemDetailsResponse;
use Comfino\Api\Response\GetProductTypes as GetProductTypesResponse;
use Comfino\Api\Response\GetWidgetKey as GetWidgetKeyResponse;
use Comfino\Api\Response\GetWidgetTypes as GetWidgetTypesResponse;
use Comfino\Api\Response\IsShopAccountActive as IsShopAccountActiveResponse;
use Comfino\Api\Response\ValidateOrder as ValidateOrderResponse;
use Comfino\Api\Serializer\Json as JsonSerializer;
use Comfino\FinancialProduct\ProductTypesListTypeEnum;
use Comfino\Shop\Order\CartInterface;
use Comfino\Shop\Order\OrderInterface;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * Comfino API client.
 *
 * @version 1.1.0
 * @author Artur Kozubski <akozubski@comperia.pl>
 */
class Client
{
    public const CLIENT_VERSION = '1.1.0';
    public const PRODUCTION_HOST = 'https://api-ecommerce.comfino.pl';
    public const SANDBOX_HOST = 'https://api-ecommerce.craty.pl';

    /** @var string */
    protected string $apiLanguage = 'pl';
    /** @var string */
    protected string $apiCurrency = 'PLN';
    /** @var string|null */
    protected ?string $customApiHost = null;
    /** @var string|null */
    protected ?string $customUserAgent = null;
    /** @var string[] */
    protected array $customHeaders = [];
    /** @var string */
    protected string $clientHostName = '';
    /** @var bool */
    protected bool $isSandboxMode = false;
    /** @var Request|null */
    protected ?Request $request = null;
    /** @var ResponseInterface|null */
    protected ?ResponseInterface $response = null;

    /**
     * Comfino API client.
     *
     * @param RequestFactoryInterface $requestFactory External PSR-18 compatible HTTP request factory.
     * @param StreamFactoryInterface $streamFactory External PSR-18 compatible stream factory.
     * @param ClientInterface $client External PSR-18 compatible HTTP client which will be used to communicate with the API.
     * @param string|null $apiKey Unique authentication key required for access to the Comfino API.
     * @param int $apiVersion Selected default API version (default: v1).
     * @param SerializerInterface|null $serializer JSON serializer.
     */
    public function __construct(
        protected readonly RequestFactoryInterface $requestFactory,
        protected readonly StreamFactoryInterface $streamFactory,
        protected ClientInterface $client,
        protected ?string $apiKey,
        protected int $apiVersion = 1,
        protected ?SerializerInterface $serializer = null ?? new JsonSerializer()
    ) { }

    /**
     * Sets custom request/response serializer.
     *
     * @param SerializerInterface $serializer
     *
     * @return void
     */
    public function setSerializer(SerializerInterface $serializer): void
    {
        $this->serializer = $serializer;
    }

    /**
     * Selects current API version.
     *
     * @param int $version Desired API version.
     *
     * @return void
     */
    public function setApiVersion(int $version): void
    {
        $this->apiVersion = $version;
    }

    /**
     * Returns current API key.
     *
     * @return string
     */
    public function getApiKey(): string
    {
        return $this->apiKey ?? '';
    }

    /**
     * Sets current API key.
     *
     * @param string $apiKey API key.
     *
     * @return void
     */
    public function setApiKey(string $apiKey): void
    {
        $this->apiKey = $apiKey;
    }

    /**
     * Returns current API language.
     *
     * @return string Language code (eg: pl, en)
     */
    public function getApiLanguage(): string
    {
        return $this->apiLanguage;
    }

    /**
     * Selects current API language.
     *
     * @param string $language Language code (eg: pl, en).
     *
     * @return void
     */
    public function setApiLanguage(string $language): void
    {
        $this->apiLanguage = $language;
    }

    /**
     * Returns current API currency.
     *
     * @return string Currency code (eg: PLN, USD, EUR, GBP).
     */
    public function getApiCurrency(): string
    {
        return $this->apiCurrency;
    }

    /**
     * Selects current API currency.
     *
     * @param string $apiCurrency Currency code (eg: PLN, USD, EUR, GBP).
     *
     * @return void
     */
    public function setApiCurrency(string $apiCurrency): void
    {
        $this->apiCurrency = $apiCurrency;
    }

    /**
     * Returns current API host.
     *
     * @return string
     */
    public function getApiHost(): string
    {
        return $this->customApiHost ?? ($this->isSandboxMode ? self::SANDBOX_HOST : self::PRODUCTION_HOST);
    }

    /**
     * Sets custom API host.
     *
     * @param string|null $host Custom API host.
     *
     * @return void
     */
    public function setCustomApiHost(?string $host): void
    {
        $this->customApiHost = $host;
    }

    /**
     * Sets custom User-Agent header.
     *
     * @param string|null $userAgent
     *
     * @return void
     */
    public function setCustomUserAgent(?string $userAgent): void
    {
        $this->customUserAgent = $userAgent;
    }

    /**
     * Adds a custom HTTP header to the API request call.
     *
     * @param string $headerName
     * @param string $headerValue
     *
     * @return void
     */
    public function addCustomHeader(string $headerName, string $headerValue): void
    {
        $this->customHeaders[$headerName] = $headerValue;
    }

    /**
     * Sets client host name.
     *
     * @param string $host
     *
     * @return void
     */
    public function setClientHostName(string $host): void
    {
        if (($filteredHost = filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) === false)) {
            $filteredHost = gethostname();
        }

        $this->clientHostName = $filteredHost !== false ? $filteredHost : '';
    }

    public function enableSandboxMode(): void
    {
        $this->isSandboxMode = true;
    }

    public function disableSandboxMode(): void
    {
        $this->isSandboxMode = false;
    }

    public function setClient(ClientInterface $client): void
    {
        $this->client = $client;
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return self::CLIENT_VERSION;
    }

    /**
     * Returns last API request.
     *
     * @return Request|null
     */
    public function getRequest(): ?Request
    {
        return $this->request;
    }

    /**
     * Checks if registered user shop account is active.
     *
     * @param string|null $cacheInvalidateUrl Integrated platform API endpoint for local cache invalidation.
     * @param string|null $configurationUrl Integrated platform API endpoint for local configuration management.
     *
     * @return bool
     *
     * @throws RequestValidationError
     * @throws ResponseValidationError
     * @throws AuthorizationError
     * @throws AccessDenied
     * @throws ServiceUnavailable
     * @throws ClientExceptionInterface
     */
    public function isShopAccountActive(?string $cacheInvalidateUrl = null, ?string $configurationUrl = null): bool
    {
        $this->request = (new IsShopAccountActiveRequest($cacheInvalidateUrl, $configurationUrl))->setSerializer($this->serializer);

        return (new IsShopAccountActiveResponse($this->request, $this->sendRequest($this->request), $this->serializer))->isActive;
    }

    /**
     * Returns a list of financial products according to the specified criteria and calculations result based on passed cart contents.
     *
     * @param LoanQueryCriteria $queryCriteria
     * @param CartInterface $cart
     *
     * @return GetFinancialProductDetailsResponse
     *
     * @throws RequestValidationError
     * @throws ResponseValidationError
     * @throws AuthorizationError
     * @throws AccessDenied
     * @throws ServiceUnavailable
     * @throws ClientExceptionInterface
     */
    public function getFinancialProductDetails(LoanQueryCriteria $queryCriteria, CartInterface $cart): GetFinancialProductDetailsResponse
    {
        $this->request = (new GetFinancialProductDetailsRequest($queryCriteria, $cart))->setSerializer($this->serializer);

        return new GetFinancialProductDetailsResponse($this->request, $this->sendRequest($this->request), $this->serializer);
    }

    /**
     * Returns a list of financial products according to the specified criteria.
     *
     * @param LoanQueryCriteria $queryCriteria
     *
     * @return GetFinancialProductsResponse
     *
     * @throws RequestValidationError
     * @throws ResponseValidationError
     * @throws AuthorizationError
     * @throws AccessDenied
     * @throws ServiceUnavailable
     * @throws ClientExceptionInterface
     */
    public function getFinancialProducts(LoanQueryCriteria $queryCriteria): GetFinancialProductsResponse
    {
        $this->request = (new GetFinancialProductsRequest($queryCriteria))->setSerializer($this->serializer);

        return new GetFinancialProductsResponse($this->request, $this->sendRequest($this->request), $this->serializer);
    }

    /**
     * Submits a loan application request.
     *
     * @param OrderInterface $order Full order data (cart, loan details).
     *
     * @return CreateOrderResponse
     *
     * @throws RequestValidationError
     * @throws ResponseValidationError
     * @throws AuthorizationError
     * @throws AccessDenied
     * @throws ServiceUnavailable
     * @throws ClientExceptionInterface
     */
    public function createOrder(OrderInterface $order): CreateOrderResponse
    {
        $this->request = (new CreateOrderRequest($order))->setSerializer($this->serializer);

        return new CreateOrderResponse($this->request, $this->sendRequest($this->request), $this->serializer);
    }

    /**
     * Validates loan application request data.
     *
     * @param OrderInterface $order
     *
     * @return ValidateOrderResponse
     *
     * @throws ClientExceptionInterface
     */
    public function validateOrder(OrderInterface $order): ValidateOrderResponse
    {
        try {
            $this->request = (new CreateOrderRequest($order, true))->setSerializer($this->serializer);

            return new ValidateOrderResponse($this->request, $this->sendRequest($this->request), $this->serializer);
        } catch (RequestValidationError $e) {
            return new ValidateOrderResponse($this->request, $e->getResponse(), $this->serializer, $e);
        }
    }

    /**
     * Returns a details of specified loan application.
     *
     * @param string $orderId Loan application ID returned by createOrder action.
     *
     * @return GetOrderResponse
     *
     * @throws RequestValidationError
     * @throws ResponseValidationError
     * @throws AuthorizationError
     * @throws AccessDenied
     * @throws ServiceUnavailable
     * @throws ClientExceptionInterface
     */
    public function getOrder(string $orderId): GetOrderResponse
    {
        $this->request = (new GetOrderRequest($orderId))->setSerializer($this->serializer);

        return new GetOrderResponse($this->request, $this->sendRequest($this->request), $this->serializer);
    }

    /**
     * Cancels a loan application.
     *
     * @param string $orderId Loan application ID returned by createOrder action.
     * @throws RequestValidationError
     * @throws ResponseValidationError
     * @throws AuthorizationError
     * @throws AccessDenied
     * @throws ServiceUnavailable
     * @throws ClientExceptionInterface
     */
    public function cancelOrder(string $orderId): void
    {
        $this->request = (new CancelOrderRequest($orderId))->setSerializer($this->serializer);

        new BaseApiResponse($this->request, $this->sendRequest($this->request), $this->serializer);
    }

    /**
     * Returns a list of available financial product types associated with an authorized shop account.
     *
     * @throws RequestValidationError
     * @throws ResponseValidationError
     * @throws AuthorizationError
     * @throws AccessDenied
     * @throws ServiceUnavailable
     * @throws ClientExceptionInterface
     */
    public function getProductTypes(ProductTypesListTypeEnum $listType): GetProductTypesResponse
    {
        $this->request = (new GetProductTypesRequest($listType))->setSerializer($this->serializer);

        return new GetProductTypesResponse($this->request, $this->sendRequest($this->request), $this->serializer);
    }

    /**
     * Returns a widget key associated with an authorized shop account.
     *
     * @throws RequestValidationError
     * @throws ResponseValidationError
     * @throws AuthorizationError
     * @throws AccessDenied
     * @throws ServiceUnavailable
     * @throws ClientExceptionInterface
     */
    public function getWidgetKey(): string
    {
        $this->request = (new GetWidgetKeyRequest())->setSerializer($this->serializer);

        return (new GetWidgetKeyResponse($this->request, $this->sendRequest($this->request), $this->serializer))->widgetKey;
    }

    /**
     * Returns a list of available widget types associated with an authorized shop account.
     *
     * @param bool $useNewApi Whether to use a new widget type and new API endpoint.
     *
     * @throws RequestValidationError
     * @throws ResponseValidationError
     * @throws AuthorizationError
     * @throws AccessDenied
     * @throws ServiceUnavailable
     * @throws ClientExceptionInterface
     */
    public function getWidgetTypes(bool $useNewApi = true): GetWidgetTypesResponse
    {
        $this->request = (new GetWidgetTypesRequest($useNewApi))->setSerializer($this->serializer);

        return new GetWidgetTypesResponse($this->request, $this->sendRequest($this->request), $this->serializer);
    }

    /**
     * Returns a complete paywall page with list of financial products according to the specified criteria.
     *
     * @param LoanQueryCriteria $queryCriteria List filtering criteria.
     * @param string|null $recalculationUrl Paywall form action URL used for offer recalculations initialized by shop cart frontends.
     *
     * @return GetPaywallResponse
     *
     * @throws RequestValidationError
     * @throws ResponseValidationError
     * @throws AuthorizationError
     * @throws AccessDenied
     * @throws ServiceUnavailable
     * @throws ClientExceptionInterface
     */
    public function getPaywall(LoanQueryCriteria $queryCriteria, ?string $recalculationUrl = null): GetPaywallResponse
    {
        $this->request = (new GetPaywallRequest($queryCriteria, $recalculationUrl))->setSerializer($this->serializer);

        return new GetPaywallResponse($this->request, $this->sendRequest($this->request, 2), $this->serializer);
    }

    /**
     * Returns a details of paywall item for specified financial product type (loan type) and shopping cart contents.
     *
     * @param int $loanAmount Requested loan amount.
     * @param LoanTypeEnum $loanType Financial product type (loan type).
     * @param CartInterface $cart Shopping cart.
     *
     * @return GetPaywallItemDetailsResponse
     *
     * @throws RequestValidationError
     * @throws ResponseValidationError
     * @throws AuthorizationError
     * @throws AccessDenied
     * @throws ServiceUnavailable
     * @throws ClientExceptionInterface
     */
    public function getPaywallItemDetails(int $loanAmount, LoanTypeEnum $loanType, CartInterface $cart): GetPaywallItemDetailsResponse
    {
        $this->request = (new GetPaywallItemDetailsRequest($loanAmount, $loanType, $cart))->setSerializer($this->serializer);

        return new GetPaywallItemDetailsResponse($this->request, $this->sendRequest($this->request), $this->serializer);
    }

    /**
     * @throws RequestValidationError
     * @throws ResponseValidationError
     * @throws ClientExceptionInterface
     */
    protected function sendRequest(Request $request, ?int $apiVersion = null): ResponseInterface
    {
        if (($trackId = !empty($this->clientHostName) ? $this->clientHostName : gethostname()) === false) {
            $trackId = 'trid-' . uniqid('', true);
        } else {
            $trackId .= ('-' . microtime(true));
        }

        $apiRequest = $request->getPsrRequest(
            $this->requestFactory,
            $this->streamFactory,
            $this->getApiHost(),
            $apiVersion ?? $this->apiVersion
        )
        ->withHeader('Content-Type', 'application/json')
        ->withHeader('Api-Language', $this->apiLanguage)
        ->withHeader('Api-Currency', $this->apiCurrency)
        ->withHeader('User-Agent', $this->getUserAgent())
        ->withHeader('Comfino-Track-Id', $trackId);

        if (count($this->customHeaders) > 0) {
            foreach ($this->customHeaders as $headerName => $headerValue) {
                $apiRequest = $apiRequest->withHeader($headerName, $headerValue);
            }
        }

        $this->response = $this->client->sendRequest(
            !empty($this->apiKey) ? $apiRequest->withHeader('Api-Key', $this->apiKey) : $apiRequest
        );

        return $this->response;
    }

    protected function getUserAgent(): string
    {
        return $this->customUserAgent ?? "Comfino API client {$this->getVersion()}";
    }
}
