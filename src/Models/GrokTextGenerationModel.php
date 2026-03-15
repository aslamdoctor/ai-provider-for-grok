<?php

declare(strict_types=1);

namespace Aslam\GrokAiProvider\Models;

use WordPress\AiClient\Providers\Http\DTO\Request;
use WordPress\AiClient\Providers\Http\Enums\HttpMethodEnum;
use WordPress\AiClient\Providers\OpenAiCompatibleImplementation\AbstractOpenAiCompatibleTextGenerationModel;
use Aslam\GrokAiProvider\Provider\GrokProvider;

/**
 * Class for text generation models used by Aslam's Provider for Grok AI.
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
}
