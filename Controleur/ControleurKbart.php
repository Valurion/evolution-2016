<?php

require_once 'Framework/Controleur.php';
require_once 'Modele/Content.php';

class ControleurKbart extends Controleur
{
    private $KBART_HEADERS = [
        'listRevues' => [
            "publication_title",            // Titre de la publication
            "print_identifier",             // Identifiant du format papier, issn sur cairn
            "online_identifier",            // Identifiant du format numérique, issn_num sur cairn
            "date_first_issue_online",      // Date du premier numéro mis en ligne
            "num_first_vol_online",         // Numéro du premier numéro mis en ligne
            "num_first_issue_online",       // Premier numéro disponible en ligne
            "date_last_issue_online",       // Date du dernier numéro disponible en ligne
            "num_last_vol_online",          // Numéro du dernier numéro mis en ligne
            "num_last_issue_online",        // Dernier numéro disponible en ligne
            "title_url",                    // Url du titre, pointant directement sur les htmls de cairn
            "first_author",                 // Auteur principal (uniquement pour les ouvrages à priori)
            "title_id",                     // Identifiant du titre
            "embargo_info",                 // Entendu de l'embargo
            "coverage_depth",               // Entendu de la couverture de contenu
            "notes",                        // Notes sur la couverture
            "publisher_name",               // Nom de l'éditeur
            "publication_type",             // Si c'est une "série" ou une "monographie"
            "date_monograph_published_print",
            "date_monograph_published_online",
            "monograph_volume",
            "monograph_edition",
            "first_editor",
            "parent_publication_title_id",
            "preceding_publication_title_id",
            "access_type",                  // Si en accès payant ou gratuit
        ],
    ];

    private $format_tab = '\t';


    public function __construct()
    {
        $this->content = new Content();
        // On crée une table ascii de caractère de controle
        $this->ASCII_ALL_CTRL = array_map("chr", range(0, 31));
        $this->ASCII_ALL_CTRL[] = chr(127);
        // On crée une table ascii de caractère blanc
        $this->ASCII_WHITESPACES = array(" ", "\t", "\n", "\v", "\f");
        // La table finale ascii des caractères ascii a supprimé
        $this->ASCII_CTRL = array_diff($this->ASCII_ALL_CTRL, $this->ASCII_WHITESPACES);

        // Sur pas mal de volume de numéros, il y a des données parasite comme `n°`
        $this->NUMERO_PARASITE_REGEXP = array(
            '/num(é|e)ro/i',
            '/vol(\.|ume)?/i',
            '/tome?/i',
            '/^\s*t\.\s*/',
            '/\s*n(o|°|˚)?(\s)*(?=\d)/i'
        );
        // On harmonise les volumes composés en `nombre-tiret-nombre`
        $this->NUMERO_HARMONIZE_REGEXP = array(
            '/(\d+)\s*[\s-:\/,.]\s*(\d+)/'
        );
    }

    /*
        Nettoie une chaine de caractères en supprimant les caractères de contrôles indésirable,
        en supprimant les balises xml/xhtml et en décodant les entités html

        TODO: Décoder les éventuelles entités xml
    */
    private function sanatizeString($string)
    {
        $string = str_replace($this->ASCII_WHITESPACES, ' ', $string);
        $string = str_replace($this->ASCII_CTRL, '', $string);
        $string = strip_tags($string);
        $string = html_entity_decode($string, ENT_QUOTES, 'UTF-8');

        return trim($string);
    }

    private function sanatizeAmp($string)
    {
        return preg_replace("#& #", "et ", $string);
    }

    private function sanatizeNumero($string)
    {
        $string = preg_replace($this->NUMERO_PARASITE_REGEXP, '', $string);
        return preg_replace($this->NUMERO_HARMONIZE_REGEXP, '\1-\2', $string);
    }

