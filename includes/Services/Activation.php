<?php

namespace proxilogFeatures\Services;

use proxilogFeatures\Interfaces\Hook;

# Sécurité
defined('ABSPATH') || exit;

class Activation implements Hook
{
  public function registerHooks(): void
  {
    register_activation_hook(PROXILOG_FEATURES_FILE, [$this, 'activate']);
  }

  # Creation de la base de données
  public function activate()
  {
    global $wpdb;

    // Nom de la table avec le préfixe WordPress
    $table_name = $wpdb->prefix . 'proxilog';

    // Récupérer le charset de la base de données
    $charset_collate = $wpdb->get_charset_collate();

    // Requête SQL pour créer la table
    $sql = "CREATE TABLE $table_name (
    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    name varchar(255) NOT NULL,
    phone varchar(50) NOT NULL,
    email varchar(255) NOT NULL,
    PRIMARY KEY (id)
  ) $charset_collate;";

    // Inclure le fichier pour utiliser dbDelta
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    // Enregistrer la version de la base de données
    add_option('proxilog_features_db_version', PROXILOG_FEATURES_VERSION);
  }
}
