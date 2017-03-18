<?php

namespace Drupal\commerce_product\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'commerce_add_to_cart' formatter.
 *
 * @FieldFormatter(
 *   id = "commerce_add_to_cart",
 *   label = @Translation("Add to cart form"),
 *   field_types = {
 *     "entity_reference",
 *   },
 * )
 */
class AddToCartFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'combine' => TRUE,
      'skip_cart' => FALSE,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);
    $form['combine'] = [
      '#type' => 'checkbox',
      '#title' => t('Combine order items containing the same product variation.'),
      '#description' => t('The order item type, referenced product variation, and data from fields exposed on the Add to Cart form must all match to combine.'),
      '#default_value' => $this->getSetting('combine'),
    ];
    $form['skip_cart'] = [
      '#type' => 'checkbox',
      '#title' => t('Skip cart, create a new order and immediately start the checkout process.'),
      '#description' => t('Adds the product to a new order and immediately goes to the checkout page.'),
      '#default_value' => $this->getSetting('skip_cart'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    if ($this->getSetting('combine')) {
      $summary[] = $this->t('Combine order items containing the same product variation.');
    }
    else {
      $summary[] = $this->t('Do not combine order items containing the same product variation.');
    }
    if ($this->getSetting('skip_cart')) {
      $summary[] = $this->t('Skip cart');
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    return [
      '#lazy_builder' => ['commerce_product.lazy_builders:addToCartForm', [
        $items->getEntity()->id(),
        $this->viewMode,
        $this->getSetting('combine'),
        $this->getSetting('skip_cart'),
      ],
      ],
      '#create_placeholder' => TRUE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    // This formatter requires both commerce_checkout and commerce_cart enabled,
    // commerce_checkout depends on cart.
    $has_cart = \Drupal::moduleHandler()->moduleExists('commerce_checkout');
    $entity_type = $field_definition->getTargetEntityTypeId();
    $field_name = $field_definition->getName();
    return $has_cart && $entity_type == 'commerce_product' && $field_name == 'variations';
  }

}
