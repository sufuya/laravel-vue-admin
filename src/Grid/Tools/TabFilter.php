<?php


namespace SmallRuralDog\Admin\Grid\Tools;

use JsonSerializable;


class TabFilter implements JsonSerializable
{
    public $searchKey = '__tab__';

    public function jsonSerialize()
    {
        return [
            'searchKey' => $this->searchKey,
            'tabs' => $this->tabs
        ];
    }
}