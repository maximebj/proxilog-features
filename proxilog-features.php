<?php

namespace proxilogFeatures;

/**
 * Plugin Name:       Proxilog Features
 * Description:       Mon premier plugin WordPress.
 * Version:           0.0.1
 * Requires at least: 6.8
 * Requires PHP:      8.0
 * Author:            Maxime BERNARD-JACQUET
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       proxilog-features
 */

# Sécurité
defined('ABSPATH') || exit;

# Constantes 
define('PROXILOG_FEATURES_VERSION', '0.0.1');
define('PROXILOG_FEATURES_DIR', plugin_dir_path(__FILE__));
define('PROXILOG_FEATURES_URL', plugin_dir_url(__FILE__));

# Chercher les fichiers
include_once PROXILOG_FEATURES_DIR . 'includes/interfaces/hook.php';
include_once PROXILOG_FEATURES_DIR . 'includes/OptionsPage.php';

# Lancer les classes
(new OptionsPage())->registerHooks();
