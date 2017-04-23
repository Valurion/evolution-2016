<?php

require_once 'Modele/Content.php';
require_once 'Modele/ContentCom.php';

/**
 * Ce service prend en charge les vérifications liées aux possibilités d'achat sur une page
 *
 * @author benjamin
 */
class ControleAchat {

    private static $ACHAT_PARTICULIER = 1;
    private static $ACHAT_INSTITUTION = 2;
    private static $ACHAT_INACTIF = 0;
    private $mode_achat = null;
    private $content;
    private $contentCom;

    function __construct() {
        $this->content = new Content();
        $this->contentCom = new ContentCom('dsn_com');
    }

    /*
     * Méthode principale de vérification des achats disponibles pour
     *  - une revue,
     *  - une revue et un numéro,
     *  - une revue, un numéro et un article
     *
     * Vérification sur base
     *  - des connexions actives (institution et profilage)
     *  - des valeurs d'achat dans les tables REVUE, NUMERO et ARTICLE
     *  - des abonnements disponibles
     *
     * @param $authInfos array reprend l'ensemble des informations d'authentification
     * @param $revue array record revue
     * @param $numero array record numero
     * @param $article array record article
     *
     * @return $array informations à afficher sur le bloc d'achat
     *
     */

    public function checkAchats($authInfos, $revue, $numero = null, $article = null) {
        $this->getModeAchat($authInfos);
        $achats = array();
        if ($this->mode_achat != self::$ACHAT_INACTIF) {
            $REVUE = $this->checkRevue($authInfos, $revue);
            if (!empty($REVUE)) {
                $achats['REVUE'] = $REVUE;
            }
            $NUMERO = $this->checkNumero($authInfos, $revue, $numero);
            if (!empty($NUMERO)) {
                $achats['NUMERO'] = $NUMERO;
                if ($NUMERO[0]['NUMERO_PRIX_ELEC'] > 0) {
                    $achats['NUMERO_ELEC'] = $NUMERO;
                }
            }
            $ARTICLE = $this->checkArticle($authInfos, $revue, $numero, $article);
            if (!empty($ARTICLE)) {
                $achats['ARTICLE'] = $ARTICLE;
            }
        }
        if (!empty($achats)) {
            $achats['DISPLAY_BLOC_ACHAT'] = 1;
            $achats["TITRE_GEN"] = (isset($revue['TITRE']) ? $revue['TITRE'] : $revue['REVUE_TITRE']);
        }
        $achats["MODE"] = $this->mode_achat;
        return $achats;
    }

    /*
     * Effectue le contrôle des possibilités d'achat de niveau REVUE
     *
     * @param $authInfos array reprend l'ensemble des informations d'authentification
     * @param $revue array record revue
     *
     * @return $array informations à afficher sur le bloc d'achat
     */

    private function checkRevue($authInfos, $revue) {
        $achatRev = array();
        if ((isset($revue['ABO_ANNEE']) ? $revue['ABO_ANNEE'] : $revue['REVUE_ABO_ANNEE']) == 1) {
            $abos = $this->content->getRevueAbos((isset($revue['ID_REVUE']) ? $revue['ID_REVUE'] : $revue['REVUE_ID_REVUE']));
            foreach ($abos as $abo) {
                if ($abo['TYPE'] == 1) {
                    $lastNums = $this->content->getNumPublieByIdRevue((isset($revue['ID_REVUE']) ? $revue['ID_REVUE'] : $revue['REVUE_ID_REVUE']), 3);
                    $abo["LAST_NUMS"] = $lastNums;
                }
                $achatRev[] = $abo;
            }
        }
        return $achatRev;
    }

    /*
     * Effectue le contrôle des possibilités d'achat de niveau NUMERO
     *
     * @param $authInfos array reprend l'ensemble des informations d'authentification
     * @param $revue array record revue
     * @param $numero array record numero
     *
     * @return $array informations à afficher sur le bloc d'achat
     */

