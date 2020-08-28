<?php


namespace SmallRuralDog\Admin\Grid\Concerns;


use SmallRuralDog\Admin\Grid\Tools\TabFilter;

trait HasTabFilter
{
    protected $tabFilter;
    protected $tabs;


    public function tabFilter()
    {
        $this->tabFilter = new TabFilter();
        return $this;
    }

    public function tabs($tabs = [])
    {
        $this->tabs = $tabs;
        return $tabs;
    }
}