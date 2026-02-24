=== AI Provider for Grok ===
Contributors: aslamdoctor
Donate link: https://github.com/sponsors/aslamdoctor
Tags: ai, grok, xai, artificial-intelligence
Requires at least: 6.9
Tested up to: 6.9
Stable tag: 1.0.0
Requires PHP: 7.4
License: GPL-3.0-or-later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

AI Provider for Grok (xAI) for the WP AI Client SDK.

== Description ==

This plugin provides Grok (by xAI) integration for the WP AI Client SDK. It enables WordPress sites to use Grok models for text generation and related AI capabilities.

**Features:**

* Text generation with Grok models
* Function calling support
* Vision input support (for compatible models)
* Automatic provider registration

Available models are dynamically discovered from the Grok API.

**Requirements:**

* PHP 7.4 or higher
* WP AI Client plugin must be installed and activated
* Grok API key from xAI
* Purchased API tokens from [x.ai](https://x.ai/) — a valid token balance is required for API requests to work

== Installation ==

1. Purchase API tokens from [x.ai](https://x.ai/) if you haven't already
2. Ensure the WP AI Client plugin is installed and activated
3. Upload the plugin files to `/wp-content/plugins/wp-ai-provider-for-grok/`
4. Activate the plugin through the 'Plugins' menu in WordPress
5. Go to Settings > AI Credentials and enter your Grok API key

== Screenshots ==

1. AI Client Credentials settings page where you enter your Grok API key.

== Usage ==

The provider automatically registers itself on the `init` hook. Once both plugins are active and your API key is configured, you can start generating text:

= Basic Text Generation =

`$text = AI_Client::prompt( 'Explain quantum computing.' )->using_provider( 'grok' )->generate_text();`

= With System Instructions =

`$text = AI_Client::prompt( 'Summarize the history of WordPress.' )->using_provider( 'grok' )->using_system_instruction( 'Be concise and accurate.' )->using_temperature( 0.2 )->using_max_tokens( 500 )->generate_text();`

= Vision (Image Input) =

`$text = AI_Client::prompt( 'Describe this image.' )->using_provider( 'grok' )->with_file( 'https://example.com/photo.jpg', 'image/jpeg' )->generate_text();`

Requires a Grok model with vision capabilities.

= JSON Output =

`$json = AI_Client::prompt( 'Analyze this topic: WordPress plugins' )->using_provider( 'grok' )->as_json_response( $schema )->generate_text();`

For full usage examples including chat history, function calling, and feature detection, see the [README on GitHub](https://github.com/aslamdoctor/wp-ai-provider-for-grok).

== Frequently Asked Questions ==

= How do I get a Grok API key? =

Visit the [xAI Console](https://console.x.ai/) to create an account and generate an API key.

= Does this plugin work without the WP AI Client? =

No, this plugin requires the WP AI Client plugin to be installed and activated. It provides the Grok-specific implementation that the WP AI Client uses.

== Changelog ==

= 1.0.0 =
* Initial release
* Support for Grok text generation models
* Function calling support
* Vision input support for compatible models

== Upgrade Notice ==

= 1.0.0 =
Initial release.
