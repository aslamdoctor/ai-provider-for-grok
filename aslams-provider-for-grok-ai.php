<?php

/**
 * Plugin Name: Aslam's Provider for Grok AI
 * Plugin URI: https://github.com/aslamdoctor/aslams-provider-for-grok-ai
 * Description: Aslam's Provider for Grok AI (xAI) for the WordPress AI Client.
 * Requires at least: 6.9
 * Requires PHP: 7.4
 * Version: 1.0.0
 * Author: aslamdoctor
 * Author URI: https://github.com/sponsors/aslamdoctor
 * License: GPL-3.0-or-later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: aslams-provider-for-grok-ai
 *
 * @package Aslam\GrokAiProvider
 */

declare(strict_types=1);

namespace Aslam\GrokAiProvider;

use WordPress\AiClient\AiClient;
use Aslam\GrokAiProvider\Provider\GrokProvider;

if (!defined('ABSPATH')) {
    return;
}

require_once __DIR__ . '/src/autoload.php';

/**
 * Registers the Grok AI provider with the AI Client.
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
