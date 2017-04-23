<?php
// require_once(Configuration::get('cairn_includes_path').'/watermarking/config/lang/eng.php');
class WatermarkingPdf
{
    public $TEMPDIR = null;

    /**
     * Class de génération des fichiers tmp et pdf en output
     */
    public function __construct() {
        /**
        * Où seront enregistré les fichiers temporaire utilisés pour la création du pdf + page de garde
        **/
        $this->TEMPDIR = Configuration::get('pdf_tempdir');
        if (!file_exists($this->TEMPDIR)) {
            mkdir($this->TEMPDIR, 0777, true);
        }
    }
    public function makeWatermarking($currentArticle, $authInfos, $pathpdf,$mode)
    {
        if (is_file($pathpdf . ".PDF")) {
            $diri = $pathpdf . ".PDF";
        } else {
            $diri = $pathpdf . ".pdf";
        }

        $ID_ARTICLE = $currentArticle ['ARTICLE_ID_ARTICLE'];

        $STAMP = $authInfos ['I'] ['NOM'] . " - " . $authInfos ['U'] ['NOM'] . " " . $authInfos ['U'] ['PRENOM'];
        // Contenu vertical affiché à droit du pdf
        if('normal'==$mode){
            $bout = 'Document téléchargé depuis www.cairn.info';
        }else {
            $bout = 'Document downloaded from www.cairn-int.info';
        }

        $download = utf8_decode($bout .' - '.$STAMP . ' - ' . $_SERVER ['REMOTE_ADDR'] . ' - ' . date("d/m/Y") . ' ' . date("H") . 'h' . date("i") . '. © ' . $currentArticle ['EDITEUR_NOM_EDITEUR'] . ' ');

        $STAMPCONTENT = '\Inputfile ' . $diri . ' $title 2 on

                        \NoBookMarkStamp remove _
                        \SetAttr ("Title" "1Cairn.info")
                        \SetAttr ("Author" "2Cairn.info")
                        \char (50)
                        \color (lightgray)
                        \transverse (on)

                        \transverse (off)
                        \color (black)
                        \char (20)
                        \Color (darkblue)
                        \zoom (on)
                        \rendering (0)
                        \linewidth (0.3)

                        \Zoom (off)
                        \char (8)
                        \Color (lightgray)
                        \Pos (lower right)
                        ' . $download . '
                        \lineColor (blue)
                        \rendering (3)
                        \linewidth (0.5)
                        \Pos (lower left)
                        ' . $download. '
                        \Pos (100 10)
                        \color (darkblue)
                        \center (on)

                        \color (darkgreen)

                        \center (off) ';

        $this->saveFile($this->TEMPDIR . "stamp" . $_SERVER ['REMOTE_ADDR'] . ".txt", $STAMPCONTENT);
        $tmpContent = $this->TEMPDIR . $ID_ARTICLE . $_SERVER ['REMOTE_ADDR'] . ".pdf";
        $tmpTop = $this->TEMPDIR . "Top" . $ID_ARTICLE . ".pdf";
        $tmpTxt = $this->TEMPDIR . "stamp" . $_SERVER ['REMOTE_ADDR'] . ".txt";
        $command = $tmpTxt . " " . $tmpTop . " " . $tmpContent;
        // Commande exécutée sur le terminal (exec): cette commande fusionne les pdf top et pdf original
        exec(Configuration::get('cairn_includes_path') . "pdstamp " . $command);
        if (!file_exists($tmpContent)) {
            die('<b>PDF Tools non installés sur cette machine !!</b>');
        }
        $result = file_get_contents($tmpContent);
        // On supprime les fichiers tmp
        unlink($tmpTxt);
        unlink($tmpTop);
        unlink($tmpContent);
        // $result contient le larticle complet (page de garde + pdf original concaténés)
        return $result;
    }
    public function saveFile($file, $content)
    {
        $fh = fopen($file, "w") or print('Fichier non enregistré');
        fwrite($fh, $content);
        fclose($fh);
    }

}

?>

