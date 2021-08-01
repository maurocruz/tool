<?php
namespace Plinct\Tool\StructuredData;

interface ItemListInterface
{
    public function items_unshift(...$items): ItemListInterface;

    public function withListItem(): ItemListInterface;
}