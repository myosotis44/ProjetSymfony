<?php

namespace App\Form\Model;

use App\Entity\Campus;
use Symfony\Component\Validator\Constraints as Assert;

class OutFilterFormModel
{
    public Campus $outFilterCampus;

    public mixed $outFilterSearch = "";

    /**
     * @Assert\GreaterThanOrEqual("Today", message="La dâte ne peut pas être antérieure à celle du jour !")
     */
    public \DateTime $outFilterStartDate;

    /**
     * @Assert\Expression("this.outFilterStartDate <= this.outFilterEndDate", message="La dâte ne peut pas être antérieure à celle de début !")
     */
    public \DateTime $outFilterEndDate;

    public array $outFilterChk;

    public function __construct()
    {
        $this->outFilterStartDate = new \DateTime('now');
        $this->outFilterEndDate = new \DateTime('now');
    }
}
