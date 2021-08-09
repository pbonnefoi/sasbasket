<?php

namespace Drupal\sas_basket_commons\Service;

use Drupal\Component\Transliteration\TransliterationInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;

/**
 * Common Repository.
 */
class CommonRepository implements CommonRepositoryInterface {

  protected $entityTypeManager;
  protected $languageManager;
  protected $transliteration;

  /**
   * DurationRangeRepository constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity Type Manager.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   Language Manager.
   * @param TransliterationInterface $transliteration
   *   Transliteration.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, LanguageManagerInterface $language_manager, TransliterationInterface $transliteration) {
    $this->entityTypeManager = $entity_type_manager;
    $this->languageManager = $language_manager;
    $this->transliteration = $transliteration;
  }

  /**
   * @param string $type
   * @param bool $check_type_langcode
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getLastPublishedNode(string $type, $check_type_langcode = FALSE) {
    $langcode = $this->languageManager->getCurrentLanguage()->getId();
    $node_storage = $this->entityTypeManager->getStorage('node');
    $query = $node_storage->getQuery();
    if ($check_type_langcode) {
      $query->condition('langcode', $langcode);
    }
    $query->condition('type', $type);
    $query->condition('status', '1');
    $query->range(0, 1);
    $query->sort('created', 'DESC');
    $nids = $query->execute();

    $node = NULL;
    if ($nids) {
      $node = $node_storage->load(array_shift($nids));
      if ($node->hasTranslation($langcode)) {
        $node = $node->getTranslation($langcode);
      }
    }

    return $node;
  }

  /**
   * @return array|\Drupal\Core\Entity\EntityInterface|null
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getCreneaux() {
    $creneauStorage = $this->entityTypeManager->getStorage('creneau_entity');
    $query = $creneauStorage->getQuery();
    $ids = $query->execute();

    $creneaux = [];
    if ($ids) {
      $creneaux = $creneauStorage->loadMultiple($ids);
    }

    return $creneaux;
  }

  /**
   * @return array|\Drupal\Core\Entity\EntityInterface|null
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getGymnases() {
    $gymnaseStorage = $this->entityTypeManager->getStorage('gymnase_entity');
    $query = $gymnaseStorage->getQuery();
    $ids = $query->execute();

    $gymnases = [];
    if ($ids) {
      $gymnases = $gymnaseStorage->loadMultiple($ids);
    }

    return $gymnases;
  }

  /**
   * Translate string to machine readable.
   *
   * @param string $human_name
   *   A string.
   *
   * @return string
   *   A machine readable string.
   */
  public function humanToMachine(string $human_name) {
    $human_name = $this->transliteration->transliterate($human_name, LanguageInterface::LANGCODE_DEFAULT, '-');
    $human_name = $this->stripAccents($human_name);
    $human_name = str_replace('.', '', $human_name);
    $human_name = strtolower($human_name);
    $human_name = preg_replace([
      '/[^a-z0-9]+/',
      '/-+/',
      '/^-+/',
      '/-+$/',
    ], ['-', '-', '', ''], $human_name);

    if (!is_string($human_name)) {
      $human_name = '';
    }

    return $human_name;
  }

  /**
   * Remove accent.
   *
   * @param string $str
   *
   * @return string
   */
  private function stripAccents(string $str) {
    return strtr(utf8_decode($str), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
  }

}
