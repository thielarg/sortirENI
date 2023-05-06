<?php

namespace App\Repository;

use App\Entity\Participant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method Participant|null find($id, $lockMode = null, $lockVersion = null)
 * @method Participant|null findOneBy(array $criteria, array $orderBy = null)
 * @method Participant[]    findAll()
 * @method Participant[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ParticipantRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Participant::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        if (!$user instanceof Participant) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newEncodedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    public function loadUserByIdentifier(string $usernameOrEmail): ?User
    {
        $entityManager = $this->getEntityManager();

        return $entityManager->createQuery(
            'SELECT u
                FROM App\Entity\User u
                WHERE u.username = :query
                OR u.email = :query'
        )
            ->setParameter('query', $usernameOrEmail)
            ->getOneOrNullResult();
    }

    /*
    * SELECT count(*) FROM participant WHERE username = $username OR mail = $mail
    */
    public function findOneByUsernameAndEmail($userName,$mail): ?Participant
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.username = :userName')
            ->setParameter('userName', $userName)
            ->orWhere('p.mail = :mail')
            ->setParameter('mail',$mail)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    /*
    * SELECT * FROM participant WHERE email = $recherche_terme OR nom = $recherche_terme OR prenom = $recherche_terme
    */
    public function rechercheDetaillee($recherche_terme = null) {
        $qb = $this->createQueryBuilder('participant');

        //si le champs de rechercher participant par mot clef est renseigné
        if($recherche_terme != null){
            //ajoute de la clause where à la requete paramétrée
            $qb->andWhere('participant.email LIKE :recherche_terme')
                ->setParameter("recherche_terme",'%'.$recherche_terme.'%')
                ->orWhere("participant.nom LIKE :recherche_terme")
                ->setParameter("recherche_terme",'%'.$recherche_terme.'%')
                ->orWhere("participant.prenom LIKE :recherche_terme")
                ->setParameter("recherche_terme",'%'.$recherche_terme.'%');

            $qb->orderBy('participant.id');
        }
        return $qb->getQuery();
    }

    // /**
    //  * @return Participant[] Returns an array of Participant objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Participant
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