    private function checkNumero($authInfos, $revue, $numero) {
        $achatNum = array();
        $activeAchat = false;

        //Condition pour l'achat de la version numérique.
        if ($numero['NUMERO_PRIX'] > 0 && ($numero['NUMERO_MOVINGWALL'] == '0000-00-00' || $numero['NUMERO_MOVINGWALL'] > date('Y-m-d')) && (isset($revue['ACHAT_NUMERO']) ? $revue['ACHAT_NUMERO'] : $revue['REVUE_ACHAT_NUMERO']) == 1 || $numero['NUMERO_PRIX_ELEC'] > 0) {
            $activeAchat = true;
        } elseif($revue['ACHAT_NUMERO'] == 1 && $numero['NUMERO_EPUISE'] == '0' && $numero['NUMERO_PRIX'] > 0) {//Achat pour la version papier.
            $activeAchat = true;
        }

        if ($activeAchat) {
            $achatNum[] = $numero;
            $achatNum[0]['REVUE_TITRE'] = (isset($revue['TITRE']) ? $revue['TITRE'] : $revue['REVUE_TITRE']);
            $achatNum[0]['REVUE_ACHAT_ELEC'] = $revue['ACHAT_NUMERO_ELEC'];
            $achatNum[0]['REVUE_ACHAT_PAPIER'] = $revue['ACHAT_NUMERO'];
        }

        return $achatNum;
    }

    /*
     * Effectue le contrôle des possibilités d'achat de niveau ARTICLE
     *
     * @param $authInfos array reprend l'ensemble des informations d'authentification
     * @param $revue array record revue
     * @param $numero array record numero
     * @param $article array record article
     *
     * @return $array informations à afficher sur le bloc d'achat
     */

    private function checkArticle($authInfos, $revue, $numero, $article) {
        $achatArt = array();
        $movingwallEligible = "0000-00-00";
        if($article["MOVINGWALL"] != "" && $article["MOVINGWALL"] != "0000-00-00"){
            $movingwallEligible = $article["MOVINGWALL"];
        }else{
            $movingwallEligible = $article["NUMERO_MOVINGWALL"];
        }
        if ($article['ARTICLE_PRIX'] > 0 && ($movingwallEligible == '0000-00-00' || $movingwallEligible > date('Y-m-d') || $article['ARTICLE_TOUJOURS_PAYANT'] == 1) && (isset($revue['ACHAT_ARTICLE']) ? $revue['ACHAT_ARTICLE'] : $revue['REVUE_ACHAT_ARTICLE']) == 1) {
            $achatArt[] = $article;
        }
        return $achatArt;
    }

    /*
     * Détermine le mode d'achat en vigueur sur la page, en fonction des informations de connexions
     *
     * @param $authInfos array reprend l'ensemble des informations d'authentification
     */

    public function getModeAchat($authInfos) {
        if (!isset($authInfos['I'])) {
            $this->mode_achat = self::$ACHAT_PARTICULIER;
        } else {
            if (isset($authInfos['U']) && $authInfos['U']['SHOWALL'] == 1) {
                $this->mode_achat = self::$ACHAT_PARTICULIER;
            } else {
                $instCreditAchat = $this->content->getCairnParamInst($authInfos['I']['ID_USER'], 'H');
                if ($instCreditAchat != null && $instCreditAchat['VALEUR'] >= 1) {
                    $this->mode_achat = self::$ACHAT_INSTITUTION;
                } else {
                    $instAchatInactif = $this->content->getCairnParamInst($authInfos['I']['ID_USER'], 'A');
                    if ($instAchatInactif != null && $instAchatInactif['VALEUR'] == 1) {
                        $this->mode_achat = self::$ACHAT_INACTIF;
                    } else {
                        $this->mode_achat = self::$ACHAT_PARTICULIER;
                    }
                }
            }
        }
        return $this->mode_achat;
    }

