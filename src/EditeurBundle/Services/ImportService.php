<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 23/03/2017
 * Time: 18:01
 */

namespace EditeurBundle\Services;

use Doctrine\ORM\EntityManager;
use EditeurBundle\Services\Logs;
use Liuggio\ExcelBundle\Factory;
use AppBundle\Entity\Etablissement;
use AppBundle\Entity\Formation;
use AppBundle\Entity\Localisation;
use AppBundle\Entity\Metier3;
use AppBundle\Entity\Tag;
use AppBundle\Entity\Discipline;

class ImportService
{
    protected $em;
    protected $log;
    protected $factory;
    protected $type;
    protected $etablissement;
    protected $tabComparaison = null;
    protected $tabCheckDoublons = null;
    protected $tabDisciplineHceres = null;
    protected $tabDisciplineSise = null;
    protected $tabDisciplineCnu = null;
    protected $tabTags = null;

    //nombre collonnes requis pour un fichier de type  formations
    const REQUIRED_NUMBER_1 = 55;
    //nombre collonnes requis pour un fichier de type laboratoires
    const REQUIRED_NUMBER_2 = 71;
    const TYPE_FORMATION = 'F';
    const TYPE_LABO = 'L';
    const COMMIT_STEP = 5;

    public function __construct(EntityManager $em, Logs $log, Factory $factory)
    {
        $this->em = $em;
        $this->log = $log;
        $this->factory = $factory;
    }

    public function import(Etablissement $etablissement, $type, $file)
    {
        $this->type = $type;
        $this->etablissement = $etablissement;
        try {
            if (!$formattedData = $this->getData($file)) {
                return false;
            }
            if (!$checkedData = $this->checkData($formattedData)) {
                return false;
            }
            if ($this->type == self::TYPE_FORMATION) {
                if (!$importation = $this->importFormationData($formattedData)) {
                    return false;
                }
            } else if ($this->type == self::TYPE_LABO) {
                if (!$importation = $this->importLaboData($formattedData)) {
                    return false;
                }
            } else {
                return false;
            }

        } catch (\Exception $e) {
            $this->log->warning('Erreur technique inattendue : '.$e->getMessage());
            return false;
        }

        return true;
    }

    public function getData($file)
    {
        $pathParts = pathinfo($file);
        $this->log->setAttribute($pathParts['dirname'], $pathParts['filename']);

        if ($pathParts['extension'] == "csv") {
            return $this->getDataFromCsv($file);
        } else if ($pathParts['extension'] == "xls") {
            return $this->getDataFromExcel($file, 'Excel5');
        } else if ($pathParts['extension'] == "xlsx") {
            return $this->getDataFromExcel($file, 'Excel2007');
        } else {
            $this->log->warning("Le fichier  n'est pas au format autorisé.");
            return false;
        }
    }

    public function getDataFromCsv($file)
    {
        $f = fopen($file, 'r');
        $dataArray = [];
        $line = 0;
        while (($rawLine = fgets($f)) !== false) {
            $line++;
            if (!$dataArray[$line] = $this->formatDataCsv($rawLine, $line)) {
                return false;
            }
        }
        fclose($f);
        return $dataArray;
    }

