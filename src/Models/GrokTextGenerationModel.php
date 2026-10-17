<?php

declare(strict_types=1);

namespace Aslam\GrokAiProvider\Models;

use WordPress\AiClient\Providers\Http\DTO\Request;
use WordPress\AiClient\Providers\Http\Enums\HttpMethodEnum;
use WordPress\AiClient\Providers\OpenAiCompatibleImplementation\AbstractOpenAiCompatibleTextGenerationModel;
use Aslam\GrokAiProvider\Provider\GrokProvider;

/**
 * Class for text generation models used by AI Provider for Grok.
 *
 * @since 1.0.0
 */
class GrokTextGenerationModel extends AbstractOpenAiCompatibleTextGenerationModel
{
    /**
     * {@inheritDoc}
     *
     * @since 1.0.0
     */
    protected function createRequest(
        HttpMethodEnum $method,
        string $path,
        array $headers = [],
        $data = null
    ): Request {
        return new Request(
            $method,
            GrokProvider::url($path),
            $headers,
            $data,
            $this->getRequestOptions()
        );
    }

    /**
     * {@inheritDoc}
     *
     * Ensures json_schema conforms to xAI / OpenAI API structure with 'name' and 'schema'.
     *
     * @since 1.0.4
     *
     * @param array<string, mixed>|null $outputSchema The output schema.
     * @return array<string, mixed> The prepared response format parameter.
     */
    protected function prepareResponseFormatParam(?array $outputSchema): array
    {
        if (is_array($outputSchema)) {
            if (isset($outputSchema['schema'])) {
                $jsonSchema = $outputSchema;
                if (!isset($jsonSchema['name'])) {
                    $jsonSchema['name'] = 'response';
                }
            } else {
                $jsonSchema = [
                    'name'   => 'response',
                    'schema' => $outputSchema,
                ];
            }

            return [
                'type'        => 'json_schema',
                'json_schema' => $jsonSchema,
            ];
        }

        return ['type' => 'json_object'];
    }
}
