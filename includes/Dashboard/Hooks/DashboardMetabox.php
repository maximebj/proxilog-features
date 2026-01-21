<?php

namespace proxilogFeatures\Dashboard\Hooks;

use proxilogFeatures\Interfaces\Hook;

# Sécurité
defined('ABSPATH') || exit;

class DashboardMetabox implements Hook
{
  public function registerHooks(): void
  {
    add_action('wp_dashboard_setup', [$this, 'addDashboardMetabox']);
    add_action('admin_init', [$this, 'handleFormSubmission']);
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
    // Vérifier si le formulaire a été soumis
    if (!isset($_POST['proxilog_contact_submit'])) {
      return;
    }

    // Vérifier le nonce
    if (
      !isset($_POST['proxilog_contact_nonce']) ||
      !wp_verify_nonce($_POST['proxilog_contact_nonce'], 'proxilog_contact_action')
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
      $errors[] = 'Le nom est requis';
    }

    if (empty($email) || !is_email($email)) {
      $errors[] = 'Un email valide est requis';
    }

    if (empty($phone)) {
      $errors[] = 'Le téléphone est requis';
    }

    if (!empty($errors)) {
      add_settings_error(
        'proxilog_contact',
        'proxilog_contact_error',
        implode('<br>', $errors),
        'error'
      );
      set_transient('proxilog_contact_errors', $errors, 45);
      return;
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
      add_settings_error(
        'proxilog_contact',
        'proxilog_contact_success',
        'Contact enregistré avec succès !',
        'success'
      );
      set_transient('proxilog_contact_success', true, 45);
    } else {
      add_settings_error(
        'proxilog_contact',
        'proxilog_contact_error',
        'Erreur lors de l\'enregistrement',
        'error'
      );
      set_transient('proxilog_contact_errors', ['Erreur lors de l\'enregistrement'], 45);
    }

    // Rediriger pour éviter la double soumission
    wp_redirect(admin_url('index.php'));
    exit;
  }

  /**
   * Afficher le contenu de la metabox
   */
  public function renderMetabox(): void
  {
    // Afficher les messages
    if (get_transient('proxilog_contact_success')) {
      echo '<div class="notice notice-success is-dismissible"><p>Contact enregistré avec succès !</p></div>';
      delete_transient('proxilog_contact_success');
    }

    $errors = get_transient('proxilog_contact_errors');
    if ($errors) {
      echo '<div class="notice notice-error is-dismissible"><p>' . implode('<br>', $errors) . '</p></div>';
      delete_transient('proxilog_contact_errors');
    }

    // Afficher le formulaire
    $this->renderForm();

    // Afficher le tableau des données
    $this->renderDataTable();
  }

  /**
   * Afficher le formulaire
   */
  private function renderForm(): void
  {
?>
    <div class="proxilog-form-container">
      <h3>Ajouter un contact</h3>
      <form method="post" action="" class="proxilog-contact-form">
        <?php wp_nonce_field('proxilog_contact_action', 'proxilog_contact_nonce'); ?>

        <div class="form-group">
          <label for="proxilog_name">Nom *</label>
          <input
            type="text"
            id="proxilog_name"
            name="proxilog_name"
            class="widefat"
            required />
        </div>

        <div class="form-group">
          <label for="proxilog_email">Email *</label>
          <input
            type="email"
            id="proxilog_email"
            name="proxilog_email"
            class="widefat"
            required />
        </div>

        <div class="form-group">
          <label for="proxilog_phone">Téléphone *</label>
          <input
            type="tel"
            id="proxilog_phone"
            name="proxilog_phone"
            class="widefat"
            required />
        </div>

        <div class="form-group">
          <?php submit_button('Enregistrer le contact', 'primary', 'proxilog_contact_submit', false); ?>
        </div>
      </form>
    </div>
  <?php
  }

  /**
   * Afficher le tableau des données
   */
  private function renderDataTable(): void
  {
    global $wpdb;
    $table_name = $wpdb->prefix . 'proxilog';

    // Récupérer les données
    $contacts = $wpdb->get_results("SELECT * FROM $table_name ORDER BY id DESC");

    if (empty($contacts)) {
      echo '<div class="proxilog-no-data"><p>Aucun contact enregistré pour le moment.</p></div>';
      return;
    }

  ?>
    <div class="proxilog-data-container">
      <h3>Contacts enregistrés (<?php echo count($contacts); ?>)</h3>
      <table class="wp-list-table widefat fixed striped">
        <thead>
          <tr>
            <th width="30%">Nom</th>
            <th width="40%">Email</th>
            <th width="30%">Téléphone</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($contacts as $contact): ?>
            <tr>
              <td><?php echo esc_html($contact->name); ?></td>
              <td><?php echo esc_html($contact->email); ?></td>
              <td><?php echo esc_html($contact->phone); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
<?php
  }
}
