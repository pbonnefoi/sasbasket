<?php

namespace Drupal\custom_entities\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for deleting a Gymnase entity revision.
 *
 * @ingroup custom_entities
 */
class GymnaseEntityRevisionDeleteForm extends ConfirmFormBase {

  /**
   * The Gymnase entity revision.
   *
   * @var \Drupal\custom_entities\Entity\GymnaseEntityInterface
   */
  protected $revision;

  /**
   * The Gymnase entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $gymnaseEntityStorage;

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
    $instance->gymnaseEntityStorage = $container->get('entity_type.manager')->getStorage('gymnase_entity');
    $instance->connection = $container->get('database');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'gymnase_entity_revision_delete_confirm';
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
    return new Url('entity.gymnase_entity.version_history', ['gymnase_entity' => $this->revision->id()]);
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
  public function buildForm(array $form, FormStateInterface $form_state, $gymnase_entity_revision = NULL) {
    $this->revision = $this->GymnaseEntityStorage->loadRevision($gymnase_entity_revision);
    $form = parent::buildForm($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->GymnaseEntityStorage->deleteRevision($this->revision->getRevisionId());

    $this->logger('content')->notice('Gymnase entity: deleted %title revision %revision.', ['%title' => $this->revision->label(), '%revision' => $this->revision->getRevisionId()]);
    $this->messenger()->addMessage(t('Revision from %revision-date of Gymnase entity %title has been deleted.', ['%revision-date' => format_date($this->revision->getRevisionCreationTime()), '%title' => $this->revision->label()]));
    $form_state->setRedirect(
      'entity.gymnase_entity.canonical',
       ['gymnase_entity' => $this->revision->id()]
    );
    if ($this->connection->query('SELECT COUNT(DISTINCT vid) FROM {gymnase_entity_field_revision} WHERE id = :id', [':id' => $this->revision->id()])->fetchField() > 1) {
      $form_state->setRedirect(
        'entity.gymnase_entity.version_history',
         ['gymnase_entity' => $this->revision->id()]
      );
    }
  }

}
