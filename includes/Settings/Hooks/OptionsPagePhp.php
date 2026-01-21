<?php

namespace proxilogFeatures\Settings\Hooks;

use proxilogFeatures\Interfaces\Hook;
use proxilogFeatures\Services\TwigService;

class OptionsPagePhp implements Hook
{
  protected $slug = 'proxilog-options-php';

  public function registerHooks(): void
  {
    add_action('admin_menu', [$this, 'addAdminMenu']);
    add_action('admin_init', [$this, 'registerSettings']);
    add_action('admin_post_proxilog_update_settings', [$this, 'updateSettings']);
  }

  /**
   * Ajoute une page d'options dans l'admin WordPress
   */
  public function addAdminMenu()
  {
    add_menu_page(
      'Options Proxilog',                    # Titre de la page
      'Options PHP',                         # Titre du menu
      'manage_options',                      # Capacité requise
      $this->slug,                           # Slug du menu
      [$this, 'adminMenuPageController'],    # Fonction de callback
      'dashicons-admin-generic',             # Icône (roue dentée)
      99                                     # Position
    );
  }

  /**
   * Affiche le contenu de la page d'options
   */
  public function adminMenuPageController()
  {
    $twig = TwigService::getInstance();

    $context = [
      'settings' => $this->getSettings(),
      'settings_updated' => isset($_GET['settings_updated']) && $_GET['settings_updated'] === 'true',
    ];

    $twig->render('admin/options-page-php.twig', $context);
  }


  /**
   * Enregistre les options dans WordPress
   */
  public function registerSettings()
  {
    // register_setting(
    //   'proxilog_features_options',
    //   'proxilog_features_is_enabled',
    //   [
    //     'type' => 'boolean',
    //     'default' => false,
    //     'show_in_rest' => true,
    //     'sanitize_callback' => 'rest_sanitize_boolean'
    //   ]
    // );
  }

  /** 
   * Met à jour les paramètres via $_POST
   */
  public function updateSettings()
  {
    // Validation du nonce
    if (!wp_verify_nonce($_POST['_wpnonce'], 'proxilog_update_settings')) {
      wp_die('Formulaire invalide');
    }

    // Vérifie le rôle utilisateur 
    if (!current_user_can('manage_options')) {
      wp_die('Vous ne pouvez pas modifier les paramètres');
    }

    // Récupération et sanitization des paramètres
    $isEnabled = isset($_POST['isEnabled']) ? true : false;
    $text = isset($_POST['text']) ? sanitize_text_field($_POST['text']) : '';
    $range = isset($_POST['range']) ? absint($_POST['range']) : 0;
    $position = isset($_POST['position']) ? sanitize_text_field($_POST['position']) : '';
    $color = isset($_POST['color']) ? sanitize_hex_color($_POST['color']) : '';

    // Mise à jour des paramètres
    update_option('proxilog_features_is_enabled', $isEnabled);
    update_option('proxilog_features_text', $text);
    update_option('proxilog_features_range', $range);
    update_option('proxilog_features_position', $position);
    update_option('proxilog_features_color', $color);

    // Redirection vers la page d'options
    wp_redirect(admin_url('admin.php?page=' . $this->slug . '&settings_updated=true'));
    exit;
  }

  /**
   * Récupère les paramètres
   */
  protected function getSettings()
  {
    return [
      'isEnabled' => (bool) get_option('proxilog_features_is_enabled', false),
      'text' => get_option('proxilog_features_text', ''),
      'range' => (int) get_option('proxilog_features_range', 0),
      'position' => get_option('proxilog_features_position', 'justify'),
      'color' => get_option('proxilog_features_color', '#219ebc'),
    ];
  }
}
