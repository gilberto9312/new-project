<?php

namespace App\Repository;

use App\Entity\Confirm;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @method Confirm|null find($id, $lockMode = null, $lockVersion = null)
 * @method Confirm|null findOneBy(array $criteria, array $orderBy = null)
 * @method Confirm[]    findAll()
 * @method Confirm[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConfirmRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry,EntityManagerInterface $manager)
    {
        parent::__construct($registry, Confirm::class);
        $this->manager = $manager;
    }

    public function save($token,$monto,$client){
        $confirm = new Confirm();
        $confirm->setToken(substr($token,0,6));
        $confirm->setMonto($monto);
        $confirm->setClient($client);
        $confirm->setStatus(true);
        $this->manager->persist($confirm);
            
        $this->manager->flush();

}

    // /**
    //  * @return Confirm[] Returns an array of Confirm objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Confirm
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
