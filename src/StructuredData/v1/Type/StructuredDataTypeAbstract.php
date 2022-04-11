<?php

declare(strict_types=1);

namespace Plinct\Tool\StructuredData\v1\Type;

abstract class StructuredDataTypeAbstract implements StructuredDataTypeInterface
{
  /**
   * @var array
   */
  protected array $data;
  /**
   * @var array
   */
  protected array $newData;

  public function __construct(array $data)
  {
    $this->setdata($data);
  }

  /**
   * @param array $data
   */
  protected function setdata(array $data): void
  {
    $this->data = $data;

		if (isset($data['description'])) {
			$this->data['description'] = strip_tags($data['description']);
		}

    $this->includeProperty('@context');
    $this->includeProperty('@type');
    $this->includeProperty('name');
    $this->includeProperty('description');
    $this->includeProperty('url');
  }

  /**
   * @param string $property
   * @param string|null $propertyValue
   */
  protected function includeProperty(string $property, string $propertyValue = null)
  {
    if (isset($this->data['@type'])) {
      if (isset($this->data[$property])) $this->newData[$property] = $this->data[$property];
      if ($propertyValue && isset($this->data[$propertyValue])) $this->newData[$property] = $this->data[$propertyValue];
    } else {
      foreach ($this->data as $key => $value) {
        if (isset($value[$property])) $this->newData[$key][$property] = $value[$property];
        if ($propertyValue && isset($value[$propertyValue])) $this->newData[$key][$property] = $value[$propertyValue];
      }
    }
  }
}
