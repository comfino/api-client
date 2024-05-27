<?php

namespace Comfino\Api;

use Comfino\Api\Dto\Payment\LoanQueryCriteria;
use Comfino\Api\Exception\AccessDenied;
use Comfino\Api\Exception\AuthorizationError;
use Comfino\Api\Exception\RequestValidationError;
use Comfino\Api\Exception\ResponseValidationError;
use Comfino\Api\Exception\ServiceUnavailable;
use Comfino\Api\Request\CancelOrder as CancelOrderRequest;
use Comfino\Api\Request\CreateOrder as CreateOrderRequest;
use Comfino\Api\Request\GetFinancialProducts as GetFinancialProductsRequest;
use Comfino\Api\Request\GetOrder as GetOrderRequest;
use Comfino\Api\Request\GetPaywall as GetPaywallRequest;
use Comfino\Api\Request\GetPaywallFragments as GetPaywallFragmentsRequest;
use Comfino\Api\Request\GetProductTypes as GetProductTypesRequest;
use Comfino\Api\Request\GetWidgetKey as GetWidgetKeyRequest;
use Comfino\Api\Request\GetWidgetTypes as GetWidgetTypesRequest;
use Comfino\Api\Request\IsShopAccountActive as IsShopAccountActiveRequest;
use Comfino\Api\Response\Base as BaseApiResponse;
use Comfino\Api\Response\CreateOrder as CreateOrderResponse;
use Comfino\Api\Response\GetFinancialProducts as GetFinancialProductsResponse;
use Comfino\Api\Response\GetOrder as GetOrderResponse;
use Comfino\Api\Response\GetPaywall as GetPaywallResponse;
use Comfino\Api\Response\GetPaywallFragments as GetPaywallFragmentsResponse;
use Comfino\Api\Response\GetProductTypes as GetProductTypesResponse;
use Comfino\Api\Response\GetWidgetKey as GetWidgetKeyResponse;
use Comfino\Api\Response\GetWidgetTypes as GetWidgetTypesResponse;
use Comfino\Api\Response\IsShopAccountActive as IsShopAccountActiveResponse;
use Comfino\Api\Serializer\Json as JsonSerializer;
use Comfino\FinancialProduct\ProductTypesListTypeEnum;
use Comfino\Paywall\PaywallViewTypeEnum;
use Comfino\Shop\Order\OrderInterface;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * Comfino API client.
 *
 * @version 1.0
 * @author Artur Kozubski <akozubski@comperia.pl>
 */
class Client
{
    protected const CLIENT_VERSION = '1.0';
    protected const PRODUCTION_HOST = 'https://api-ecommerce.comfino.pl';
    protected const SANDBOX_HOST = 'https://api-ecommerce.ecraty.pl';

    /** @var string */
    protected string $apiLanguage = 'pl';
    /** @var string|null */
    protected ?string $customApiHost = null;
    /** @var string|null */
    protected ?string $customUserAgent = null;
    /** @var bool */
    protected bool $isSandboxMode = false;

    /**
     * Comfino API client.
     *
     * @param RequestFactoryInterface $requestFactory External PSR-18 compatible HTTP request factory
     * @param StreamFactoryInterface $streamFactory External PSR-18 compatible stream factory
     * @param ClientInterface $client External PSR-18 compatible HTTP client which will be used to communicate with the API
     * @param string|null $apiKey Unique authentication key required for access to the Comfino API
     * @param int $apiVersion Selected API version (default: v1)
     */
    public function __construct(
        protected readonly RequestFactoryInterface $requestFactory,
        protected readonly StreamFactoryInterface $streamFactory,
        protected readonly ClientInterface $client,
        protected readonly ?string $apiKey,
        protected int $apiVersion = 1,
        protected ?SerializerInterface $serializer = null
    ) {
        $this->serializer = $serializer ?? new JsonSerializer();
    }

