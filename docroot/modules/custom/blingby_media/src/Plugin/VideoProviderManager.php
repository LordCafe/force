<?php
namespace Drupal\blingby_media\Plugin;

use Drupal\Component\Plugin\Factory\DefaultFactory;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Provides a Blingby Video Provider plugin manager.
 *
 * @see \Drupal\blingby_media\Annotation\VideoProvider
 * @see \Drupal\blingby_media\Plugin\VideoProviderInterface
 * @see plugin_api
 */
class VideoProviderManager extends DefaultPluginManager {

  /**
   * Constructs a ArchiverManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct(
      'Plugin/VideoProvider',
      $namespaces,
      $module_handler,
      'Drupal\blingby_media\Plugin\VideoProviderInterface',
      'Drupal\blingby_media\Annotation\VideoProvider'
    );
    $this->alterInfo('blingby_video_provider_info');
    $this->setCacheBackend($cache_backend, 'blingby_video_provider_info_plugins');
  }

}