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
	 * @var bool
	 */
	private bool $isType = true;

	/**
	 * @param array|int|null $value
	 */
	public function __construct(array|int|null $value)
	{
		if (is_array($value) && isset($value['@context']) && isset($value['@type'])) {
			$this->type = $value['@type'];
			$this->idname = $this->type ? "id" . lcfirst($this->type) : null;
			$this->data = $value;
			$this->identifier = $value['identifier'] ?? null;
		} else {
			$this->isType = false;
		}
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
	 * @return ?string
	 */
	public function getType(): ?string
	{
		return $this->isType ? $this->type : null;
	}

	/**
	 * @param string $property
	 * @return mixed|null
	 */
	public function getValue(string $property): mixed
	{
		return $this->isType ? $this->data[$property] : null;
	}

	/**
	 * @param ?string $property
	 * @return false|mixed
	 */
	public function getPropertyValue(?string $property): mixed
	{
		if ($this->isType && $this->identifier) {
			foreach ($this->identifier as $propertyValue) {
				if ($propertyValue['name'] === $property) {
					return $propertyValue['value'];
				}
			}
		}
		return null;
	}
}
