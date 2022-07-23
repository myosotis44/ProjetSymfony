<?php

namespace App\Repository;

use App\Entity\Sortie;
use App\Form\Model\OutFilterFormModel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sortie>
 *
 * @method Sortie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sortie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sortie[]    findAll()
 * @method Sortie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }

    public function add(Sortie $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Sortie $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function outFilterDQLGenerator(OutFilterFormModel $outFilterFormModel)
    {
        $queryBuilder = $this->createQueryBuilder('o');

        $queryBuilder
                -> Where('o.campus = :campus')
                -> setParameter('campus', $outFilterFormModel->outFilterCampus);

        if($outFilterFormModel->outFilterSearch) {
            $queryBuilder
                -> andWhere('o.nom LIKE :search')
                -> setParameter('search', '%'.$outFilterFormModel->outFilterSearch.'%');
        }

        $queryBuilder
            -> andWhere(':startDate <= o.dateHeureDebut')
            -> andWhere('o.dateHeureDebut <= :endDate')
            -> setParameter('startDate', $outFilterFormModel->outFilterStartDate)
            -> setParameter('endDate', $outFilterFormModel->outFilterEndDate);

        $query = $queryBuilder->getQuery();

        return ($query->getResult());

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

//    /**
//     * @return Sortie[] Returns an array of Sortie objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Sortie
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
