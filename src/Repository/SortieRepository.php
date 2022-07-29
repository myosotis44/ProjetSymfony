<?php

namespace App\Repository;

use App\Entity\Participant;
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

    public function outFilterDQLGenerator(OutFilterFormModel $outFilterFormModel, Participant $connectedUser)
    {
        $queryBuilder = $this->createQueryBuilder('o');

        if (in_array('ChkSub', $outFilterFormModel->outFilterChk)) {
            $queryBuilder
                ->Join('o.participants', 'p')
                ->andWhere('p.mail = :connectedUserMail')
                ->setParameter('connectedUserMail', $connectedUser->getMail());
        }

        if (in_array('ChkNotSub', $outFilterFormModel->outFilterChk)) {
            $queryBuilder
//marche aussi    ->leftJoin('o.participants', 'q')
//marche aussi    ->addSelect('q')
//marche aussi    ->andWhere('q.mail IS NULL');
                ->leftJoin('o.participants', 'q')
                ->andWhere(':connectedUser not member of o.participants')
                ->setParameter('connectedUser', $connectedUser);
        }

        if (in_array('ChkOrg', $outFilterFormModel->outFilterChk)) {
            $queryBuilder
                ->andWhere('o.organisateur = :connectedUser')
                ->setParameter('connectedUser', $connectedUser);
        }

        if (in_array('ChkEnd', $outFilterFormModel->outFilterChk)) {
            $queryBuilder
                ->andWhere('o.dateHeureDebut < :now')
                ->setParameter('now', new \DateTime('now') );
        }

        if (!in_array('ChkEnd', $outFilterFormModel->outFilterChk)) {
            $queryBuilder
                ->andWhere(':startDate <= o.dateHeureDebut')
                ->andWhere('o.dateHeureDebut <= :endDate')
                ->setParameter('startDate', $outFilterFormModel->outFilterStartDate)
                ->setParameter('endDate', $outFilterFormModel->outFilterEndDate);
        }

        if($outFilterFormModel->outFilterSearch != null) {
            $queryBuilder
                ->andWhere('o.nom LIKE :search')
                ->setParameter('search', '%'.$outFilterFormModel->outFilterSearch.'%');
        }

        $queryBuilder
            ->andWhere('o.campus = :campus')
            ->setParameter('campus', $outFilterFormModel->outFilterCampus)
            ->orderBy('o.dateHeureDebut');

        $query = $queryBuilder->getQuery();

        return ($query->getResult());

    }


    /**
     * @return Sortie[] Returns an array of Sortie objects
     */
    public function returnActive(): array
    {
        $list = ["En création","Ouverte","Activité en cours","Activité Terminée"];
        dump($this->createQueryBuilder('s')
            ->where('s.etat_id IN(:list)')
            ->setParameter('list', array_values($list))
            ->getQuery());
        return $this->createQueryBuilder('s')
            ->where('s.etat IN(:list)')
            ->setParameter('list', $list)
            ->getQuery()
            ->getResult();
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
