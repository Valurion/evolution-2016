<?php


class ParseDatas {
    const concat_authors = "||";
    const concat_name = "&&";


    public static function cleanString($string) {
        return strip_tags(trim($string));
    }


    public static function cleanArrayString($array) {
        return array_filter(array_map('self::cleanString', $array));
    }


    public static function cleanAttributeString($string) {
        return json_encode(self::cleanString($string));
    }


    /***********************************************************************************************
     * Fonctions permettant de transformer et nettoyer des données
     * en vue d'une insertion dans un fichier csv
     **********************************************************************************************/
    public static function cleanCSVString($string) {
        if ($string === null) return $string;
        if (is_numeric($string)) {
            $string = str_replace('.', ',', $string);
        };
        $string = str_replace("\n", ' ', $string);
        $string = str_replace('"', "''", $string);
        $string = "\"$string\"";
        return $string;
    }


    public static function arrayToCSVRow($strings) {
        $row = array_values($strings);
        $row = array_map("self::cleanCSVString", $row);
        $row = implode(';', $row);
        return $row;
    }


    public static function arrayToCSVHeader($array, $beforeColumns=array(), $afterColumns=array()) {
        if (count($array) === 0) return $array;
        $header = array_keys($array[0]);
        $header = array_merge($beforeColumns, $header, $afterColumns);
        $header = array_map("self::cleanCSVString", $header);
        $header = implode(';', $header);
        return $header;
    }


    public static function arrayToCSV($array) {
        if (count($array) === 0) return $array;
        $csv = [self::arrayToCSVHeader($array)];
        foreach ($array as $row) {
            array_push($csv, self::arrayToCSVRow($row));
        }
        return $csv;
    }


    public static function formatCSVToHTML($csv) {
        // À l'arrache, mais suffisant pour debugger un CSV
        return "<table border='1'><tr><td>" . str_replace(
            ["\n", ";"],
            ["</td></tr>\n<tr><td>", "</td><td>"],
            $csv
        ) . "</td></tr></table>";
    }

    /**
    *   Concatène et transforme pour l'affichage une liste d'auteurs récupérés depuis sql et concatenée avec des caractères.
    *
    * @param array $rawAuthors  La liste des auteurs récupérés depuis sql
    * @param int $cut  À partir de quel auteur la concaténation doit être stoppé et doit être inséré $cutAlt.
    *                   Si inférieur à 1, ce paramètre est ignoré.
    * @param str $joinAuthors  La string qui servira de concaténation entre les différents auteurs. Par défaut à ', '
    * @param str $joinName  La string qui servira de concaténation entre le nom, prénom, etc. d'un auteur. Par défaut à ' '
    * @param str $cutAlt  La string insérée en fin de concaténation si $cut est défini. Par défaut à '<i>et al.</i>'
    * @param str $withIdAuthor  Si un id est inséré dans rawAuthors. Si à vrai, cet id sera supprimé lors de la normalisation
    * @param str $splitAuthorsOn  Le caractère qui sert de pivot entre les auteurs dans la string. Par défaut à self::concat_authors
    * @param str $splitNameOn  Le caractère qui sert de pivot entre les attributs d'un auteur dans la string. Par défaut à self::concat_name
    *
    * @return str
    **/
    public function stringifyRawAuthors($rawAuthors, $cut=0, $joinAuthors=null, $joinName=null, $cutAlt=null, $withIdAuthor=true, $splitAuthorsOn=null, $splitNameOn=null) {
        $joinName = $joinName ? $joinName : ' ';
        $joinAuthors = $joinAuthors ? $joinAuthors : ', ';
        $cutAlt = $cutAlt ? $cutAlt : '<i>et al.</i>';
        $splitAuthorsOn = $splitAuthorsOn ? $splitAuthorsOn : self::concat_authors;
        $splitNameOn = $splitNameOn ? $splitNameOn : self::concat_name;

        $authors = array();
        foreach (explode($splitAuthorsOn, $rawAuthors) as $index => $author) {
            if (!!$cutAlt && ($cut > 0) && ($index >= $cut)) {
                array_push($authors, $cutAlt);
                break;
            }
            $author = explode($splitNameOn, $author);
            $author = self::cleanArrayString($author);
            if ($withIdAuthor) {
                array_pop($author);
            }
            array_push($authors, implode($joinName, $author));
        }
        $authors = implode($joinAuthors, $authors);
        return $authors;
    }


