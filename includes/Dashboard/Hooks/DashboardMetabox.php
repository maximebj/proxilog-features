<?php

namespace proxilogFeatures\Dashboard\Hooks;

use proxilogFeatures\Interfaces\Hook;
use proxilogFeatures\Services\TwigService;

# Sécurité
defined('ABSPATH') || exit;

class DashboardMetabox implements Hook
{
  public function registerHooks(): void
  {
    add_action('wp_dashboard_setup', [$this, 'addDashboardMetabox']);
    add_action('admin_post_proxilog_add_contact', [$this, 'handleFormSubmission']);
    add_action('admin_enqueue_scripts', [$this, 'enqueueStyles']);
  }

  /**
   * Ajouter la metabox au dashboard
   */
  public function addDashboardMetabox(): void
  {
    wp_add_dashboard_widget(
      'proxilog_contact_widget',
      'Formulaire de Contact Proxilog',
      [$this, 'renderMetabox']
    );
  }

  /**
   * Charger les styles CSS
   */
  public function enqueueStyles($hook): void
  {
    // Charger uniquement sur le dashboard
    if ($hook !== 'index.php') {
      return;
    }

    wp_enqueue_style(
      'proxilog-dashboard-metabox',
      PROXILOG_FEATURES_URL . 'assets/css/dashboard-metabox.css',
      [],
      PROXILOG_FEATURES_VERSION
    );
  }

  /**
   * Gérer la soumission du formulaire
   */
  public function handleFormSubmission(): void
  {
    // Vérifier le nonce
    if (
      !isset($_POST['_wpnonce']) ||
      !wp_verify_nonce($_POST['_wpnonce'], 'proxilog_add_contact')
    ) {
      wp_die('Action non autorisée');
    }

    // Vérifier les permissions
    if (!current_user_can('manage_options')) {
      wp_die('Permissions insuffisantes');
    }

    // Récupérer et nettoyer les données
    $name = sanitize_text_field($_POST['proxilog_name']);
    $email = sanitize_email($_POST['proxilog_email']);
    $phone = sanitize_text_field($_POST['proxilog_phone']);

    // Valider les données
    $errors = [];

    if (empty($name)) {
      $errors[] = 'Le nom est requis.';
    }

    if (empty($email) || !is_email($email)) {
      $errors[] = 'Un email valide est requis.';
    }

    if (empty($phone)) {
      $errors[] = 'Le téléphone est requis.';
    }

    if (!empty($errors)) {
      set_transient('proxilog_contact_errors', $errors, 45);
      wp_redirect(admin_url('index.php'));
      exit;
    }

    // Insérer dans la base de données
    global $wpdb;
    $table_name = $wpdb->prefix . 'proxilog';

    $result = $wpdb->insert(
      $table_name,
      [
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
      ],
      ['%s', '%s', '%s']
    );

    if ($result !== false) {
      set_transient('proxilog_contact_success', true, 45);
    } else {
      set_transient('proxilog_contact_errors', ['Erreur lors de l\'enregistrement'], 45);
    }

    wp_redirect(admin_url('index.php'));
    exit;
  }

  /**
   * Afficher le contenu de la metabox
   */
  public function renderMetabox(): void
  {
    $twig = TwigService::getInstance();

    // Afficher les messages
    if (get_transient('proxilog_contact_success')) {
      $twig->render('admin/notice.twig', [
        'message' => 'Contact enregistré avec succès !',
        'type' => 'success',
      ]);
      delete_transient('proxilog_contact_success');
    }

    $errors = get_transient('proxilog_contact_errors');
    if ($errors) {
      $twig->render('admin/notice.twig', [
        'message' => implode(' ', $errors),
        'type' => 'error',
      ]);
      delete_transient('proxilog_contact_errors');
    }

    // Afficher le tableau des données
    $this->renderDataTable();

    // Afficher le formulaire
    $this->renderForm();
  }

  /**
   * Afficher le formulaire
   */
  private function renderForm(): void
  {
    $twig = TwigService::getInstance();
    $twig->render('admin/dashboard-metabox-form.twig');
  }

  /**
   * Afficher le tableau des données
   */
  private function renderDataTable(): void
  {
    global $wpdb;
    $table_name = $wpdb->prefix . 'proxilog';

    // Récupérer les données
    $sql = $wpdb->prepare("SELECT * FROM %i ORDER BY id DESC", $table_name);
    $contacts = $wpdb->get_results($sql);

    // Afficher le tableau des données
    $twig = TwigService::getInstance();
    $twig->render('admin/dashboard-metabox-clients-table.twig', [
      'contacts' => $contacts,
    ]);
  }
}
