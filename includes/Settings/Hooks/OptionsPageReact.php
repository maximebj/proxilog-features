<?php

namespace proxilogFeatures\Settings\Hooks;

use proxilogFeatures\Interfaces\Hook;

use WP_Block_Editor_Context;

# Sécurité
defined('ABSPATH') || exit;

class OptionsPageReact implements Hook
{
  protected $slug = 'proxilog-options-react';

  public function registerHooks(): void
  {
    add_action('admin_menu', [$this, 'addAdminMenu']);
    add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);
    add_action('rest_api_init', [$this, 'registerRestRoutes']);
  }

  /**
   * Ajoute une page d'options dans l'admin WordPress
   */
  public function addAdminMenu()
  {
    add_menu_page(
      'Options Proxilog',                    # Titre de la page
      'Options React',                       # Titre du menu
      'manage_options',                      # Capacité requise
      $this->slug,                           # Slug du menu
      [$this, 'addAdminMenuPage'],           # Fonction de callback
      'dashicons-performance',               # Icône (roue dentée)
      99                                     # Position
    );
  }

  /**
   * Affiche le contenu de la page d'options
   */
  public function addAdminMenuPage()
  {
    echo '<div id="proxilog-features-root"></div>';
  }


  /**
   * Enregistre et charge les scripts
   */
  public function enqueueAssets($base)
  {
    if ($base !== 'toplevel_page_' . $this->slug) {
      return;
    }

    $file_path = PROXILOG_FEATURES_DIR . 'build/index.asset.php';

    if (!file_exists($file_path)) {
      return;
    }

    $asset_file = include $file_path;

    wp_enqueue_script(
      'proxilog-features',
      PROXILOG_FEATURES_URL . 'build/index.js',
      $asset_file['dependencies'],
      $asset_file['version'],
      true
    );

    wp_enqueue_style(
      'proxilog-features',
      PROXILOG_FEATURES_URL . 'build/style-index.css',
      [],
      $asset_file['version']
    );

    // Enqueue WordPress components styles for proper ToggleControl appearance
    wp_enqueue_style('wp-components');

    // Envoyer les réglages de l'éditeur en JS
    $custom_settings = [];
    $block_editor_context = new WP_Block_Editor_Context(['name' => 'modern-fields']);
    $editor_settings = get_block_editor_settings($custom_settings, $block_editor_context);

    wp_localize_script('proxilog-features', 'proxilogFeatures', [
      'editorSettings' => $editor_settings,
      'settings' => $this->getSettings()
    ]);
  }

  /**
   * Enregistre les routes REST
   */
  public function registerRestRoutes()
  {
    register_rest_route('proxilog-features/v1', '/settings', [
      'methods' => 'GET',
      'callback' => [$this, 'getSettings'],
      'permission_callback' => [$this, 'checkPermissions']
    ]);

    register_rest_route('proxilog-features/v1', '/settings', [
      'methods' => 'POST',
      'callback' => [$this, 'saveSettings'],
      'permission_callback' => [$this, 'checkPermissions'],
      'args' => [
        'isEnabled' => [
          'required' => true,
          'type' => 'boolean',
          'sanitize_callback' => 'rest_sanitize_boolean'
        ],
        'text' => [
          'required' => true,
          'type' => 'string',
          'sanitize_callback' => 'sanitize_text_field'
        ],
        'range' => [
          'required' => true,
          'type' => 'integer',
          'sanitize_callback' => 'absint'
        ],
        'position' => [
          'required' => true,
          'type' => 'string',
          'sanitize_callback' => 'sanitize_text_field'
        ],
        'color' => [
          'required' => true,
          'type' => 'string',
          'sanitize_callback' => 'sanitize_hex_color'
        ]
      ]
    ]);
  }

  /**
   * Récupère les paramètres
   */
  public function getSettings()
  {
    return [
      'isEnabled' => (bool) get_option('proxilog_features_is_enabled', false),
      'text' => get_option('proxilog_features_text', ''),
      'range' => (int) get_option('proxilog_features_range', 0),
      'position' => get_option('proxilog_features_position', 'justify'),
      'color' => get_option('proxilog_features_color', '#219ebc'),
    ];
  }

  /**
   * Sauvegarde les paramètres
   */
  public function saveSettings($request)
  {
    $isEnabled = $request->get_param('isEnabled');
    $text = $request->get_param('text');
    $range = $request->get_param('range');
    $position = $request->get_param('position');
    $color = $request->get_param('color');

    // Si on voulait envoyer les données à un autre site, on pourrait utiliser cette méthode
    /*
    wp_remote_post(
      'https://www.monapp.fr/api/v1/settings',
      [
        'method' => 'POST',
        'body' => json_encode([
          'isEnabled' => $isEnabled,
        ])
      ]
    );
    */

    // On enregistre les paramètres dans la base de données
    update_option('proxilog_features_is_enabled', $isEnabled);
    update_option('proxilog_features_text', $text);
    update_option('proxilog_features_range', $range);
    update_option('proxilog_features_position', $position);
    update_option('proxilog_features_color', $color);

    return wp_send_json_success([
      'isEnabled' => $isEnabled,
      'text' => $text,
      'range' => $range,
      'position' => $position,
      'color' => $color
    ]);
  }

  /**
   * Vérifie les permissions
   */
  public function checkPermissions()
  {
    return current_user_can('manage_options');
  }
}
