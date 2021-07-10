<?php
include_once('../config/symbini.php');
include_once($SERVER_ROOT.'/classes/ChecklistManager.php');
include_once($SERVER_ROOT.'/classes/ChecklistAdmin.php');
if($CHECKLIST_FG_EXPORT) include_once($SERVER_ROOT.'/classes/ChecklistFGExportManager.php');
include_once($SERVER_ROOT.'/content/lang/checklists/checklist.'.$LANG_TAG.'.php');
header("Content-Type: text/html; charset=".$CHARSET);

$action = array_key_exists("submitaction",$_REQUEST)?$_REQUEST["submitaction"]:"";
$clValue = array_key_exists("cl",$_REQUEST)?$_REQUEST["cl"]:0;
$dynClid = array_key_exists("dynclid",$_REQUEST)?$_REQUEST["dynclid"]:0;
$pageNumber = array_key_exists("pagenumber",$_REQUEST)?$_REQUEST["pagenumber"]:1;
$pid = array_key_exists("pid",$_REQUEST)?$_REQUEST["pid"]:"";
$thesFilter = array_key_exists("thesfilter",$_REQUEST)?$_REQUEST["thesfilter"]:0;
$taxonFilter = array_key_exists("taxonfilter",$_REQUEST)?$_REQUEST["taxonfilter"]:"";
$showAuthors = array_key_exists("showauthors",$_REQUEST)?$_REQUEST["showauthors"]:0;
$showCommon = array_key_exists("showcommon",$_REQUEST)?$_REQUEST["showcommon"]:0;
$showImages = array_key_exists("showimages",$_REQUEST)?$_REQUEST["showimages"]:0;
$showVouchers = array_key_exists("showvouchers",$_REQUEST)?$_REQUEST["showvouchers"]:0;
$showAlphaTaxa = array_key_exists("showalphataxa",$_REQUEST)?$_REQUEST["showalphataxa"]:0;
$searchCommon = array_key_exists("searchcommon",$_REQUEST)?$_REQUEST["searchcommon"]:0;
$searchSynonyms = array_key_exists("searchsynonyms",$_REQUEST)?$_REQUEST["searchsynonyms"]:0;
$defaultOverride = array_key_exists("defaultoverride",$_REQUEST)?$_REQUEST["defaultoverride"]:0;
$editMode = array_key_exists("emode",$_REQUEST)?$_REQUEST["emode"]:0;
$printMode = array_key_exists("printmode",$_REQUEST)?$_REQUEST["printmode"]:0;
$exportDoc = array_key_exists("exportdoc",$_REQUEST)?$_REQUEST["exportdoc"]:0;

$statusStr='';
$locStr = '';

//Search Synonyms is default
if($action != "Rebuild List" && !array_key_exists('dllist_x',$_POST)) $searchSynonyms = 1;
if($action == "Rebuild List") $defaultOverride = 1;

$clManager = new ChecklistManager();
if($clValue){
	$statusStr = $clManager->setClValue($clValue);
}
elseif($dynClid){
	$clManager->setDynClid($dynClid);
}
$clArray = Array();
if($clValue || $dynClid){
	$clArray = $clManager->getClMetaData();
}
$activateKey = $KEY_MOD_IS_ACTIVE;
$showDetails = 0;
if($clValue && $clArray["defaultSettings"]){
	$defaultArr = json_decode($clArray["defaultSettings"], true);
	$showDetails = $defaultArr["ddetails"];
	if(!$defaultOverride){
		if(array_key_exists('dcommon',$defaultArr)){$showCommon = $defaultArr["dcommon"];}
		if(array_key_exists('dimages',$defaultArr)){$showImages = $defaultArr["dimages"];}
		if(array_key_exists('dvouchers',$defaultArr)){$showVouchers = $defaultArr["dvouchers"];}
		if(array_key_exists('dauthors',$defaultArr)){$showAuthors = $defaultArr["dauthors"];}
		if(array_key_exists('dalpha',$defaultArr)){$showAlphaTaxa = $defaultArr["dalpha"];}
	}
	if(isset($defaultArr['activatekey'])) $activateKey = $defaultArr['activatekey'];
}
if($pid) $clManager->setProj($pid);
elseif(array_key_exists("proj",$_REQUEST)) $pid = $clManager->setProj($_REQUEST['proj']);
if($thesFilter) $clManager->setThesFilter($thesFilter);
if($taxonFilter) $clManager->setTaxonFilter($taxonFilter);
$clManager->setLanguage($LANG_TAG);
if($searchCommon){
	$showCommon = 1;
	$clManager->setSearchCommon();
}
if($searchSynonyms) $clManager->setSearchSynonyms();
if($showAuthors) $clManager->setShowAuthors();
if($showCommon) $clManager->setShowCommon();
if($showImages) $clManager->setShowImages();
if($showVouchers) $clManager->setShowVouchers();
if($showAlphaTaxa) $clManager->setShowAlphaTaxa();
$clid = $clManager->getClid();
$pid = $clManager->getPid();

