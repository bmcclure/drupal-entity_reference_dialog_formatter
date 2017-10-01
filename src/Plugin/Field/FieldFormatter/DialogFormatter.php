<?php

namespace Drupal\entity_reference_dialog_formatter\Plugin\Field\FieldFormatter;

use Drupal\Core\Annotation\Translation;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\Annotation\FieldFormatter;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'dialog' formatter.
 *
 * @FieldFormatter(
 *   id = "entity_reference_dialog",
 *   module = "entity_reference_views_formatter",
 *   label = @Translation("Dialog"),
 *   field_types = {
 *     "entity_reference",
 *   }
 * )
 */
class DialogFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];
    $dialogOptions = [];
    $width = $this->getSetting('dialog_width');
    if ($width) {
      $dialogOptions['width'] = $width;
    }

    /** @var EntityReferenceItem $item */
    foreach ($items as $item) {
      if (!empty($item->target_id) && isset($item->entity)) {
        /** @var EntityInterface $entity */
        $entity = $item->entity;

        $element[] = entity_reference_dialog_formatter_link($this->getTitle($entity), $entity->getEntityTypeId(), $entity->id(), 'full', $dialogOptions);
      }
    }

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'use_entity_label' => TRUE,
      'link_title' => '',
      'dialog_width' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);

    $element['use_entity_label'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use entity label'),
      '#description' => $this->t('Uses the entity label as the link title instead of the text specified.'),
      '#default_value' => $this->getSetting('use_entity_label'),
    ];

    $element['link_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Link title'),
      '#description' => $this->t('The link title to use if not using the entity label.'),
      '#default_value' => $this->getSetting('link_title'),
    ];

    $element['dialog_width'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Dialog width'),
      '#description' => $this->t('Enter a width value, or leave blank for automatic width.'),
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $useEntityLabel = $this->getSetting('use_entity_label');
    $width = $this->getSetting('dialog_width');
    if (!$width) {
      $width = 'auto';
    }

    $summary = [];
    $summary[] = $this->t('Renders the field as a Dialog link.');
    $summary[] = $useEntityLabel ? 'Using the entity label' : 'Using the specified title';
    if (!$useEntityLabel) {
      $summary[] = $this->t('Link title: ') . $this->getSetting('link_title');
    }
    $summary[] = $this->t("Dialog width: $width");

    return $summary;
  }

  /**
   * Get the correct title value to use for this reference.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   *
   * @return string
   *   The title.
   */
  protected function getTitle(EntityInterface $entity) {
    if ($this->getSetting('use_entity_label')) {
      return $entity->label();
    }

    return $this->getSetting('link_title');
  }
}
