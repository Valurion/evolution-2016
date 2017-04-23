<?php
    // Fonctions spécifiques
    // Vérifie si le dernier caractère de la chaine est un point de ponctuation
    function hasPonctuation($string) {
        // Tableau
        $array      = array(".", ";", "?", "!", ",");
        $lastChar   = substr($string, -1);

        if (in_array($lastChar, $array)) {return "1";}
        else {return "0";}
    }
    // Si le titre ne comporte pas de point de ponctuation, on lui ajoute un point (par défaut)
    function formatTitre($string) {
        if(hasPonctuation($string) == "0") {return $string.". ";}
        else {return $string;}
    }
    // Supprime la ponctuation
    function removePonctuation($string) {
        if(hasPonctuation($string) == "1") {return trim(substr($string, 0, -1));}
        else {return $string;}
    }
    // Formatage des auteurs et des contributeurs du numéro au format des auteurs de l'article
    function formatNumeroAuteurs($auteurs) {
        // Init
        $liste = array();

        foreach($auteurs as $auteur) {
            $liste[] = $auteur["AUTEUR_PRENOM"].":".$auteur["AUTEUR_NOM"].":".$auteur["AUTEUR_ID_AUTEUR"].":".$auteur["AUTEUR_ATTRIBUT"];
        }
        return implode(",", $liste);
    }
    // Formatage des auteurs afin qu'ils deviennent des contributeurs (chapitre des ouvrages)
    function formatAuteursToContributeur($auteurs, $contribution) {
        // Init
        $liste = array();

        foreach($auteurs as $i => $auteur) {
            if($i == 0) {$liste[] = $auteur["AUTEUR_PRENOM"].":".$auteur["AUTEUR_NOM"].":".$auteur["AUTEUR_ID_AUTEUR"].":".$contribution;}
            else {$liste[] = $auteur["AUTEUR_PRENOM"].":".$auteur["AUTEUR_NOM"].":".$auteur["AUTEUR_ID_AUTEUR"].":";}
        }
        return implode(",", $liste);
    }

    // Formatage des auteurs et des contributeurs (retourne un array avec les auteurs et les contributeurs)
    function formatAuteurEtContributeurs($auteurs_et_contributeurs) {
        // Init
        $liste_auteurs          = "";
        $liste_contributeurs    = "";
        $countAuteurs           = 0;
        $countContrib           = 0;

        // Explosion du tableau des auteurs
        if($auteurs_et_contributeurs != "") {
            $auteurs = explode(",", $auteurs_et_contributeurs);

            // Parcours de chaque élément du tableau (chaque auteur & contributeur)
            foreach ($auteurs as $auteur) {
                // Explosion du tableau
                $auteurParams       = explode(':', $auteur);
                $auteurPrenom       = $auteurParams[0];
                $auteurNom          = $auteurParams[1];
                
                // Définition des contributeurs (ceux ayant un attribut)
                if(($auteurParams[3]) && (trim($auteurParams[3]) != "")) {
                    $auteurContribution = strtolower($auteurParams[3]);
                    $liste_contributeurs .= $auteurContribution.' <span class="UpperCase">' . $auteurNom . '</span> ' . $auteurPrenom . ', ';
                    $countContrib++;
                }
                // Définition des auteurs (ceux n'ayant pas d'attribut)
                else {
                    // Tant qu'il n'y a pas de contribution, il s'agit d'un auteur
                    if($countContrib == 0) {
                        // On affiche MAXIMUM 3 auteurs
                        if($countAuteurs < 3) {
                            $liste_auteurs .= '<span class="UpperCase">' . $auteurNom . '</span> ' . $auteurPrenom . ', ';
                        }
                        $countAuteurs++;
                    }
                    // ...sinon, c'est un contributeur
                    else {
                        if($countContrib < 3) {
                            $liste_contributeurs .= '<span class="UpperCase">' . $auteurNom . '</span> ' . $auteurPrenom . ', ';                            
                        }
                        $countContrib++;
                    }
                }
            }
            // Nettoyage des données
            // Si il y a plus de 3 auteurs/contributeurs, on ajoute et al. à la suite
            if($countAuteurs > 3) {$liste_auteurs = rtrim($liste_auteurs, ", ");$liste_auteurs .= "<i> et al.</i>, ";}
            if($countContrib > 3) {$liste_contributeurs = rtrim($liste_contributeurs, ", ");$liste_contributeurs .= "<i> et al</i>";} // On ne marque pas le . ici car les contributeurs terminent par un . (voir plus bas)

            // Cas spéciaux
            // Certains noms d'auteurs contiennent des initiales, on remplace donc (on ajoute aussi le cas d'une double virgule juste au cas où) :
            $liste_auteurs          = str_replace(array(".,", ",,"), ",", $liste_auteurs);
            $liste_contributeurs    = str_replace(array(".,", ",,"), ",", $liste_contributeurs);
        }

        // Retour des données
        return array("AUTEURS" => $liste_auteurs, "CONTRIBUTEURS" => $liste_contributeurs);
    }

    // Formatage des auteurs et des contributeurs pour le format MLA (retourne un array avec les auteurs et les contributeurs)
    // Il existe quelques différences, notamment dans le nombre de contributeurs à afficher, l'italique à ne pas afficher sur le terme et al, ...
    function formatAuteurEtContributeursMLA($auteurs_et_contributeurs) {
        // Init
        $liste_auteurs          = "";
        $liste_contributeurs    = "";
        $countAuteurs           = 0;
        $countContrib           = 0;

        $totalAuteurs           = 0;
        $totalContrib           = 0;

        $firstAuteur            = "";
        $firstContrib           = "";

        // Explosion du tableau des auteurs
        if($auteurs_et_contributeurs != "") {
            $auteurs = explode(",", $auteurs_et_contributeurs);

            // Calcul du nombre total AVANT traitement
            foreach ($auteurs as $auteur) {
                // Explosion du tableau
                $auteurParams       = explode(':', $auteur);
                $auteurPrenom       = $auteurParams[0];
                $auteurNom          = $auteurParams[1];

                // Définition des contributeurs (ceux ayant un attribut)
                if(($auteurParams[3]) && (trim($auteurParams[3]) != "")) { $totalContrib++; }
                // Définition des auteurs (ceux n'ayant pas d'attribut)
                else {
                    // Tant qu'il n'y a pas de contribution, il s'agit d'un auteur
                    if($countContrib == 0) { $totalAuteurs++; }
                    // ...sinon, c'est un contributeur
                    else { $totalContrib++; }
                }
            }

            // Parcours de chaque élément du tableau (chaque auteur & contributeur)
            foreach ($auteurs as $auteur) {
                // Explosion du tableau
                $auteurParams       = explode(':', $auteur);
                $auteurPrenom       = $auteurParams[0];
                $auteurNom          = $auteurParams[1];
                
                // Définition des contributeurs (ceux ayant un attribut)
                if(($auteurParams[3]) && (trim($auteurParams[3]) != "")) {
                    $auteurContribution = strtolower($auteurParams[3]);
                    $liste_contributeurs .= $auteurContribution.' <span class="UpperCase">' . $auteurNom . '</span> ' . $auteurPrenom . ', ';
                    $countContrib++;

                    // On concerve le 1er contributeur
                    if($countContrib == 1) {$firstContrib = $liste_contributeurs;}
                }
                // Définition des auteurs (ceux n'ayant pas d'attribut)
                else {
                    // Tant qu'il n'y a pas de contribution, il s'agit d'un auteur
                    if($countContrib == 0) {
                        // On affiche MAXIMUM 3 auteurs
                        if($countAuteurs < 3) {
                            // On traite le 1er auteur différemment (et on le conserve)
                            if($countAuteurs == 0) {$liste_auteurs .= $firstAuteur = '<span class="UpperCase">' . $auteurNom . '</span>, ' . $auteurPrenom . ', ';}
                            // Les suivants...
                            else {
                                // Pour le dernier auteur, on affiche un ET
                                if($countAuteurs == $totalAuteurs-1) {$liste_auteurs .= ' et ' . $auteurPrenom . ' <span class="UpperCase">' . $auteurNom . '</span>, ';}
                                else {$liste_auteurs .= $auteurPrenom . ' <span class="UpperCase">' . $auteurNom . '</span>, ';}
                            }
                        }
                        $countAuteurs++;
                    }
                    // ...sinon, c'est un contributeur
                    else {
                        if($countContrib < 3) {
                            $liste_contributeurs .= '<span class="UpperCase">' . $auteurNom . '</span> ' . $auteurPrenom . ', ';                            
                        }
                        $countContrib++;
                    }
                }
            }
            // Nettoyage des données
            // Si il y a plus de 3 auteurs/contributeurs, on ajoute et al. à la suite
            if($countAuteurs > 3) {$liste_auteurs = $firstAuteur." et al";}
            if($countContrib > 3) {$liste_contributeurs = $firstContrib." et al";} // On ne marque pas le . ici car les contributeurs terminent par un . (voir plus bas)

            // Cas spéciaux
            // Certains noms d'auteurs contiennent des initiales, on remplace donc (on ajoute aussi le cas d'une double virgule juste au cas où) :
            $liste_auteurs          = str_replace(array(".,", ",,"), ",", $liste_auteurs);
            $liste_contributeurs    = str_replace(array(".,", ",,"), ",", $liste_contributeurs);
        }

        // Retour des données
        return array("AUTEURS" => $liste_auteurs, "CONTRIBUTEURS" => $liste_contributeurs);
    }

    // Formatage des auteurs et des contributeurs (retourne un array avec les auteurs et les contributeurs)
    function formatAuteurEtContributeursAPA($auteurs_et_contributeurs) {
        // Init
        $liste_auteurs          = "";
        $liste_contributeurs    = "";
        $countAuteurs           = 0;
        $countContrib           = 0;

        $totalAuteurs           = 0;
        $totalContrib           = 0;

        $lastAuteur             = "";
        $lastContrib            = "";

        // Explosion du tableau des auteurs
        if($auteurs_et_contributeurs != "") {
            $auteurs = explode(",", $auteurs_et_contributeurs);

            // Calcul du nombre total AVANT traitement
            foreach ($auteurs as $auteur) {
                // Explosion du tableau
                $auteurParams       = explode(':', $auteur);
                $auteurPrenom       = $auteurParams[0];
                $auteurNom          = $auteurParams[1];

                // Définition des contributeurs (ceux ayant un attribut)
                if(($auteurParams[3]) && (trim($auteurParams[3]) != "")) { $totalContrib++; }
                // Définition des auteurs (ceux n'ayant pas d'attribut)
                else {
                    // Tant qu'il n'y a pas de contribution, il s'agit d'un auteur
                    if($totalContrib == 0) { $totalAuteurs++; }
                    // ...sinon, c'est un contributeur
                    else { $totalContrib++; }
                }
            }

            // Parcours de chaque élément du tableau (chaque auteur & contributeur)
            foreach ($auteurs as $auteur) {
                // Explosion du tableau
                $auteurParams       = explode(':', $auteur);
                $auteurPrenom       = trim($auteurParams[0]);
                $auteurNom          = trim($auteurParams[1]);
                
                // Définition des contributeurs (ceux ayant un attribut)
                if(($auteurParams[3]) && (trim($auteurParams[3]) != "")) {
                    $auteurContribution = strtolower($auteurParams[3]);
                    //$liste_contributeurs .=  substr($auteurPrenom, 0, 1) .'. <span class="UpperCase">' . $auteurNom . '</span>, ';
                    $liste_contributeurs .=  mb_substr($auteurPrenom, 0, 1, "utf-8") .'. <span class="UpperCase">' . $auteurNom . '</span>, ';
                    $countContrib++;
                }
                // Définition des auteurs (ceux n'ayant pas d'attribut)
                else {
                    // Tant qu'il n'y a pas de contribution, il s'agit d'un auteur
                    if($countContrib == 0) {                        

                        // Pour le dernier auteur, on affiche un
                        if(($countAuteurs == $totalAuteurs-1) && ($totalAuteurs > 1)) {
                            //$liste_auteurs .= '& <span class="UpperCase">' . $auteurNom . '</span>, ' . substr($auteurPrenom, 0, 1) . '., ';
                            $liste_auteurs .= '& <span class="UpperCase">' . $auteurNom . '</span>, ' . mb_substr($auteurPrenom, 0, 1, "utf-8") . '., ';
                        }
                        else {
                            // Si il y a plus de 8 auteurs
                            if($totalAuteurs > 8) {
                                if($countAuteurs < 6) {
                                    //$liste_auteurs .= '<span class="UpperCase">' . $auteurNom . '</span>, ' . substr($auteurPrenom, 0, 1) . '., ';
                                    $liste_auteurs .= '<span class="UpperCase">' . $auteurNom . '</span>, ' . mb_substr($auteurPrenom, 0, 1, "utf-8") . '., ';
                                }
                                if($countAuteurs == 6) {
                                    $liste_auteurs .= "...";
                                }
                            }
                            // Moins de 8 auteurs
                            else {
                                //$liste_auteurs .= '<span class="UpperCase">' . $auteurNom . '</span>, ' . substr($auteurPrenom, 0, 1) . '., ';
                                $liste_auteurs .= '<span class="UpperCase">' . $auteurNom . '</span>, ' . mb_substr($auteurPrenom, 0, 1, "utf-8") . '., ';
                            }
                        }
                        $countAuteurs++;
                    }
                    // ...sinon, c'est un contributeur
                    else {
                        // Pour le dernier auteur, on affiche un (Dir)
                        if(($countContrib == $totalContrib-1) && ($totalContrib > 1)) {
                            //$liste_contributeurs .=  '& '.substr($auteurPrenom, 0, 1) .'. <span class="UpperCase">' . $auteurNom . '</span> (Dir), ';
                            $liste_contributeurs .=  '& '.mb_substr($auteurPrenom, 0, 1, "utf-8") .'. <span class="UpperCase">' . $auteurNom . '</span> (Dir), ';
                        }
                        else {
                            //$liste_contributeurs .=  substr($auteurPrenom, 0, 1) .'. <span class="UpperCase">' . $auteurNom . '</span>, ';
                            $liste_contributeurs .=  mb_substr($auteurPrenom, 0, 1, "utf-8") .'. <span class="UpperCase">' . $auteurNom . '</span>, ';
                        }
                        $countContrib++;
                    }                    
                }
            }            
            // Cas spéciaux
            // Certains noms contiennent des éléments à remplacer :
            $liste_auteurs          = str_replace(array(", &", ",&"), " &", $liste_auteurs);
            $liste_auteurs          = str_replace(array("., ..."), " ... ", $liste_auteurs);
            $liste_auteurs          = str_replace(array(".."), ".", $liste_auteurs);

            $liste_contributeurs    = str_replace(array(", &", ",&"), " &", $liste_contributeurs);
            $liste_contributeurs    = str_replace(array("., ..."), " ... ", $liste_contributeurs);
            $liste_contributeurs    = str_replace(array(".."), ".", $liste_contributeurs);     
        }

        // Retour des données
        return array("AUTEURS" => $liste_auteurs, "CONTRIBUTEURS" => $liste_contributeurs);
    }

    // Cette fonction traite les contributeurs comme les auteurs
    function formatContributeursAtFirstAPA($auteurs_et_contributeurs) {
        // Init
        $liste_contributeurs    = "";
        $liste_traducteurs      = "";

        $countContrib           = 0;
        $totalContrib           = 0;

        $countTraducteur        = 0;
        $totalTraducteur        = 0;

        $lastContrib            = "";
        $lastTraducteur         = "";

        // Explosion du tableau des auteurs
        if($auteurs_et_contributeurs != "") {
            $auteurs = explode(",", $auteurs_et_contributeurs);

            // Calcul du nombre total AVANT traitement
            foreach ($auteurs as $auteur) {
                // Explosion du tableau
                $auteurParams       = explode(':', $auteur);
                $auteurPrenom       = $auteurParams[0];
                $auteurNom          = $auteurParams[1];
                $auteurContribution = strtolower($auteurParams[3]);

                // Définition des contributeurs et des traducteurs
                if(($auteurContribution) && (trim($auteurContribution) != "")) {
                    if(strpos($auteurContribution, 'trad') !== false) {$totalTraducteur++;}
                    else {$totalContrib++;}
                }
                // Définition des auteurs (ceux n'ayant pas d'attribut)
                else {
                    // Comptage des contributeurs et des traducteurs
                    // Les contributeurs sont listés AVANT les traducteurs
                    if($totalContrib != 0 && $totalTraducteur == 0) { $totalContrib++; }
                    else {$totalTraducteur++;}
                }
            }

            // Parcours de chaque élément du tableau (chaque auteur & contributeur)
            foreach ($auteurs as $auteur) {
                // Explosion du tableau
                $auteurParams       = explode(':', $auteur);
                $auteurPrenom       = trim($auteurParams[0]);
                $auteurNom          = trim($auteurParams[1]);

                // Détection des initiales
                //if(strpos($auteurPrenom, ".") === false) {$auteurPrenom = substr($auteurPrenom, 0, 1);}
                if(strpos($auteurPrenom, ".") === false) {$auteurPrenom = mb_substr($auteurPrenom, 0, 1, "utf-8");}
                
                // Définition des contributeurs et traducteurs (ceux ayant un attribut)
                if(($auteurParams[3]) && (trim($auteurParams[3]) != "")) {
                    $auteurContribution = strtolower($auteurParams[3]);
                    
                    // Liste des traducteurs
                    if(strpos($auteurContribution, 'trad') !== false) {
                        $liste_traducteurs .= '<span class="UpperCase">' . $auteurNom . '</span>, ' . $auteurPrenom . '., ';
                    }
                    // Liste des contributeurs
                    else {
                        $liste_contributeurs .= '<span class="UpperCase">' . $auteurNom . '</span>, ' . $auteurPrenom . '., ';
                        $countContrib++;
                    }
                }
                // Définition des contributeurs et des traducteurs
                else {
                    // Tant qu'il n'y a pas de traducteur, il s'agit d'un contributeur...
                    if($countTraducteur == 0) { 
                        // Pour le dernier contributeur, on affiche un (Dir)
                        if(($countContrib == $totalContrib-1) && ($totalContrib > 1)) {
                            $liste_contributeurs .= '& <span class="UpperCase">' . $auteurNom . '</span>, ' . $auteurPrenom . '. (Dir)';
                        }
                        else {
                            $liste_contributeurs .= '<span class="UpperCase">' . $auteurNom . '</span>, ' . $auteurPrenom . '., ';
                        }
                        $countContrib++;
                    }
                    // ...sinon, c'est un traducteur
                    else {  
                        // Pour le dernier traducteur, on affiche un (Trad)
                        if(($countTraducteur == $totalTraducteur-1) && ($totalTraducteur > 1)) {
                            $liste_traducteurs .= '& <span class="UpperCase">' . $auteurNom . '</span>, ' . $auteurPrenom . '. (Trad)';
                        }
                        else {
                            $liste_traducteurs .= '<span class="UpperCase">' . $auteurNom . '</span>, ' . $auteurPrenom . '., ';
                        }
                        $countTraducteur++;
                    }                    
                }
            }            
            // Cas spéciaux
            // Certains noms contiennent des éléments à remplacer :
            $liste_contributeurs    = str_replace(array(", &", ",&"), " &", $liste_contributeurs);
            $liste_contributeurs    = str_replace(array("., ..."), " ... ", $liste_contributeurs);
            $liste_contributeurs    = str_replace(array(".."), ".", $liste_contributeurs);

            $liste_traducteurs    = str_replace(array(", &", ",&"), " &", $liste_traducteurs);
            $liste_traducteurs    = str_replace(array("., ..."), " ... ", $liste_traducteurs);
            $liste_traducteurs    = str_replace(array(".."), ".", $liste_traducteurs);      
        }

        // Retour des données
        return array("CONTRIBUTEURS" => $liste_contributeurs, "TRADUCTEURS" => $liste_traducteurs);
    }
?>