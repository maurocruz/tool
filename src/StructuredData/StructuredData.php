<?php
namespace Plinct\Tool\StructuredData;

class StructuredData extends StructuredDataAbstract implements ItemListInterface {

    public function __construct(string $type, $context) {
        parent::__construct($type,$context);
    }

    public function ItemList(string $name = null): ItemListInterface {
        $this->response['name'] = $name;
        return $this;
    }

    public function items_unshift(...$items): ItemListInterface {
        $args = func_get_args();
        foreach ($args as $value) {
            $this->itemListElement[] = $value;
        }
        return $this;
    }

    public function withListItem(): ItemListInterface
    {
        $this->withListItem = true;
        return $this;
    }

    public function ready(): array {
        if ($this->itemListElement) {
            parent::readyItemList();
        }
        return $this->response;
    }
}