    public function checkArticleSolo($authInfos, $article) {
        $achatArt = array();
        $movingwallEligible = "0000-00-00";
        if($article["MOVINGWALL"] != "" && $article["MOVINGWALL"] != "0000-00-00"){
            $movingwallEligible = $article["MOVINGWALL"];
        }else{
            $movingwallEligible = $article["NUMERO_MOVINGWALL"];
        }
        if ($article['ARTICLE_PRIX'] > 0 && ($movingwallEligible == '0000-00-00' || $movingwallEligible > date('Y-m-d') || $article['ARTICLE_TOUJOURS_PAYANT'] == 1) && (isset($article['ACHAT_ARTICLE']) ? $article['ACHAT_ARTICLE'] : $article['REVUE_ACHAT_ARTICLE']) == 1) {
            $achatArt[] = $article;
        }
        return $achatArt;
    }

    public function hasAccessToArticle($authInfos, $article, $numero = null, $revue = null, $lookup = 1) {
        //On vérifie d'abord si l'article est libre d'accès
        $movingwallEligible = "0000-00-00";
        if($article["MOVINGWALL"] != "" && $article["MOVINGWALL"] != "0000-00-00"){
            $movingwallEligible = $article["MOVINGWALL"];
        }else{
            $movingwallEligible = $article["NUMERO_MOVINGWALL"];
        }
        if ($article['ARTICLE_PRIX'] == 0 || ($movingwallEligible != '0000-00-00' && $movingwallEligible <= date('Y-m-d') && $article['ARTICLE_TOUJOURS_PAYANT'] == 0)) {
            return true;
        }
        //Sinon, on vérifie d'abord pour l'utilisateur
        if (isset($authInfos['U'])) {
            $idUser = $authInfos['U']['ID_USER'];
            $achat = $this->contentCom->searchAchatArticle($idUser, $article['ARTICLE_ID_ARTICLE'], $article['NUMERO_ID_NUMPUBLIE']);
            if ($achat) {
                return true;
            }
        }
        // Si on n'a pas trouvé d'info pour l'utilisateur, on vérifie pour l'institution
        // Ou pour les particuliers hors institution mais inscrit à une licence
        $accessInst = $this->hasAccessByInst($authInfos, $article['NUMERO_ID_NUMPUBLIE'], $article['REVUE_ID_REVUE']);
        if ($accessInst) {
            return true;
        }

        if ($lookup == 1) {
            //Si on n'a rien trouvé sur l'article, on remonte sur le numéro
            if ($numero == null) {
                $numero = $this->content->getNumpublieById($article['NUMERO_ID_NUMPUBLIE'])[0];
            }
            return $this->hasAccessToNumero($authInfos, $numero, $revue, 1, 0);
        } else {
            return false;
        }
    }

    public function hasAccessToNumero($authInfos, $numero, $revue = null, $lookup = 1, $lookInst = 1, $formatAchat = null) {
        // Les numéros qui ont dépassé la barrière mobile sont automatiquement disponible
        if (($formatAchat === 'E')
            && ($numero['NUMERO_MOVINGWALL'] != '0000-00-00')
            && ($numero['NUMERO_MOVINGWALL'] <= date('Y-m-d'))
        ) { return true; }
        //On vérifie d'abord pour l'utilisateur
        if (isset($authInfos['U'])) {
            $idUser = $authInfos['U']['ID_USER'];
            $achat = $this->contentCom->searchAchatNumero($idUser, $numero['NUMERO_ID_NUMPUBLIE'], $numero['NUMERO_ID_REVUE'], $formatAchat);
            if ($achat) {
                return true;
            }
        }
        //Si on n'a pas trouvé d'info pour l'utilisateur, on vérifie pour l'institution
        if ($lookInst == 1) {
            $accessInst = $this->hasAccessByInst($authInfos, $numero['NUMERO_ID_NUMPUBLIE'], $numero['NUMERO_ID_REVUE']);
            if ($accessInst) {
                return true;
            }
        }

        if ($lookup == 1) {
            //Si on n'a rien trouvé sur l'article, on remonte sur le numéro
            if ($revue == null) {
                $revue = $this->content->getRevuesById($numero['NUMERO_ID_REVUE'])[0];
            }
            return $this->hasAccessToRevue($authInfos, $revue, 0);
        } else {
            return false;
        }
    }

