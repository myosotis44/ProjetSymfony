<?php

namespace App\Services;

use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use Symfony\Component\Security\Core\User\UserInterface;

class OutServices
{
    protected $etatRepository;
    protected $sortieRepository;

    public function __construct(EtatRepository $etatRepository, SortieRepository $sortieRepository) {
        $this->etatRepository = $etatRepository;
        $this->sortieRepository = $sortieRepository;
    }

    public function actionsFilter($filteredOuts, UserInterface $connectedUser) {

        foreach ($filteredOuts as $eachFilteredOut) {

            $outRegisteredUser = $eachFilteredOut->getParticipants()->contains($connectedUser);
            $actions = array();

          if ($eachFilteredOut->getEtat()->getLibelle() === 'En création'
                && $eachFilteredOut->getOrganisateur() == $connectedUser) {
                $actions[] = 'Modifier';
                $actions[] = 'Publier';
            }
            else {
                $actions[] = 'Afficher';
            }

            if ($eachFilteredOut->getEtat()->getLibelle() === 'Ouverte') {

                if (new \DateTime() > $eachFilteredOut->getDateLimiteInscription()) {

                    $eachFilteredOut->setEtat($this->etatRepository->findOneBy(['libelle'=>'Clôturée']));
                    $this->sortieRepository->add($eachFilteredOut, true);
                }
                else {
                    if ($eachFilteredOut->getOrganisateur() == $connectedUser) {
                        $actions[] = 'Annuler';
                    }
                    if ($outRegisteredUser) {
                        $actions[] = 'Se désister';
                    }
                    else {
                        $actions[] = 'S\'inscrire';
                    }
                }
            }

            if ($eachFilteredOut->getEtat()->getLibelle() === 'Clôturée') {

                if ( new \DateTime() > $eachFilteredOut->getDateHeureDebut()) {
                    $eachFilteredOut->setEtat($this->etatRepository->findOneBy(['libelle'=>'Activité en cours']));
                    $this->sortieRepository->add($eachFilteredOut, true);
                }
                else {
                    if ($eachFilteredOut->getOrganisateur() == $connectedUser) {
                        $actions[] = 'Annuler';
                    }
                    if ($outRegisteredUser) {
                        $actions[] = 'Se désister';
                    }
                }
            }

            if ($eachFilteredOut->getEtat()->getLibelle() === 'Activité en cours') {

                $DateHeureDebut = new \DateTime($eachFilteredOut->getDateHeureDebut()->format('d-m-Y H:i'));
                $duree = $eachFilteredOut->getDuree();
                $DateHeureFin = $DateHeureDebut->modify("+ $duree minutes");

                if ( new \DateTime() > $DateHeureFin) {
                    $eachFilteredOut->setEtat($this->etatRepository->findOneBy(['libelle'=>'Activité Terminée']));
                    $this->sortieRepository->add($eachFilteredOut, true);
                }
            }

            if ($eachFilteredOut->getEtat()->getLibelle() === 'Activité Terminée' ||
                $eachFilteredOut->getEtat()->getLibelle() === 'Annulée') {

                $DateHeureDebut = new \DateTime($eachFilteredOut->getDateHeureDebut()->format('d-m-Y H:i'));
                $duree = $eachFilteredOut->getDuree() + (30 * 24 * 60);
                $DateHeureHistorisation = $DateHeureDebut->modify("+ $duree minutes");

                if ( new \DateTime() > $DateHeureHistorisation) {
                    $eachFilteredOut->setEtat($this->etatRepository->findOneBy(['libelle'=>'Activité Historisée']));
                    $this->sortieRepository->add($eachFilteredOut, true);
                }
            }

            $eachFilteredOut->setNbParticipants($eachFilteredOut->getParticipants()->count());

            $eachFilteredOut->setActions($actions);
        }

        return $filteredOuts;

    }
}