    //ok
    public function formatDataCsv($rawLine, $line)
    {
        $formattedData = [];
        $data = explode(";", $rawLine);

        if ($this->checkNbFields($data, $line)) {

            if ($this->type == self::TYPE_FORMATION) {
                list(
                    $formattedData['formation']['annee'],
                    $formattedData['etablissement']['code'],
                    $formattedData['etablissement']['nom'],
                    $formattedData['localisation']['nom'],
                    $formattedData['localisation']['lat'],
                    $formattedData['localisation']['long'],
                    $formattedData['localisation']['adresse'],
                    $formattedData['localisation']['complement_adresse'],
                    $formattedData['localisation']['ville'],
                    $formattedData['localisation']['code'],
                    $formattedData['localisation']['cedex'],
                    $formattedData['localisation']['region'],
                    $formattedData['localisation']['pays'],
                    $formattedData['formation']['typeDiplome'],
                    $formattedData['formation']['niveau'],
                    $formattedData['formation']['lmd'],
                    $formattedData['formation']['modalite_thesaurus'],
                    $formattedData['formation']['ects'],
                    $formattedData['formation']['url'],
                    $formattedData['formation']['nom'],
                    /*K*/, //service de rattachement
                    /* domaine_sise_1 */,
                    $formattedData['discipline']['abreviation_sise_1']/*type SISE*/,
                    /* domaine_sise_2 */,
                    $formattedData['discipline']['abreviation_sise_2']/*type SISE*/,
                    /* domaine_sise_3 */,
                    $formattedData['discipline']['abreviation_sise_3']/*type SISE*/,
                    /* domaine_sise_4 */,
                    $formattedData['discipline']['abreviation_sise_4']/*type SISE*/,
                    /* domaine_sise_5 */,
                    $formattedData['discipline']['abreviation_sise_5']/*type SISE*/,
                    $formattedData['discipline']['nom_cnu_1']/*type CNU*/,
                    $formattedData['discipline']['nom_cnu_2']/*type CNU*/,
                    $formattedData['discipline']['nom_cnu_3']/*type CNU*/,
                    $formattedData['discipline']['nom_cnu_4']/*type CNU*/,
                    $formattedData['discipline']['nom_cnu_5']/*type CNU*/,
                    /* domaine_hcere_1 */,
                    $formattedData['discipline']['abreviation_hceres_1']/*type HCERE*/,
                    /* domaine_hcere_2 */,
                    $formattedData['discipline']['abreviation_hceres_2']/*type HCERE*/,
                    /* domaine_hcere_3 */,
                    $formattedData['discipline']['abreviation_hceres_3']/*type HCERE*/,
                    /* domaine_hcere_4 */,
                    $formattedData['discipline']['abreviation_hceres_4']/*type HCERE*/,
                    /* domaine_hcere_5 */,
                    $formattedData['discipline']['abreviation_hceres_5']/*type HCERE*/,
                    $formattedData['tags']['nom']/*variable compose*/,
                    ,//$formattedData['discipline']['nom_nw3_1']/*type NW3*/,
                    ,//$formattedData['discipline']['nom_nw3_2']/*type NW3*/,
                    $formattedData['formation']['effectif'],
                    $formattedData['metier']['code_1'], //table formation_has_metier
                    $formattedData['metier']['code_2'],
                    $formattedData['metier']['code_3'],
                    $formattedData['metier']['code_4'],
                    $formattedData['metier']['code_5'],
                    ) = array_map('trim', $data);
            } else if ($this->type == self::TYPE_LABO) {
                //TODO for Labo
                list(
                    $formattedData['etablissement']['code'],
                    $formattedData['etablissement']['nom'],
                    , //service de rattachement
                    $formattedData['labo']['type'],
                    $formattedData['labo']['code'],
                    $formattedData['labo']['nom'],
                    $formattedData['labo']['sigle'],
                    $formattedData['labo']['etab_ext'],
                    $formattedData['localisation']['nom'],
                    $formattedData['localisation']['lat'],
                    $formattedData['localisation']['long'],
                    $formattedData['localisation']['adresse'],
                    $formattedData['localisation']['complement_adresse'],
                    $formattedData['localisation']['ville'],
                    $formattedData['localisation']['code'],
                    $formattedData['localisation']['cedex'],
                    $formattedData['localisation']['region'],
                    $formattedData['localisation']['pays'],
                    $formattedData['labo']['url_1'],
                    $formattedData['lab']['url_2'],
                    $formattedData['lab']['url_3'],
                    $formattedData['labo']['mailContact'],
                    $formattedData['ed']['nom'],
                    /* domaine_sise_1 */,
                    $formattedData['discipline']['abreviation_sise_1']/*type SISE*/,
                    /* domaine_sise_2 */,
                    $formattedData['discipline']['abreviation_sise_2']/*type SISE*/,
                    /* domaine_sise_3 */,
                    $formattedData['discipline']['abreviation_sise_3']/*type SISE*/,
                    /* domaine_sise_4 */,
                    $formattedData['discipline']['abreviation_sise_4']/*type SISE*/,
                    /* domaine_sise_5 */,
                    $formattedData['discipline']['abreviation_sise_5']/*type SISE*/,
                    $formattedData['discipline']['nom_cnu_1']/*type CNU*/,
                    $formattedData['discipline']['nom_cnu_2']/*type CNU*/,
                    $formattedData['discipline']['nom_cnu_3']/*type CNU*/,
                    $formattedData['discipline']['nom_cnu_4']/*type CNU*/,
                    $formattedData['discipline']['nom_cnu_5']/*type CNU*/,
                    /* domaine_hcere_1 */,
                    $formattedData['discipline']['abreviation_hceres_1']/*type HCERE*/,
                    /* domaine_hcere_2 */,
                    $formattedData['discipline']['abreviation_hceres_2']/*type HCERE*/,
                    /* domaine_hcere_3 */,
                    $formattedData['discipline']['abreviation_hceres_3']/*type HCERE*/,
                    /* domaine_hcere_4 */,
                    $formattedData['discipline']['abreviation_hceres_4']/*type HCERE*/,
                    /* domaine_hcere_5 */,
                    $formattedData['discipline']['abreviation_hceres_5']/*type HCERE*/,
                    $formattedData['tags']['nom']/*variable compose*/,
                    ,//$formattedData['discipline']['nom_nw3_1']/*type NW3*/,
                    ,//$formattedData['discipline']['nom_nw3_2']/*type NW3*/,
                    $formattedData['labo']['effectif'],
                    $formattedData['labo']['effectif_hesam'],
                    $formattedData['axe']['nom_1'],
                    $formattedData['axe']['nom_2'],
                    $formattedData['axe']['nom_3'],
                    $formattedData['axe']['nom_4'],
                    $formattedData['axe']['nom_5'],
                    $formattedData['axe']['nom_6'],
                    $formattedData['axe']['nom_7'],
                    $formattedData['equipement']['nom'], //avec ;
                    $formattedData['membre']['nom_prenom_1'],
                    $formattedData['membre']['email_1'],
                    $formattedData['membre']['nom_prenom_2'],
                    $formattedData['membre']['email_2'],
                    $formattedData['membre']['nom_prenom_3'],
                    $formattedData['membre']['email_3'],
                    $formattedData['membre']['nom_prenom_4'],
                    $formattedData['membre']['email_4'],
                    $formattedData['membre']['nom_prenom_5'],
                    $formattedData['membre']['email_5'],

                    ) = array_map('trim', $data);

            }
        } else {
            return false;
        }

        return $formattedData;
    }