    /*
        Harmonise les données issus de la base pour la barrière mobile.
        Prend un entier en paramètre et retourne un 2-uplet où le premier terme est l'unité de temps relative
        et le second terme le type de données en accès libre.

        Règles de barrière mobile:

        * Au dessus de 16, il n'y a jamais d'accès libre sur les articles, seuls les résumés sont en accès libre
        * Entre 15 et 1, les articles sont en accès libre seulement si la date de parution dépassent ce nombre d'années (par rapport à l'année civile en cours)
        * Sinon, tous les articles sont en accès libre

        Règles d'unité de temps relative:

        * La première lettre défini le type d'embargo. Si égale à `R`, l'embargo est AVANT l'unité de temps. Si égale à `P`, l'embargo est APRÈS l'unité de temps.
        * Les chiffres qui suivent sont la valeur de temps.
        * La dernière lettre correspond à l'unité de temps. Peut être `Y|M|D` pour respectivement année/mois/jour.

        Par exemple:
            P1Y => L'accès à l'ensemble du contenu, à l'exception de l'année civile en cours
            R2Y => L'accès à l'ensemble du contenu dans l’année civile précédente et l’année civile actuelle
            R180D => L'accès à l'ensemble du contenu à partir d'il y a exactement 6 mois jusqu’à ce jour
            P6M => L'accès à l'ensemble du contenu, à l'exception des 6 derniers mois civils

        Cela signifie aussi que avec l'unité `Y`, la barrière mobile change une fois par an, avec `M` mensuellement et avec `D` une fois par jour.
    */
    public function sanatizeEmbargo($integer)
    {
        if ($integer > 16) {
            return array('P0Y', 'abstracts');
        }
        if ($integer > 0) {
            return array('P'.$integer.'Y', 'fulltext');
        }
        return array('P0Y', 'fulltext');
    }


    /*
        Formatte le nom d'une licence selon les règles suivantes :

        - On transforme les lettres en dehors de l'espace ASCII en caractères correspondant
        - On remplace les guillements par des espaces
        - On récupère le nom du fournisseur (qu'on camelise)
        - On récupère le nom de la catégorie (qu'on met en petite casse et qu'on camelise)
        - On récupère le nom du consortium (qu'on met en petite casse et qu'on camelise)
        - On récupère le nom de la licence, (qu'on camelise)
        - On concatène les différents noms récupérés plus haut
        - On supprime les caractères blancs

        Oui, c'est n'importe quoi que ce soit le nom de la licence qui contienne toutes ces informations.
        Oui, j'en ai déjà parlé au chef.
        Mais non, ce ne sera pas "corrigé" dans l'immédiat.
    */
    public function sanatizeLicenceName($licenceName)
    {
        // Les locales ont l'air d'être configuré en POSIX ou C sur les serveurs de cairn
        // On contourne cela en injectant un jeu de locale compatible
        setlocale(LC_CTYPE, 'fr_FR.UTF-8');
        $licenceName = iconv("utf-8", "ascii//TRANSLIT", $licenceName);
        $licenceName = preg_replace('/\'|"/', ' ', $licenceName);

        // Le nom du fournisseur du fichier. Bon, à moins qu'on soit racheté, ça devrait toujours être cairn
        $provider = 'cairn';
        $provider = ucwords(strtolower($provider));

        // Le nom de la catégorie. Tous les caractères alphanumériques entre le début du nom et le premier tiret
        // Sinon, prend une valeur par défaut ("None" à l'heure d'écrire ce commentaire)
        $category = array();
        preg_match("/^\s*([a-zA-Z0-9]+)\s*-\s*/", $licenceName, $category);
        $category = count($category) > 1 ? $category[1] : 'NONE';
        $category = ucwords(strtolower($category));

        // Le nom du consortium. Je ne sais pas vraiment ce que c'est.
        // Tout les caractères entre crochets, et dont les crochets sont en fin de nom
        // Sinon, prend "global" par défaut
        $consortium = array();
        preg_match("/\s*\[\s*(.*?)\s*\]\s*$/", $licenceName, $consortium);
        $consortium = count($consortium) > 1 ? $consortium[1] : 'global';
        $consortium = ucwords(strtolower($consortium));

        // Le nom de la licence
        // En gros, tout ce qui n'est pas matché par les éléments plus haut
        $packageName = array();
        preg_match("/^(?:\s*[a-zA-Z0-9]+\s*-\s*)?(.*?)(?:\s*\[\s*.*?\s*\]\s*)?$/", $licenceName, $packageName);
        $packageName = count($packageName) > 1 ? $packageName[1] : 'unknownPackage';
        $packageName = ucwords($packageName);

        // On formalise le nom de la licence afin d'être utilisé comme nom de fichier pour le fichier de sortie
        $licenceName = $provider . '_' . $consortium . '_' . $category . '-' . $packageName . '_' . date('o-m-d');
        $licenceName = preg_replace('/\s+/', '', $licenceName);

        //Nouveau cas : gestion des virgules dans les noms des fichiers.
        $licenceName = str_replace(',', '-', $licenceName);

        return $licenceName;
    }



