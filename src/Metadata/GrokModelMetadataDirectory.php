<?php

declare(strict_types=1);

namespace Aslam\GrokAiProvider\Metadata;

use WordPress\AiClient\Files\Enums\FileTypeEnum;
use WordPress\AiClient\Files\Enums\MediaOrientationEnum;
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
use Aslam\GrokAiProvider\Provider\GrokProvider;

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
            new SupportedOption(OptionEnum::outputSchema()),
            new SupportedOption(OptionEnum::functionDeclarations()),
            new SupportedOption(OptionEnum::customOptions()),
        ];

        if (is_callable([OptionEnum::class, 'webSearch'])) {
            $baseOptions[] = new SupportedOption(OptionEnum::webSearch());
        }

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

        $imageCapabilities = [
            CapabilityEnum::imageGeneration(),
        ];
        $imageOptions = [
            new SupportedOption(OptionEnum::inputModalities(), [[ModalityEnum::text()]]),
            new SupportedOption(OptionEnum::outputModalities(), [[ModalityEnum::image()]]),
            new SupportedOption(OptionEnum::candidateCount()),
            new SupportedOption(OptionEnum::outputMimeType(), ['image/png', 'image/jpeg', 'image/webp']),
            new SupportedOption(OptionEnum::outputFileType(), [FileTypeEnum::inline(), FileTypeEnum::remote()]),
            new SupportedOption(OptionEnum::outputMediaOrientation(), [
                MediaOrientationEnum::square(),
                MediaOrientationEnum::landscape(),
                MediaOrientationEnum::portrait(),
            ]),
            new SupportedOption(OptionEnum::outputMediaAspectRatio(), ['1:1', '3:2', '2:3']),
            new SupportedOption(OptionEnum::customOptions()),
        ];

        $modelsData = (array) $responseData['data'];

        $models = array_values(
            array_filter(
                array_map(
                    static function (array $modelData) use ($textCapabilities, $textOnlyOptions, $visionOptions, $imageCapabilities, $imageOptions): ?ModelMetadata {
                        $modelId = $modelData['id'];

                        // Skip video models for now as xAI does not provide an OpenAI-compatible video endpoint.
                        if (str_contains($modelId, 'video')) {
                            return null;
                        }

                        // Image generation models (e.g. grok-2-image-1212, grok-imagine-image).
                        if (str_contains($modelId, 'image') || str_contains($modelId, 'imagine')) {
                            return new ModelMetadata(
                                $modelId,
                                $modelId,
                                $imageCapabilities,
                                $imageOptions
                            );
                        }

                        // Vision models support image input (models with 'vision' or Grok 3+ multimodal models).
                        if (str_contains($modelId, 'vision') || preg_match('/^grok-[3-9]/', $modelId)) {
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

        // Prefer higher version numbers (e.g. grok-4.20 > grok-4.7 > grok-4.3 > grok-2).
        $aMatch = preg_match('/^grok-([0-9]+(?:\.[0-9]+)*)/', $aId, $aMatches);
        $bMatch = preg_match('/^grok-([0-9]+(?:\.[0-9]+)*)/', $bId, $bMatches);
        if ($aMatch && $bMatch) {
            $versionComp = version_compare($bMatches[1], $aMatches[1]);
            if ($versionComp !== 0) {
                return $versionComp; // Higher version first.
            }
        } elseif ($aMatch) {
            return -1; // Versioned models come before unversioned ones.
        } elseif ($bMatch) {
            return 1;
        }

        // Fallback: Sort alphabetically.
        return strcmp($aId, $bId);
    }
}
