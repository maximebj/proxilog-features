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
