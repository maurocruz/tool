<?php
namespace Plinct\Tool;

class TypeBuilder
{
	/**
	 * @var array
	 */
	private array $data;
	/**
	 * @var ?string
	 */
	private ?string $idname;
	/**
	 * @var mixed
	 */
	private ?array $identifier;
	/**
	 * @var string|mixed|null
	 */
	private ?string $type;

	/**
	 * @param array $value
	 */
	public function __construct(array $value) {
		$this->type = $value['@type'] ?? null;
		$this->idname = $this->type ? "id".lcfirst($this->type) : null;
		$this->data = $value;
		$this->identifier = $value['identifier'] ?? null;
	}

	/**
	 * @return int|null
	 */
	public function getId(): ?int
	{
		return (int) $this->getPropertyValue($this->idname);
	}

	public function getIdthing(): ?string
	{
		return $this->getPropertyValue('idthing');
	}

	/**
	 * @return string
	 */
	public function getType(): string
	{
		return $this->type;
	}

	/**
	 * @param string $property
	 * @return mixed|null
	 */
	public function getValue(string $property) {
		return $this->data[$property] ?? null;
	}

	/**
	 * @param ?string $property
	 * @return false|mixed
	 */
	public function getPropertyValue(?string $property)	{
		if ($this->identifier) {
			foreach ($this->identifier as $propertyValue) {
				if ($propertyValue['name'] === $property) {
					return $propertyValue['value'];
				}
			}
		}
		return null;
	}
}