    public function getDataFromExcel($file, $version)
    {
        $dataArray = [];
        /**
        * s'il y a erreur de createReader
        *
        * il faut ajouter createReader dans factory du liuggio/ExcelBundle
        * car a l'heure actuel dernier version recuperable par composer il n'a pas cette fonction qui return @return \PHPExcel_Reader_IReader
        *  public function createReader($type = 'Excel5'){ return call_user_func(array($this->phpExcelIO, 'createReader'), $type);}
        */

        $reader = $this->factory->createReader($version);
        $canread = $reader->canRead($file);
        $phpExcelObject = $reader->load($file);
        //on traite que deuzieme onglait
        $activeSheetName = 'Formations Diplômes';
        $worksheet = $phpExcelObject->getSheet(0);
        $contents = $worksheet->toArray(null,true,true,true);
        $highestRow = $worksheet->getHighestRow();
        //$highestColumn = $worksheet->getHighestColumn();
       // $columns = $this->get_range('A', $highestColumn);

        for ($line = 2; $line <= $highestRow; $line++) {
            if ($this->formatDataExcel(array_map('trim',$contents[$line]), $line)) {
                $dataArray[$line] = $this->formatDataExcel(array_map('trim', $contents[$line]), $line);
            } else {
                return false;
            }
        }
        return $dataArray;
    }