    public function hasAccessToRevue($authInfos, $revue, $lookInst = 1, $numIntoRevue = 0) {
        //On vérifie d'abord pour l'utilisateur
        if (isset($authInfos['U'])) {
            $idUser = $authInfos['U']['ID_USER'];
            $abos = $this->contentCom->getAboRevue($idUser, isset($revue['ID_REVUE']) ? $revue['ID_REVUE'] : $revue['REVUE_ID_REVUE']);
            foreach ($abos as $abo) {
                if ($abo['ANNEE_DEBUT'] != '') {
                    //A l'année
                    if ($abo['ANNEE_DEBUT'] == date('Y') || ($abo['ANNEE_DEBUT'] == (date('Y') - 1) && ((date('z') + 1) <= $revue['GRACE']) )) {
                        return true;
                    }
                } else {
                    //Au numéro...
                    $idFirstNum = $abo['ID_NUMPUBLIE'];
                    $idRevue = $abo['ID_REVUE'];
                    $idAbon = $abo['ID_ABON'];
                    $aboDetail = $this->content->getAboDetails($idAbon, $idRevue);
                    $numeros = $this->content->getNumerosFrom($idRevue, $idFirstNum, $aboDetail['NOMBRE'] + 1);
                    if (count($numeros) < $aboDetail['NOMBRE'] + 1) {
                        return true;
                    } else {
                        $startDate = $numeros[0]['DATE_PARUTION'];
                        $endDate = $numeros[count($numeros) - 1]['DATE_PARUTION'];
                        if (date('Y-m-d') > $startDate && date('Y-m-d') <= $endDate) {
                            return true;
                        }
                    }
                }
            }
        }
        //Si on n'a pas trouvé d'info pour l'utilisateur, on vérifie pour l'institution
        if ($lookInst == 1) {
            $accessInst = $this->hasAccessByInst($authInfos, $numIntoRevue == 1 ? $revue['ID_NUMPUBLIE'] : null, $revue['ID_REVUE']);
            if ($accessInst) {
                return true;
            }
        }
        return false;
    }


    private function hasAccessByInst($authInfos, $idNumpublie, $idRevue) {
        // Les particuliers peuvent aussi être inscrit à une licence, sans qu'il soit dans une institution
        foreach (['I', 'U'] as $typeUser) {
            if (!isset($authInfos[$typeUser])) continue;
            if ($this->content->searchLicence($authInfos[$typeUser]['ID_USER'], $idRevue)) return true;
            if ($this->content->searchLicence($authInfos[$typeUser]['ID_USER'], $idNumpublie)) return true;
        }
        if (isset($authInfos['I'])) {
            if ($idNumpublie != null) {
                if ($this->contentCom->searchAchatNumero($authInfos['I']['ID_USER'], $idNumpublie, $idRevue)) return true;
            }
        }
        return false;
    }


