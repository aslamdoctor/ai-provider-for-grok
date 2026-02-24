<?php

/**
 * Plugin Name: AI Provider for Grok
 * Plugin URI: https://github.com/aslamdoctor/wp-ai-provider-for-grok
 * Description: AI Provider for Grok (xAI) for the WordPress AI Client.
 * Requires at least: 6.9
 * Requires PHP: 7.4
 * Version: 1.0.0
 * Author: aslamdoctor
 * Author URI: https://github.com/sponsors/aslamdoctor
 * License: GPL-3.0-or-later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: wp-ai-provider-for-grok
 *
 * @package WordPress\GrokAiProvider
 */

declare(strict_types=1);

namespace WordPress\GrokAiProvider;

use WordPress\AiClient\AiClient;
use WordPress\GrokAiProvider\Provider\GrokProvider;

if (!defined('ABSPATH')) {
    return;
}

require_once __DIR__ . '/src/autoload.php';

/**
 * Registers the AI Provider for Grok with the AI Client.
 *
 * @since 1.0.0
 *
 * @return void
 */
function register_provider(): void
{
    if (!class_exists(AiClient::class)) {
        return;
    }

    $registry = AiClient::defaultRegistry();

    if ($registry->hasProvider(GrokProvider::class)) {
        return;
    }

    $registry->registerProvider(GrokProvider::class);
}

add_action('init', __NAMESPACE__ . '\\register_provider', 5);