    public function formatDataExcel($data, $line)
    {
        $formattedData = [];

        if ($this->checkNbFields($data, $line)) {

            if ($this->type == self::TYPE_FORMATION) {
                $formattedData['formation']['annee'] = $data['A'];
                $formattedData['etablissement']['code'] = $data['B'];
                $formattedData['etablissement']['nom'] = $data['C'];
                $formattedData['localisation']['nom'] = $data['D'];
                $formattedData['localisation']['lat'] = $data['E'];
                $formattedData['localisation']['long'] = $data['F'];
                $formattedData['localisation']['adresse'] = $data['G'];
                $formattedData['localisation']['complement_adresse'] = $data['H'];
                $formattedData['localisation']['ville'] = $data['I'];
                $formattedData['localisation']['code'] = $data['J'];
                $formattedData['localisation']['cedex'] = $data['K'];
                $formattedData['localisation']['region'] = $data['L'];
                $formattedData['localisation']['pays'] = $data['M'];
                $formattedData['formation']['typeDiplome'] = $data['N'];
                $formattedData['formation']['niveau'] = $data['O'];
                $formattedData['formation']['lmd'] = $data['P'];
                $formattedData['formation']['modalite_thesaurus'] = $data['Q'];
                $formattedData['formation']['ects'] = $data['R'];
                $formattedData['formation']['url'] = $data['S'];
                $formattedData['formation']['nom'] = $data['T'];
                /* U service de rattachement */
                /* V domaine_sise_1 */
                $formattedData['discipline']['abreviation_sise_1'] = $data['W'];
                /* X domaine_sise_2 */
                $formattedData['discipline']['abreviation_sise_2'] = $data['Y'];
                /* Z domaine_sise_3 */
                $formattedData['discipline']['abreviation_sise_3'] = $data['AA'];
                /* AB domaine_sise_4 */
                $formattedData['discipline']['abreviation_sise_4'] = $data['AC'];
                /* AD domaine_sise_5 */
                $formattedData['discipline']['abreviation_sise_5'] = $data['AE'];
                $formattedData['discipline']['nom_cnu_1'] = $data['AF'];
                $formattedData['discipline']['nom_cnu_2'] = $data['AG'];
                $formattedData['discipline']['nom_cnu_3'] = $data['AH'];
                $formattedData['discipline']['nom_cnu_4'] = $data['AI'];
                $formattedData['discipline']['nom_cnu_5'] = $data['AJ'];
                /* AK domaine_hceres_1 */
                $formattedData['discipline']['abreviation_hceres_1'] = $data['AL'];
                /* AM domaine_hceres_2 */
                $formattedData['discipline']['abreviation_hceres_2'] = $data['AN'];
                /* AO domaine_hcere_3 */
                $formattedData['discipline']['abreviation_hceres_3'] = $data['AP'];
                /* AQ domaine_hceres_4 */
                $formattedData['discipline']['abreviation_hceres_4'] = $data['AR'];
                /* AS domaine_hceres_5 */
                $formattedData['discipline']['abreviation_hceres_5'] = $data['AT'];
                $formattedData['tags']['nom'] = $data['AU'];
                $formattedData['discipline']['nom_nw3_1'] = $data['AV'];
                $formattedData['discipline']['nom_nw3_2'] = $data['AW'];
                $formattedData['formation']['effectif'] = $data['AX'];
                $formattedData['metier']['code_1'] = $data['AY']; // table formation_has_metier
                $formattedData['metier']['code_2'] = $data['AZ'];
                $formattedData['metier']['code_3'] = $data['BA'];
                $formattedData['metier']['code_4'] = $data['BB'];
                $formattedData['metier']['code_5'] = $data['BC'];

            } else if ($this->type == self::TYPE_LABO) {
                $formattedData['etablissement']['code'] = $data[''];
                $formattedData['etablissement']['nom'] = $data[''];
                //service de rattachement
                $formattedData['labo']['type'] = $data[''];
                $formattedData['labo']['code'] = $data[''];
                $formattedData['labo']['nom'] = $data[''];
                $formattedData['labo']['sigle'] = $data[''];
                $formattedData['labo']['etab_ext'] = $data[''];
                $formattedData['localisation']['nom'] = $data[''];
                $formattedData['localisation']['lat'] = $data[''];
                $formattedData['localisation']['long'] = $data[''];
                $formattedData['localisation']['adresse'] = $data[''];
                $formattedData['localisation']['complement_adresse'] = $data[''];
                $formattedData['localisation']['ville'] = $data[''];
                $formattedData['localisation']['code'] = $data[''];
                $formattedData['localisation']['cedex'] = $data[''];
                $formattedData['localisation']['region'] = $data[''];
                $formattedData['localisation']['pays'] = $data[''];
                $formattedData['labo']['url_1'] = $data[''];
                $formattedData['lab']['url_2'] = $data[''];
                $formattedData['lab']['url_3'] = $data[''];
                $formattedData['labo']['mailContact'] = $data[''];
                $formattedData['ed']['nom'] = $data[''];
                /* domaine_sise_1 */
                $formattedData['discipline']['abreviation_sise_1'] = $data['']; /*type SISE*/
                /* domaine_sise_2 */
                $formattedData['discipline']['abreviation_sise_2'] = $data[''];/*type SISE*/
                /* domaine_sise_3 */
                $formattedData['discipline']['abreviation_sise_3'] = $data[''];/*type SISE*/
                /* domaine_sise_4 */
                $formattedData['discipline']['abreviation_sise_4'] = $data[''];/*type SISE*/
                /* domaine_sise_5 */
                $formattedData['discipline']['abreviation_sise_5'] = $data[''];/*type SISE*/
                $formattedData['discipline']['nom_cnu_1'] = $data[''];/*type CNU*/
                $formattedData['discipline']['nom_cnu_2'] = $data[''];/*type CNU*/
                $formattedData['discipline']['nom_cnu_3'] = $data[''];/*type CNU*/
                $formattedData['discipline']['nom_cnu_4'] = $data[''];/*type CNU*/
                $formattedData['discipline']['nom_cnu_5'] = $data[''];/*type CNU*/
                /* domaine_hcere_1 */
                $formattedData['discipline']['abreviation_hceres_1'] = $data[''];/*type HCERE*/
                /* domaine_hcere_2 */
                $formattedData['discipline']['abreviation_hceres_2'] = $data[''];/*type HCERE*/
                /* domaine_hcere_3 */
                $formattedData['discipline']['abreviation_hceres_3'] = $data[''];/*type HCERE*/
                /* domaine_hcere_4 */
                $formattedData['discipline']['abreviation_hceres_4'] = $data[''];/*type HCERE*/
                /* domaine_hcere_5 */
                $formattedData['discipline']['abreviation_hceres_5'] = $data[''];/*type HCERE*/
                $formattedData['tags']['nom'] = $data[''];/*variable compose*/
                //$formattedData['discipline']['nom_nw3_1'] = $data[''];/*type NW3*/
                //$formattedData['discipline']['nom_nw3_2'] = $data[''];/*type NW3*/
                $formattedData['labo']['effectif'] = $data[''];
                $formattedData['labo']['effectif_hesam'] = $data[''];
                $formattedData['axe']['nom_1'] = $data[''];
                $formattedData['axe']['nom_2'] = $data[''];
                $formattedData['axe']['nom_3'] = $data[''];
                $formattedData['axe']['nom_4'] = $data[''];
                $formattedData['axe']['nom_5'] = $data[''];
                $formattedData['axe']['nom_6'] = $data[''];
                $formattedData['axe']['nom_7'] = $data[''];
                $formattedData['equipement']['nom'] = $data['']; //avec ;
                $formattedData['membre']['nom_prenom_1'] = $data[''];
                $formattedData['membre']['email_1'] = $data[''];
                $formattedData['membre']['nom_prenom_2'] = $data[''];
                $formattedData['membre']['email_2'] = $data[''];
                $formattedData['membre']['nom_prenom_3'] = $data[''];
                $formattedData['membre']['email_3'] = $data[''];
                $formattedData['membre']['nom_prenom_4'] = $data[''];
                $formattedData['membre']['email_4'] = $data[''];
                $formattedData['membre']['nom_prenom_5'] = $data[''];
                $formattedData['membre']['email_5'] = $data[''];
            }
        } else {
            return false;
        }

        return $formattedData;
    }

