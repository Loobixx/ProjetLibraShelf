<?php

namespace App\Repository;

use App\Entity\Ouvrage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Ouvrage>
 */
class OuvrageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ouvrage::class);
    }

    public function findByFilters(array $filters): array
    {
        $qb = $this->createQueryBuilder('o');

        // Recherche par mot-clé
        if (!empty($filters['query'])) {
            $qb->andWhere('o.titre LIKE :query OR o.editeur LIKE :query')
                ->setParameter('query', '%' . $filters['query'] . '%');
        }

        // Filtre par auteur
        if (!empty($filters['auteur'])) {
            $qb->leftJoin('o.auteurs', 'a');

            if (is_object($filters['auteur']) && method_exists($filters['auteur'], 'getId')) {
                $qb->andWhere('a.id = :auteurId')
                    ->setParameter('auteurId', $filters['auteur']->getId());
            } else {
                $qb->andWhere('a.id = :auteurId')
                    ->setParameter('auteurId', $filters['auteur']);
            }
        }

        // Filtre par Catégorie
        if (!empty($filters['categorie'])) {
            $qb->andWhere('o.categories LIKE :cat')
                ->setParameter('cat', '%' . $filters['categorie'] . '%');
        }

        // Filtre par Langue
        if (!empty($filters['langue'])) {
            $qb->andWhere('o.langues LIKE :lang')
                ->setParameter('lang', '%' . $filters['langue'] . '%');
        }

        // Filtre par Année
        if (!empty($filters['annee'])) {
            $qb->andWhere('o.annee = :annee')
                ->setParameter('annee', $filters['annee']);
        }

        // Filtre Disponibilité
        if (!empty($filters['disponible']) && $filters['disponible'] == true) {
            $qb->join('o.exemplaires', 'e')
                ->andWhere('e.disponible = :isDispo')
                ->setParameter('isDispo', true);
        }

        // Gestion du Tri
        $sort = $filters['sort'] ?? 'titre_asc';

        switch ($sort) {
            case 'titre_desc':
                $qb->orderBy('o.titre', 'DESC');
                break;
            case 'annee_desc':
                $qb->orderBy('o.annee', 'DESC');
                break;
            case 'annee_asc':
                $qb->orderBy('o.annee', 'ASC');
                break;
            case 'titre_asc':
            default:
                $qb->orderBy('o.titre', 'ASC');
                break;
        }

        return $qb->getQuery()->getResult();
    }
}
