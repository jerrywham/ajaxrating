<?php
/**
 * Plugin Ajaxrating
 * 
 * v0.2 04/10/2013
 *
 * @author Cyril MAGUIRE
 **/
class ajaxrating extends plxPlugin {

	public $rating_unitwidth = 30; // the width (in pixels) of each rating unit (star, etc.)
	// if you changed your graphic to be 50 pixels wide, you should change the value above

	public $ip = '';
	public $units = 10;
	public $static = false;
	public $staticvide = false;

	/**
	 * Constructeur de la classe
	 *
	 * @param	default_lang	langue par défaut
	 * @return	stdio
	 * @author	Cyril MAGUIRE
	 **/
	public function __construct($default_lang) {
		parent::__construct($default_lang);
		//set some variables
		$this->ip = md5($_SERVER['REMOTE_ADDR']);
		if (!$this->units) {$this->units = 10;}
		if (!$this->static) {$this->static = FALSE;}

		# Ajouts des hooks
		$this->addHook('rating_bar', 'rating_bar');
		$this->addHook('ThemeEndHead', 'ThemeEndHead');
		$this->addHook('plxMotorPreChauffageBegin', 'plxMotorPreChauffageBegin');
		$this->addHook('plxShowConstruct', 'plxShowConstruct');
	}

	/**
	 * Méthode utilisée à l'activation du plugin
	 * 
	 * @author Cyril MAGUIRE
	 */
	public function onActivate() {
		$plxMotor = plxMotor::getInstance();

    	# Si le dossier de vote n'existe pas, on le crée
    	if ($plxMotor->version == '5.1.6') {
    		if (!is_dir(PLX_ROOT.'data/configuration/plugins')) {
	    		mkdir(PLX_ROOT.'data/configuration/plugins');
	    		chmod(PLX_ROOT.'data/configuration/plugins', 0777);
	    	}
	    	if (!is_dir(PLX_ROOT.'data/configuration/plugins/ajaxrating')) {
	    		mkdir(PLX_ROOT.'data/configuration/plugins/ajaxrating');
	    		chmod(PLX_ROOT.'data/configuration/plugins/ajaxrating', 0777);
	    	}
    	} else {
    		if (!is_dir(PLX_ROOT.PLX_CONFIG_PATH.'plugins/ajaxrating')) {
	    		mkdir(PLX_ROOT.PLX_CONFIG_PATH.'plugins/ajaxrating');
	    		chmod(PLX_ROOT.PLX_CONFIG_PATH.'plugins/ajaxrating', 0777);
	    	}
    	}
    	
    	# On crée également le fichier pour le rendu ajax
    	$this->staticvide = $plxMotor->aConf['racine_themes'].$plxMotor->style.'/static-vide.php';
    	if (!is_file($this->staticvide)) {
    		file_put_contents(PLX_ROOT.$this->staticvide, '<?php $plxShow->staticContent(); ?>');
    	}
    	$this->setParam('staticvide',$this->staticvide,'string');
    	$this->saveParams();
    }

