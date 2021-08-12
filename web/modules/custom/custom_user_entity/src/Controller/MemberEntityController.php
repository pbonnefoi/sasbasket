<?php

namespace Drupal\custom_user_entity\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Url;
use Drupal\custom_user_entity\Entity\MemberEntityInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class MemberEntityController.
 *
 *  Returns responses for Member entity routes.
 */
class MemberEntityController extends ControllerBase implements ContainerInjectionInterface {

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
   * Displays a Member entity revision.
   *
   * @param int $member_entity_revision
   *   The Member entity revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($member_entity_revision) {
    $member_entity = $this->entityTypeManager()->getStorage('member_entity')
      ->loadRevision($member_entity_revision);
    $view_builder = $this->entityTypeManager()->getViewBuilder('member_entity');

    return $view_builder->view($member_entity);
  }

  /**
   * Page title callback for a Member entity revision.
   *
   * @param int $member_entity_revision
   *   The Member entity revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($member_entity_revision) {
    $member_entity = $this->entityTypeManager()->getStorage('member_entity')
      ->loadRevision($member_entity_revision);
    return $this->t('Revision of %title from %date', [
      '%title' => $member_entity->label(),
      '%date' => $this->dateFormatter->format($member_entity->getRevisionCreationTime()),
    ]);
  }

  /**
   * Generates an overview table of older revisions of a Member entity.
   *
   * @param \Drupal\custom_user_entity\Entity\MemberEntityInterface $member_entity
   *   A Member entity object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(MemberEntityInterface $member_entity) {
    $account = $this->currentUser();
    $member_entity_storage = $this->entityTypeManager()->getStorage('member_entity');

    $langcode = $member_entity->language()->getId();
    $langname = $member_entity->language()->getName();
    $languages = $member_entity->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $member_entity->label()]) : $this->t('Revisions for %title', ['%title' => $member_entity->label()]);

    $header = [$this->t('Revision'), $this->t('Operations')];
    $revert_permission = (($account->hasPermission("revert all member entity revisions") || $account->hasPermission('administer member entity entities')));
    $delete_permission = (($account->hasPermission("delete all member entity revisions") || $account->hasPermission('administer member entity entities')));

    $rows = [];

    $vids = $member_entity_storage->revisionIds($member_entity);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\custom_user_entity\MemberEntityInterface $revision */
      $revision = $member_entity_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = $this->dateFormatter->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $member_entity->getRevisionId()) {
          $link = $this->l($date, new Url('entity.member_entity.revision', [
            'member_entity' => $member_entity->id(),
            'member_entity_revision' => $vid,
          ]));
        }
        else {
          $link = $member_entity->link($date);
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
              Url::fromRoute('entity.member_entity.translation_revert', [
                'member_entity' => $member_entity->id(),
                'member_entity_revision' => $vid,
                'langcode' => $langcode,
              ]) :
              Url::fromRoute('entity.member_entity.revision_revert', [
                'member_entity' => $member_entity->id(),
                'member_entity_revision' => $vid,
              ]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.member_entity.revision_delete', [
                'member_entity' => $member_entity->id(),
                'member_entity_revision' => $vid,
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

    $build['member_entity_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
