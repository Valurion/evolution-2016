<?php

require_once 'Modele/Content.php';

/**
 * Permet d'enrichir un tableau d'articles avec les boutons issus du décodage du champ CONFIG_ARTICLE
 *
 * @author benjamin
 */
class ContentArticle {

    private $content;
    private $article_libelles;
    private $typesAchat = array();

    function __construct() {
        $this->content = new Content();
        $this->article_libelles = $this->content->getArticlesLibelles();
    }

    public function setTypesAchat($typesAchat) {
        $this->typesAchat = $typesAchat;
        return $this;
    }

    /*
     * Appel à partir d'un numéro précis
     */

    public function readContentArticlesFromNumero(&$articles, $typePubLib, $revue, $numero, $numeroFilter, $numeroIsbn, $authInfos, $accessOk, $lookup) {
        //On enrichit le tableau $article avant de le filer à la vue
        foreach ($articles as &$article) {
            $arrayListeConfigArticle = array();
            $configs_articles = explode(',', $article['ARTICLE_CONFIG_ARTICLE']);

            $this->checkResume($arrayListeConfigArticle, $article, $configs_articles[0], $typePubLib, $revue['REVUE_URL_REWRITING'], $numero['NUMERO_ANNEE'], $numero['NUMERO_NUMERO'], $numeroFilter, $numeroIsbn);

            $this->checkOthers($arrayListeConfigArticle, $article, $configs_articles, $typePubLib, $revue['REVUE_URL_REWRITING'], $numero['NUMERO_ANNEE'], $numero['NUMERO_NUMERO'], $numeroFilter, $numeroIsbn, $authInfos, $accessOk, $lookup, $numero);

            $article['LISTE_CONFIG_ARTICLE'] = $arrayListeConfigArticle;
        }
    }

    /*
     * Appel à partir d'une liste d'articles
     */

    public function readContentArticles(&$articles, $typePubLib, $authInfos) {
        //On enrichit le tableau $article avant de le filer à la vue
        foreach ($articles as &$article) {
            if ($typePubLib == '') {
                $typePubLib = $this->getTypePubLib($article);
            }
            $arrayListeConfigArticle = array();
            $configs_articles = explode(',', $article['ARTICLE_CONFIG_ARTICLE']);

            $this->checkResume($arrayListeConfigArticle, $article, $configs_articles[0], $typePubLib, $article['REVUE_URL_REWRITING'], $article['NUMERO_ANNEE'], $article['NUMERO_NUMERO'], $article['NUMERO_URL_REWRITING'], $article['NUMERO_ISBN']);

            $this->checkOthers($arrayListeConfigArticle, $article, $configs_articles, $typePubLib, $article['REVUE_URL_REWRITING'], $article['NUMERO_ANNEE'], $article['NUMERO_NUMERO'], $article['NUMERO_URL_REWRITING'], $article['NUMERO_ISBN'], $authInfos);

            $article['LISTE_CONFIG_ARTICLE'] = $arrayListeConfigArticle;
        }
    }

    private function getTypePubLib($article) {
        $typepub = isset($article['REVUE_TYPEPUB']) ? $article['REVUE_TYPEPUB'] : $article['TYPEPUB'];
        switch ($typepub) {
            case 1:
                return 'revue';
                break;
            case 2:
                return 'magazine';
                break;
            default:
                return '';
        }
    }

