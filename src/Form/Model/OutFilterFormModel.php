<?php

namespace App\Form\Model;

class OutFilterFormModel
{
    public $outFilterCampus;
    public $outFilterSearch;
    public $outFilterStartDate;
    public $outFilterEndDate;
    public $outFilterChk;
    public $outFilterChkOrg;
    public $outFilterChkSub;
    public $outFilterChkNotSub;
    public $outFilterChkEnd;

    public function getOutFilterCampus() : ?string
    {
        return $this->outFilterCampus;
    }

    public function __toString() : string
    {
        return $this->getOutFilterCampus();
    }

}
