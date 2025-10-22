<?php

namespace proxilogFeatures;

class OptionsPage implements Hook
{

  public function registerHooks(): void
  {
    add_action('admin_menu', [$this, 'addAdminMenu']);
    add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);
    add_action('rest_api_init', [$this, 'registerRestRoutes']);
    add_action('init', [$this, 'registerSettings']);
  }

  /**
   * Ajoute une page d'options dans l'admin WordPress
   */
  public function addAdminMenu()
  {
    add_menu_page(
      'Options Proxilog',                    # Titre de la page
      'Options',                             # Titre du menu
      'manage_options',                      # Capacité requise
      'proxilog-options',                    # Slug du menu
      [$this, 'addAdminMenuPage'],           # Fonction de callback
      'dashicons-admin-generic',             # Icône (roue dentée)
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
    if ($base !== 'toplevel_page_proxilog-options') {
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
  }

  /**
   * Enregistre les options dans WordPress
   */
  public function registerSettings()
  {
    register_setting(
      'proxilog_features_options',
      'proxilog_features_is_enabled',
      [
        'type' => 'boolean',
        'default' => false,
        'show_in_rest' => true,
        'sanitize_callback' => 'rest_sanitize_boolean'
      ]
    );
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
      'isEnabled' => get_option('proxilog_features_is_enabled', false)
    ];
  }

  /**
   * Sauvegarde les paramètres
   */
  public function saveSettings($request)
  {
    $isEnabled = $request->get_param('isEnabled');

    $result = update_option('proxilog_features_is_enabled', $isEnabled);

    return wp_send_json_success([
      'message' => 'Settings saved successfully',
      'isEnabled' => $isEnabled
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
