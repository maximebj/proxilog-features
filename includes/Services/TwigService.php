<?php

namespace proxilogFeatures\Services;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\Extension\DebugExtension;
use Twig\TwigFunction;
use Twig\TwigFilter;
use WP_Error;

class TwigService
{
  private static ?TwigService $instance = null;
  private ?Environment $twig = null;
  private array $globalContext = [];

  /**
   * Constructeur privé pour le pattern Singleton
   */
  private function __construct()
  {
    $this->init();
  }

  /**
   * Récupère l'instance unique du service
   */
  public static function getInstance(): TwigService
  {
    if (self::$instance === null) {
      self::$instance = new self();
    }
    return self::$instance;
  }

  /**
   * Initialise Twig avec le loader de fichiers et l'environnement
   */
  private function init(): void
  {
    // Chemin des templates
    $templatesPath = PROXILOG_FEATURES_DIR . 'templates';

    // Créer le dossier templates s'il n'existe pas
    if (!file_exists($templatesPath)) {
      wp_mkdir_p($templatesPath);
    }

    // Créer le loader de fichiers
    $loader = new FilesystemLoader($templatesPath);

    // Configuration de l'environnement Twig
    // Désactiver le cache en développement pour voir les changements immédiatement
    $isDebug = defined('WP_DEBUG') && WP_DEBUG;
    $options = [
      'cache' => $isDebug ? false : PROXILOG_FEATURES_DIR . 'cache/twig',
      'debug' => $isDebug,
      'auto_reload' => true, // Toujours activer auto_reload pour détecter les changements
    ];

    // Créer l'environnement Twig
    $this->twig = new Environment($loader, $options);

    // Ajouter l'extension de debug si en mode debug
    if ($isDebug) {
      $this->twig->addExtension(new DebugExtension());
    }

    // Ajouter les filtres WordPress pour la sécurité
    $this->addWordPressFilters();

    // Initialiser le contexte global
    $this->addGlobalContext();
  }

  /**
   * Ajoute les filtres et fonctions WordPress pour la sécurité dans Twig
   */
  private function addWordPressFilters(): void
  {
    // Filtre esc_html
    $this->twig->addFilter(new TwigFilter('esc_html', 'esc_html'));

    // Filtre esc_attr
    $this->twig->addFilter(new TwigFilter('esc_attr', 'esc_attr'));

    // Filtre esc_url
    $this->twig->addFilter(new TwigFilter('esc_url', 'esc_url'));

    // Filtre esc_js
    $this->twig->addFilter(new TwigFilter('esc_js', 'esc_js'));

    // Filtre wp_kses_post
    $this->twig->addFilter(new TwigFilter('wp_kses_post', 'wp_kses_post'));

    // Fonctions WordPress essentielles
    $this->twig->addFunction(new TwigFunction('wp_get_option', function ($option, $default = false) {
      return get_option($option, $default);
    }));

    $this->twig->addFunction(new TwigFunction('wp_get_bloginfo', function ($show = '', $filter = 'raw') {
      return get_bloginfo($show, $filter);
    }));

    $this->twig->addFunction(new TwigFunction('wp_admin_url', function ($path = '', $scheme = 'admin') {
      return admin_url($path, $scheme);
    }));

    $this->twig->addFunction(new TwigFunction('wp_nonce_field', function ($action = -1, $name = '_wpnonce', $referer = true, $echo = false) {
      return wp_nonce_field($action, $name, $referer, $echo);
    }, ['is_safe' => ['html']]));

    $this->twig->addFunction(new TwigFunction('wp_create_nonce', function ($action = -1) {
      return wp_create_nonce($action);
    }));

    $this->twig->addFunction(new TwigFunction('wp_current_user_can', function ($capability) {
      return current_user_can($capability);
    }));

    $this->twig->addFunction(new TwigFunction('wp_get_current_user_id', function () {
      return get_current_user_id();
    }));

    $this->twig->addFunction(new TwigFunction('wp_submit_button', function ($text = 'Enregistrer les modifications', $type = 'primary', $name = '', $wrap = false, $other_attributes = []) {
      return submit_button($text, $type, $name, $wrap, $other_attributes);
    }));
  }