    /**
    * À partir des données récupérés depuis la base de données, reconstruit l'url
    *
    * @param const $type        Le type de données (numéro, ouvrage, auteur, etc.)
    * @param const $typepub:    Le type de publication (revue, encyclopédie, collectifs, etc.)
    *                           Pour un auteur, n'est pas utilisé
    * @param array $data:       Les données formattés pour la transformation en url
    * @return string:           L'url
    *
    * TODO: utilisé les ID_* en dernier recours
    **/
    public function reconstructUrl($type, $typepub, $datas) {
        $CONSTANTS = Service::get('Constants');
        $url = array();
        if ($type === $CONSTANTS::IS_AUTEUR) {
            $url[] = 'publications-de';
            $url[] = $datas['nom'];
            $url[] = $datas['prenom'];
            $url[] = '-'.$datas['id_auteur'];
        }
        switch ($typepub) {
            case $CONSTANTS::TYPEPUB_REVUE:
            case $CONSTANTS::TYPEPUB_MAGAZINE:
                $url[] = $typepub === $CONSTANTS::TYPEPUB_MAGAZINE ? 'magazine' : 'revue';
                $url[] = $datas['url_rewriting'];
                if ($type === $CONSTANTS::IS_NUMERO || $type === $CONSTANTS::IS_ARTICLE) {
                    $url[] = $datas['annee'];
                    $url[] = $datas['numero'];
                }
                if ($type === $CONSTANTS::IS_ARTICLE) {
                    $url[] = 'page-'.$datas['page_debut'];
                }
                break;
            case $CONSTANTS::TYPEPUB_OUVRAGE:
            case $CONSTANTS::TYPEPUB_ENCYCLOPEDIE:
                if ($type === $CONSTANTS::IS_REVUE) {
                    $url[] = 'collection';
                    $url[] = $datas['url_rewriting'];
                }
                if ($type === $CONSTANTS::IS_NUMERO || $type === $CONSTANTS::IS_ARTICLE) {
                    $url[] = $datas['url_rewriting'];
                    $url[] = '-'.$datas['isbn'];
                }
                if ($type === $CONSTANTS::IS_ARTICLE) {
                    $url[] = 'page-'.$datas['page_debut'];
                }
                break;
            default:
                break;
        }
        return implode('-', $url) . '.htm';
    }


    /* ELEMENTS EN PROVENANCE DE vue/CommonBlocs/blocAddToBasket */
    /**
    * À partir des données récupérés depuis la base de données, construit un tableau formaté
    *
    * @param array $descAchats  Paramètres d'achat de l'article (typepub, prix, ...)
    * @return array:            Tableau des données formatées
    **/
    public function getPurchasesArticle($descAchats) {
        $basket = array();
        $isPurchaseArticle = isset($descAchats['ARTICLE']);

        if ($isPurchaseArticle) {

            // Récupération du nombre de caractères
            if (isset($descAchats['countCharactersFullText'])) {
                $nbreCaracteres = ', '.number_format($descAchats['countCharactersFullText'], 0, '.', '&nbsp;').' caractères';
            }

            //Définition des données
            $achat = $descAchats['ARTICLE'][0];
            $isOuvrage = in_array($achat['REVUE_TYPEPUB'], [3, 5, 6]);
            $isMagazine = in_array($achat['REVUE_TYPEPUB'], [2]);
            $descAchat = array(
                'url' => "mon_panier.php?ID_ARTICLE=".$achat['ARTICLE_ID_ARTICLE'],
                'title' => "".($isOuvrage ? 'ce chapitre' : 'cet article')."<br />en version électronique",
                //'desc' => "La version électronique de " . ($isOuvrage ? 'ce chapitre' : "cet article")." sera immédiatement accessible en ligne sur votre compte \"Mon Cairn.info\".<br />Un lien vous sera transmis par email pour y accéder.",
                'desc' => "La version électronique de cet article " . ($isMagazine ? '(format HTML'.$nbreCaracteres.')' : '(format HTML et PDF'.$nbreCaracteres.')')." sera immédiatement accessible en ligne sur votre compte Mon Cairn.info.<br />Un lien vous sera également transmis par email pour y accéder.",
                'price' => $achat['ARTICLE_PRIX'],
                'icon' => "icon-elec-reader",
            );
            
            array_push($basket, $descAchat);
        }
        return $basket;
    }

