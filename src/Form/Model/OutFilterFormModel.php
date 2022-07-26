<?php

namespace App\Form\Model;

use App\Entity\Campus;
use Symfony\Component\Validator\Constraints as Assert;

class OutFilterFormModel
{
    public Campus $outFilterCampus;

    public $outFilterSearch;

    /**
     * @Assert\GreaterThanOrEqual("Today", message="Cette dâte ne peut pas être antérieure à la dâte du jour !")
     */
    public \DateTime $outFilterStartDate;

    /**
     * @Assert\Expression("this.outFilterStartDate <= this.outFilterEndDate", message="La dâte de fin ne peut pas être antérieure à la date de début !")
     */
    public \DateTime $outFilterEndDate;

    public array $outFilterChk;

    public function __construct()
    {
        $this->outFilterStartDate = new \DateTime('now');
        $this->outFilterEndDate = new \DateTime('now + 1 month');
    }
}

