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

AI Provider for Grok (xAI) for the PHP AI Client SDK.

== Description ==

This plugin provides Grok (by xAI) integration for the PHP AI Client SDK. It enables WordPress sites to use Grok models for text generation and related AI capabilities.

**Features:**

* Text generation with Grok models
* Function calling support
* Vision input support (for compatible models)
* Automatic provider registration

Available models are dynamically discovered from the Grok API.

**Requirements:**

* PHP 7.4 or higher
* PHP AI Client plugin must be installed and activated
* Grok API key from xAI

== Installation ==

1. Ensure the PHP AI Client plugin is installed and activated
2. Upload the plugin files to `/wp-content/plugins/wp-ai-provider-for-grok/`
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Go to Settings > AI Credentials and enter your Grok API key

== Frequently Asked Questions ==

= How do I get a Grok API key? =

Visit the [xAI Console](https://console.x.ai/) to create an account and generate an API key.

= Does this plugin work without the PHP AI Client? =

No, this plugin requires the PHP AI Client plugin to be installed and activated. It provides the Grok-specific implementation that the PHP AI Client uses.

== Changelog ==

= 1.0.0 =
* Initial release
* Support for Grok text generation models
* Function calling support
* Vision input support for compatible models

== Upgrade Notice ==

= 1.0.0 =
Initial release.
