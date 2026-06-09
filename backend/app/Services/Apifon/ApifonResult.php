<?php

namespace App\Services\Apifon;

/**
 * Immutable value object summarising the outcome of one Apifon API call.
 */
class ApifonResult
{
    public function __construct(
        public readonly bool $success,
        public readonly array $requestPayload,
        public readonly ?array $responsePayload = null,
        public readonly ?int $httpStatus = null,
        public readonly ?int $durationMs = null,
        public readonly ?string $errorMessage = null,
    ) {
    }

    public static function success(
        array $requestPayload,
        ?array $responsePayload = null,
        ?int $httpStatus = null,
        ?int $durationMs = null,
    ): self {
        return new self(
            success: true,
            requestPayload: $requestPayload,
            responsePayload: $responsePayload,
            httpStatus: $httpStatus,
            durationMs: $durationMs,
        );
    }

    public static function failure(
        array $requestPayload,
        string $errorMessage,
        ?array $responsePayload = null,
        ?int $httpStatus = null,
        ?int $durationMs = null,
    ): self {
        return new self(
            success: false,
            requestPayload: $requestPayload,
            responsePayload: $responsePayload,
            httpStatus: $httpStatus,
            durationMs: $durationMs,
            errorMessage: $errorMessage,
        );
    }
}