if(array_key_exists('dllist_x',$_POST)){
	$clManager->downloadChecklistCsv();
	exit();
}
elseif(array_key_exists('printlist_x',$_POST)){
	$printMode = 1;
}

$isEditor = false;
if($IS_ADMIN || (array_key_exists("ClAdmin",$USER_RIGHTS) && in_array($clid,$USER_RIGHTS["ClAdmin"]))){
	$isEditor = true;

	//Add species to checklist
	if(array_key_exists("tidtoadd",$_POST)){
		$dataArr = array();
		$dataArr["tid"] = $_POST["tidtoadd"];
		if($_POST["familyoverride"]) $dataArr["familyoverride"] = $_POST["familyoverride"];
		if($_POST["habitat"]) $dataArr["habitat"] = $_POST["habitat"];
		if($_POST["abundance"]) $dataArr["abundance"] = $_POST["abundance"];
		if($_POST["notes"]) $dataArr["notes"] = $_POST["notes"];
		if($_POST["source"]) $dataArr["source"] = $_POST["source"];
		if($_POST["internalnotes"]) $dataArr["internalnotes"] = $_POST["internalnotes"];
		$setRareSpp = false;
		if($_POST["cltype"] == 'rarespp') $setRareSpp = true;
		$clAdmin = new ChecklistAdmin();
		$clAdmin->setClid($clid);
		$statusStr = $clAdmin->addNewSpecies($dataArr,$setRareSpp);
	}
}
$taxaArray = Array();
if($clValue || $dynClid){
	$taxaArray = $clManager->getTaxaList($pageNumber,($printMode?0:500));
    if($CHECKLIST_FG_EXPORT){
        $fgManager = new ChecklistFGExportManager();
        if($clValue){
            $fgManager->setClValue($clValue);
        }
        elseif($dynClid){
            $fgManager->setDynClid($dynClid);
        }
        $fgManager->setSqlVars();
        $fgManager->setLanguage($LANG_TAG);
        $fgManager->primeDataArr();
    }
}
if($clArray["locality"]){
    $locStr = $clArray["locality"];
    if($clValue && $clArray["latcentroid"]) $locStr .= " (".$clArray["latcentroid"].", ".$clArray["longcentroid"].")";
}
?>
<html>
<head>
	<meta charset="<?php echo $CHARSET; ?>">
	<title><?php echo $DEFAULT_TITLE; ?><?php echo $LANG['RESCHECK'];?><?php echo $clManager->getClName(); ?></title>
    <link type="text/css" href="../css/bootstrap.css" rel="stylesheet" />
		<?php include($SERVER_ROOT . '/metalinks.php'); ?>

	<link href="<?php echo $CLIENT_ROOT; ?>/css/jquery-ui.css" type="text/css" rel="stylesheet" />
    <script src="<?php echo $CLIENT_ROOT; ?>/js/jquery.js" type="text/javascript"></script>
    <script src="<?php echo $CLIENT_ROOT; ?>/js/jquery-ui.js" type="text/javascript"></script>
    <script src="<?php echo $CLIENT_ROOT; ?>/js/jquery.popupoverlay.js" type="text/javascript"></script>
    <script type="text/javascript">
		<?php include_once($SERVER_ROOT.'/config/googleanalytics.php'); ?>
	</script>
	<script type="text/javascript">
        <?php if($clid) echo 'var clid = '.$clid.';'; ?>

        <?php if($clManager->getClName()) echo 'var checklistName = "'.$clManager->getClName().'";'; ?>

        var checklistName = "<?php echo $clManager->getClName(); ?>";
        var checklistAuthors = "<?php echo $clArray["authors"]; ?>";
        var checklistCitation = "<?php echo $clArray["publication"]; ?>";
        var checklistLocality = "<?php echo $locStr; ?>";
        var checklistAbstract = "<?php echo $clArray["abstract"]; ?>";
        var checklistNotes = "<?php echo $clArray["notes"]; ?>";
        var fieldguideDisclaimer = "This field guide was produced through the <?php echo $DEFAULT_TITLE; ?> portal. This field guide is intended for educational use only, no commercial uses are allowed. It is created under Fair Use copyright provisions supporting educational uses of information. All rights are reserved to authors and photographers unless otherwise specified.";

        function lazyLoadData(index,callback){
            var startindex = 0;
            if(index > 0) startindex = (index*lazyLoadCnt) + 1;
            var http = new XMLHttpRequest();
            var url = "rpc/fieldguideexporter.php";
            var params = 'rows='+lazyLoadCnt+'&photogArr='+JSON.stringify(photog)+'&photoNum='+photoNum+'&start='+startindex+'&cl=<?php echo $clValue."&pid=".$pid."&dynclid=".$dynClid."&thesfilter=".($thesFilter?$thesFilter:1); ?>';
            //console.log(url+'?'+params);
            http.open("POST", url, true);
            http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            http.onreadystatechange = function() {
                if(http.readyState == 4 && http.status == 200) {
                    callback(http.responseText);
                }
            };
            http.send(params);
        }
	</script>
	<script type="text/javascript" src="../js/symb/checklists.checklist.js?ver=201805"></script>
    <?php
    if($CHECKLIST_FG_EXPORT){
        ?>
        <script src="<?php echo $CLIENT_ROOT; ?>/js/pdfmake.min.js" type="text/javascript"></script>
        <script src="<?php echo $CLIENT_ROOT; ?>/js/vfs_fonts.js" type="text/javascript"></script>
        <script src="<?php echo $CLIENT_ROOT; ?>/js/jszip.min.js" type="text/javascript"></script>
        <script src="<?php echo $CLIENT_ROOT; ?>/js/FileSaver.min.js" type="text/javascript"></script>
        <script src="<?php echo $CLIENT_ROOT; ?>/js/symb/checklists.fieldguideexport.js?ver=59" type="text/javascript"></script>
        <?php
    }
    ?>
	<style type="text/css">
		#sddm{margin:0;padding:0;z-index:30;}
		#sddm:hover {background-color:#EAEBD8;}
		#sddm img{padding:3px;}
		#sddm:hover img{background-color:#EAEBD8;}
		#sddm li{margin:0px;padding: 0;list-style: none;float: left;font: bold 11px arial}
		#sddm li a{display: block;margin: 0 1px 0 0;padding: 4px 10px;width: 60px;background: #5970B2;color: #FFF;text-align: center;text-decoration: none}
		#sddm li a:hover{background: #49A3FF}
		#sddm div{position: absolute;visibility:hidden;margin:0;padding:0;background:#EAEBD8;border:1px solid #5970B2}
		#sddm div a	{position: relative;display:block;margin:0;padding:5px 10px;width:auto;white-space:nowrap;text-align:left;text-decoration:none;background:#EAEBD8;color:#2875DE;font-weight:bold;}
		#sddm div a:hover{background:#49A3FF;color:#FFF}

        a.boxclose{
            float:right;
            width:36px;
            height:36px;
            background:transparent url(../images/spatial_close_icon.png) repeat top left;
            margin-top:-35px;
            margin-right:-35px;
            cursor:pointer;
        }

        #loader {
            position: absolute;
            left: 50%;
            top: 50%;
            z-index: 1;
            width: 150px;
            height: 150px;
            margin: -75px 0 0 -75px;
            border: 16px solid #f3f3f3;
            border-radius: 50%;
            border-top: 16px solid #3498db;
            width: 120px;
            height: 120px;
            -webkit-animation: spin 2s linear infinite;
            animation: spin 2s linear infinite;
        }

        #loaderMessage {
            position: absolute;
            top: 65%;
            z-index: 1;
            font-size: 25px;
            font-weight: bold;
            text-align: center;
            width: 100%;
            color: #f3f3f3;
        }

        @-webkit-keyframes spin {
            0% { -webkit-transform: rotate(0deg); }
            100% { -webkit-transform: rotate(360deg); }
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
	</style>
</head>
<body <?php echo ($printMode?'style="background-color:#ffffff;"':'class="w3-theme-l5"'); ?>>
	<?php include($SERVER_ROOT.'/header.php'); ?>
	<div class="w3-container w3-content" style="max-width:1400px;margin-top:80px">
	  <!-- The Grid -->
	  <div class="w3-row">
			<div class="w3-col m9">
				<div class="w3-card w3-round w3-white w3-container">
					<!-- Checklist Name -->
					<a href="checklist.php?cl=<?php echo $clValue."&proj=".$pid."&dynclid=".$dynClid; ?>">
						<h2><?php echo $clManager->getClName(); ?></h2>
					</a>
					<!-- Checklist Author(s) -->
						<b><?php echo $LANG['AUTHORS'];?></b>
						<?php echo $clArray["authors"]; ?>
					<!-- Checklist Pub(s) -->
					<?php
						if($clArray["publication"]){
							$pubStr = $clArray["publication"];
							if(substr($pubStr,0,4)=='http' && !strpos($pubStr,' ')) $pubStr = '<a href="'.$pubStr.'" target="_blank">'.$pubStr."</a>";
							echo "<div><span style='font-weight:bold;'>".(isset($LANG['CITATION'])?$LANG['CITATION']:'Citation').":</span> ".$pubStr."</div>";
						} ?>
					<hr />
					<!-- Stats -->
					<p>
						<b><?php echo $LANG['FAMILIES'];?></b>
						<?php echo $clManager->getFamilyCount(); ?>
					</p>
					<p>
						<b><?php echo $LANG['GENERA'];?></b>
						<?php echo $clManager->getGenusCount(); ?>
					</p>
					<p>
						<b><?php echo $LANG['SPECIES'];?></b>
						<?php echo $clManager->getSpeciesCount(); ?>
						<?php echo $LANG['SPECRANK'];?>
					</p>
					<p>
						<b><?php echo $LANG['TOTTAX'];?></b>
						<?php echo $clManager->getTaxaCount(); ?>
												<?php echo $LANG['INCLUDSUB'];?>
					</p><hr />

					<!-- Checklist -->
					<?php
					$prevfam = '';
					if($showImages){
						echo '<div style="clear:both;">&nbsp;</div>';
						foreach($taxaArray as $tid => $sppArr){
							$family = $sppArr['family'];
							$tu = (array_key_exists('tnurl',$sppArr)?$sppArr['tnurl']:'');
							$u = (array_key_exists('url',$sppArr)?$sppArr['url']:'');
							$imgSrc = ($tu?$tu:$u);
							?>
							<div class="tndiv">
								<div class="tnimg" style="<?php echo ($imgSrc?"":"border:1px solid black;"); ?>">
									<?php
									$spUrl = "../taxa/index.php?taxauthid=1&taxon=$tid&cl=".$clid;
									if($imgSrc){
										$imgSrc = (array_key_exists("imageDomain",$GLOBALS)&&substr($imgSrc,0,4)!="http"?$GLOBALS["imageDomain"]:""). $imgSrc;
										if(!$printMode) echo "<a href='".$spUrl."' target='_blank'>";
										echo "<img src='".$imgSrc."' />";
										if(!$printMode) echo "</a>";
									} else {
										?>
										<div style="margin-top:50px;">
											<b><?php echo $LANG['IMAGE'];?><br/><?php echo $LANG['NOTY'];?><br/><?php echo $LANG['AVAIL'];?></b>
										</div>
										<?php
									}
									?>
								</div>
								<div>
									<?php
									if(!$printMode) echo '<a href="'.$spUrl.'" target="_blank">';
									echo '<b>'.$sppArr['sciname'].'</b>';
									if(!$printMode) echo '</a>';
									if(array_key_exists('vern',$sppArr)){
										echo "<div style='font-weight:bold;'>".$sppArr["vern"]."</div>";
									}
									if(!$showAlphaTaxa){
										if($family != $prevfam){
											?>
											<div class="familydiv" id="<?php echo $family; ?>">
												[<?php echo $family; ?>]
											</div>
											<?php
											$prevfam = $family;
										}
									}
								}
							} else{
	              $voucherArr = $clManager->getVoucherArr();
	              foreach($taxaArray as $tid => $sppArr){
	                if(!$showAlphaTaxa){
	                  $family = $sppArr['family'];
	                  if($family != $prevfam){
	                    $famUrl = "../taxa/index.php?taxauthid=1&taxon=$family&cl=".$clid;
	                    ?>
	                    <div class="familydiv" id="<?php echo $family;?>" style="margin:15px 0px 5px 0px;font-weight:bold;font-size:120%;">
	                      <a href="<?php echo $famUrl; ?>" target="_blank" style="color:black;"><?php echo $family;?></a>
	                    </div>
	                    <?php
	                    $prevfam = $family;
	                  }
	                }
	                $spUrl = "../taxa/index.php?taxauthid=1&taxon=$tid&cl=".$clid;
	                echo "<div id='tid-$tid' style='margin:0px 0px 3px 10px;'>";
	                echo '<div style="clear:left">';
	                if(!preg_match('/\ssp\d/',$sppArr["sciname"]) && !$printMode) echo "<a href='".$spUrl."' target='_blank'>";
	                echo "<b><i>".$sppArr["sciname"]."</b></i> ";
	                if(array_key_exists("author",$sppArr)) echo $sppArr["author"];
	                if(!preg_match('/\ssp\d/',$sppArr["sciname"]) && !$printMode) echo "</a>";
	                if(array_key_exists('vern',$sppArr)){
	                  echo " - <span style='font-weight:bold;'>".$sppArr["vern"]."</span>";
	                }
	                if($isEditor){
	                  //Delete species or edit details specific to this taxon (vouchers, notes, habitat, abundance, etc
	                  ?>
	                  <span class="editspp" style="display:<?php echo ($editMode?'inline':'none'); ?>;">
	                    <a href="#" onclick="return openPopup('clsppeditor.php?tid=<?php echo $tid."&clid=".$clid; ?>','editorwindow');">
	                      <img src='../images/edit.png' style='width:13px;' title='edit details' />
	                    </a>
	                  </span>
	                  <?php
	                  if($showVouchers && array_key_exists("dynamicsql",$clArray) && $clArray["dynamicsql"]){
	                    ?>
	                    <span class="editspp" style="display:none;">
	                      <a href="#" onclick="return openPopup('../collections/list.php?db=all&thes=1&reset=1&taxa=<?php echo $tid."&targetclid=".$clid."&targettid=".$tid;?>','editorwindow');">
	                        <img src='../images/link.png' style='width:13px;' title='Link Voucher Specimens' />
	                      </a>
	                    </span>
	                    <?php
	                  }
	                }
	                echo "</div>\n";
	                if($showVouchers){
	                  $voucStr = '';
	                  if(array_key_exists($tid,$voucherArr)){
	                    $voucCnt = 0;
	                    foreach($voucherArr[$tid] as $occid => $collName){
	                      $voucStr .= ', ';
	                      if($voucCnt == 4 && !$printMode){
	                        $voucStr .= '<a href="#" id="morevouch-'.$tid.'" onclick="return toggleVoucherDiv('.$tid.');">'.$LANG['MORE'].'</a>'.
	                          '<span id="voucdiv-'.$tid.'" style="display:none;">';
	                      }
	                      if(!$printMode) $voucStr .= '<a href="#" onclick="return openIndividualPopup('.$occid.')">';
	                      $voucStr .= $collName;
	                      if(!$printMode) $voucStr .= "</a>\n";
	                      $voucCnt++;
	                    }
	                    if($voucCnt > 4 && !$printMode) $voucStr .= '</span><a href="#" id="lessvouch-'.$tid.'" style="display:none;" onclick="return toggleVoucherDiv('.$tid.');">'.$LANG['LESS'].'</a>';
	                    $voucStr = substr($voucStr,2);
	                  }
	                  $noteStr = '';
	                  if(array_key_exists('notes',$sppArr)){
	                    $noteStr = $sppArr['notes'];
	                  }
	                  if($noteStr || $voucStr){
	                    echo "<div style='margin-left:15px;'>".$noteStr.($noteStr && $voucStr?'; ':'').$voucStr."</div>";
	                  }
	                }
	                echo "</div>\n";
	              }
	            }
							$taxaLimit = ($showImages?$clManager->getImageLimit():$clManager->getTaxaLimit());
							$pageCount = ceil($clManager->getTaxaCount()/$taxaLimit);
							$argStr = "";
							if($pageCount > 1 && !$printMode){
								if(($pageNumber)>$pageCount) $pageNumber = 1;
								$argStr .= "&cl=". $clValue ."&dynclid=". $dynClid .($showCommon?"&showcommon=".$showCommon:""). ($showVouchers?"&showvouchers=".$showVouchers:"");
								$argStr .= ($showAuthors?"&showauthors=".$showAuthors:""). ($clManager->getThesFilter()?"&thesfilter=". $clManager->getThesFilter():"");
								$argStr .= ($pid?"&pid=".$pid:""). ($showImages?"&showimages=".$showImages:""). ($taxonFilter?"&taxonfilter=".$taxonFilter:"");
								$argStr .= ($searchCommon?"&searchcommon=".$searchCommon:""). ($searchSynonyms?"&searchsynonyms=".$searchSynonyms:"");
								$argStr .= ($showAlphaTaxa?"&showalphataxa=".$showAlphaTaxa:"");
								$argStr .= ($defaultOverride?"&defaultoverride=".$defaultOverride:"");
								echo "<hr /><div>". $LANG['PAGE'] ."<b>". ($pageNumber) ."</b>". $LANG['OF'] ."<b>$pageCount</b>: ";
								for($x=1;$x<=$pageCount;$x++){
									if($x>1) echo " | ";
									if(($pageNumber) == $x){
										echo "<b>";
									} else {
										echo "<a href='checklist.php?pagenumber=".$x.$argStr."'>";
									}
									echo ($x);
									if(($pageNumber) == $x){
										echo "</b>";
									} else {
										echo "</a>";
									}
								}
								echo "</div><hr />";
							} ?>
				</div> <!-- ends card -->
			</div> <!-- ends left col -->

			<!-- Start Right Col -->
			<div class="w3-col m3">
				<div class="w3-card w3-round w3-white w3-container w3-margin">
					<!-- Options Box -->
					<div id="cloptiondiv">
						<form name="optionform" action="checklist.php" method="post">
							<fieldset style="background-color:white;padding-bottom:10px;">
									<legend><b><?php echo $LANG['OPTIONS'];?></b></legend>
								<!-- Taxon Filter option -->
									<div id="taxonfilterdiv" title="Filter species list by family or genus">
										<div>
											<b><?php echo $LANG['SEARCH'];?></b>
										<input type="text" id="taxonfilter" name="taxonfilter" value="<?php echo $taxonFilter;?>" size="20" />
									</div>
									<div>
										<div style="margin-left:10px;">
											<?php
												if($displayCommonNames){
													echo "<input data-role='none' type='checkbox' name='searchcommon' value='1'".($searchCommon?"checked":"")."/>".$LANG['COMMON']."<br/>";
												}
											?>
											<input data-role='none' type="checkbox" name="searchsynonyms" value="1"<?php echo ($searchSynonyms?"checked":"");?>/><?php echo $LANG['SYNON'];?>
																				</div>
									</div>
								</div>
									<!-- Thesaurus Filter -->
									<div>
										<b><?php echo $LANG['FILTER'];?></b><br/>
										<select data-role='none' name='thesfilter' id='thesfilter'>
										<option value='0'><?php echo $LANG['OGCHECK'];?></option>
										<?php
											$taxonAuthList = Array();
											$taxonAuthList = $clManager->getTaxonAuthorityList();
											foreach($taxonAuthList as $taCode => $taValue){
												echo "<option value='".$taCode."'".($taCode == $clManager->getThesFilter()?" selected":"").">".$taValue."</option>\n";
											}
										?>
									</select>
								</div>
								<div>
									<?php
										//Display Common Names: 0 = false, 1 = true
											if($displayCommonNames) echo "<input data-role='none' id='showcommon' name='showcommon' type='checkbox' value='1' ".($showCommon?"checked":"")."/>".$LANG['COMMON']."";
									?>
								</div>
								<div>
									<!-- Display as Images: 0 = false, 1 = true  -->
										<!-- <input data-role='none' name='showimages' type='checkbox' value='1' <?php #echo ($showImages?"checked":""); ?> onclick="showImagesChecked(this.form);" />
																		<?php #echo $LANG['DISPLAYIMG'];?> -->
								</div>
								<?php if($clValue){ ?>
									<div style='display:<?php echo ($showImages?"none":"block");?>' id="showvouchersdiv">
										<!-- Display as Vouchers: 0 = false, 1 = true  -->
											<input data-role='none' name='showvouchers' type='checkbox' value='1' <?php echo ($showVouchers?"checked":""); ?>/>
																				<?php echo $LANG['NOTESVOUC'];?>
									</div>
								<?php } ?>
								<div style='display:<?php echo ($showImages?"none":"block");?>' id="showauthorsdiv">
									<!-- Display Taxon Authors: 0 = false, 1 = true  -->
										<input data-role='none' name='showauthors' type='checkbox' value='1' <?php echo ($showAuthors?"checked":""); ?>/>
																		<?php echo $LANG['TAXONAUT'];?>
								</div>
								<div style='' id="showalphataxadiv">
									<!-- Display Taxa Alphabetically: 0 = false, 1 = true  -->
										<input data-role='none' name='showalphataxa' type='checkbox' value='1' <?php echo ($showAlphaTaxa?"checked":""); ?>/>
																		<?php echo $LANG['TAXONABC'];?>
								</div>
								<div style="margin:5px 0px 0px 5px;">
									<input type='hidden' name='cl' value='<?php echo $clid; ?>' />
									<input type='hidden' name='dynclid' value='<?php echo $dynClid; ?>' />
									<input type="hidden" name="proj" value="<?php echo $pid; ?>" />
									<input type='hidden' name='defaultoverride' value='1' />
									<?php if(!$taxonFilter) echo "<input type='hidden' name='pagenumber' value='".$pageNumber."' />"; ?>
									<button type="submit" name="submitaction" class="w3-button w3-theme-d2 w3-margin-bottom" onclick="changeOptionFormAction('checklist.php?cl=<?php echo $clValue."&proj=".$pid."&dynclid=".$dynClid; ?>','_self');">Rebuild List</button>

									<div class="button" style='float:right;margin-right:10px;width:16px;height:16px;padding:2px;' title="Download Checklist">
										<i class="fa fa-table" name="dllist" value="Download List" onclick="changeOptionFormAction('checklist.php?cl=<?php echo $clValue."&proj=".$pid."&dynclid=".$dynClid; ?>','_self');"></i>
									</div>
									<div class="button" style='float:right;margin-right:10px;width:16px;height:16px;padding:2px;' title="Print in Browser">
										<i class="fa fa-print" name="printlist" value="Print List" onclick="changeOptionFormAction('checklist.php','_blank');"></i>
									</div>
									<div class="button" id="wordicondiv" style='float:right;margin-right:10px;width:16px;height:16px;padding:2px;<?php echo ($showImages?'display:none;':''); ?>' title="Export to DOCX">
										<i class="fa fa-file-word-o" name="exportdoc" value="Export to DOCX" onclick="changeOptionFormAction('defaultchecklistexport.php','_self');"></i>
									</div>
								</div>
									<?php
									if($CHECKLIST_FG_EXPORT){
											?>
											<div style="margin:5px 0px 0px 5px;clear:both;">
													<a class="" href="#" onclick="openFieldGuideExporter();"><b>Open Export Panel</b></a>
											</div>
											<?php
									}
									?>
							</fieldset>
						</form>
					</div>
				</div> <!-- ends card -->
			</div> <!-- ends right col -->
		</div> <!-- ends grid row -->
	<?php
	if(!$printMode) include($SERVER_ROOT.'/footer.php');
	?>
</div> <!-- ends contents container -->
</body>
</html>