    /**
    * À partir des données récupérés depuis la base de données, construit un tableau formaté
    *
    * @param array $descAchats          Paramètres d'achat de l'article (typepub, prix, ...)
    * @param $hasAccessToElec           Paramètre d'accès à la version electronique
    * @return array:                    Tableau des données formatées
    *
    * const PURCHASE_NUMERO_ELEC = 0;
    * const PURCHASE_NUMERO_PAPER = 1;
    **/
    public function getPurchasesNumero($descAchats, $hasAccessToElec) {
        $basket = array();
        $isSetDescAchatNumero = isset($descAchats['NUMERO']);
        $isPurchaseNumero = $isSetDescAchatNumero
            && ($descAchats['NUMERO'][0]['REVUE_ACHAT_PAPIER'] == 1)
            && (
                ($descAchats['MODE'] != 2) || (!isset($descAchats['ARTICLE']))
            )
            && ($descAchats['NUMERO'][0]['NUMERO_EPUISE'] != 1)
            && ($descAchats['NUMERO'][0]['NUMERO_PRIX'] > 0);
        $isPurchaseNumeroElec = isset($descAchats['NUMERO_ELEC'])
            && $descAchats['MODE'] != 2
            && !$hasAccessToElec
            && ($descAchats['NUMERO_ELEC'][0]['REVUE_ACHAT_ELEC'] == 1)
            && !$hasAccessToElec;

        //Modification du code le 22/01/2016. Par Dimitry (Cairn).
        $element = ($isSetDescAchatNumero && $descAchats['NUMERO'][0]['REVUE_TYPEPUB'] == 3) ? 'cet ouvrage' : 'ce numéro';

        //Pour la partie papier ou électronique;
        $numeroPapierOrElectronique = '';
        $description = '';
        if ($isSetDescAchatNumero && $descAchats['NUMERO'][0]['NUMERO_MOVINGWALL'] != '0000-00-00' && $descAchats['NUMERO'][0]['NUMERO_MOVINGWALL'] <= date('Y-m-d')) {
            $numeroPapierOrElectronique = 'papier';
            $description = "La version papier de " . $element . " vous sera envoyée par la poste à l'adresse de livraison que vous nous aurez fournie.";
        } else {
            $numeroPapierOrElectronique = 'papier + électronique';
            $description = "La version électronique de " . $element . " sera immédiatement accessible en ligne sur votre compte Mon Cairn.info.<br />Un lien vous sera transmis par email pour y accéder.<br />La version papier vous sera envoyée par la poste à l'adresse de livraison que vous nous aurez fournie.";
        }

        if ($isPurchaseNumero) {
            $achat = $descAchats['NUMERO'][0];
            array_push($basket, array(
                'url' => "mon_panier.php?ID_NUMPUBLIE=" . $achat['NUMERO_ID_NUMPUBLIE'],
                'title' => "" . $element . " en version " . $numeroPapierOrElectronique ,
                'desc' => $description,
                'price' => $achat['NUMERO_PRIX'],
                'icon' => 'icon-book-elec-reader',
                //'type' => PURCHASE_NUMERO_PAPER,
                'type' => 1,
                'version' => 'Papier + électronique'
            ));
        }
        if ($isPurchaseNumeroElec) {
            $achat = $descAchats['NUMERO_ELEC'][0];
            array_push($basket, array(
                'url' => "mon_panier.php?VERSION=ELEC&ID_NUMPUBLIE=".$achat['NUMERO_ID_NUMPUBLIE'],
                'title' => "" . $element . " en version électronique",
                'desc' => ucfirst ($element) . " sera immédiatement accessible en ligne sur votre compte Mon Cairn.info (format HTML et PDF).<br />Il ne vous sera pas envoyé : un lien vous sera transmis par email pour y accéder.",
                'price' => $achat['NUMERO_PRIX_ELEC'],
                'icon' => 'icon-elec-reader',
                //'type' => PURCHASE_NUMERO_ELEC,
                'type' => 0,
                'version' => 'Électronique'
            ));
        }
        return $basket;
    }