    /**
	 * Méthode de traitement du hook plxShowConstruct qui détermine les url des fichiers statiques pour les votes
	 *
	 * @return	stdio
	 * @author	Cyril MAGUIRE
	 **/
    public function plxShowConstruct() {

    	if (!is_file($this->staticvide)) {
    		plxUtils::write('<?php $plxShow->staticContent(); ?>', PLX_ROOT.$this->getParam('staticvide'));
    	}

		# infos sur la page statique
		$string = "	\$array = array();";
		$string .= "if(\$this->plxMotor->mode=='rating') {";
		$string .= "	\$array[\$this->plxMotor->cible] = array(
			'name'		=> 'rating',
			'menu'		=> '',
			'url'		=> 'plxdb',
			'readable'	=> 1,
			'active'	=> 1,
			'group'		=> ''
		);
		}";
		$string .= "if(\$this->plxMotor->mode=='ajaxrating') {";
		$string .= "	\$array[\$this->plxMotor->cible] = array(
			'name'		=> 'rating',
			'menu'		=> '',
			'url'		=> 'plxrpc',
			'readable'	=> 1,
			'active'	=> 1,
			'group'		=> ''
		);
		}";
		$string .= "	\$this->plxMotor->aStats = array_merge(\$this->plxMotor->aStats, \$array);";
		echo "<?php ".$string." ?>";
    }

    /**
	 * Méthode de traitement du hook plxMotorPreChauffageBegin qui détermine le template des fichiers statiques pour les votes
	 *
	 * @return	stdio
	 * @author	Cyril MAGUIRE
	 **/
    public function plxMotorPreChauffageBegin() {

		$string = "
		if(\$this->get && preg_match('/^plxdb\/?/',\$this->get)) {
			\$this->mode = 'rating';
			\$this->cible = '../../plugins/ajaxrating/static';
			\$this->template = 'static-vide.php';
			return true;
		}if(\$this->get && preg_match('/^plxrpc\/?/',\$this->get)) {
			\$this->mode = 'ajaxrating';
			\$this->cible = '../../plugins/ajaxrating/static';
			\$this->template = 'static-vide.php';
			return true;
		}
		";

		echo "<?php ".$string." ?>";
    }
	
	/**
	 * Méthode qui importe les bibliothèques javascript et la css nécessaires au fonctionnement du système de vote
	 *
	 * @return	stdio
	 * @author	Cyril MAGUIRE
	 **/
	public function ThemeEndHead() {

		echo "\t".'<script type="text/javascript" src="'.PLX_PLUGINS.'ajaxrating/js/behavior.js"></script>'."\n";
		echo "\t".'<script type="text/javascript" src="'.PLX_PLUGINS.'ajaxrating/js/rating.js"></script>'."\n";
		echo "\t".'<link rel="stylesheet" type="text/css" href="'.PLX_PLUGINS.'ajaxrating/css/rating.css" />'."\n";
	}

	/**
	 * Méthode qui récupère les informations contenues dans les fichiers de vote
	 * 
	 * @param filename string nom du fichier à analyser
	 * @return rating array tableau des valeurs des votes
	 * 
	 * @author Cyril MAGUIRE.
	 */
	public function parseBdd($filename) {
		# Mise en place du parseur XML
		$data = implode('',file($filename));
		$parser = xml_parser_create(PLX_CHARSET);
		xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
		xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,0);
		xml_parse_into_struct($parser,$data,$values,$iTags);
		xml_parser_free($parser);
		# Recuperation des valeurs de nos champs XML
		$rating['total_votes'] = plxUtils::getValue($values[$iTags['total_votes'][0]]['value']);
		$rating['total_value'] = plxUtils::getValue($values[$iTags['total_value'][0]]['value']);
		$rating['used_ips'] = plxUtils::getValue($values[$iTags['used_ips'][0]]['value']);

		# On retourne le tableau
		return $rating;
	}

	/**
	 * Méthode qui enregistre ou modifie les fichiers de vote pour chaque appel de hook
	 * 
	 * @param content array contenu des balises du fichier
	 * @param filename string le chemin du fichier à créer/modifier
	 * 
	 * @return void
	 * @author Cyril MAGUIRE.
	 */
	public function editRecordInBdd($content, $filename) {

		# On genere le contenu de notre fichier XML
		$xml = "<?xml version='1.0' encoding='".PLX_CHARSET."'?>\n";
		$xml .= "<insert>\n";
		$xml .= "\t<total_votes>".intval($content['total_votes'])."</total_votes>\n";
		$xml .= "\t<total_value>".intval($content['total_value'])."</total_value>\n";
		$xml .= "\t<used_ips><![CDATA[".plxUtils::cdataCheck($content['used_ips'])."]]></used_ips>\n";
		$xml .= "</insert>\n";
		
		# On ecrit ce contenu dans notre fichier XML
		return plxUtils::write($xml, $filename);
		
	}

	/**
	 * Méthode qui affiche le système de vote
	 * 
	 * @param params array tableau des différents paramètres
	 * 					   $params[0] = index de la balise habritant le système de vote (obligatoire; doit être unique sur la page)	
	 * 					   $params[1] = index de l'article sur lequel se situe le/les système(s) de vote (obligatoire)	
	 * 					   $params[2] = nombre d'étoiles à afficher (optionnel; 10 par défaut)	
	 * 					   $params[3] = variable pour cloturer les votes (optionnel; false par défaut)	
	 * @return string
	 * 
	 * @author Cyril MAGUIRE.
	 */
	public function rating_bar($params) {
	  	$plxMotor = plxMotor::getInstance();
	  	$id = $params[0];
	  	$artId = $params[1];
	  	$units = (isset($params[2]) ? $params['2'] : $this->units);
	  	$static = (isset($params[3]) ? $params['3'] : $this->static);

	  	# Chargement du fichier de données
	  	if ($plxMotor->version == '5.1.6') {
	  		$filename = PLX_ROOT.'data/configuration/plugins/ajaxrating/'.$id.'.'.$plxMotor->plxGlob_arts->aFiles[$artId];
	  	} else {
	  		$filename = PLX_ROOT.PLX_CONFIG_PATH.'plugins/ajaxrating/'.$id.'.'.$plxMotor->plxGlob_arts->aFiles[$artId];
	  	}
	  	
		# Récupération des données de la barre de vote utilisée
		if (is_file($filename))
			$query=$this->parseBdd($filename);
		else
			$query=array();
		# Insertion d'un enregistrement s'il n'y en a pas pour la barre de vote utilisée
		if (count($query) == 0) {
			$query = array(
				'total_votes'=>0,
				'total_value'=>0,
				'used_ips'=>''
			);
			$this->editRecordInBdd($query,$filename);
		}

		# Récurpération du nombre total de votes
		if ($query['total_votes'] < 1) {
			$count = 0;
		} else {
			$count=$query['total_votes'];
		}

		$current_rating=(double)$query['total_value']; //total number of rating added together and stored
		$tense=($count>1) ? $this->getLang('L_VOTES') : $this->getLang('L_VOTE'); //plural form votes/vote
		$cast=($count>1) ? $this->getLang('L_CAST_PLURAL') : $this->getLang('L_CAST'); //plural form cast/cast

		# On construit la barre de vote en fonction de l'historique de vote de l'utilisateur
		$voted = (empty($query['used_ips'])  ? false : true);
		if ($voted) {
			# Le tableau d'ips est serialisé lors de l'enregistrement (voir pages statiques)
			$ips = unserialize($query['used_ips']);
			$voted = (in_array($this->ip,$ips) ? true : false);
		}

		# Construction de la barre de vote
		# Calcul du nombre d'étoiles à "allumer" (largeur de la balise current-rating)
		$rating_width = ($count>0) ? number_format($current_rating/$count,2)*$this->rating_unitwidth : number_format($current_rating,2)*$this->rating_unitwidth;
		# Moyenne des votes, avec 1 ou 2 décimales
		$rating1 = ($count>0) ? number_format($current_rating/$count,1) : number_format($current_rating,1);
		$rating2 = ($count>0) ? number_format($current_rating/$count,2) : number_format($current_rating,2);

		# On adapte l'url en fonction de la prise en compte ou non de la réécriture d'url
		$racine = (($plxMotor->aConf['urlrewriting'] == 1) ? $plxMotor->racine : $plxMotor->racine.'index.php?');

	if ($static == 'static') {

			$static_rater = array();
			$static_rater[] .= "\n".'<div class="ratingblock">';
			$static_rater[] .= '<div id="unit_long'.$id.'">';
			$static_rater[] .= '<ul id="unit_ul'.$id.'" class="unit-rating" style="width:'.$this->rating_unitwidth*$units.'px;">';
			$static_rater[] .= '<li class="current-rating" style="width:'.$rating_width.'px;">'.$this->getLang('L_CURRENTLY').' '.$rating2.'/'.$units.'</li>';
			$static_rater[] .= '</ul>';
			$static_rater[] .= '<p class="static"><span id="root" style="display:none;">'.$plxMotor->racine.'</span>&nbsp;'.$this->getLang('L_RATING').'&nbsp;: <strong> '.$rating1.'</strong>/'.$units.' ('.$count.' '.$tense.'&nbsp;'.$cast.') <em>'.$this->getLang('L_STATIC').'</em></p>';
			$static_rater[] .= '</div>';
			$static_rater[] .= '</div>'."\n\n";

			echo implode("\n", $static_rater);


	} else {

	      $rater ='';
	      $rater.='<div class="ratingblock">';

	      $rater.='<div id="unit_long'.$id.'">';
	      $rater.='  <ul id="unit_ul'.$id.'" class="unit-rating" style="width:'.$this->rating_unitwidth*$units.'px;">';
	      $rater.='     <li class="current-rating" style="width:'.$rating_width.'px;"><span style="display:none;">'.$this->getLang('L_ARTICLE').'&nbsp;</span><span id="art'.$id.'" style="display:none;">'.$artId.'</span>'.$this->getLang('L_CURRENTLY').'&nbsp;'.$rating2.'/'.$units.'</li>';

	      for ($ncount = 1; $ncount <= $units; $ncount++) { // loop from 1 to the number of units
	           if(!$voted) { // if the user hasn't yet voted, draw the voting stars
	              $rater.='<li><a href="'.$racine.'plxdb.php&amp;j='.$ncount.'&amp;q='.$id.'&amp;t='.$this->ip.'&amp;c='.$units.'&amp;a='.$artId.'" title="'.$ncount.' '.$this->getLang('L_OUT_OF').' '.$units.'" class="r'.$ncount.'-unit rater" rel="nofollow">'.$ncount.'</a></li>';
	           }
	      }
	      $ncount=0; // resets the count

	      $rater.='  </ul>';
	      $rater.='  <p';
	      if($voted){ $rater.=' class="voted"'; }
	      $rater.='><span id="root" style="display:none;">'.$racine.'</span>&nbsp;'.$this->getLang('L_RATING').'&nbsp;: <strong> '.$rating1.'</strong>/'.$units.' ('.$count.' '.$tense.'&nbsp;'.$cast.')';
	      $rater.='  </p>';
	      $rater.='</div>';
	      $rater.='</div>';
	      echo $rater;
		}
	}
}
?>