    public function checkNbFields(array $data, $line)
    {
        $nbFields = count($data);
        if (($this->type == 1 && $nbFields != self::REQUIRED_NUMBER_1) || ($this->type == 2 && $nbFields != self::REQUIRED_NUMBER_2) ) {
            $msg = "Ln $line : Le nombre des colonnes ( $nbFields ) est erroné ou non compatible.";
            $this->log->warning($msg);
            return false;
        }
        return true;
    }

    public function checkData($formattedData)
    {
        $valid = true;

        foreach ($formattedData as $line => $data) {
            if (empty($data)) {
                $valid = false;
            } else {
                //Formation
                if ($this->type == self::TYPE_FORMATION) {
                    if (!$this->checkFormationData($data['formation'], $line)) {
                        $valid = false;
                    }
                    //formation_has_metier3
                    if (!$this->checkMetierData($data['metier'], $line)) {
                        $valid = false;
                    }
                }
                //Localisation
                if (!$this->checkLocalisationData($data['localisation'], $line)) {
                    $valid = false;
                }
                //Etablissement
                if (!$this->checkEtablissementData($data['etablissement'], $line)) {
                    $valid = false;
                }
                if (!$this->checkDisciplineData($data['discipline'], $line)) {
                    $valid = false;
                }
            }
        }

        return $valid;
    }

    public function checkLocalisationData($data, $line)
    {
        $valid = true;

        $requiredParams = array('adresse', 'ville', 'code');
        $checkRequiredParams = $this->checkMandatoryParameters($data, $requiredParams);

        if ($checkRequiredParams['success'] === false) {
            $msg = sprintf('Ln %d : Les champs obligatoires "%s" sont manquants pour les données localisation, de la formation', $line, $checkRequiredParams['param']);
            $this->log->warning($msg);
            $valid = false;
        }
        //existLocalisation
        //todo ajouter gestion envoie mail a admin sil la localisation n'est pas presente dans la base
        if (!$this->em
            ->getRepository('AppBundle:Localisation')
            ->existLocalisation($data['adresse'], $data['code'], $data['ville'])) {
            $msg = sprintf('Ln %d : la localisation inconnue : %s %s %s.', $line, $data['adresse'], $data['code'], $data['ville']);
            $this->log->warning($msg);
            $valid = false;
        }
        return $valid;
    }

    public function checkMetierData($dataMetier, $line) {

        $valid = true;

        for ($i = 1; $i <= 5; $i++) {
            $metier = 'code_'. $i;
            if (!empty(trim($dataMetier[$metier]))) {
                if (!$this->em->getRepository('AppBundle:Metier3')->existMetier($dataMetier[$metier])) {
                    $msg = sprintf('Ln %d : le metier pour le code ROME  "%s".', $line, $dataMetier[$metier]);
                    $this->log->warning($msg);
                    $valid = false;
                }
            }
        }
        return $valid;
    }

    public function checkFormationData($data, $line)
    {
        $valid = true;

        $requiredParams = array('annee', 'typeDiplome', 'nom');
        $checkRequiredParams = $this->checkMandatoryParameters($data, $requiredParams);

        if ($checkRequiredParams['success'] === false) {
            $msg = sprintf('Ln %d : Les champs obligatoires "%s" sont manquants pour les données formation', $line, $checkRequiredParams['param']);
            $this->log->warning($msg);
            $valid = false;
        }
        // verifier format annee

        $pattern = '/^\d{4}$/';
        if ($data['annee'] != '' && !preg_match($pattern, $data['annee'])) {
            $msg = sprintf('Ln %d : Mauvais format de date : "%s"', $line, $data['annee']);
            $this->log->warning($msg);
            $valid = false;
        }

        if ($this->initTabComparaison()) {
            $strFormation = $this->getStrFormation($data['nom'], $data['typeDiplome'], $data['niveau'], $data['annee']);
            if (array_key_exists($strFormation, $this->tabCheckDoublons)) {
               $msg = sprintf('Ln %d : La formation "%s | %s | %s | %s" existe déjà dans la BDD avec identifiant %d', $line, $data['nom'], $data['typeDiplome'], $data['niveau'], $data['annee'], $this->tabCheckDoublons[$strFormation]);
               $this->log->warning($msg);
               $valid = false;
           }
        }

        return $valid;
    }