    /**
    * À partir des données récupérés depuis la base de données, construit un tableau formaté
    *
    * @param array $descAchats  Paramètres d'achat de l'article (typepub, prix, ...)
    * @return array:            Tableau des données formatées
    **/
    public function getPurchasesRevue($descAchats) {
        $basket     = array();

        $yearNow    = intval(date('Y'));
        $basePara   =  "Cet abonnement vous donne accès aux versions électronique (format HTML et PDF) et papier de cette revue.<br />
                        L'ensemble des numéros et des articles de cette revue sera immédiatement accessible en ligne sur votre compte Mon Cairn.info.<br />
                        Les numéros papier compris dans cet abonnement vous seront envoyés par la poste à l'adresse de livraison que vous nous aurez fournie, 
                        au fur et à mesure de leur parution.";
        $elecPara   =  "Cet abonnement vous donne accès à la version électronique (format HTML et PDF) de cette revue. Vous ne recevrez pas de numéros papier, 
                        mais l'ensemble des numéros et des articles de cette revue sera immédiatement accessible en ligne sur votre compte Mon Cairn.info.";

        if (!isset($descAchats['REVUE'])) $descAchats['REVUE'] = array();
        foreach ($descAchats['REVUE'] as $achat) {

            // Définition de l'URL
            $baseUrl = 'mon_panier.php?ID_REVUE='.$achat['ID_REVUE'].'&ID_ABON='.$achat['ID_ABON'];

            // Définition du paragraphe  
            // Version électronique uniquement              
            if($achat['ELEC_SEUL'] == 1) {$paragraphe = $elecPara;} 
            // Version papier ET électronique
            else {$paragraphe = $basePara;}

            // Types
            if ($achat['TYPE'] == 0) {
                //Chargement de l'année -1
                if (str_replace(' ', '', $achat['NEXTANNEE']) == '-1') {
                   array_push($basket, array(
                        'url' => $baseUrl.'&ANNEE='.($yearNow - 1),
                        'title' => $achat['LIBELLE'].' '.($yearNow - 1),
                        'desc' => $paragraphe,
                        'price' => $achat['PRIX'],
                        'icon' => 'icon-book-elec-reader',
                        'periode' => ($yearNow - 1)."/1",
                        'elec_seul' => $achat['ELEC_SEUL'],
                        'formule' => $achat['LIBELLE']
                    ));
                }

                //Chargement de l'année en cours.
                array_push($basket, array(
                    'url' => $baseUrl.'&ANNEE='.$yearNow,
                    'title' => $achat['LIBELLE'].' '.($achat['TYPE'] == 0 ? $yearNow : ''),
                    'desc' => $paragraphe,
                    'price' => $achat['PRIX'],
                    'icon' => 'icon-book-elec-reader',
                    'periode' => $yearNow."/1",
                    'elec_seul' => $achat['ELEC_SEUL'],
                    'formule' => $achat['LIBELLE']
                ));

                //chargement de l'année n + 1.
                if (str_replace(' ', '', $achat['NEXTANNEE']) == '0') {
                    array_push($basket, array(
                        'url' => $baseUrl.'&ANNEE='.($yearNow + 1),
                        'title' => $achat['LIBELLE'].' '.($yearNow + 1),
                        'desc' => $paragraphe,
                        'price' => $achat['PRIX'],
                        'icon' => 'icon-book-elec-reader',
                        'periode' => ($yearNow + 1)."/1",
                        'elec_seul' => $achat['ELEC_SEUL'],
                        'formule' => $achat['LIBELLE']
                    ));
                }

            } else {
                foreach ($achat['LAST_NUMS'] as $achatLastNumero) {
                    array_push($basket, array(
                        'url' => $baseUrl.'&ID_NUMPUBLIE='.$achatLastNumero['ID_NUMPUBLIE'],
                        'title' => $achat['LIBELLE'].' <span class="yellow" style="margin-left: 0.5em; font-size: 0.9em;">(à partir du n°'.$achatLastNumero['ANNEE'].'/'.$achatLastNumero['NUMERO'].')</span>',
                        'desc' => $paragraphe,
                        'price' => $achat['PRIX'],
                        'icon' => 'icon-book-elec-reader',
                        'periode' => $achatLastNumero['ANNEE'].'/'.$achatLastNumero['NUMERO'],
                        'elec_seul' => $achat['ELEC_SEUL'],
                        'formule' => $achat['LIBELLE']
                    ));
                }
            }
        };
        return $basket;
    }

    // Sur base de getPurchasesRevue()
    public function countPurchaseRevue($descAchats) {
        // Init
        $count = 0;

        // Boucle sur le tableau
        foreach ($descAchats['REVUE'] as $achat) {
            if ($achat['TYPE'] == 0) {
                if (str_replace(' ', '', $achat['NEXTANNEE']) == '-1') {$count++;}
                if (str_replace(' ', '', $achat['NEXTANNEE']) == '0') {$count++;}
                $count++;
            }
            else {
                foreach ($achat['LAST_NUMS'] as $achatLastNumero) {
                    $count++;
                }
            } 
        }

        return $count;
    }

