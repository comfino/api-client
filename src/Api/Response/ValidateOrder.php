<?php

namespace Comfino\Api\Response;

use Comfino\Api\Exception\RequestValidationError;
use Comfino\Api\HttpErrorExceptionInterface;
use Comfino\Api\Request;
use Comfino\Api\SerializerInterface;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Message\ResponseInterface;

class ValidateOrder extends CreateOrder
{
    /** @var string Unique track ID associated with every API request. */
    public readonly string $trackId;
    /** @var bool Success flag. */
    public readonly bool $success;
    /** @var int HTTP status code. */
    public readonly int $httpStatusCode;
    /** @var string[] List of validation errors as pairs [fieldName => errorMessage]. */
    public readonly array $errors;
    /** @var bool Low level network error. */
    public readonly bool $isNetworkError;
    /** @var int Error code. */
    public readonly int $errorCode;

    /**
     * @inheritDoc
     */
    public function __construct(Request $request, ResponseInterface $response, SerializerInterface $serializer, ?\Throwable $exception = null)
    {
        parent::__construct($request, $response, $serializer, $exception);

        $this->trackId = ($this->headers['Comfino-Track-Id'] ?? '');
        $this->success = ($exception === null);

        $httpStatusCode = $response->getStatusCode();
        $errors = [];
        $isNetworkError = false;
        $errorCode = 0;

        if ($exception !== null) {
            // We have an API error.
            if ($exception instanceof HttpErrorExceptionInterface) {
                // API logic and communication errors.
                $httpStatusCode = $exception->getStatusCode();

                if ($exception instanceof RequestValidationError) {
                    // We have a request validation error.
                    if (is_array($deserializedResponseBody = $exception->getDeserializedResponseBody())) {
                        // Special case for CreateOrder validation errors.
                        if (isset($deserializedResponseBody['errors'])) {
                            $errors = $deserializedResponseBody['errors'];
                        } elseif (isset($deserializedResponseBody['message'])) {
                            $errors = [$deserializedResponseBody['message']];
                        } elseif ($exception->getCode() >= 400) {
                            $errors = $deserializedResponseBody;
                        } else {
                            $errors = [$exception->getMessage()];
                        }
                    } else {
                        $errors = [$exception->getMessage()];
                    }
                }
            } elseif ($exception instanceof ClientExceptionInterface) {
                // Other HTTP client errors.
                $errors = [$exception->getMessage()];
                $errorCode = $exception->getCode();

                if ($exception instanceof NetworkExceptionInterface) {
                    // Low level network errors.
                    $isNetworkError = true;
                }
            }
        }

        $this->httpStatusCode = $httpStatusCode;
        $this->errors = $errors;
        $this->isNetworkError = $isNetworkError;
        $this->errorCode = $errorCode;
    }

/*    protected function processResponseBody(array|string|bool|null|float|int $deserializedResponseBody): void
    {
        if ($this->success) {
            parent::processResponseBody($deserializedResponseBody);
        }
    }*/
}
