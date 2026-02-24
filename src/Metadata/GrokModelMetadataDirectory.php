<?php

declare(strict_types=1);

namespace WordPress\GrokAiProvider\Metadata;

use WordPress\AiClient\Messages\Enums\ModalityEnum;
use WordPress\AiClient\Providers\Http\DTO\Request;
use WordPress\AiClient\Providers\Http\DTO\Response;
use WordPress\AiClient\Providers\Http\Enums\HttpMethodEnum;
use WordPress\AiClient\Providers\Http\Exception\ResponseException;
use WordPress\AiClient\Providers\Models\DTO\ModelMetadata;
use WordPress\AiClient\Providers\Models\DTO\SupportedOption;
use WordPress\AiClient\Providers\Models\Enums\CapabilityEnum;
use WordPress\AiClient\Providers\Models\Enums\OptionEnum;
use WordPress\AiClient\Providers\OpenAiCompatibleImplementation\AbstractOpenAiCompatibleModelMetadataDirectory;
use WordPress\GrokAiProvider\Provider\GrokProvider;

/**
 * Class for the Grok model metadata directory.
 *
 * @since 1.0.0
 *
 * @phpstan-type ModelsResponseData array{
 *     data: list<array{id: string}>
 * }
 */
class GrokModelMetadataDirectory extends AbstractOpenAiCompatibleModelMetadataDirectory
{
    /**
     * {@inheritDoc}
     *
     * @since 1.0.0
     */
    protected function createRequest(HttpMethodEnum $method, string $path, array $headers = [], $data = null): Request
    {
        return new Request(
            $method,
            GrokProvider::url($path),
            $headers,
            $data
        );
    }

    /**
     * {@inheritDoc}
     *
     * @since 1.0.0
     */
    protected function parseResponseToModelMetadataList(Response $response): array
    {
        /** @var ModelsResponseData $responseData */
        $responseData = $response->getData();
        if (!isset($responseData['data']) || !$responseData['data']) {
            throw ResponseException::fromMissingData('Grok', 'data');
        }

        $baseOptions = [
            new SupportedOption(OptionEnum::systemInstruction()),
            new SupportedOption(OptionEnum::candidateCount()),
            new SupportedOption(OptionEnum::maxTokens()),
            new SupportedOption(OptionEnum::temperature()),
            new SupportedOption(OptionEnum::topP()),
            new SupportedOption(OptionEnum::stopSequences()),
            new SupportedOption(OptionEnum::presencePenalty()),
            new SupportedOption(OptionEnum::frequencyPenalty()),
            new SupportedOption(OptionEnum::outputMimeType(), ['text/plain', 'application/json']),
            new SupportedOption(OptionEnum::functionDeclarations()),
            new SupportedOption(OptionEnum::customOptions()),
        ];

        $textOnlyOptions = array_merge($baseOptions, [
            new SupportedOption(OptionEnum::inputModalities(), [[ModalityEnum::text()]]),
            new SupportedOption(OptionEnum::outputModalities(), [[ModalityEnum::text()]]),
        ]);

        $visionOptions = array_merge($baseOptions, [
            new SupportedOption(
                OptionEnum::inputModalities(),
                [
                    [ModalityEnum::text()],
                    [ModalityEnum::text(), ModalityEnum::image()],
                ]
            ),
            new SupportedOption(OptionEnum::outputModalities(), [[ModalityEnum::text()]]),
        ]);

        $textCapabilities = [
            CapabilityEnum::textGeneration(),
            CapabilityEnum::chatHistory(),
        ];

        $modelsData = (array) $responseData['data'];

        $models = array_values(
            array_filter(
                array_map(
                    static function (array $modelData) use ($textCapabilities, $textOnlyOptions, $visionOptions): ?ModelMetadata {
                        $modelId = $modelData['id'];

                        // Skip image generation models (not supported yet).
                        if (str_contains($modelId, 'image') || str_contains($modelId, 'imagine')) {
                            return null;
                        }

                        // Vision models support image input.
                        if (str_contains($modelId, 'vision')) {
                            return new ModelMetadata(
                                $modelId,
                                $modelId,
                                $textCapabilities,
                                $visionOptions
                            );
                        }

                        // All other grok models are text generation.
                        return new ModelMetadata(
                            $modelId,
                            $modelId,
                            $textCapabilities,
                            $textOnlyOptions
                        );
                    },
                    $modelsData
                )
            )
        );

        usort($models, [$this, 'modelSortCallback']);

        return $models;
    }

    /**
     * Callback function for sorting models by ID, to be used with `usort()`.
     *
     * Prefers higher version numbers (grok-3 > grok-2), non-beta over beta,
     * and "latest" variants over dated ones.
     *
     * @since 1.0.0
     *
     * @param ModelMetadata $a First model.
     * @param ModelMetadata $b Second model.
     * @return int Comparison result.
     */
    protected function modelSortCallback(ModelMetadata $a, ModelMetadata $b): int
    {
        $aId = $a->getId();
        $bId = $b->getId();

        // Prefer non-beta models over beta models.
        $aIsBeta = str_contains($aId, '-beta');
        $bIsBeta = str_contains($bId, '-beta');
        if ($aIsBeta && !$bIsBeta) {
            return 1;
        }
        if ($bIsBeta && !$aIsBeta) {
            return -1;
        }

        // Prefer "latest" variants over dated ones.
        if (str_contains($aId, '-latest') && !str_contains($bId, '-latest')) {
            return -1;
        }
        if (str_contains($bId, '-latest') && !str_contains($aId, '-latest')) {
            return 1;
        }

        // Prefer higher version numbers (e.g. grok-3 > grok-2).
        $aMatch = preg_match('/^grok-(\d+)/', $aId, $aMatches);
        $bMatch = preg_match('/^grok-(\d+)/', $bId, $bMatches);
        if ($aMatch && $bMatch) {
            $aVersion = (int) $aMatches[1];
            $bVersion = (int) $bMatches[1];
            if ($aVersion !== $bVersion) {
                return $bVersion - $aVersion; // Higher version first.
            }
        }

        // Fallback: Sort alphabetically.
        return strcmp($aId, $bId);
    }
}