    /**
     * Sets custom request/response serializer.
     *
     * @param SerializerInterface $serializer
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
        return $this->apiKey;
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
     * @param string $language Language code (eg: pl, en)
     * @return void
     */
    public function setApiLanguage(string $language): void
    {
        $this->apiLanguage = $language;
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
     * @param string|null $host Custom API host
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
     * @return void
     */
    public function setCustomUserAgent(?string $userAgent): void
    {
        $this->customUserAgent = $userAgent;
    }

    public function enableSandboxMode(): void
    {
        $this->isSandboxMode = true;
    }

    public function disableSandboxMode(): void
    {
        $this->isSandboxMode = false;
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return self::CLIENT_VERSION;
    }

    /**
     * Checks if registered user shop account is active.
     *
     * @return bool
     * @throws RequestValidationError
     * @throws ResponseValidationError
     * @throws AuthorizationError
     * @throws AccessDenied
     * @throws ServiceUnavailable
     * @throws ClientExceptionInterface
     */
    public function isShopAccountActive(): bool
    {
        try {
            $request = (new IsShopAccountActiveRequest())->setSerializer($this->serializer);

            return (new IsShopAccountActiveResponse($this->sendRequest($request), $this->serializer))->isActive;
        } catch (RequestValidationError | ResponseValidationError | AuthorizationError | AccessDenied | ServiceUnavailable $e) {
            if (isset($request)) {
                $e->setRequestBody($request->getRequestBody() ?? '');
            }

            throw $e;
        }
    }

    /**
     * Returns a list of financial products according to the specified criteria.
     *
     * @param LoanQueryCriteria $queryCriteria
     * @return GetFinancialProductsResponse
     * @throws RequestValidationError
     * @throws ResponseValidationError
     * @throws AuthorizationError
     * @throws AccessDenied
     * @throws ServiceUnavailable
     * @throws ClientExceptionInterface
     */
    public function getFinancialProducts(LoanQueryCriteria $queryCriteria): GetFinancialProductsResponse
    {
        try {
            $request = (new GetFinancialProductsRequest($queryCriteria))->setSerializer($this->serializer);

            return new GetFinancialProductsResponse($this->sendRequest($request), $this->serializer);
        } catch (RequestValidationError | ResponseValidationError | AuthorizationError | AccessDenied | ServiceUnavailable $e) {
            if (isset($request)) {
                $e->setRequestBody($request->getRequestBody() ?? '');
            }

            throw $e;
        }
    }

    /**
     * Submits a loan application.
     *
     * @param OrderInterface $order Full order data (cart, loan details)
     * @return CreateOrderResponse
     * @throws RequestValidationError
     * @throws ResponseValidationError
     * @throws AuthorizationError
     * @throws AccessDenied
     * @throws ServiceUnavailable
     * @throws ClientExceptionInterface
     */
    public function createOrder(OrderInterface $order): CreateOrderResponse
    {
        try {
            $request = (new CreateOrderRequest($order))->setSerializer($this->serializer);

            return new CreateOrderResponse($this->sendRequest($request), $this->serializer);
        } catch (RequestValidationError | ResponseValidationError | AuthorizationError | AccessDenied | ServiceUnavailable $e) {
            if (isset($request)) {
                $e->setRequestBody($request->getRequestBody() ?? '');
            }

            throw $e;
        }
    }

    /**
     * Returns a details of specified loan application.
     *
     * @param string $orderId Loan application ID returned by createOrder action
     * @return GetOrderResponse
     * @throws RequestValidationError
     * @throws ResponseValidationError
     * @throws AuthorizationError
     * @throws AccessDenied
     * @throws ServiceUnavailable
     * @throws ClientExceptionInterface
     */
    public function getOrder(string $orderId): GetOrderResponse
    {
        try {
            $request = (new GetOrderRequest($orderId))->setSerializer($this->serializer);

            return new GetOrderResponse($this->sendRequest($request), $this->serializer);
        } catch (RequestValidationError | ResponseValidationError | AuthorizationError | AccessDenied | ServiceUnavailable $e) {
            if (isset($request)) {
                $e->setRequestBody($request->getRequestBody() ?? '');
            }

            throw $e;
        }
    }

    /**
     * Cancels a loan application.
     *
     * @param string $orderId Loan application ID returned by createOrder action
     * @throws RequestValidationError
     * @throws ResponseValidationError
     * @throws AuthorizationError
     * @throws AccessDenied
     * @throws ServiceUnavailable
     * @throws ClientExceptionInterface
     */
    public function cancelOrder(string $orderId): void
    {
        try {
            $request = (new CancelOrderRequest($orderId))->setSerializer($this->serializer);

            new BaseApiResponse($this->sendRequest($request), $this->serializer);
        } catch (RequestValidationError | ResponseValidationError | AuthorizationError | AccessDenied | ServiceUnavailable $e) {
            if (isset($request)) {
                $e->setRequestBody($request->getRequestBody() ?? '');
            }

            throw $e;
        }
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
        try {
            $request = (new GetProductTypesRequest($listType))->setSerializer($this->serializer);

            return new GetProductTypesResponse($this->sendRequest($request), $this->serializer);
        } catch (RequestValidationError | ResponseValidationError | AuthorizationError | AccessDenied | ServiceUnavailable $e) {
            if (isset($request)) {
                $e->setRequestBody($request->getRequestBody() ?? '');
            }

            throw $e;
        }
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
        try {
            $request = (new GetWidgetKeyRequest())->setSerializer($this->serializer);

            return (new GetWidgetKeyResponse($this->sendRequest($request), $this->serializer))->widgetKey;
        } catch (RequestValidationError | ResponseValidationError | AuthorizationError | AccessDenied | ServiceUnavailable $e) {
            if (isset($request)) {
                $e->setRequestBody($request->getRequestBody() ?? '');
            }

            throw $e;
        }
    }

    /**
     * Returns a list of available widget types associated with an authorized shop account.
     *
     * @throws RequestValidationError
     * @throws ResponseValidationError
     * @throws AuthorizationError
     * @throws AccessDenied
     * @throws ServiceUnavailable
     * @throws ClientExceptionInterface
     */
    public function getWidgetTypes(): GetWidgetTypesResponse
    {
        try {
            $request = (new GetWidgetTypesRequest())->setSerializer($this->serializer);

            return new GetWidgetTypesResponse($this->sendRequest($request), $this->serializer);
        } catch (RequestValidationError | ResponseValidationError | AuthorizationError | AccessDenied | ServiceUnavailable $e) {
            if (isset($request)) {
                $e->setRequestBody($request->getRequestBody() ?? '');
            }

            throw $e;
        }
    }

    /**
     * Returns a complete paywall page with list of financial products according to the specified criteria.
     *
     * @param LoanQueryCriteria $queryCriteria
     * @param PaywallViewTypeEnum|null $viewType
     * @return GetPaywallResponse
     * @throws RequestValidationError
     * @throws ResponseValidationError
     * @throws AuthorizationError
     * @throws AccessDenied
     * @throws ServiceUnavailable
     * @throws ClientExceptionInterface
     */
    public function getPaywall(LoanQueryCriteria $queryCriteria, ?PaywallViewTypeEnum $viewType = null): GetPaywallResponse
    {
        try {
            $request = (new GetPaywallRequest($queryCriteria, $viewType))->setSerializer($this->serializer);

            return new GetPaywallResponse($this->sendRequest($request), $this->serializer);
        } catch (RequestValidationError | ResponseValidationError | AuthorizationError | AccessDenied | ServiceUnavailable $e) {
            if (isset($request)) {
                $e->setRequestBody($request->getRequestBody() ?? '');
            }

            throw $e;
        }
    }

    /**
     * Returns a list of paywall fragments.
     *
     * @throws RequestValidationError
     * @throws ResponseValidationError
     * @throws AuthorizationError
     * @throws AccessDenied
     * @throws ServiceUnavailable
     * @throws ClientExceptionInterface
     */
    public function getPaywallFragments(): GetPaywallFragmentsResponse
    {
        try {
            $request = (new GetPaywallFragmentsRequest())->setSerializer($this->serializer);

            return new GetPaywallFragmentsResponse($this->sendRequest($request), $this->serializer);
        } catch (RequestValidationError | ResponseValidationError | AuthorizationError | AccessDenied | ServiceUnavailable $e) {
            if (isset($request)) {
                $e->setRequestBody($request->getRequestBody() ?? '');
            }

            throw $e;
        }
    }

    /**
     * @throws RequestValidationError
     * @throws ResponseValidationError
     * @throws ClientExceptionInterface
     */
    protected function sendRequest(Request $request): ResponseInterface
    {
        $apiRequest = $request->getPsrRequest(
            $this->requestFactory,
            $this->streamFactory,
            $this->getApiHost(),
            $this->apiVersion
        )->withHeader('Content-Type', 'application/json')
            ->withHeader('Api-Language', $this->apiLanguage)
            ->withHeader('User-Agent', $this->getUserAgent()
        );

        return $this->client->sendRequest(
            !empty($this->apiKey) ? $apiRequest->withHeader('Api-Key', $this->apiKey) : $apiRequest
        );
    }

    protected function getUserAgent(): string
    {
        return $this->customUserAgent ?? "Comfino API client {$this->getVersion()}";
    }
}