    public function formatHeader($header)
    {
        switch ($this->format) {
            case 'html':
                $formatHtml = '<tr>';
                foreach ($header as $key => $value) {
                    $formatHtml .= '<th>'.$value.'</th>';
                }
                $formatHtml .= '</tr>';
                return $formatHtml;
            case 'csv':
                return utf8_decode(implode(';', $header)."\n");
            case 'tsv':
            default:
                return implode("\t", $header)."\n";
        }
    }



    public function formatCells($cells)
    {
        switch ($this->format) {
            case 'html':
                $formatHtml = '<tr>';
                foreach ($cells as $key => $value) {
                    $formatHtml .= '<td>'.$value.'</td>';
                }
                $formatHtml .= '</tr>';
                return $formatHtml;
            case 'csv':
                return utf8_decode(implode(';', $cells)."\n");
            case 'tsv':
            default:
                return implode("\t", $cells)."\n";
        }
    }



    public function onBeginTable($filename = null)
    {
        if ($filename === null) {
            $filename = "Cairn_Global_AllJournals_" . date('o-m-d');
        }
        switch ($this->format) {
            case 'csv':
                header("Content-Type: text/csv; charset=ISO-8859-1");
                header("Content-disposition: filename=$filename.csv");
                break;
            case 'html':
                header("Content-Type: text/html; charset=utf-8");
                echo "
                    <head>
                        <meta charset='utf-8'>
                        <title>$filename</title>
                    </head>
                ";
                echo '<table border=1>';
                break;
            case 'tsv':
            default:
                header("Content-Type: application/csv-tab-delimited-table; charset=utf-8");
                header("Content-disposition: filename=$filename.txt");
                break;
        }
    }



    public function onEndTable()
    {
        switch ($this->format) {
            case 'html':
                echo '</table>';
                break;
            default:
                break;
        }
    }



    // La variable permettant d'accéder au modèle pour cairn*_pub
    private $content;
    private $format;


    public function index()
    {
        $this->format = $this->requete->getParametre('format', 'tsv');
        $this->format_tab = $this->format === 'csv' ? ';' : "\t";

        // On flush automatiquement la sortie, pour avoir un reporting en temps réel qui est
        // un peu plus cool pour l'utilisateur
        // @apache_setenv('no-gzip', 1);
        @ini_set('zlib.output_compression', 0);
        @ini_set('implicit_flush', 1);
        for ($i = 0; $i < ob_get_level(); $i++) {
            ob_end_flush();
        }
        ob_implicit_flush(1);

        $typePub = $this->requete->getParametre('typepub', 1);

        if ($typePub == 1) {
            $this->listRevues2();
        } else {
            $this->listOuvragesPoches();
        }

    }

