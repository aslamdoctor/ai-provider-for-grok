<?php

declare(strict_types=1);

namespace Aslam\GrokAiProvider\Provider;

use WordPress\AiClient\AiClient;
use WordPress\AiClient\Common\Exception\RuntimeException;
use WordPress\AiClient\Providers\ApiBasedImplementation\AbstractApiProvider;
use WordPress\AiClient\Providers\ApiBasedImplementation\ListModelsApiBasedProviderAvailability;
use WordPress\AiClient\Providers\Contracts\ModelMetadataDirectoryInterface;
use WordPress\AiClient\Providers\Contracts\ProviderAvailabilityInterface;
use WordPress\AiClient\Providers\DTO\ProviderMetadata;
use WordPress\AiClient\Providers\Enums\ProviderTypeEnum;
use WordPress\AiClient\Providers\Http\Enums\RequestAuthenticationMethod;
use WordPress\AiClient\Providers\Models\Contracts\ModelInterface;
use WordPress\AiClient\Providers\Models\DTO\ModelMetadata;
use Aslam\GrokAiProvider\Metadata\GrokModelMetadataDirectory;
use Aslam\GrokAiProvider\Models\GrokImageGenerationModel;
use Aslam\GrokAiProvider\Models\GrokTextGenerationModel;

/**
 * Class for AI Provider for Grok.
 *
 * @since 1.0.0
 */
class GrokProvider extends AbstractApiProvider
{
    /**
     * {@inheritDoc}
     *
     * @since 1.0.0
     */
    protected static function baseUrl(): string
    {
        return 'https://api.x.ai/v1';
    }

    /**
     * {@inheritDoc}
     *
     * @since 1.0.0
     */
    protected static function createModel(
        ModelMetadata $modelMetadata,
        ProviderMetadata $providerMetadata
    ): ModelInterface {
        $capabilities = $modelMetadata->getSupportedCapabilities();
        foreach ($capabilities as $capability) {
            if ($capability->isTextGeneration()) {
                return new GrokTextGenerationModel($modelMetadata, $providerMetadata);
            }
            if ($capability->isImageGeneration()) {
                return new GrokImageGenerationModel($modelMetadata, $providerMetadata);
            }
        }

        throw new RuntimeException(
            'Unsupported model capabilities: ' . implode( ', ', $capabilities )
        );
    }

    /**
     * {@inheritDoc}
     *
     * @since 1.0.0
     */
    protected static function createProviderMetadata(): ProviderMetadata
    {
        $providerMetadataArgs = [
            'grok',
            'Grok (xAI)',
            ProviderTypeEnum::cloud(),
            'https://console.x.ai/',
        ];
        // Authentication method support was added in 0.4.0.
        if (class_exists(RequestAuthenticationMethod::class)) {
            $providerMetadataArgs[] = RequestAuthenticationMethod::apiKey();
        }
        // Provider description support was added in 1.2.0.
        if (defined(AiClient::class . '::VERSION') && version_compare(AiClient::VERSION, '1.2.0', '>=')) {
            if (function_exists('__')) {
                $providerMetadataArgs[] = __('Text and image generation with Grok models by xAI.', 'aslams-provider-for-grok-ai');
            } else {
                $providerMetadataArgs[] = 'Text and image generation with Grok models by xAI.';
            }

            // Provider logo support was added in 1.3.0.
            if (version_compare(AiClient::VERSION, '1.3.0', '>=')) {
                $providerMetadataArgs[] = dirname(__DIR__, 2) . '/assets/logo.svg';
            }
        }
        return new ProviderMetadata(...$providerMetadataArgs);
    }

    /**
     * {@inheritDoc}
     *
     * @since 1.0.0
     */
    protected static function createProviderAvailability(): ProviderAvailabilityInterface
    {
        // Check valid API access by attempting to list models.
        return new ListModelsApiBasedProviderAvailability(
            static::modelMetadataDirectory()
        );
    }

    /**
     * {@inheritDoc}
     *
     * @since 1.0.0
     */
    protected static function createModelMetadataDirectory(): ModelMetadataDirectoryInterface
    {
        return new GrokModelMetadataDirectory();
    }
}
