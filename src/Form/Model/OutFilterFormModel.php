<?php

namespace App\Form\Model;

use Doctrine\ORM\Mapping as ORM;

class OutFilterFormModel
{
    public $outFilterCampus;
    public $outFilterSearch;
    public $outFilterStartDate;
    public $outFilterEndDate;
    public $outFilterChk;



    public function outFilterDQLGenerator(OutFilterFormModel $outFilterFormModel)
    {
        dump($outFilterFormModel->outFilterCampus->getNom());
        $queryBuilder = $this->createQueryBuilder('o');
        $queryBuilder
            -> andWhere('o.nom = :campusNom')
            ->setParameter('campusNom', $outFilterFormModel->outFilterCampus->getNom());

        $query = $queryBuilder->getQuery();
        return $query->getResult();

        /*       dump(
                   $outFilterFormModel->outFilterCampus->getNom(),
                   $outFilterFormModel->outFilterSearch,
                   $outFilterFormModel->outFilterStartDate,
                   $outFilterFormModel->outFilterEndDate,


               );

               if (in_array('ChkOrg', $outFilterFormModel->outFilterChk)) {
                   dump('ChkOrg is ok');
               }*/
    }




}
