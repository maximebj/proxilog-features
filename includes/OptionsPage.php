<?php

namespace proxilogFeatures;

class OptionsPage implements Hook
{

  public function registerHooks(): void
  {
    add_action('admin_menu', [$this, 'addAdminMenu']);
    add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);
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
  }
}
