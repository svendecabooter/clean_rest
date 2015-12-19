<?php

/**
 * @file
 * Contains \Drupal\clean_rest\Normalizer\ContentEntityNormalizer.
 */

namespace Drupal\clean_rest\Normalizer;

use Drupal\serialization\Normalizer\EntityNormalizer;
use Drupal\Core\Field\FieldItemList;

/**
 * Normalizes/denormalizes Drupal content entities into a clean structure
 * for pretty serialized output in the REST module.
 */
class ContentEntityNormalizer extends EntityNormalizer {

  /**
   * The interface or class that this Normalizer supports.
   *
   * @var array
   */
  protected $supportedInterfaceOrClass = ['Drupal\Core\Entity\ContentEntityInterface'];

  /**
   * How to normalize the field attribute.
   * Possible values: rendered | raw | cleaner_default
   * @var string
   */
  protected $attribute_mode = 'cleaner_default';

  /**
   * {@inheritdoc}
   */
  public function normalize($object, $format = NULL, array $context = array()) {
    $context += array(
      'account' => NULL,
    );

    $attributes = [];
    //$display = entity_get_display($object->getEntityTypeId(), $object->bundle(), 'full');
    //$content = $display->build($object);
    //$renderer = \Drupal::service('renderer');

    foreach ($object as $name => $field) {
      if ($field->access('view', $context['account'])) {
        switch ($this->attribute_mode) {
          case 'rendered':
            $field_value = $this->getRenderedFieldValue($object, $field);
            break;
          case 'raw':
            $field_value = $this->getRawFieldValue($object, $field);
            break;
          case 'cleaner_default':
            $field_value = $this->getCleanerDefaultFieldValue($object, $field);
            break;
        }

        $attributes[$name] = $this->serializer->normalize($field_value, $format, $context);
      }
    }

    return $attributes;
  }

  /**
   * Render the field value of an entity in a given view mode.
   *
   * @param $object
   * @param $field
   * @param $view_mode
   * @return mixed
   */
  private function getRenderedFieldValue($object, $field, $view_mode = 'full') {
    $field_name = $field->getName();
    $field_value = $object->$field_name->view($view_mode);
    return drupal_render($field_value);
  }

  /**
   * Render the raw field value of an entity.
   *
   * @param $object
   * @param $field
   * @return string
   */
  private function getRawFieldValue($object, $field) {
    $field_name = $field->getName();
    $raw = '';
    switch ($field_name) {
      case 'uid':
        $raw = $object->getOwnerId();
        break;
      case 'type':
        $raw = $object->getType();
      default:
        $raw = $object->$field_name->value;
    }
    return $raw;
  }

  /**
   * Normalize the same as the default ContentEntityNormalizer
   * but normalize single values in a cleaner way:
   *
   * @param $object
   * @param $field
   */
  private function getCleanerDefaultFieldValue($object, $field) {
    $field_name = $field->getName();
    if ($field instanceof FieldItemList) {

      $multiple = $object->getFieldDefinition($field_name)->getFieldStorageDefinition()->isMultiple();
      if (!$multiple && isset($object->$field_name->value)) {
        return $object->$field_name->value;
      }
    }
    return $field;
  }
}
