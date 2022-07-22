<?php

namespace App\Services;

use App\Form\Model\OutFilterFormModel;
use Doctrine\ORM\EntityManager;

class OutFilterService
{
    public function DQLGenerator (OutFilterFormModel $outFilterFormModel, EntityManager $entityManager)
    {
        dump(
            $outFilterFormModel->outFilterCampus->getNom(),
            $outFilterFormModel->outFilterSearch,
            $outFilterFormModel->outFilterStartDate,
            $outFilterFormModel->outFilterEndDate,


        );

         if (in_array('ChkOrg', $outFilterFormModel->outFilterChk)) {
             dump('ChkOrg is ok');
         }









    }

}
