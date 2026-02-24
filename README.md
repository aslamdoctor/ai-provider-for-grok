# AI Provider for Grok

An AI Provider for [Grok](https://x.ai/) (by xAI) for the [WP AI Client](https://github.com/WordPress/wp-ai-client) SDK. Works as both a Composer package and a WordPress plugin.

## Requirements

- PHP 7.4 or higher
- [wordpress/wp-ai-client](https://github.com/WordPress/wp-ai-client) must be installed
- Purchased API tokens from [x.ai](https://x.ai/) — a valid token balance is required for API requests to work

## Installation

### As a WordPress Plugin

1. Purchase API tokens from [x.ai](https://x.ai/) if you haven't already
2. Download the plugin files
3. Upload to `/wp-content/plugins/wp-ai-provider-for-grok/`
4. Ensure the WP AI Client plugin is installed and activated
5. Activate the plugin through the WordPress admin
6. Go to **Settings > AI Credentials** and enter your Grok API key

![AI Client Credentials settings page](assets/screenshot-ai-credentials.png)

## Usage

### Basic Text Generation

```php
use WordPress\AI_Client\AI_Client;

$text = AI_Client::prompt( 'Explain quantum computing in simple terms.' )
    ->using_provider( 'grok' )
    ->generate_text();

echo $text;
```

### With System Instructions and Options

```php
$text = AI_Client::prompt( 'Summarize the history of WordPress.' )
    ->using_provider( 'grok' )
    ->using_system_instruction( 'You are a tech historian. Be concise and accurate.' )
    ->using_temperature( 0.2 )
    ->using_max_tokens( 500 )
    ->generate_text();
```

### JSON Output

```php
$schema = [
    'type'       => 'object',
    'properties' => [
        'title'   => [ 'type' => 'string' ],
        'summary' => [ 'type' => 'string' ],
        'tags'    => [ 'type' => 'array', 'items' => [ 'type' => 'string' ] ],
    ],
    'required'   => [ 'title', 'summary' ],
];

$json = AI_Client::prompt( 'Analyze this topic: WordPress plugin development' )
    ->using_provider( 'grok' )
    ->as_json_response( $schema )
    ->generate_text();

$data = json_decode( $json, true );
```

### Vision (Image Input)

Grok vision models can analyze images. You can pass an image URL, base64 string, or data URI using `with_file()`:

```php
// Using an image URL
$text = AI_Client::prompt( 'Describe what you see in this image.' )
    ->using_provider( 'grok' )
    ->with_file( 'https://example.com/photo.jpg', 'image/jpeg' )
    ->generate_text();

// Using a base64-encoded image
$base64 = base64_encode( file_get_contents( '/path/to/image.png' ) );
$text = AI_Client::prompt( 'What objects are in this image?' )
    ->using_provider( 'grok' )
    ->with_file( $base64, 'image/png' )
    ->generate_text();

// Using a WordPress attachment
$image_url = wp_get_attachment_url( $attachment_id );
$text = AI_Client::prompt( 'Generate alt text for this image.' )
    ->using_provider( 'grok' )
    ->with_file( $image_url, 'image/jpeg' )
    ->generate_text();
```

> **Note:** Vision support requires a Grok model with vision capabilities (e.g. models containing "vision" in their ID). The plugin automatically detects and flags these models.

### Multiple Candidates

```php
$texts = AI_Client::prompt( 'Write a tagline for a coffee shop.' )
    ->using_provider( 'grok' )
    ->generate_texts( 3 );

// $texts is an array of 3 different responses
foreach ( $texts as $text ) {
    echo $text . "\n";
}
```

### Chat History

```php
use WordPress\AiClient\Messages\DTO\UserMessage;
use WordPress\AiClient\Messages\DTO\ModelMessage;
use WordPress\AiClient\Messages\DTO\MessagePart;

$history = [
    new UserMessage( [ new MessagePart( 'What is PHP?' ) ] ),
    new ModelMessage( [ new MessagePart( 'PHP is a server-side scripting language...' ) ] ),
];

$text = AI_Client::prompt( 'How does it compare to Python?' )
    ->using_provider( 'grok' )
    ->with_history( ...$history )
    ->generate_text();
```

### Function Calling

```php
use WordPress\AiClient\Tools\DTO\FunctionDeclaration;
use WordPress\AiClient\Tools\DTO\FunctionResponse;

$functions = [
    new FunctionDeclaration(
        'get_weather',
        'Get current weather for a location',
        [
            'type'       => 'object',
            'properties' => [
                'location' => [ 'type' => 'string', 'description' => 'City name' ],
                'units'    => [ 'type' => 'string', 'enum' => [ 'celsius', 'fahrenheit' ] ],
            ],
            'required'   => [ 'location' ],
        ]
    ),
];

// First request — the model may return a function call
$result = AI_Client::prompt( 'What is the weather in New York?' )
    ->using_provider( 'grok' )
    ->using_function_declarations( ...$functions )
    ->generate_result();

// Check the response for function calls
$message = $result->toMessage();
foreach ( $message->getParts() as $part ) {
    if ( $part->isFunctionCall() ) {
        $call = $part->getFunctionCall();

        // Execute the function and send the response back
        $response = new FunctionResponse(
            $call->getId(),
            $call->getName(),
            [ 'temperature' => '22°C', 'condition' => 'Sunny' ]
        );

        $final = AI_Client::prompt()
            ->using_provider( 'grok' )
            ->with_function_response( $response )
            ->generate_text();

        echo $final;
    }
}
```

### Feature Detection

```php
$prompt = AI_Client::prompt( 'Hello!' )
    ->using_provider( 'grok' );

if ( $prompt->is_supported_for_text_generation() ) {
    echo $prompt->generate_text();
} else {
    echo 'Text generation is not available.';
}
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
