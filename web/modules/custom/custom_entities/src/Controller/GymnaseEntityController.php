<?php

namespace Drupal\custom_entities\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Url;
use Drupal\custom_entities\Entity\GymnaseEntityInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class GymnaseEntityController.
 *
 *  Returns responses for Gymnase entity routes.
 */
class GymnaseEntityController extends ControllerBase implements ContainerInjectionInterface {

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
   * Displays a Gymnase entity revision.
   *
   * @param int $gymnase_entity_revision
   *   The Gymnase entity revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($gymnase_entity_revision) {
    $gymnase_entity = $this->entityTypeManager()->getStorage('gymnase_entity')
      ->loadRevision($gymnase_entity_revision);
    $view_builder = $this->entityTypeManager()->getViewBuilder('gymnase_entity');

    return $view_builder->view($gymnase_entity);
  }

  /**
   * Page title callback for a Gymnase entity revision.
   *
   * @param int $gymnase_entity_revision
   *   The Gymnase entity revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($gymnase_entity_revision) {
    $gymnase_entity = $this->entityTypeManager()->getStorage('gymnase_entity')
      ->loadRevision($gymnase_entity_revision);
    return $this->t('Revision of %title from %date', [
      '%title' => $gymnase_entity->label(),
      '%date' => $this->dateFormatter->format($gymnase_entity->getRevisionCreationTime()),
    ]);
  }

  /**
   * Generates an overview table of older revisions of a Gymnase entity.
   *
   * @param \Drupal\custom_entities\Entity\GymnaseEntityInterface $gymnase_entity
   *   A Gymnase entity object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(GymnaseEntityInterface $gymnase_entity) {
    $account = $this->currentUser();
    $gymnase_entity_storage = $this->entityTypeManager()->getStorage('gymnase_entity');

    $langcode = $gymnase_entity->language()->getId();
    $langname = $gymnase_entity->language()->getName();
    $languages = $gymnase_entity->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $gymnase_entity->label()]) : $this->t('Revisions for %title', ['%title' => $gymnase_entity->label()]);

    $header = [$this->t('Revision'), $this->t('Operations')];
    $revert_permission = (($account->hasPermission("revert all gymnase entity revisions") || $account->hasPermission('administer gymnase entity entities')));
    $delete_permission = (($account->hasPermission("delete all gymnase entity revisions") || $account->hasPermission('administer gymnase entity entities')));

    $rows = [];

    $vids = $gymnase_entity_storage->revisionIds($gymnase_entity);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\custom_entities\GymnaseEntityInterface $revision */
      $revision = $gymnase_entity_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = $this->dateFormatter->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $gymnase_entity->getRevisionId()) {
          $link = $this->l($date, new Url('entity.gymnase_entity.revision', [
            'gymnase_entity' => $gymnase_entity->id(),
            'gymnase_entity_revision' => $vid,
          ]));
        }
        else {
          $link = $gymnase_entity->link($date);
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
              Url::fromRoute('entity.gymnase_entity.translation_revert', [
                'gymnase_entity' => $gymnase_entity->id(),
                'gymnase_entity_revision' => $vid,
                'langcode' => $langcode,
              ]) :
              Url::fromRoute('entity.gymnase_entity.revision_revert', [
                'gymnase_entity' => $gymnase_entity->id(),
                'gymnase_entity_revision' => $vid,
              ]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.gymnase_entity.revision_delete', [
                'gymnase_entity' => $gymnase_entity->id(),
                'gymnase_entity_revision' => $vid,
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

    $build['gymnase_entity_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
