<?php

namespace Drupal\vote_result\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\votingapi\VoteResultFunctionManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use function array_diff_key;
use function array_filter;
use function array_keys;
use function array_map;

/**
 * Class VoteResTypeForm.
 */
class VoteResTypeForm extends EntityForm {

  /** @var EntityTypeBundleInfoInterface */
  protected $bundleInfo;

  /** @var VoteResultFunctionManager */
  protected $voteResultFunctionManager;

  public function __construct(EntityTypeBundleInfoInterface $bundle_info,
                              VoteResultFunctionManager $voteResultFunctionManager) {
    $this->bundleInfo = $bundle_info;

    $this->voteResultFunctionManager = $voteResultFunctionManager;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.bundle.info'),
      $container->get('plugin.manager.votingapi.resultfunction')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $label = $form_state->getValue('label');
    if (empty($label)) {
      $id = $form_state->getValue('id');
      $form_state->setValue('label', $this->getBundleLabel($id));
    }
    $fields = array_filter($form_state->getValue('fields'), function ($item) {
      return $item['selected'];
    });
    $form_state->setValue('fields', array_map(function ($item) {
      unset($item['selected']);
      return $item;
    }, $fields));
  }

  protected function getBundleLabel($id) {
    $vote_bundles = $this->bundleInfo->getBundleInfo('vote');
    return $vote_bundles[$id]['label'];
  }

  protected function getVoteBundles($current = FALSE) {

    $vote_bundles = $this->bundleInfo->getBundleInfo('vote');
    $exists_bundles = $this->bundleInfo->getBundleInfo('vote_res');

    if ($current) {
      unset($exists_bundles[$current]);
    }

    $available_bundles = array_diff_key($vote_bundles, $exists_bundles);

    return array_map(function ($item) {
      return $item['label'];
    }, $available_bundles);
  }


  protected function getExistsBundles() {
    return $this->bundleInfo->getBundleInfo('vote_res');
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $vote_res_type = $this->entity;

    $form['id'] = [
      '#type' => 'select',
      '#title' => $this->t('Vote type'),
      '#default_value' => $vote_res_type->id(),
      '#options' => $this->getVoteBundles($vote_res_type->id()),
      '#description' => $this->t("Vote type for the Vote result type."),
      '#disabled' => !empty($vote_res_type->id()),
      '#required' => TRUE,
    ];

    $form['label'] = [
      '#type' => 'value',
      '#value' => $vote_res_type->label(),
    ];

    $form['fields'] = [
      '#type' => 'fieldset',
      '#header' => ['result_function' => '', 'field_name' => $this->t('Field name')],
      '#title' => $this->t('Fields'),
      '#tree' => TRUE,
    ];

    $fields = $vote_res_type->get('fields');
    foreach ($this->voteResultFunctionManager->getDefinitions() as $definition) {
      $id = $definition['id'];
      $value = isset($fields[$id]) ? $fields[$id] : [];
      $item = [];
      $item['selected'] = [
        '#type' => 'checkbox',
        '#title' => $definition['label'],
        '#default_value' => !empty($value),

      ];
      $item['result_function'] = [
        '#type' => 'value',
        '#value' => $definition['id'],
      ];

      $item['field_name'] = [
        '#type' => 'textfield',
        '#default_value' => $value['field_name'],
        '#states' => [
          'visible' => [
            ':input[name="fields[' . $id . '][selected]"]' => ['checked' => TRUE],
          ],
          'required' => [
            ':input[name="fields[' . $id . '][selected]"]' => ['checked' => TRUE],
          ],
        ],
      ];

      $form['fields'][$id] = $item;
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $vote_res_type = $this->entity;
    $status = $vote_res_type->save();

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label Vote result type.', [
          '%label' => $vote_res_type->label(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label Vote result type.', [
          '%label' => $vote_res_type->label(),
        ]));
    }
    $form_state->setRedirectUrl($vote_res_type->toUrl('collection'));
  }

}