    private function listRevues2()
    {
        // On filtre selon une licence donnée si le paramètre est fourni, sinon on affiche toutes les revues
        $idLicence = $this->requete->getParametre('licence', null);
        $includeFree = $this->requete->getParametre('free', true) == true;
        $includePay = $this->requete->getParametre('pay', true) == true;
        $revues = $this->content->getAllRevuesForKbart($idLicence);

        $filename = null;
        $licence = null;
        if ($idLicence !== null) {
            $licence = $this->content->getLicenceForKbart($idLicence);
            if ($licence) {
                $filename = $this->sanatizeLicenceName($licence['name']);
            }
        }
        $this->onBeginTable($filename);
        $header = $this->KBART_HEADERS['listRevues'];
        echo $this->formatHeader($header);


        $indexedRevues = array();

        foreach ($revues as $revue) {
            // Si la revue a changé d'éditeur, on n'affiche pas l'ancienne version de la revue
            if ($revue['current_rev']) {
                continue;
            }
            $idRevue = $revue['id_revue'];
            $lastIdRevue = trim($revue['last_rev']);

            if(!empty($lastIdRevue)) { //on cherche le dernier idrevue de manière recurcive
                $tmpIdLastRevue = $lastIdRevue;
                while(!empty($tmpIdLastRevue)) {
                    $tmpIdLastRevue = $this->content->getLastIdRevuePrec($tmpIdLastRevue);
                    $tmpIdLastRevue = trim($tmpIdLastRevue["last_rev"]);
                    if(!empty($tmpIdLastRevue)) $lastIdRevue = $tmpIdLastRevue;
                }
            }

            //certains champs proviennent toujours du premier num indépendament du fait qu'ils soient gratuit ou payant
            $realFirstNumero = $this->content->getFirstNumeroForKbart($lastIdRevue ? $lastIdRevue : $idRevue);

            $numeros = array();
            if ($includeFree) {
                $numeros['first-free'] = $this->content->getFirstFreeNumeroForKbart($lastIdRevue ? $lastIdRevue : $idRevue);
                $numeros['last-free'] = $this->content->getLastFreeNumeroForKbart($lastIdRevue ? $lastIdRevue : $idRevue);
            }
            if ($includePay) {
                $numeros['first-pay'] = $this->content->getFirstPayNumeroForKbart($idRevue);
                $numeros['last-pay'] = $this->content->getLastPayNumeroForKbart($idRevue);
            }

            // Cas théorique où une revue avec un changement d'éditeur n'a pas de numéro gratuit sur l'ancienne édition
            if ($includeFree && $lastIdRevue && (!$numeros['first-free'])) {
                $numeros['first-free'] = $this->content->getFirstFreeNumeroForKbart($idRevue);
            }
            if ($includeFree && $lastIdRevue && (!$numeros['last-free'])) {
                $numeros['last-free'] = $this->content->getLastFreeNumeroForKbart($idRevue);
            }


            // On filtre les revues qui n'ont aucun numéro
            if (count(array_filter($numeros)) === 0) {
                continue;
            }

            // Nettoyage des données pour la revue
            $revue['publication_title'] = $this->sanatizeAmp($this->sanatizeString($revue['publication_title']));
            $revue['print_identifier'] = strtoupper($revue['print_identifier']);
            $revue['online_identifier'] = strtoupper($revue['online_identifier']);

            if ($revue['publication_title'] === '') {
                continue;
            }

            $revue['title_id'] = 'revue-'.$revue['title_url'];  // Suite au mail de ABES, ils conseillent plutôt de mettre les fins d'urls
            $revue['title_url'] = 'http://' . Configuration::get('urlSite', 'cairn.info') . '/revue-'.$revue['title_url'].'.htm';
            $revue['publisher_name'] = $this->sanatizeAmp($this->sanatizeString($revue['publisher_name']));
            $revue['coverage_depth'] = 'fulltext';  // TODO: pour cairn-int, ce n'est pas forcément vrai
            $revue['publication_type'] = 'serial';

            $movingWall = $revue["movingwall"];
            $firstNumero = null;
            $lastNumero = null;

            // Nettoyage des données pour les numéros
            foreach ($numeros as $key => &$numero) {
                if ($numero === false) {
                    continue;
                }
                $numero['numero'] = $this->sanatizeAmp(
                    $this->sanatizeString(
                        $numero['numero'].(!!$numero['numeroa'] ? '-'.$numero['numeroa'] : '')
                ));
                $numero['volume'] = $this->sanatizeNumero(
                                    $this->sanatizeAmp(
                                    $this->sanatizeString(
                                        $numero['volume']
                                    )));
                // On a besoin de savoir le premier et le dernier numéro publié, peu importe qu'il soit payant ou gratuit
                $numero['date'] = strtotime($numero['date']);
                if (!$firstNumero || ($numero['date'] <= $firstNumero['date'])) {
                    $firstNumero = &$numero;
                }
                if (!$lastNumero || ($numero['date'] >= $lastNumero['date'])) {
                    $lastNumero = &$numero;
                }
            }

            // On sépare en deux (si nécéssaire) les revues en une partie en accès libre et une autre en accès payant
            // Cela se traduit par deux lignes par revues
            foreach (['free', 'pay'] as $key) {
                // Filtre sur la partie gratuite d'une revue
                if ($key === 'free') {
                    if (!$includeFree) {
                        continue;
                    }
                    if ($movingWall > 12) { //plus de 12 ans = toujours patant
                        continue;
                    }
                }
                // Filtre sur la partie payante d'une revue
                if ($key === 'pay') {
                    if (!$includePay) {
                        continue;
                    }
                    if($movingWall <= 0) { //pas de movingwall = tout est gratuit
                        continue;
                    }
                }
                $embargoInfo = '';
                // On affiche la période d'embargo uniquement si elle est définit au niveau de la revue
                // ET si c'est filtré sur autre chose qu'uniquement les informations de revues sous licence payante
                if (($revue["movingwall"] > 0) && ($revue["movingwall"] <= 12) && !(!$includeFree && $includePay)) {
                    $embargoInfo = ($key === 'pay' ? 'R' : 'P') . $movingWall . 'Y';
                }

                $row = array_merge(
                    $revue,
                    [
                        'access_type' => $key === 'pay' ? 'P' : 'F',
                        'embargo_info' => $embargoInfo,
                    ],
                    [
                        'num_first_issue_online' => $this->getOnlyNumber($realFirstNumero['volume']) == 'H.-S.' ? 'H.-S.' : $realFirstNumero['numero'],
                        'date_first_issue_online' => $realFirstNumero['annee'],
                        'num_first_vol_online' => $realFirstNumero['annee'],
                    ],
                    [
                        'num_last_issue_online' => !$revue['is_stop'] ? null : $lastNumero['numero'],
                        'date_last_issue_online' => !$revue['is_stop'] ? null : $lastNumero['annee'],
                        'num_last_vol_online' => !$revue['is_stop'] ? null : $lastNumero['annee'],
                    ]
                );

                // On formatte la ligne et on l'affiche
                $result = array();
                foreach ($header as $colname) {
                    $result[] = isset($row[$colname]) ? $row[$colname] : '';
                }
                echo $this->formatCells($result);
            }
        }
        $this->onEndTable();
    }

