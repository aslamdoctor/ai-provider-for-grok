# AI Provider for Grok

An AI Provider for [Grok](https://x.ai/) (by xAI) for the [PHP AI Client](https://github.com/WordPress/php-ai-client) SDK. Works as both a Composer package and a WordPress plugin.

## Requirements

- PHP 7.4 or higher
- [wordpress/php-ai-client](https://github.com/WordPress/php-ai-client) must be installed

## Installation

### As a WordPress Plugin

1. Download the plugin files
2. Upload to `/wp-content/plugins/wp-ai-provider-for-grok/`
3. Ensure the PHP AI Client plugin is installed and activated
4. Activate the plugin through the WordPress admin
5. Go to **Settings > AI Credentials** and enter your Grok API key

## Usage

### With WordPress

The provider automatically registers itself with the PHP AI Client on the `init` hook. Simply ensure both plugins are active and configure your API key:

```php
// Set your Grok API key (or use the GROK_API_KEY environment variable)
putenv('GROK_API_KEY=your-api-key');

// Use the provider
$result = AiClient::prompt('Hello, world!')
    ->usingProvider('grok')
    ->generateTextResult();
```

### As a Standalone Package

```php
use WordPress\AiClient\AiClient;
use WordPress\GrokAiProvider\Provider\GrokProvider;

// Register the provider
$registry = AiClient::defaultRegistry();
$registry->registerProvider(GrokProvider::class);

// Set your API key
putenv('GROK_API_KEY=your-api-key');

// Generate text
$result = AiClient::prompt('Explain quantum computing')
    ->usingProvider('grok')
    ->generateTextResult();

echo $result->toText();
```

## Supported Models

Available models are dynamically discovered from the Grok API. This includes text generation models and, for compatible models, vision input support. See the [xAI documentation](https://docs.x.ai/) for the full list of available models.

## Configuration

The provider uses the `GROK_API_KEY` environment variable for authentication. You can set this in your environment or via PHP:

```php
putenv('GROK_API_KEY=your-api-key');
```

Alternatively, configure your API key through the WordPress admin at **Settings > AI Credentials**.

## License

GPL-2.0-or-later
