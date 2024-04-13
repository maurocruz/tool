<?php
declare(strict_types=1);
namespace Plinct\Tool;

class TypeBuilder
{
	/**
	 * @var array
	 */
	private array $data;
	private string $idname;
	private $identifier;

	public function __construct(array $value) {
		$type = $value['@type'] ?? null;
		$this->idname = "id".lcfirst($type);
		$this->data = $value;
		$this->identifier = $value['identifier'];
	}

	public function getId(): ?int
	{
		return (int) $this->getPropertyValue($this->idname);
	}

	public function getValue(string $property) {
		return $this->data[$property] ?? null;
	}
	
	public function getPropertyValue(string $property)	{
		foreach ($this->identifier as $propertyValue) {
			if ($propertyValue['name'] === $property) {
				return $propertyValue['value'];
			}
		}
		return false;
	}
}