  /**
   * Ajoute le contexte global (données extension)
   */
  private function addGlobalContext(): void
  {
    // Constantes de l'extension
    $this->globalContext['constants'] = [
      'PROXILOG_FEATURES_VERSION' => PROXILOG_FEATURES_VERSION,
      'PROXILOG_FEATURES_DIR' => PROXILOG_FEATURES_DIR,
      'PROXILOG_FEATURES_URL' => PROXILOG_FEATURES_URL,
    ];

    // Ajouter le contexte global à Twig
    foreach ($this->globalContext as $key => $value) {
      $this->twig->addGlobal($key, $value);
    }
  }

  /**
   * Retourne le contexte global
   */
  public function getGlobalContext(): array
  {
    return $this->globalContext;
  }

  /**
   * Compile un template avec le contexte fourni et retourne le résultat
   * 
   * @param string $template Chemin du template relatif au dossier templates/
   * @param array $context Contexte additionnel à passer au template
   * @return string HTML rendu
   */
  public function compile(string $template, array $context = []): string
  {
    if ($this->twig === null) {
      throw new \RuntimeException('Twig n\'est pas initialisé');
    }

    try {
      return $this->twig->render($template, $context);
    } catch (\Twig\Error\LoaderError $e) {
      throw new \RuntimeException('Erreur Twig Loader: ' . $e->getMessage(), 0, $e);
    } catch (\Twig\Error\RuntimeError $e) {
      throw new \RuntimeException('Erreur Twig Runtime: ' . $e->getMessage(), 0, $e);
    } catch (\Twig\Error\SyntaxError $e) {
      throw new \RuntimeException('Erreur Twig Syntax: ' . $e->getMessage(), 0, $e);
    }
  }

  /**
   * Rend un template avec le contexte fourni et l'affiche directement
   * 
   * @param string $template Chemin du template relatif au dossier templates/
   * @param array $context Contexte additionnel à passer au template
   * @return void
   */
  public function render(string $template, array $context = []): void
  {
    echo $this->compile($template, $context);
  }

  /**
   * Vide le cache Twig
   * 
   * @return bool True si le cache a été vidé avec succès, false sinon
   */
  public function clearCache(): bool
  {
    $cacheDir = PROXILOG_FEATURES_DIR . 'cache/twig';

    if (!file_exists($cacheDir)) {
      return true; // Le cache n'existe pas, donc c'est déjà "vide"
    }

    // Supprimer récursivement tous les fichiers du cache
    $files = new \RecursiveIteratorIterator(
      new \RecursiveDirectoryIterator($cacheDir, \RecursiveDirectoryIterator::SKIP_DOTS),
      \RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($files as $fileinfo) {
      $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
      @$todo($fileinfo->getRealPath());
    }

    // Supprimer le dossier cache lui-même
    global $wp_filesystem;
    if (empty($wp_filesystem)) {
      require_once ABSPATH . '/wp-admin/includes/file.php';
      WP_Filesystem();
    }
    $wp_filesystem->rmdir($cacheDir);

    // Réinitialiser l'environnement Twig pour forcer la recompilation
    $this->init();

    return true;
  }

  /**
   * Désactive le cache Twig (utile pour le développement)
   * 
   * @return void
   */
  public function disableCache(): void
  {
    if ($this->twig === null) {
      return;
    }

    // Recréer l'environnement sans cache
    $templatesPath = PROXILOG_FEATURES_DIR . 'templates';
    $loader = new FilesystemLoader($templatesPath);

    $options = [
      'cache' => false,
      'debug' => true,
      'auto_reload' => true,
    ];

    $this->twig = new Environment($loader, $options);
    $this->twig->addExtension(new DebugExtension());
    $this->addWordPressFilters();
    $this->addGlobalContext();
  }

  /**
   * Empêche la clonage de l'instance
   */
  private function __clone() {}

  /**
   * Empêche la désérialisation de l'instance
   */
  public function __wakeup()
  {
    throw new \Exception("Cannot unserialize singleton");
  }
}
