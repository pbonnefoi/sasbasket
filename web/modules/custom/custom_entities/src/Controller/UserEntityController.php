<?php

namespace Drupal\custom_entities\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Url;
use Drupal\custom_entities\Entity\UserEntityInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class UserEntityController.
 *
 *  Returns responses for User entity routes.
 */
class UserEntityController extends ControllerBase implements ContainerInjectionInterface {

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
   * Displays a User entity revision.
   *
   * @param int $user_entity_revision
   *   The User entity revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($user_entity_revision) {
    $user_entity = $this->entityTypeManager()->getStorage('user_entity')
      ->loadRevision($user_entity_revision);
    $view_builder = $this->entityTypeManager()->getViewBuilder('user_entity');

    return $view_builder->view($user_entity);
  }

  /**
   * Page title callback for a User entity revision.
   *
   * @param int $user_entity_revision
   *   The User entity revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($user_entity_revision) {
    $user_entity = $this->entityTypeManager()->getStorage('user_entity')
      ->loadRevision($user_entity_revision);
    return $this->t('Revision of %title from %date', [
      '%title' => $user_entity->label(),
      '%date' => $this->dateFormatter->format($user_entity->getRevisionCreationTime()),
    ]);
  }

  /**
   * Generates an overview table of older revisions of a User entity.
   *
   * @param \Drupal\custom_entities\Entity\UserEntityInterface $user_entity
   *   A User entity object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(UserEntityInterface $user_entity) {
    $account = $this->currentUser();
    $user_entity_storage = $this->entityTypeManager()->getStorage('user_entity');

    $langcode = $user_entity->language()->getId();
    $langname = $user_entity->language()->getName();
    $languages = $user_entity->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $user_entity->label()]) : $this->t('Revisions for %title', ['%title' => $user_entity->label()]);

    $header = [$this->t('Revision'), $this->t('Operations')];
    $revert_permission = (($account->hasPermission("revert all user entity revisions") || $account->hasPermission('administer user entity entities')));
    $delete_permission = (($account->hasPermission("delete all user entity revisions") || $account->hasPermission('administer user entity entities')));

    $rows = [];

    $vids = $user_entity_storage->revisionIds($user_entity);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\custom_entities\UserEntityInterface $revision */
      $revision = $user_entity_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = $this->dateFormatter->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $user_entity->getRevisionId()) {
          $link = $this->l($date, new Url('entity.user_entity.revision', [
            'user_entity' => $user_entity->id(),
            'user_entity_revision' => $vid,
          ]));
        }
        else {
          $link = $user_entity->link($date);
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
              Url::fromRoute('entity.user_entity.translation_revert', [
                'user_entity' => $user_entity->id(),
                'user_entity_revision' => $vid,
                'langcode' => $langcode,
              ]) :
              Url::fromRoute('entity.user_entity.revision_revert', [
                'user_entity' => $user_entity->id(),
                'user_entity_revision' => $vid,
              ]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.user_entity.revision_delete', [
                'user_entity' => $user_entity->id(),
                'user_entity_revision' => $vid,
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

    $build['user_entity_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