    private function checkResume(&$arrayListeConfigArticle, $article, $config_article, $typePubLib, $revue_url_rewriting, $numero_annee, $numero_numero, $numeroFilter, $numeroIsbn) {
        switch ($config_article) {
            case '1': //Résumé
                $configArticle = [
                    'LIB' => $this->article_libelles['RES']['LIBELLE'],
                    'HREF' => str_replace('[ARTICLE_ID_ARTICLE]', $article['ARTICLE_ID_ARTICLE'], $this->article_libelles['RES']['HREF'])
                ];

                // Ajout du token dans l'url
                if((Configuration::get('allow_preprod', false) === '1') && isset($_GET['token']))
                {
                    $configArticle['HREF'] = $configArticle['HREF'] . "&token=" . $_GET['token'];
                }

                array_push($arrayListeConfigArticle, $configArticle);
                break;
            case '2': //première page
                $configArticle = [
                    'LIB' => $this->article_libelles['1PAGE']['LIBELLE'],
                    'HREF' => str_replace('[ARTICLE_ID_ARTICLE]', $article['ARTICLE_ID_ARTICLE'], $this->article_libelles['1PAGE']['HREF'])
                ];

                // Ajout du token dans l'url
                if((Configuration::get('allow_preprod', false) === '1') && isset($_GET['token']))
                {
                    $configArticle['HREF'] = $configArticle['HREF'] . "?token=" . $_GET['token'];
                }

                array_push($arrayListeConfigArticle, $configArticle);
                break;
            case '3': //premières lignes
                if ($typePubLib != "") {
                    $linkPremLignes = $typePubLib . '-' . $revue_url_rewriting . '-' . $numero_annee . '-' . $numero_numero . '-p-' . $article['ARTICLE_PAGE_DEBUT'] . '.htm';
                } else {
                    $linkPremLignes = $numeroFilter . '--' . $numeroIsbn . '-p-' . $article['ARTICLE_PAGE_DEBUT'] . '.htm';
                }
                $configArticle = [
                    'LIB' => $this->article_libelles['1LIGNE']['LIBELLE'],
                    'HREF' => str_replace('[ARTICLE_RESUME_HREF]', $linkPremLignes, $this->article_libelles['1LIGNE']['HREF'])
                ];

                // Ajout du token dans l'url
                if((Configuration::get('allow_preprod', false) === '1') && isset($_GET['token']))
                {
                    $configArticle['HREF'] = $configArticle['HREF'] . "?token=" . $_GET['token'];
                }

                array_push($arrayListeConfigArticle, $configArticle);
                break;
        }
    }

