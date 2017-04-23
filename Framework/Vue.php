<?php

require_once 'Configuration.php';

/**
 * Classe modélisant une vue
 *
 * @version 1.0
 * @author Baptiste Pesquet
 */
class Vue {

    /** Nom du fichier associé à la vue */
    private $fichier;

    /** Titre de la vue (défini dans le fichier vue) */
    private $titre;

    /** Métadonnées de la vue, qui seront transmis dans /html/head
    *       Prend la forme d'une liste, où chaque element est une table de hashage composé d'une clé tagname et
    *       Exemple ::
    *           [
                    array(
                        'tagname' => 'meta',
                        'content' => 'balise invalide'
                        'attributes' => [
                            array('name' => 'charset', 'value' => 'utf-8')
                        ]
                    ),
                    array(
                        'tagname' => 'link',
                        'attributes' => [
                            array('name' => 'type', 'value' => 'text/css'),
                            array('name' => 'rel', 'value' => 'stylesheet'),
                            array('name' => 'href', 'value' => '/style.css')
                        ]
                    ),
                ]
            Crééra :
                <meta charset='utf-8'>balise invalide</meta>
                <link type="text/css" rel="stylesheet" href="/style.css"></link>
    */
    private $headers;

    private $javascripts;


    /**
     * Constructeur
     *
     * @param string $action Action à laquelle la vue est associée
     * @param string $controleur Nom du contrôleur auquel la vue est associée
     * @param array $headers  Les métadonnées de la page. Par exemple, en html, ce qui est dans /html/head
     */
    public function __construct($action, $controleur = "", $view = null, $headers = null) {
        // Détermination du nom du fichier vue à partir de l'action et du constructeur
        // La convention de nommage des fichiers vues est : Vue/<$controleur>/<$action>.php

        $fichier = Configuration::get('dirVue') . "/";
        if ($controleur != "") {
            $fichier = $fichier . $controleur . "/";
        }
        if ($view == null) {
            $this->fichier = $fichier . $action . ".php";
        } else {
            $this->fichier = $fichier . $view;
        }
        $this->headers = is_array($headers) ? $headers : array();
        $this->javascripts = array();
    }

    /**
     * Génère et affiche la vue
     *
     * @param array $donnees Données nécessaires à la génération de la vue
     * @param boolean gabarit Spécifie le gabarit à utiliser. S'il n'est pas fourni,
     *                          on prend le gabarit par défaut dans la configuration
     */
    public function generer($donnees, $gabarit = null) {
        // Génération de la partie spécifique de la vue
        $contenu = $this->genererFichier($this->fichier, $donnees);
        $contenu_erreur = null;
        if (isset($donnees["error_tpl"])) {
            $contenu_erreur = $this->genererFichier($donnees["error_tpl"], $donnees);
        }
        // On définit une variable locale accessible par la vue pour la racine Web
        // Il s'agit du chemin vers le site sur le serveur Web
        // Nécessaire pour les URI de type controleur/action/id
        $racineWeb = Configuration::get("racineWeb", "/");
        $corsURL = Configuration::get("crossDomainUrl");
        // Génération du gabarit commun utilisant la partie spécifique
        if ($gabarit == 'none') {
            $vue = $contenu;
        } else {
            $vue = $this->genererFichier(
                Configuration::get('dirVue').'/'.($gabarit == null ? Configuration::get("gabaritDefaut") : $gabarit),
                array(
                    'titre' => $this->titre,
                    'contenu' => $contenu,
                    'contenu_erreur' => $contenu_erreur,
                    'racineWeb' => $racineWeb,
                    'authInfos' => isset($donnees['authInfos']) ? $donnees['authInfos'] : null,
                    'modeAchat' => isset($donnees['modeAchat']) ? $donnees['modeAchat'] : null,
                    'corsURL' => $corsURL
                )
            );
        }
        // Renvoi de la vue générée au navigateur
        echo $vue;
    }

    /**
     * Génère un fichier vue et renvoie le résultat produit
     *
     * @param string $fichier Chemin du fichier vue à générer
     * @param array $donnees Données nécessaires à la génération de la vue
     * @return string Résultat de la génération de la vue
     * @throws Exception Si le fichier vue est introuvable
     */
    private function genererFichier($fichier, $donnees) {
        if (file_exists($fichier)) {
            // Rend les éléments du tableau $donnees accessibles dans la vue
            if ($donnees != NULL)
                extract($donnees);
            // Démarrage de la temporisation de sortie
            ob_start();
            // Inclut le fichier vue
            // Son résultat est placé dans le tampon de sortie
            require $fichier;
            // Arrêt de la temporisation et renvoi du tampon de sortie
            return ob_get_clean();
        } else {
            throw new Exception("Fichier '$fichier' introuvable");
        }
    }

    /**
     * Nettoie une valeur insérée dans une page HTML
     * Permet d'éviter les problèmes d'exécution de code indésirable (XSS) dans les vues générées
     *
     * @param string $valeur Valeur à nettoyer
     * @return string Valeur nettoyée
     */
    private function nettoyer($valeur) {
        return htmlspecialchars($valeur, ENT_QUOTES, 'UTF-8', false);
    }

    private function sanatizeXmlToText($string) {
        return html_entity_decode(strip_tags($string), ENT_QUOTES|ENT_XML1, 'UTF-8');
    }

    private function arrayToXmlTags($array) {
        $tags = array();
        foreach ($array as $tagDesc) {
            $tagName = $tagDesc['tagname'];
            if ($tagName === '!COMMENT') {
                $tag = '<!-- ' . $tagDesc['content'] . ' -->';
                $tags[] = $tag;
                continue;
            }
            if (!$tagName) { continue; }

            $tag = "<".$tagName;
            if (!isset($tagDesc['attributes'])) $tagDesc['attributes'] = array();
            foreach ($tagDesc['attributes'] as $attribute) {
                $tag .= ' '.$attribute['name'].'="'.$attribute['value'].'"';
            }
            if (isset($tagDesc['content'])) {
                $tag .= '>'.$tagDesc['content'].'</'.$tagName;
            }
            if (isset($tagDesc['autoclose']) && ($tagDesc['autoclose'] === true)) {
                $tag .= ' /';
            }
            $tag .= '>';
            $tags[] = $tag;
        }
        return $tags;
    }

    public function getHeaders($format, $join="\n") {
        if ($format === 'html') {
            return implode($join, $this->arrayToXmlTags($this->headers));
        }
    }


    public function getJavascripts() {
        $scripts = [];
        foreach ($this->javascripts as $script) {
            if (strpos($script, '<script') === 0) {
                $scripts[] = $script;
            } else {
                $scripts[] = "<script>\n$script\n</script>";
            }
        }
        return "\n" . implode("\n", $scripts) . "\n";
    }
}