    public function checkEtablissementData($data, $line)
    {
        $valid = true;

        $requiredParams = array('code', 'nom');
        $checkRequiredParams = $this->checkMandatoryParameters($data, $requiredParams);

        if ($checkRequiredParams['success'] === false) {
            $msg = sprintf('Ln %d : Les champs obligatoires "%s" sont manquants pour les données établissement', $line, $checkRequiredParams['param']);
            $this->log->warning($msg);
            $valid = false;
        }

        //verification si l'etablissement demande est bien avec meme id et meme code
        if ($data['code'] != '' && !$this->em->getRepository('AppBundle:Etablissement')->verifyEtablissementByCodeAndId($this->etablissement->getEtablissementId(), $data['code'])) {
            $msg = sprintf('Ln %d : L\'établissement inconnu avec son identifiant %d et son code %s', $line, $this->etablissement->getEtablissementId(), $data['code']);
            $this->log->warning($msg);
            $valid = false;
        }
        return $valid;
    }

    public function checkDisciplineData($data, $line)
    {
        $valid = true;

        if (!$this->checkDisciplineParameters($data)) {
            $msg = sprintf("Ln %d : Une ou plusieurs valeurs  des disciplines type SISE, CNU et HCERES est/sont absente(s)", $line);
            $this->log->warning($msg);
            $valid = false;
        }
        if (!$this->checkDisciplinesValues($data, $line)) {
            $valid = false;
        }
        return $valid;
    }

    /**
     * verification la presence les champs obligatoires pour traitement
     */
    public function checkMandatoryParameters($data, $requiredParams = null)
    {
        $res['success'] = true;

        if ($requiredParams != null) {
            foreach ($data as $key => $val) {
                if (trim($val) == '') {
                    unset($data[$key]);
                }
            }

            $missingParams = array_diff_key(array_flip($requiredParams), $data);

            if (!empty($missingParams)) {
                $missingParamList = implode(', ', array_keys($missingParams));
                $res['success'] = false;
                $res['param'] = $missingParamList;
            }
        }
        return $res;
    }

    public function checkDisciplineParameters($disciplines) {
        //RG au moins un champs pour SISE, CNU et HCERES est présent (en tout 3 champs au minimum)
        //TODO trim avant
        if (($disciplines['abreviation_sise_1'] == ''
                && $disciplines['abreviation_sise_2'] == ''
                && $disciplines['abreviation_sise_3'] == ''
                && $disciplines['abreviation_sise_4'] == ''
                && $disciplines['abreviation_sise_5'] == '')
            || ($disciplines['nom_cnu_1']  == ''
                && $disciplines['nom_cnu_2'] == ''
                && $disciplines['nom_cnu_3'] == ''
                && $disciplines['nom_cnu_4'] == ''
                && $disciplines['nom_cnu_5'] == '')
            || ($disciplines['abreviation_hceres_1'] == ''
                && $disciplines['abreviation_hceres_2'] == ''
                && $disciplines['abreviation_hceres_3'] == ''
                && $disciplines['abreviation_hceres_4'] == ''
                && $disciplines['abreviation_hceres_5'] == '')
        ) {
            return false;
        }
        return true;
    }

    public function checkDisciplinesValues($disciplines, $line)
    {
        for ($i = 1; $i <= 5; $i++) {
            //SISE
            $sise = 'abreviation_sise_'.$i;
            if ($disciplines[$sise] != '') {
                if (!$this->disciplineExists($disciplines[$sise], 'SISE')) {
                    $msg = sprintf('Ln %d : La valeur de discipline SISE "%s" est inconnue.', $line, $disciplines[$sise]);
                    $this->log->warning($msg);
                    return false;
                }
            }
            $hceres = 'abreviation_hceres_'.$i;
            if ($disciplines[$hceres] != '') {
                if (!$this->disciplineExists($disciplines[$hceres], 'HCERES')) {
                    $msg = sprintf('Ln %d : La valeur de discipline HCERES "%s" est inconnue.', $line, $disciplines[$hceres]);
                    $this->log->warning($msg);
                    return false;
                }
            }
            $cnu = 'nom_cnu_'.$i;
            if ($disciplines[$cnu] != '') {
                if (!$this->disciplineExists($disciplines[$cnu], 'CNU')) {
                    $msg = sprintf('Ln %d : La valeur de discipline CNU "%s" est inconnue.', $line, $disciplines[$cnu]);
                    $this->log->warning($msg);
                    return false;
                }
            }
        }
        return true;
    }

    public function importLaboData($formattedData)
    {
        return true;
    }