    private function checkOthers(&$arrayListeConfigArticle, $article, $configs_articles, $typePubLib, $revue_url_rewriting, $numero_annee, $numero_numero, $numeroFilter, $numeroIsbn, $authInfos, $accessOk = 0, $lookup = 1, $numero=null) {
        if ($accessOk == 1 || Service::get('ControleAchat')->hasAccessToArticle($authInfos, $article, null, null, $lookup)) {
            if ($configs_articles[1] == 1) { //HTML
                if ($typePubLib != '') {
                    if (isset($numero) && $numero['NUMERO_PREPUB'] === '1') {
                        $valHtml = 'article.php?ID_ARTICLE='.$article['ARTICLE_ID_ARTICLE'];
                    } else {
                        $valHtml = $typePubLib . '-' . $revue_url_rewriting . '-' . $numero_annee . '-' . $numero_numero . '-page-' . $article['ARTICLE_PAGE_DEBUT'] . '.htm';
                    }
                } else {
                    $valHtml = $numeroFilter . '--' . $numeroIsbn . '-page-' . $article['ARTICLE_PAGE_DEBUT'] . '.htm';
                }
                $configArticle = [
                    'LIB' => $this->article_libelles['HTM']['LIBELLE'],
                    'HREF' => str_replace('[ARTICLE_HREF]', $valHtml, $this->article_libelles['HTM']['HREF'])
                ];

                // Ajout du token dans l'url
                if((Configuration::get('allow_preprod', false) === '1') && isset($_GET['token']))
                {
                    $configArticle['HREF'] = $configArticle['HREF'] . "?token=" . $_GET['token'];
                }

                array_push($arrayListeConfigArticle, $configArticle);
            }
            if ($configs_articles[2] == 1) { //Feuilletage
                $configArticle = [
                    'LIB' => $this->article_libelles['FEUILLE']['LIBELLE'],
                    'HREF' => str_replace('[ARTICLE_ID_ARTICLE]', $article['ARTICLE_ID_ARTICLE'], $this->article_libelles['FEUILLE']['HREF'])
                ];

                // Ajout du token dans l'url
                if((Configuration::get('allow_preprod', false) === '1') && isset($_GET['token']))
                {
                    $configArticle['HREF'] = $configArticle['HREF'] . "?token=" . $_GET['token'];
                }

                array_push($arrayListeConfigArticle, $configArticle);
            }
            if ($configs_articles[3] == 1) { //PDF
                $configArticle = [
                    'LIB' => $this->article_libelles['PDF']['LIBELLE'],
                    'HREF' => str_replace('[ARTICLE_ID_ARTICLE]', $article['ARTICLE_ID_ARTICLE'], $this->article_libelles['PDF']['HREF'])
                ];

                // Ajout du token dans l'url
                if((Configuration::get('allow_preprod', false) === '1') && isset($_GET['token']))
                {
                	$configArticle['HREF'] = $configArticle['HREF'] . "&token=" . $_GET['token'];
                }

                array_push($arrayListeConfigArticle, $configArticle);
            }
            if ($configs_articles[4] == 1) { //Excel
                $configArticle = [
                    'LIB' => $this->article_libelles['EXCELL']['LIBELLE'],
                    'HREF' => str_replace('[ARTICLE_ID_ARTICLE]', $article['ARTICLE_ID_ARTICLE'], $this->article_libelles['EXCELL']['HREF'])
                ];
                array_push($arrayListeConfigArticle, $configArticle);
            }
            if (count($configs_articles) > 5 && $configs_articles[5] == 1) { //Diffusion par partenaire externe
                $configArticle = array(
                    'LIB' => $article['PORTAIL_NOM_PORTAIL'],
                    'HREF' => $article['ARTICLE_URL_PORTAIL']
                );
                array_push($arrayListeConfigArticle, $configArticle);
            }
        } else {
            if (isset($this->typesAchat['ARTICLE'])) {
                $achatArticle = $this->typesAchat['ARTICLE'];
            } else {
                $achatArticle = Service::get('ControleAchat')->checkArticleSolo(null, $article);
            }
            if (empty($achatArticle)) {
                // Définition du type (article ou chapitre)
                if(($numero["REVUE_TYPEPUB"] == 1) || ($article["REVUE_TYPEPUB"] == 2)) {$labelArticle = "Cet article";}
                else {$labelArticle = "Ce chapitre";}
                //Pas accès et pas achetable
                $configArticle = array(
                        "BEFORE" => "<i>".$labelArticle." ne peut être acheté à l'unité</i> ",
                        "LIB" => "<b>En savoir plus</b>",
                        "HREF" => "http://aide.cairn.info/je-ne-vois-pas-le-panier-dachat-pourquoi/",
                        "CLASS" => "link",
                        "TARGET" => "_blank"
                    );
                array_push($arrayListeConfigArticle, $configArticle);
                return;
            } else {
                if (count($configs_articles) > 5 && $configs_articles[5] == 1) { //Diffusion par partenaire externe
                    $configArticle = array(
                        'LIB' => $article['PORTAIL_NOM_PORTAIL'],
                        'HREF' => $article['ARTICLE_URL_PORTAIL']
                    );
                    array_push($arrayListeConfigArticle, $configArticle);
                    return;
                }
                //On regarde les boutons d'achat qu'il faut placer...
                $mode = $this->typesAchat['MODE'];
                    if (
                        ($mode == 2) || (
                            // Quand on est connecté en tant qu'utilisateur ET en tant qu'institution ET que l'utilisateur a activé la possibilité d'acheter n'importe quel article, la demande a quand même la priorité
                            isset($authInfos['I'])
                            && isset($authInfos['U'])
                            && ($authInfos['U']['SHOWALL'] == 1)
                        )
                    ) {
                    $configArticle = array(
                        'LIB' => 'Demander cet article',
                        'HREF' => 'mes_demandes.php?ID_ARTICLE=' . $article['ARTICLE_ID_ARTICLE']
                    );
                    array_push($arrayListeConfigArticle, $configArticle);
                } else if ($mode == 1) {
                    $configArticle = array(
                        'LIB' => '<span class="button first">Consulter</span>
                                  <span class="icon icon-add-to-cart"></span>
                                  <span class="button last">' . $article['ARTICLE_PRIX'] . ' €</span>',
                        'HREF' => 'mon_panier.php?ID_ARTICLE=' . $article['ARTICLE_ID_ARTICLE'],
                        'CLASS' => 'wrapper_buttons_add-to-cart'
                    );
                    array_push($arrayListeConfigArticle, $configArticle);
                }
            }
        }
    }

