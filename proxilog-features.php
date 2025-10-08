<?php

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

# Hooks
add_action('admin_menu', 'proxilog_add_admin_menu');

# Fonctions

/**
 * Ajoute une page d'options dans l'admin WordPress
 */
function proxilog_add_admin_menu()
{
  add_menu_page(
    'Options Proxilog',                    # Titre de la page
    'Options',                             # Titre du menu
    'manage_options',                      # Capacité requise
    'proxilog-options',                    # Slug du menu
    'proxilog_options_page',               # Fonction de callback
    'dashicons-admin-generic',             # Icône (roue dentée)
    99                                     # Position
  );
}

/**
 * Affiche le contenu de la page d'options
 */
function proxilog_options_page()
{
?>
  <div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
  </div>
<?php
}
