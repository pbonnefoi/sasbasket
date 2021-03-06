<?php

namespace Drupal\custom_entities\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for deleting a Creneau entity revision.
 *
 * @ingroup custom_entities
 */
class CreneauEntityRevisionDeleteForm extends ConfirmFormBase {

  /**
   * The Creneau entity revision.
   *
   * @var \Drupal\custom_entities\Entity\CreneauEntityInterface
   */
  protected $revision;

  /**
   * The Creneau entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $creneauEntityStorage;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->creneauEntityStorage = $container->get('entity_type.manager')->getStorage('creneau_entity');
    $instance->connection = $container->get('database');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'creneau_entity_revision_delete_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete the revision from %revision-date?', [
      '%revision-date' => format_date($this->revision->getRevisionCreationTime()),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.creneau_entity.version_history', ['creneau_entity' => $this->revision->id()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $creneau_entity_revision = NULL) {
    $this->revision = $this->CreneauEntityStorage->loadRevision($creneau_entity_revision);
    $form = parent::buildForm($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->CreneauEntityStorage->deleteRevision($this->revision->getRevisionId());

    $this->logger('content')->notice('Creneau entity: deleted %title revision %revision.', ['%title' => $this->revision->label(), '%revision' => $this->revision->getRevisionId()]);
    $this->messenger()->addMessage(t('Revision from %revision-date of Creneau entity %title has been deleted.', ['%revision-date' => format_date($this->revision->getRevisionCreationTime()), '%title' => $this->revision->label()]));
    $form_state->setRedirect(
      'entity.creneau_entity.canonical',
       ['creneau_entity' => $this->revision->id()]
    );
    if ($this->connection->query('SELECT COUNT(DISTINCT vid) FROM {creneau_entity_field_revision} WHERE id = :id', [':id' => $this->revision->id()])->fetchField() > 1) {
      $form_state->setRedirect(
        'entity.creneau_entity.version_history',
         ['creneau_entity' => $this->revision->id()]
      );
    }
  }

}