    public function readButtonsForInter(&$articles, $authInfos, $accessOk = 0, &$currentArticle = null) {
        foreach ($articles as &$article) {
            $arrayListeConfigArticle = array();
            //1 - Le bouton résumé
            $resume = $this->content->checkResumeInter($article['ARTICLE_ID_ARTICLE']);
            $configArticle = ($resume == 1 ? str_replace('[ARTICLE_ID_ARTICLE]', $article['ARTICLE_ID_ARTICLE'], $this->article_libelles['RES']['HREF']) : "");
            array_push($arrayListeConfigArticle, $configArticle);

            //2 - Le bouton français
            $frenchText = 1;
            if ($article['ARTICLE_LANGUE_INTEGRALE'] == 'en' && $article['ARTICLE_LANGUE'] == 'en' && $article['ARTICLE_ID_ARTICLE_S'] == '') {
                $frenchText = 0;
            }
            $configArticle = ($frenchText == 1 ? ("http://www.cairn.info/article.php?ID_ARTICLE=" . $article['ARTICLE_ID_ARTICLE']) : "");
            array_push($arrayListeConfigArticle, $configArticle);

            //3 - Le bouton anglais
            $href = '';
            if ($article['ARTICLE_LANGUE_INTEGRALE'] == 'en') {
                $accessArt = $accessOk;
                if ($accessArt == 0) {
                    $accessArt = (Service::get('ControleAchat')->hasAccessToArticle($authInfos, $article, null, null, 1) == true ? 1 : 0);
                }
                if ($accessArt == 1) {
                    $href = "./article-" . $article['ARTICLE_ID_ARTICLE'] . "--" . $article['ARTICLE_URL_REWRITING_EN'] . ".htm";
                } else {
                    $href = 'my_cart.php?ID_ARTICLE=' . $article['ARTICLE_ID_ARTICLE'];
                }
            }
            $configArticle = $href;
            array_push($arrayListeConfigArticle, $configArticle);
            $article['LISTE_CONFIG_ARTICLE'] = $arrayListeConfigArticle;

            if ($currentArticle != null && $currentArticle['ARTICLE_ID_ARTICLE'] == $article['ARTICLE_ID_ARTICLE']) {
                $currentArticle['LISTE_CONFIG_ARTICLE'] = $arrayListeConfigArticle;
            }
        }
    }

    public function readButtonsForInterFromSearch($listIds, $authInfos) {
        $articles = $this->content->getArticlesFromIds($listIds);
        $this->readButtonsForInter($articles, $authInfos);
        $articleButtons = array();
        foreach ($articles as $article) {
            $articleButtons[$article['ARTICLE_ID_ARTICLE']] = $article['LISTE_CONFIG_ARTICLE'];
            if ($article['ARTICLE_PRIX'] == 0 || ($article['NUMERO_MOVINGWALL'] != '0000-00-00' && $article['NUMERO_MOVINGWALL'] <= date('Ymd') && $article['ARTICLE_TOUJOURS_PAYANT'] == 0)) {
                $articleButtons[$article['ARTICLE_ID_ARTICLE']][] = '0';
            } else {
                $articleButtons[$article['ARTICLE_ID_ARTICLE']][] = $article['ARTICLE_PRIX'];
            }
            $articleButtons[$article['ARTICLE_ID_ARTICLE']][] = $article['ARTICLE_URL_REWRITING_EN'];
            $articleButtons[$article['ARTICLE_ID_ARTICLE']][] = $article['ARTICLE_ID_ARTICLE_S'];
        }
        return $articleButtons;
    }

}
