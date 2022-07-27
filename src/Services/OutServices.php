<?php

namespace App\Services;

use Symfony\Component\Security\Core\User\UserInterface;

class OutServices
{
    public function actionsFilter($filteredOuts, UserInterface $connectedUser) {

        foreach ($filteredOuts as $eachFilteredOut) {

            $nbParticipants = 0;
            $outRegisteredUser = false;
            $actions = array();


            foreach ($eachFilteredOut->getParticipants() as $eachParticipant) {
                $nbParticipants ++;
                if ($eachParticipant == $connectedUser) {
                    $outRegisteredUser = true;
                }
            }

            if ($eachFilteredOut->getEtat()->getLibelle() === 'En création'
                && $eachFilteredOut->getOrganisateur() == $connectedUser) {
                $actions[] = 'Modifier';
                $actions[] = 'Publier';
            }
            else {
                $actions[] = 'Afficher';
            }

            if ($eachFilteredOut->getEtat()->getLibelle() === 'Ouverte') {
                if ($outRegisteredUser) {
                    $actions[] = 'Se désister';
                }
                else {
                    if ($eachFilteredOut->getOrganisateur() == $connectedUser) {
                        $actions[] = 'Annuler';
                    }
                    else {
                        $actions[] = 'S\'inscrire';
                    }
                }
            }

            if ($eachFilteredOut->getEtat()->getLibelle() === 'Clôturée' && $outRegisteredUser) {
                $actions[] = 'Se désister';
            }

            $eachFilteredOut->setNbParticipants($nbParticipants);
            $eachFilteredOut->setActions($actions);


        }

    }
}