    public function importFormationData($formattedData)
    {
        //verifier si la formation ou le labo existe
        $valid = true;

        foreach ($formattedData as $line => $data) {
            try {
                $formation = new Formation();
                $formation->addEtablissement($this->etablissement);
                //$this->etablissement->addFormation($formation);

                if ($objId = $this->getObjId($data['formation'])) {
                    $formation->setObjetId($objId);
                } else {
                    $formationId = $formation->getFormationId();
                    $formation->setObjetId('F' . $formationId);
                }
                $formation->setNom($data['formation']['nom']);
                $formation->setLmd($data['formation']['lmd']);
                /*
                //TODO  modalite_thesaurus
                //data_error in line : 5 Message : Expected value of type "AppBundle\Entity\Thesaurus" for association field "AppBundle\Entity\Formation#$modalite_thesaurus", got "string" instead.
                $formation->setModaliteThesaurus($data['formation']['modalite_thesaurus']);
                */

                //TODO  add code interne formation

                $formation->setEcts($data['formation']['ects']);
                //$formation->setDescription($data['formation']['description']);
                $formation->setUrl($data['formation']['url']);
                $formation->setAnnee($data['formation']['annee']);
                $formation->setNiveau($data['formation']['niveau']);
                $formation->setTypeDiplome($data['formation']['typeDiplome']);
                $formation->setDateCreation(new \DateTime());
                $formation->setLastUpdate(new \DateTime());
                if ($data['formation']['effectif'] != '') {
                    $formation->setEffectif($data['formation']['effectif']);
                }

                //Localisation
                $localisation = $this->em
                    ->getRepository('AppBundle:Localisation')
                    ->findOneBy(array('adresse' => $data['localisation']['adresse'], 'code' => $data['localisation']['code'], 'ville' => $data['localisation']['ville']));

                $formation->addLocalisation($localisation);

                //Disciplines et Debouché
                for ($i = 1; $i <= 5; $i++) {
                    //SISE
                    $sise = 'abreviation_sise_' . $i;
                    if ($data['discipline'][$sise] != '') {
                        $discipline = $this->em
                            ->getRepository('AppBundle:Discipline')
                            ->findOneBy(array('nom' => $data['discipline'][$sise], 'type' => 'SISE'));

                        $formation->addDiscipline($discipline);
                    }
                    $hceres = 'abreviation_hceres_' . $i;
                    if ($data['discipline'][$hceres] != '') {
                        $discipline = $this->em
                            ->getRepository('AppBundle:Discipline')
                            ->findOneBy(array('nom' => $data['discipline'][$hceres], 'type' => 'HCERES'));

                        $formation->addDiscipline($discipline);
                    }
                    $cnu = 'nom_cnu_' . $i;
                    if ($data['discipline'][$cnu] != '') {
                        $discipline = $this->em
                            ->getRepository('AppBundle:Discipline')
                            ->findOneBy(array('nom' => $data['discipline'][$cnu], 'type' => 'CNU'));

                        $formation->addDiscipline($discipline);
                    }
                }

                //Tags
                if ($data['tags']['nom'] !='') {
                    $tags = explode(';', $data['tags']['nom']);

                    foreach ($tags as $value) {
                        //verifier sil est deja dans la base, avant il faut supprimer accents etc..
                        $valueSansAccents = $this->removeAccents($value);
                        if (!$this->tagExists($valueSansAccents)) {
                            $tag = new Tag();
                            $tag->setNom($value);
                            $tag->setDateCreation(new \DateTime());
                            $tag->setLastUpdate(new \DateTime());
                            $this->em->persist($tag);
                            //on ajout nouvelle entree dans tabTags
                            $newTag = array($valueSansAccents => $tag->getTagId());
                            array_merge($this->tabTags, $newTag);
                        } else {
                            $tag = $this->em
                                ->getRepository('AppBundle:Tag')
                                ->find($this->tabTags[$valueSansAccents]);
                        }
                        $formation->addTag($tag);
                    }
                }

                $this->em->persist($formation);
                if ((($line % self::COMMIT_STEP) == 0)) {
                    $this->em->flush();
                }
            } catch (\Exception $e) {
                $this->log->warning('data_error in line : '.$line.' Message : '.$e->getMessage());
                $valid = false;
            }
        }
        $this->em->flush();
        return $valid;
    }

    public function getStrFormation($nom, $typeDiplome, $niveau, $annee = null)
    {
        return $this->removeAccents($nom).$this->removeAccents($typeDiplome).$this->removeAccents($niveau).(($annee === null)?:$this->removeAccents($annee));
    }

    public function removeAccents($str)
    {
        $str = htmlentities($str, ENT_NOQUOTES, $charset='utf-8');

        $str = preg_replace('#&([A-za-z])(?:acute|cedil|caron|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);
        $str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str); // pour les ligatures e.g. '&oelig;'
        $str = preg_replace('#&[^;]+;()#', '', $str); // supprime les autres caractères
        $str = preg_replace("#[\\(\\)\\[\\]\\|\\-\\+\\{\\}_;:, ']+#", '', $str); // supprime les autres caractères
        $str = trim(strtolower($str));
        return $str;
    }