    /**
     * Cette méthode va permettre d'obtenir
     * les informations pour les ouvrages et
     * les encyclopédies de poche.
     */
    private function listOuvragesPoches() {
        // On filtre selon une licence donnée si le paramètre est fourni, sinon on affiche toutes les revues
        $idLicence = $this->requete->getParametre('licence', null);
        $includeFree = $this->requete->getParametre('free', true) == true;
        $includePay = $this->requete->getParametre('pay', true) == true;
        $typePub = $this->requete->getParametre('typepub');

        $revues = $this->content->getAllOuvragesPochesForKbart($idLicence, $typePub);

        //Récupération de la liste des autheurs, dans un tableau.
        $tabNumPubli = array();
        foreach ($revues as $revue) {
            $tabNumPubli[] = $revue['ID_NUMPUBLIE'];
        }
        $tabNumPubli = array_unique($tabNumPubli);
        $tabAuthors = $this->content->getFirstAuthorForPublication($tabNumPubli);
        //Fin de la récupération.

        //Génération du nom du fichier.
        $filename = null;
        $licence = null;
        if ($idLicence !== null) {
                $licence = $this->content->getLicenceForKbart($idLicence);
            if ($licence) {
                $filename = $this->sanatizeLicenceName($licence['name']);
            }
        } else {
            switch ($typePub) {
                case 3 :
                    $filename = 'Cairn_Global_Ouvrages-General_' . date('Y-m-d');
                    break;
                case 6 :
                    $filename = 'Cairn_Global_Poches-General_' . date('Y-m-d');
                    break;
                default:
                    break;
            }
        }

        $this->onBeginTable($filename);

        //Génération de l'entête du fichier.
        $header = $this->KBART_HEADERS['listRevues'];
        echo $this->formatHeader($header);

        $indexedRevues = array();

        foreach ($revues as $revue) {

            // Si la revue a changé d'éditeur, on n'affiche pas l'ancienne version de la revue
            $idRevue = $revue['id_revue'];
            $lastIdRevue = trim($revue['last_rev']);

            if(!empty($lastIdRevue)) { //on cherche le dernier idrevue de manière recurcive
                $tmpIdLastRevue = $lastIdRevue;
                while(!empty($tmpIdLastRevue)) {
                    $tmpIdLastRevue = $this->content->getLastIdRevuePrec($tmpIdLastRevue);
                    $tmpIdLastRevue = trim($tmpIdLastRevue["last_rev"]);
                    if(!empty($tmpIdLastRevue)) $lastIdRevue = $tmpIdLastRevue;
                }
            }

            //certains champs proviennent toujours du premier num indépendament du fait qu'ils soient gratuit ou payant
            $realFirstNumero = $this->content->getFirstNumeroForKbart($lastIdRevue ? $lastIdRevue : $idRevue);

            // Nettoyage des données pour la revue
            $revue['publication_title'] = $this->sanatizeAmp($this->sanatizeString($revue['publication_title']));
            $revue['print_identifier'] = strtoupper($revue['print_identifier']);
            $revue['online_identifier'] = strtoupper($revue['online_identifier']);

            //Suite au mail de ABES, ils conseillent plutôt de mettre les fins d'urls
            $revue['title_id'] = $revue['title_url'] . '--' . $revue['ISBN'];
            $revue['title_url'] = 'http://' . Configuration::get('urlSite', 'cairn.info') . '/' . $revue['title_url'] . '--' . $revue['ISBN'] . '.htm';
            $revue['publisher_name'] = $this->sanatizeAmp($this->sanatizeString($revue['publisher_name']));
            $revue['coverage_depth'] = 'fulltext';  // TODO: pour cairn-int, ce n'est pas forcément vrai
            $revue['publication_type'] = 'serial';

            $movingWall = $revue["movingwall"];
            $firstNumero = null;
            $lastNumero = null;

            //Pour les date de publication
            if (!isset($revue['date_monograph_published_print']) || $revue['date_monograph_published_print'] === '0000-00-00') {
                $revue['date_monograph_published_print'] = '';
            }
            if (!isset($revue['date_monograph_published_online']) || $revue['date_monograph_published_online'] === '0000-00-00') {
                $revue['date_monograph_published_online'] = '';
            }

            //Pour les auteurs
            if (isset($tabAuthors[$revue['ID_NUMPUBLIE']]) && !empty($tabAuthors[$revue['ID_NUMPUBLIE']])) {
                $revue['first_author'] = $tabAuthors[$revue['ID_NUMPUBLIE']];
            } else {
                $revue['first_author'] = '';
            }

            //Nouvelle version de l'affichage des valeurs dans les cellules.
            $revue['access_type'] = 'P';
            $revue['embargo_info'] = $embargoInfo;

            $revue['num_first_issue_online'] = '';
            $revue['date_first_issue_online'] = '';
            $revue['num_first_vol_online'] = '';

            $revue['num_last_issue_online'] = '';
            $revue['date_last_issue_online'] = '';
            $revue['num_last_vol_online'] = '';

            //Pour le champ : monograph_edition.
            $revue['monograph_edition'] = strip_tags($revue['monograph_edition']);
            //

            $revue['parent_publication_title_id'] = $revue['nom_revue'];
            $revue['publication_type'] = 'monograph';

            // On formatte la ligne et on l'affiche
            $result = array();
            foreach ($header as $colname) {
                $result[] = isset($revue[$colname]) ? $revue[$colname] : '';
            }
            echo $this->formatCells($result);
        }

        $this->onEndTable();
    }

    /**
     * Cette fonction a pour objectif de conserver
     * uniquement les nombres dans une chaîne de
     * caractère.
     */
    private function getOnlyNumber($string, $annee = null) {

        if (preg_match('/[0-9]/i', $string)) {
            $string = preg_replace('/[^0-9-:&]/', ' ', $string);
        }

        //Pour le cas où il y aurait des mots.
        $tabWordOut = array('Tome ', 'Vol. ', 'Volume ', 'n° ');
        $string = str_replace($tabWordOut, '', $string);

        //Pour les nombres sans séparateur.
        $string = preg_replace('/(\d+)(\s+)(\d+)/', '$1 / $3', $string);

        //Pour la partie HS.
        $string = ($string == 'HS') ? 'H.-S.' : $string;

        if (isset($annee) && ($string == 'H.-S.')) {
            $string = $annee;
        }

        //Pour la partie saison et mois.
        $tabPeriode = array('janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre', 'printemps', 'été', 'automne', 'hiver');
        if (isset($annee) && (in_array(strtolower($string), $tabPeriode))) {
            $string = $annee;
        }

        return $string;
    }

}