    // Sur base de getPurchasesRevue(), on récupère le prix le plus haut et le prix le plus bas
    public function MinAndMaxPriceOfRevue($descAchats) {
        // Init
        $price      = array();
        $minPrice   = 9999; // Valeur haute (le prix ne pourra être que plus petit)
        $maxPrice   = 0;    // Valeur basse (le plix ne pourra être que plus grand)

        // Boucle sur le tableau
        foreach ($descAchats['REVUE'] as $achat) {
            if ($achat['TYPE'] == 0) {
                if($achat['PRIX'] < $minPrice) {$minPrice = $achat['PRIX'];}
                if($achat['PRIX'] > $maxPrice) {$maxPrice = $achat['PRIX'];}
            }
            else {
                foreach ($achat['LAST_NUMS'] as $achatLastNumero) {
                    if($achat['PRIX'] < $minPrice) {$minPrice = $achat['PRIX'];}
                    if($achat['PRIX'] > $maxPrice) {$maxPrice = $achat['PRIX'];}
                }
            }
        }

        // Prix initial
        if($minPrice == 9999) {$minPrice = 0;}
        $price["minPrice"] = $minPrice;
        $price["maxPrice"] = $maxPrice;
        return $price;
    }

    // Sur base de getPurchasesNumero()
    public function countPurchaseNumero($descAchats, $accessElecOk) {
        // Init
        $purchase   = Service::get("ParseDatas")->getPurchasesNumero($descAchats, $accessElecOk);
        $count      = count($purchase);
        return $count;
    }

    // Sur base de getPurchasesNumero(), on récupère le prix le plus haut et le prix le plus bas
    public function MinAndMaxPriceOfNumero($descAchats) {
        // Init
        $purchases  = Service::get("ParseDatas")->getPurchasesNumero($descAchats, $accessElecOk);
        $price      = array();
        $minPrice   = 9999; // Valeur haute (le prix ne pourra être que plus petit)
        $maxPrice   = 0;    // Valeur basse (le plix ne pourra être que plus grand)

        // Boucle sur le tableau
        foreach ($purchases as $purchase) {
            if($purchase['price'] < $minPrice) {$minPrice = $purchase['price'];} 
            if($purchase['price'] > $maxPrice) {$maxPrice = $purchase['price'];} 
        }

        // Prix initial
        if($minPrice == 9999) {$minPrice = 0;}
        $price["minPrice"] = $minPrice;
        $price["maxPrice"] = $maxPrice;
        return $price;
    }

    // Sur base de getPurchasesArticle()
    public function countPurchaseArticle($descAchats) {
        // Init
        $purchase   = Service::get("ParseDatas")->getPurchasesArticle($descAchats);
        $count      = count($purchase);
        return $count;
    }

    // Sur base de getPurchasesArticle(), on récupère le prix le plus haut et le prix le plus bas
    public function MinAndMaxPriceOfArticle($descAchats) {
        // Init
        $purchases  = Service::get("ParseDatas")->getPurchasesArticle($descAchats);
        $price      = array();
        $minPrice   = 9999; // Valeur haute (le prix ne pourra être que plus petit)
        $maxPrice   = 0;    // Valeur basse (le plix ne pourra être que plus grand)

        // Boucle sur le tableau
        foreach ($purchases as $purchase) {
            if($purchase['price'] < $minPrice) {$minPrice = $purchase['price'];} 
            if($purchase['price'] > $maxPrice) {$maxPrice = $purchase['price'];} 
        }

        // Prix initial
        if($minPrice == 9999) {$minPrice = 0;}
        $price["minPrice"] = $minPrice;
        $price["maxPrice"] = $maxPrice;
        return $price;
    }


    /***********************************************************************************************
     * Fonctions permettant de récupérer le nom du mois en fonction de la langue choisie
     * Ex.: 1 => Janvier, 12 => Décembre
     **********************************************************************************************/
    public function getMonthLabel($mois, $lang) {
        // Tableau des mois
        $array = array("fr" => array(1 => "janvier", "février", "mars", "avril", "mai", "juin", "juillet", "août", "septembre", "octobre", "novembre", "decembre"),
                       "en" => array(1 => "january", "february", "march", "april", "may", "june", "july", "august", "september", "october", "november", "december"));

        // Nettoyage (si la valeur du mois commence par un 0)
        $mois = ltrim($mois, "0");

        // Retour
        return $array[$lang][$mois];
    }

    /***********************************************************************************************
     * Cette fonction construit l'URL CrossDomain suivant les données de la configuration
     **********************************************************************************************/
    public function getCrossDomainUrl() {
        // Récupération des valeurs
        //$protocol   = trim(Configuration::get('crossDomainProtocol', null));  // HTTP ou HTTPS
        $protocol   = "http";
        $domain     = trim(Configuration::get('crossDomainUrl', null));       // Domaine complet

        // Uniformisation avant traitement
        if(substr($protocol, -3) == "://") {$protocol = substr($protocol, 0, -3);}
        if(substr($domain, -1) == "/") {$domain = substr($domain, 0, -1);}

        // Construction
        $url = $protocol."://".$domain; // Ex.: http://www.cairn.info 

        // Retour
        return $url;
    }
}
