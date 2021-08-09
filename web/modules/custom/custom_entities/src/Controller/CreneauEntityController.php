<?php

namespace Drupal\custom_entities\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Url;
use Drupal\custom_entities\Entity\CreneauEntityInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CreneauEntityController.
 *
 *  Returns responses for Creneau entity routes.
 */
class CreneauEntityController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * The date formatter.
   *
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormatter;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  protected $renderer;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->dateFormatter = $container->get('date.formatter');
    $instance->renderer = $container->get('renderer');
    return $instance;
  }

  /**
   * Displays a Creneau entity revision.
   *
   * @param int $creneau_entity_revision
   *   The Creneau entity revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($creneau_entity_revision) {
    $creneau_entity = $this->entityTypeManager()->getStorage('creneau_entity')
      ->loadRevision($creneau_entity_revision);
    $view_builder = $this->entityTypeManager()->getViewBuilder('creneau_entity');

    return $view_builder->view($creneau_entity);
  }

  /**
   * Page title callback for a Creneau entity revision.
   *
   * @param int $creneau_entity_revision
   *   The Creneau entity revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($creneau_entity_revision) {
    $creneau_entity = $this->entityTypeManager()->getStorage('creneau_entity')
      ->loadRevision($creneau_entity_revision);
    return $this->t('Revision of %title from %date', [
      '%title' => $creneau_entity->label(),
      '%date' => $this->dateFormatter->format($creneau_entity->getRevisionCreationTime()),
    ]);
  }

  /**
   * Generates an overview table of older revisions of a Creneau entity.
   *
   * @param \Drupal\custom_entities\Entity\CreneauEntityInterface $creneau_entity
   *   A Creneau entity object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(CreneauEntityInterface $creneau_entity) {
    $account = $this->currentUser();
    $creneau_entity_storage = $this->entityTypeManager()->getStorage('creneau_entity');

    $langcode = $creneau_entity->language()->getId();
    $langname = $creneau_entity->language()->getName();
    $languages = $creneau_entity->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $creneau_entity->label()]) : $this->t('Revisions for %title', ['%title' => $creneau_entity->label()]);

    $header = [$this->t('Revision'), $this->t('Operations')];
    $revert_permission = (($account->hasPermission("revert all creneau entity revisions") || $account->hasPermission('administer creneau entity entities')));
    $delete_permission = (($account->hasPermission("delete all creneau entity revisions") || $account->hasPermission('administer creneau entity entities')));

    $rows = [];

    $vids = $creneau_entity_storage->revisionIds($creneau_entity);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\custom_entities\CreneauEntityInterface $revision */
      $revision = $creneau_entity_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = $this->dateFormatter->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $creneau_entity->getRevisionId()) {
          $link = $this->l($date, new Url('entity.creneau_entity.revision', [
            'creneau_entity' => $creneau_entity->id(),
            'creneau_entity_revision' => $vid,
          ]));
        }
        else {
          $link = $creneau_entity->link($date);
        }

        $row = [];
        $column = [
          'data' => [
            '#type' => 'inline_template',
            '#template' => '{% trans %}{{ date }} by {{ username }}{% endtrans %}{% if message %}<p class="revision-log">{{ message }}</p>{% endif %}',
            '#context' => [
              'date' => $link,
              'username' => $this->renderer->renderPlain($username),
              'message' => [
                '#markup' => $revision->getRevisionLogMessage(),
                '#allowed_tags' => Xss::getHtmlTagList(),
              ],
            ],
          ],
        ];
        $row[] = $column;

        if ($latest_revision) {
          $row[] = [
            'data' => [
              '#prefix' => '<em>',
              '#markup' => $this->t('Current revision'),
              '#suffix' => '</em>',
            ],
          ];
          foreach ($row as &$current) {
            $current['class'] = ['revision-current'];
          }
          $latest_revision = FALSE;
        }
        else {
          $links = [];
          if ($revert_permission) {
            $links['revert'] = [
              'title' => $this->t('Revert'),
              'url' => $has_translations ?
              Url::fromRoute('entity.creneau_entity.translation_revert', [
                'creneau_entity' => $creneau_entity->id(),
                'creneau_entity_revision' => $vid,
                'langcode' => $langcode,
              ]) :
              Url::fromRoute('entity.creneau_entity.revision_revert', [
                'creneau_entity' => $creneau_entity->id(),
                'creneau_entity_revision' => $vid,
              ]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.creneau_entity.revision_delete', [
                'creneau_entity' => $creneau_entity->id(),
                'creneau_entity_revision' => $vid,
              ]),
            ];
          }

          $row[] = [
            'data' => [
              '#type' => 'operations',
              '#links' => $links,
            ],
          ];
        }

        $rows[] = $row;
      }
    }

    $build['creneau_entity_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
