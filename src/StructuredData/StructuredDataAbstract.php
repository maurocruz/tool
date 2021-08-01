<?php
namespace Plinct\Tool\StructuredData;

class StructuredDataAbstract {
    protected static $CONTEXT = "https://schema.org";
    protected $response;
    protected $itemListElement;
    protected $withListItem;

    public function __construct(string $type, $context) {
        $context = $context ?? self::$CONTEXT;
        $this->response = [
            "@context" => $context,
            "@type" => $type
        ];
    }

    protected function readyItemList() {
        $itemListElement = null;
        if ($this->withListItem) {
            foreach ($this->itemListElement as $key => $item) {
                $itemListElement[] = [
                    "@type"=>"ListItem",
                    "position"=> $key+1,
                    "item"=>$item
                ];
            }
        }
        $this->response['itemListElement'] = $itemListElement ?? $this->itemListElement;
    }
}