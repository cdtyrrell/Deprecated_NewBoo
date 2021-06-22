<?php
include_once('../config/symbini.php');
include_once($SERVER_ROOT.'/content/lang/taxa/index.'.$LANG_TAG.'.php');
include_once($SERVER_ROOT.'/classes/TaxonProfileManager.php');
Header("Content-Type: text/html; charset=".$CHARSET);

$taxonValue = array_key_exists("taxon",$_REQUEST)?$_REQUEST["taxon"]:"";
$taxAuthId = array_key_exists("taxauthid",$_REQUEST)?$_REQUEST["taxauthid"]:1;
$clValue = array_key_exists("cl",$_REQUEST)?$_REQUEST["cl"]:0;
$projValue = array_key_exists("proj",$_REQUEST)?$_REQUEST["proj"]:0;
$lang = array_key_exists("lang",$_REQUEST)?$_REQUEST["lang"]:$DEFAULT_LANG;
$descrDisplayLevel = array_key_exists("displaylevel",$_REQUEST)?$_REQUEST["displaylevel"]:"";

//if(!$projValue && !$clValue) $projValue = $defaultProjId;

$taxonManager = new TaxonProfileManager();
if($taxAuthId || $taxAuthId === "0") $taxonManager->setTaxAuthId($taxAuthId);
if($clValue) $taxonManager->setClName($clValue);
if($projValue) $taxonManager->setProj($projValue);
if($lang) $taxonManager->setLanguage($lang);
if($taxonValue) {
	$taxonManager->setTaxon($taxonValue);
	$taxonManager->setAttributes();
}
$ambiguous = $taxonManager->getAmbSyn();
$acceptedName = $taxonManager->getAcceptance();
$synonymArr = $taxonManager->getSynonymArr();
$spDisplay = $taxonManager->getDisplayName();
$taxonRank = $taxonManager->getRankId();
$links = $taxonManager->getTaxaLinks();
$vernStr = $taxonManager->getVernacularStr();
$synStr = $taxonManager->getSynonymStr();
if($links){
	foreach($links as $linkKey => $linkUrl){
		if($linkUrl['title'] == 'REDIRECT'){
			$locUrl = str_replace('--SCINAME--',rawurlencode($taxonManager->getSciName()),$linkUrl['url']);
			header('Location: '.$locUrl);
			exit;
		}
	}
}

$styleClass = '';
if($taxonRank > 180) $styleClass = 'species';
elseif($taxonRank == 180) $styleClass = 'genus';
else $styleClass = 'higher';

$displayLocality = 0;
$isEditor = false;
if($SYMB_UID){
	if($IS_ADMIN || array_key_exists("TaxonProfile",$USER_RIGHTS)){
		$isEditor = true;
	}
	if($IS_ADMIN || array_key_exists("CollAdmin",$USER_RIGHTS) || array_key_exists("RareSppAdmin",$USER_RIGHTS) || array_key_exists("RareSppReadAll",$userRights)){
		$displayLocality = 1;
	}
}
if($taxonManager->getSecurityStatus() == 0){
	$displayLocality = 1;
}
$taxonManager->setDisplayLocality($displayLocality);
$descr = Array();

if(file_exists('includes/config/taxaProfileTemplateCustom.php')){
    include('includes/config/taxaProfileTemplateCustom.php');
}
else{
    include('includes/config/taxaProfileTemplateDefault.php');
}
?>

<html>
<head>
	<title><?php echo $DEFAULT_TITLE." - ".$spDisplay; ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $CHARSET; ?>"/>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link href="../css/speciesprofilebase.css?ver=<?php echo $CSS_VERSION; ?>" type="text/css" rel="stylesheet" />
	<link href="../css/speciesprofile.css<?php echo (isset($CSS_VERSION_LOCAL)?'?ver='.$CSS_VERSION_LOCAL:''); ?>" type="text/css" rel="stylesheet" />

	<link rel="stylesheet" href="../css/w3.css">
	<link rel="stylesheet" href="../css/w3-theme-light-green.css">
	<link rel='stylesheet' href='https://fonts.googleapis.com/css?family=Open+Sans'>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

	<link href="../css/jquery-ui.css" type="text/css" rel="Stylesheet" />
	<script type="text/javascript" src="../js/jquery.js"></script>
	<script type="text/javascript" src="../js/jquery-ui.js"></script>
	<style>
	html, body, h1, h2, h3, h4, h5 {font-family: "Open Sans", sans-serif}
	</style>
	<script type="text/javascript">
		<?php include_once($SERVER_ROOT.'/config/googleanalytics.php'); ?>
	</script>
	<script type="text/javascript">
		var currentLevel = <?php echo ($descrDisplayLevel?$descrDisplayLevel:"1"); ?>;
		var levelArr = new Array(<?php echo ($descr?"'".implode("','",array_keys($descr))."'":""); ?>);
		var tid = <?php echo $taxonManager->getTid(); ?>
	</script>
	<script src="../js/symb/taxa.index.js?ver=20170310" type="text/javascript"></script>
	<script src="../js/symb/taxa.editor.js?ver=20140619" type="text/javascript"></script>
    <?php
    if(isset($CSSARR)){
        foreach($CSSARR as $cssVal){
            echo '<link href="includes/config/'.$cssVal.'?ver=150106" type="text/css" rel="stylesheet" id="editorCssLink" />';
        }
    }
    if(isset($JSARR)){
        foreach($JSARR as $jsVal){
            echo '<script src="includes/config/'.$jsVal.'?ver=150106" type="text/javascript"></script>';
        }
    }
    ?>
</head>
<body class="w3-light-grey">
<?php
$displayLeftMenu = false;
include($SERVER_ROOT.'/header.php');
?>
<div id="innertable" class="w3-content w3-container" style="margin-top:80px">
	<div id="toprow" class="w3-row">
		<div class="w3-col m12">
	  	<?php
	      foreach($topRowElements as $e){
	        echo $e;
	      }
	    ?>
	  </div>
	</div>

	<!-- "Middle Row" -->
	<div id="middlerow" class="w3-row">
		<div id="leftcolumn" class="w3-col m3">
	    <?php
	      foreach($leftColumnElements as $e){
	          echo $e;
	      }
	    ?>
		</div>

    <div id="rightcolumn" class="w3-col m9">
            <?php
            foreach($rightColumnElements as $e){
                echo $e;
            }
            ?>
    </div>
	</div>

	<div id="bottomrow" class="w3-row">
		<div class="w3-col m12">
        <?php
        foreach($bottomRowElements as $e){
            echo $e;
        }
        ?>
    </div>
	</div>

  <div id="footerrow" class="w3-row">
		<div class="w3-col m12">
      <?php
      foreach($footerRowElements as $e){
          echo $e;
      }
      ?>
    </div>
	</div>
</div>
<?php
include($SERVER_ROOT.'/footer.php');
?>
</body>
</html>
