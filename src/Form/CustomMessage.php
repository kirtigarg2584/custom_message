<?php

namespace Drupal\custom_message\Form;

use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityManager;
use Drupal\Core\Form\ConfigFormBase;

/**
 * Implements the CustomMessage class.
 */
class CustomMessage extends ConfigFormBase {

  protected $ids = [];
  protected $content = [];
  protected $tempFlag = 0;

  /**
   * Constructor to initialize class properties.
   */
  public function __construct(EntityManager $type) {
    $this->entityManager = $type;
  }

  /**
   * Constructor to initialize class properties.
   */
  public static function create(ContainerInterface $container) {

    return new static(
      $container->get('entity.manager'),
      $container->get('config.factory')
    );

  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'custom_message_id';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['custom_message.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $conf = $this->config('custom_message.settings');

    $form['#attached']['library'][] = 'custom_message/drupal.custom_message';

    $form['custom_message'] = [
      '#type' => 'container',
      '#prefix' => '<div id="custom_message_add_more">',
      '#suffix' => '</div>',
    ];

    $contentTypes_list = $this->entityManager->getStorage('node_type')->loadMultiple();

    $this->content['all_types'] = ' All';
    
    foreach ($contentTypes_list as $value) {
      $this->content[$value->get('name')] = $value->get('name');
    }

    if (empty($form_state->get('count'))) { 
      
      if ($conf->get('count') && $form_state->get('flag')!= 10){
        $form_state->set('count', $conf->get('count'));
      }

      else {
        $form_state->set('count', 1);
      }

    }
    
    if (count($this->ids) === 0) {
      for ($i = 0; $i < $form_state->get('count');$i++) {
        array_push($this->ids, $i);
      }
    }
    
    foreach ($this->ids as $i) {

      $form['custom_message']['content_type' . $i] = [
        '#type' => 'select',
        '#title' => $this->t('Content - Types'),
        '#empty_option' => $this->t('- Select -'),
        '#default_value' => ($form_state->get('flag') != 10) ? ( $conf->get('content_type' . $i) ? : NULL) : NULL,
        '#options' => $this->content,
      ];

      $form['custom_message']['action_required' . $i] = [
        '#type' => 'select',
        '#title' => $this->t('Action'),
        '#empty_option' => $this->t('- Select -'),
        '#default_value' => ($form_state->get('flag') != 10) ? ( $conf->get('action_required' . $i) ?  : NULL) : NULL,
        '#options' => [
          'all_actions' => 'All',
          'created' => 'Create',
          'updated' => 'Update',
          'deleted' => 'Delete',
        ],
      ];
      

      $form['custom_message']['message' . $i] = [
        '#type' => 'textfield',
        '#title' => $this->t('Message'),
        '#default_value' => ($form_state->get('flag') != 10) ? ($conf->get('message' . $i)   ?: NULL) : NULL,
      ];

      $form['custom_message']['remove' . $i] = [
        '#type' => 'submit',
        '#value' => $this->t('Remove'),
        '#name' => $i,
        '#suffix' => '<div></div>',
        '#submit' => ['::removeOne'],
        '#ajax' => [
          'callback' => '::fieldCallback',
          'wrapper' => 'custom_message_add_more',
        ],
      ];

    }

    $form['save'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
    ];

    $form['add_more'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add More'),
      '#submit' => ['::addOne'],
      '#ajax' => [
        'callback' => '::fieldCallback',
        'wrapper' => 'custom_message_add_more',
      ],
    ];

    $form['browse'] = [
      '#type' => 'markup',
      '#theme' => 'token_tree_link',
      '#token_types' => ['node'],
    ];

    return $form;

  }

  /**
   * Validates form data before submission.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

    $trigerring_element = $form_state->getTriggeringElement()['#name'];
    $values = $form_state->getValues('custom_message');
    $count = $form_state->get('count');
    foreach ($this->ids as $key => $value) {

      if (empty($values['content_type' . $key]) && !$trigerring_element) {
        $form_state->setErrorByName('content_type' . $key, $this->t('Content Type is empty'));
      }
      
      if (empty($values['action_required' . $key]) && !$trigerring_element) {
        $form_state->setErrorByName('action_required' . $key, $this->t('Action Required is empty'));
      }
      
      if (empty($values['message' . $key]) && !$trigerring_element) {
        $form_state->setErrorByName('message' . $key, $this->t('Message field is empty'));
      }

      for ($i = $value + 1; $i < $count; $i++) {
        if ($values['content_type' . $value] == $values['content_type' . $i]) {
          if ($values['action_required' . $value] == $values['action_required' . $i]) {
            $this->tempFlag = 1;
            $form_state->setErrorByName('content_type' . $i, $this->t('Combination for the same Content type and action can not be repeated'));
            $form_state->setErrorByName('action_required' . $i, $this->t('Combination for the same Content type and action can not be repeated'));
            $form_state->setErrorByName('content_type' . $value, $this->t('Combination for the same Content type and action can not be repeated'));
            $form_state->setErrorByName('action_required' . $value, $this->t('Combination for the same Content type and action can not be repeated'));
          }
        }
      }
    }
  }

  /**
   * Returns the container of the rows.
   */
  public function fieldCallback(array &$form, FormStateInterface $form_state) {

    return $form['custom_message'];
  }

  /**
   * Adds row_id to the ids array.
   *
   * Adds 1 to the count.
   */
  public function addOne(array &$form, FormStateInterface $form_state) {

    array_push($this->ids, end($this->ids) + 1);
    $form_state->set('count', ($form_state->get('count') + 1));
    $form_state->setRebuild();
    
  }

  /**
   * Deletes specified row_id from the ids array.
   *
   * Subtract 1 from  count.
   */
  public function removeOne(array &$form, FormStateInterface $form_state) {

    $trigerring_element = $form_state->getTriggeringElement()['#name'];
    
    unset($this->ids[$trigerring_element]);
    $form_state->set('count', ($form_state->get('count') - 1));
    
    if ($form_state->get('count')== 0 ){
      $form_state->set('flag', 10);
    }
    $form_state->setRebuild();

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $values = $form_state->getValues('custom_message');
    $config = $this->config('custom_message.settings')->delete()->save();
    $tmp = 0;
    foreach ($this->ids as $value) {
      $config->set('content_type' . $tmp, $values['content_type' . $value]);
      $config->set('action_required' . $tmp, $values['action_required' . $value]);
      $config->set('message' . $tmp, $values['message' . $value]);
      $tmp++;
    }

    $config->set('count', $form_state->get('count'));

    $config->save();

    if (!$this->tempFlag) {
      drupal_set_message($this->t('Changes saved'));
    }

  }

}
