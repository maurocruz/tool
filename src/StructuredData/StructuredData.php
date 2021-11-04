<?php

declare(strict_types=1);

namespace Plinct\Tool\StructuredData;

class StructuredData extends StructuredDataAbstract implements ItemListInterface
{
    public function __construct(string $type, $context) {
        parent::__construct($type,$context);
    }

    /**
     * @param string|null $name
     * @return ItemListInterface
     */
    public function ItemList(string $name = null): ItemListInterface {
        $this->response['name'] = $name;
        return $this;
    }

    /**
     * @param ...$items
     * @return ItemListInterface
     */
    public function items_unshift(...$items): ItemListInterface {
        $args = func_get_args();
        foreach ($args as $value) {
            $this->itemListElement[] = $value;
        }
        return $this;
    }

    /**
     * @return ItemListInterface
     */
    public function withListItem(): ItemListInterface
    {
        $this->withListItem = true;
        return $this;
    }

    /**
     * @return array
     */
    public function ready(): array {
        if ($this->itemListElement) {
            parent::readyItemList();
        }
        return $this->response;
    }
}