    public function getTabDisciplineSise()
    {
        if ($this->tabDisciplineSise === null) {
            $tabDisciplines = array();
            $repository = $this->em->getRepository('AppBundle:Discipline');
            $disciplines = $repository->findAllDisciplines('SISE');

            foreach ($disciplines as $discipline) {
                $key = $discipline['nom'];
                $tabDisciplines[$key] = null;
            }
            $this->tabDisciplineSise = $tabDisciplines;
        }
        return $this->tabDisciplineSise;
    }

    public function getTabDisciplineHceres()
    {
        if ($this->tabDisciplineHceres === null) {
            $tabDisciplines = array();
            $disciplines = $this->em->getRepository('AppBundle:Discipline')->findAllDisciplines('HCERES');
            foreach ($disciplines as $discipline) {
                $key = $discipline['nom'];
                $tabDisciplines[$key] = null;
            }
            $this->tabDisciplineHceres = $tabDisciplines;
        }
        return $this->tabDisciplineHceres;
    }

    public function getTabDisciplineCnu()
    {
        if ($this->tabDisciplineCnu === null) {
            $tabDisciplines = array();
            $disciplines = $this->em->getRepository('AppBundle:Discipline')->findAllDisciplines('CNU');
            foreach ($disciplines as $discipline) {
                $key = $discipline['nom'];
                $tabDisciplines[$key] = null;
            }
            $this->tabDisciplineCnu = $tabDisciplines;
        }
        return $this->tabDisciplineCnu;
    }

    public function getTabTags()
    {
        if ($this->tabTags === null) {
            $tag = array();
            $tags = $this->em->getRepository('AppBundle:Tag')->getAllNom();
            foreach ($tags as $value) {
                $key = $this->removeAccents($value['nom']);
                $tag[$key] = $value['tagId'];
            }
            $this->tabTags = $tag;
        }
        return $this->tabTags;
    }

    private function disciplineExists($discipline, $type)
    {
        switch ($type) {
            case 'SISE':
                if ($this->tabDisciplineSise === null) {
                    $this->getTabDisciplineSise();
                }
                return array_key_exists($discipline, $this->tabDisciplineSise);
                break;
            case 'HCERES':
                if ($this->tabDisciplineHceres === null) {
                    $this->getTabDisciplineHceres();
                }
                return array_key_exists($discipline, $this->tabDisciplineHceres);
                break;
            case 'CNU':
                if ($this->tabDisciplineCnu === null) {
                    $this->getTabDisciplineCnu();
                }
                return array_key_exists($discipline, $this->tabDisciplineCnu);
                break;
            default:
                return false;
        }
    }

    private function tagExists($tag)
    {
        if ($this->tabTags === null) {
            $this->getTabTags();
        }
        return array_key_exists($tag, $this->tabTags);
    }

    public function initTabComparaison()
    {
        if ($this->tabComparaison === null || $this->tabCheckDoublons === null) {
            if ($this->type == self::TYPE_FORMATION) {
                if ($this->getTabComparaisonFormations()) {
                    return true;
                }
            } else {
                if ($this->getTabComparaisonLabo()) {
                    return true;
                }
            }
        }
        return true;
    }

    public function getTabComparaisonFormations()
    {
        $list =  $this->etablissement->getFormation();
        $dataComparaison = [];
        $dataCheckDoublons = [];

        foreach ($list as $val) {
            //changer getFormationId par getObjetId de qu'il est en place
            $str = $this->getStrFormation($val->getNom(), $val->getTypeDiplome(), $val->getNiveau());
            $str2 = $this->getStrFormation($val->getNom(), $val->getTypeDiplome(), $val->getNiveau(), $val->getAnnee());
            $dataComparaison[$str] = $val->getObjetId();
            $dataCheckDoublons[$str2] = $val->getObjetId();
        }

        $this->tabComparaison = $dataComparaison;
        $this->tabCheckDoublons = $dataCheckDoublons;
        return true;
    }

    //TODO add check doublons
    public function getTabComparaisonLabo()
    {
        $list =  $this->etablissement->getLabo();
        $data = [];

        foreach ($list as $val) {
            //changer getLaboId par getObjetId de qu'il est en place
            $str = $this->getStrFormation($val->getNom());
            $data[$str] = 'L'.$val->getObjetId();
        }

        if (count($list) !== count($data)) {
            throw new \Exception('Doublons dans la liste de labo (BDD)');
        }
        return $data;
    }

    function getObjId($data)
    {
        $strFormation = $this->getStrFormation($data['nom'], $data['typeDiplome'], $data['niveau']);
        if ($this->initTabComparaison() && array_key_exists($strFormation, $this->tabComparaison)) {
            return $this->tabComparaison[$strFormation];
        }
        return false;
    }
}