    public function whichButtonsForArticles($authInfos, $articleIds) {
        $modeAchat = $this->getModeAchat($authInfos);
        $articles = $this->content->getArticlesFromIds($articleIds);
        $articleButtons = array();
        foreach ($articles as $article) {
            if ($this->hasAccessToArticle($authInfos, $article)) {
                $articleButtons[$article['ARTICLE_ID_ARTICLE']]['STATUT'] = 1;
            } else {
                $check = $this->checkArticleSolo($authInfos, $article);
                if (!empty($check)) {
                    $articleButtons[$article['ARTICLE_ID_ARTICLE']]['STATUT'] = 2;

                    if (
                        ($modeAchat == 2) || (
                            // Quand on est connecté en tant qu'utilisateur ET en tant qu'institution ET que l'utilisateur a activé la possibilité d'acheter n'importe quel article, la demande a quand même la priorité
                            isset($authInfos['I'])
                            && isset($authInfos['U'])
                            && ($authInfos['U']['SHOWALL'] == 1)
                        )
                    ) {
                        $articleButtons[$article['ARTICLE_ID_ARTICLE']]['LIB'] = 'Demander cet article';
                        $articleButtons[$article['ARTICLE_ID_ARTICLE']]['HREF'] = 'mes_demandes.php?ID_ARTICLE=' . $article['ARTICLE_ID_ARTICLE'];
                    } else if ($modeAchat == 1) {
                        $articleButtons[$article['ARTICLE_ID_ARTICLE']]['LIB'] = '<span class="button first">Consulter</span>
                                    <span class="icon icon-add-to-cart"></span>
                                    <span class="button last">' . $article['ARTICLE_PRIX'] . ' €</span>';
                        $articleButtons[$article['ARTICLE_ID_ARTICLE']]['HREF'] = 'mon_panier.php?ID_ARTICLE=' . $article['ARTICLE_ID_ARTICLE'];
                        $articleButtons[$article['ARTICLE_ID_ARTICLE']]['CLASS'] = 'wrapper_buttons_add-to-cart';
                    }
                } else {
                    // Définition du type (article ou chapitre)
                    if(($article["REVUE_TYPEPUB"] == 1) || ($article["REVUE_TYPEPUB"] == 2)) {$labelArticle = "Cet article";}
                    else {$labelArticle = "Ce chapitre";}
                    // Configuration du bouton
                    $articleButtons[$article['ARTICLE_ID_ARTICLE']]['STATUT'] = 0;
                    $articleButtons[$article['ARTICLE_ID_ARTICLE']]['BEFORE'] = "<i>".$labelArticle." ne peut être acheté à l'unité</i> ";
                    $articleButtons[$article['ARTICLE_ID_ARTICLE']]['LIB'] = "<b>En savoir plus</b>";
                    $articleButtons[$article['ARTICLE_ID_ARTICLE']]['HREF'] = "http://aide.cairn.info/je-ne-vois-pas-le-panier-dachat-pourquoi/";
                    $articleButtons[$article['ARTICLE_ID_ARTICLE']]['CLASS'] = "link-underline";
                    $articleButtons[$article['ARTICLE_ID_ARTICLE']]['TARGET'] = "_blank";
                }
            }
        }
        return $articleButtons;
    }

    public function verifyAbo($abo, $revue) {
        if ($abo['ANNEE_DEBUT'] != '') {
            //A l'année
            if ($abo['ANNEE_DEBUT'] == date('Y') || ($abo['ANNEE_DEBUT'] == (date('Y') - 1) && ((date('z') + 1) <= $revue['GRACE']) )) {
                return true;
            }
        } else {
            //Au numéro...
            $idFirstNum = $abo['ID_NUMPUBLIE'];
            $idRevue = $abo['ID_REVUE'];
            $idAbon = $abo['ID_ABON'];
            $aboDetail = $this->content->getAboDetails($idAbon, $idRevue);
            $numeros = $this->content->getNumerosFrom($idRevue, $idFirstNum, $aboDetail['NOMBRE'] + 1);
            if (count($numeros) < $aboDetail['NOMBRE'] + 1) {
                return true;
            } else {
                $startDate = $numeros[0]['DATE_PARUTION'];
                $endDate = $numeros[count($numeros) - 1]['DATE_PARUTION'];
                if (date('Y-m-d') > $startDate && date('Y-m-d') <= $endDate) {
                    return true;
                }
            }
        }

        return false;
    }

}
