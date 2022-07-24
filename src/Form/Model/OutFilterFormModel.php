<?php

namespace App\Form\Model;


class OutFilterFormModel
{
    public $outFilterCampus;
    public $outFilterSearch;
    public \DateTime $outFilterStartDate;
    public \DateTime $outFilterEndDate;
    public $outFilterChk;

    public function __construct()
    {
        $this->outFilterStartDate = new \DateTime('now');
        $this->outFilterEndDate = new \DateTime('now');
    }

}
