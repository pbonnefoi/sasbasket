<?php

namespace Drupal\sas_basket_commons\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\office_hours\Plugin\Field\FieldType\OfficeHoursItem;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CustomEntitiesController.
 *
 * @package Drupal\louvre_commons\Controller
 */
class PlanningsController extends ControllerBase {

  protected $commonRepository;

  /**
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *
   * @return \Drupal\Core\Controller\ControllerBase|\Drupal\sas_basket_commons\Controller\PlanningsController
   */
  public static function create(ContainerInterface $container) {
    $commonRepository = $container->get('sas_basket_commons.common_repository');

    return new static($commonRepository);
  }

  /**
   * PlanningsController constructor.
   *
   * @param $commonRepository
   */
  public function __construct($commonRepository) {
    $this->commonRepository = $commonRepository;
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Symfony\Component\Validator\Exception\InvalidArgumentException
   */
  public function callback(Request $request) {
    $creneaux = $this->commonRepository->getCreneaux();
    $gymnases = $this->commonRepository->getGymnases();
    $creneaux_sorted = [];
    foreach ($gymnases as $gymnase) {
      $creneaux_sorted[$gymnase->id()] = [
        'name' => $gymnase->label(),
        'creneaux' =>  [
          0 => [],
          1 => [],
          2 => [],
          3 => [],
          4 => [],
          5 => [],
          6 => [],
        ],
      ];
    }
    foreach ($creneaux as $creneau) {
      $creneau_value = $creneau->get('field_creneau')->first();
      if ($creneau_value instanceof OfficeHoursItem) {
        $gymnase_id = $creneau->get('field_gymnase')->first()->getEntity()->id();
        if ($gymnase_id) {
          $starthour = $creneau_value->get('starthours')->getValue();
          $start = substr_replace($starthour, 'H', 2, 0);
          $endhour = $creneau_value->get('endhours')->getValue();
          $end = substr_replace($endhour, 'H', 2, 0);
          $creneaux_sorted[$gymnase_id]['creneaux'][$creneau_value->get('day')->getValue()][$starthour] = [
            'start' => $start,
            'end' => $end,
            'name' => $creneau->label(),
          ];
        }
      }
    }

    foreach ($creneaux_sorted as $gid => $creneau_sorted) {
      foreach ($creneau_sorted['creneaux'] as $day_id => $day) {
        ksort($creneaux_sorted[$gid]['creneaux'][$day_id]);
      }
    }
    ksort($creneaux_sorted);

    $render = [
      '#theme' => 'plannings',
      '#creneaux' => $creneaux_sorted,
    ];

    return $render;
  }

}
