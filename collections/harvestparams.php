<?php
include_once('../config/symbini.php');
include_once($SERVER_ROOT.'/config/includes/searchVarDefault.php');
include_once($SERVER_ROOT.'/classes/OccurrenceManager.php');
header("Content-Type: text/html; charset=".$charset);
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

if(file_exists($SERVER_ROOT.'/config/includes/searchVarCustom.php')){
    include($SERVER_ROOT.'/config/includes/searchVarCustom.php');
}

$collManager = new OccurrenceManager();
$collArr = Array();
$stArr = Array();
$stArrCollJson = '';
$stArrSearchJson = '';

if(isset($_REQUEST['taxa']) || isset($_REQUEST['country']) || isset($_REQUEST['state']) || isset($_REQUEST['county']) || isset($_REQUEST['local']) || isset($_REQUEST['elevlow']) || isset($_REQUEST['elevhigh']) || isset($_REQUEST['upperlat']) || isset($_REQUEST['pointlat']) || isset($_REQUEST['collector']) || isset($_REQUEST['collnum']) || isset($_REQUEST['eventdate1']) || isset($_REQUEST['eventdate2']) || isset($_REQUEST['catnum']) || isset($_REQUEST['typestatus']) || isset($_REQUEST['hasimages'])){
    $stArr = $collManager->getSearchTerms();
    $stArrSearchJson = json_encode($stArr);
}

if(isset($_REQUEST['db'])){
    if(is_array($_REQUEST['db']) || $_REQUEST['db'] == 'all'){
        $collArr['db'] = $collManager->getSearchTerm('db');
        $stArrCollJson = json_encode($collArr);
    }
}
?>

<html>
<head>
  <title><?php echo $defaultTitle.' '.$SEARCHTEXT['PAGE_TITLE']; ?></title>
  <?php include($SERVER_ROOT . '/metalinks.php'); ?>
	<link href="../css/jquery-ui.css" type="text/css" rel="Stylesheet" />
	<script type="text/javascript" src="../js/jquery.js"></script>
	<script type="text/javascript" src="../js/jquery-ui.js"></script>
  <script type="text/javascript" src="../js/symb/collections.harvestparams.js?ver=9"></script>
  <script type="text/javascript">
        var starrJson = '';

        $(document).ready(function() {
            <?php
            if($stArrCollJson){
                echo "sessionStorage.jsoncollstarr = '".$stArrCollJson."';\n";
            }

            if($stArrSearchJson){
                ?>
                starrJson = '<?php echo $stArrSearchJson; ?>';
                sessionStorage.jsonstarr = starrJson;
                setHarvestParamsForm();
                <?php
            }
            else{
                ?>
                if(sessionStorage.jsonstarr){
                    starrJson = sessionStorage.jsonstarr;
                    setHarvestParamsForm();
                }
                <?php
            }
            ?>
        });

        function checkHarvestparamsForm(frm){
            <?php
            if(!$SOLR_MODE){
                ?>
                //make sure they have filled out at least one field.
                if ((frm.taxa.value == '') && (frm.country.value == '') && (frm.state.value == '') && (frm.county.value == '') &&
                    (frm.locality.value == '') && (frm.upperlat.value == '') && (frm.pointlat.value == '') && (frm.catnum.value == '') &&
                    (frm.elevhigh.value == '') && (frm.eventdate2.value == '') && (frm.typestatus.checked == false) && (frm.hasimages.checked == false) && (frm.hasgenetic.checked == false) &&
                    (frm.collector.value == '') && (frm.collnum.value == '') && (frm.eventdate1.value == '') && (frm.elevlow.value == '')) {
                    if(sessionStorage.jsoncollstarr){
                        var jsonArr = JSON.parse(sessionStorage.jsoncollstarr);
                        for(i in jsonArr){
                            if(jsonArr[i] == 'all'){
                                alert("Please fill in at least one search parameter!");
                                return false;
                            }
                        }
                    }
                    else{
                        alert("Please fill in at least one search parameter!");
                        return false;
                    }
                }
                <?php
            }
            ?>

            if(frm.upperlat.value != '' || frm.bottomlat.value != '' || frm.leftlong.value != '' || frm.rightlong.value != ''){
                // if Lat/Long field is filled in, they all should have a value!
                if(frm.upperlat.value == '' || frm.bottomlat.value == '' || frm.leftlong.value == '' || frm.rightlong.value == ''){
                    alert("Error: Please make all Lat/Long bounding box values contain a value or all are empty");
                    return false;
                }

                // Check to make sure lat/longs are valid.
                if(Math.abs(frm.upperlat.value) > 90 || Math.abs(frm.bottomlat.value) > 90 || Math.abs(frm.pointlat.value) > 90){
                    alert("Latitude values can not be greater than 90 or less than -90.");
                    return false;
                }
                if(Math.abs(frm.leftlong.value) > 180 || Math.abs(frm.rightlong.value) > 180 || Math.abs(frm.pointlong.value) > 180){
                    alert("Longitude values can not be greater than 180 or less than -180.");
                    return false;
                }
                if(parseFloat(frm.upperlat.value) < parseFloat(frm.bottomlat.value)){
                    alert("Your northern latitude value is less then your southern latitude value. Please correct this.");
                    return false;
                }
                if(parseFloat(frm.leftlong.value) > parseFloat(frm.rightlong.value)){
                    alert("Your western longitude value is greater then your eastern longitude value. Please correct this. Note that western hemisphere longitudes in the decimal format are negitive.");
                    return false;
                }
            }

            //Same with point radius fields
            if(frm.pointlat.value != '' || frm.pointlong.value != '' || frm.radius.value != ''){
                if(frm.pointlat.value == '' || frm.pointlong.value == '' || frm.radius.value == ''){
                    alert("Error: Please make all Lat/Long point-radius values contain a value or all are empty");
                    return false;
                }
            }

            if(frm.elevlow.value || frm.elevhigh.value){
                if(isNaN(frm.elevlow.value) || isNaN(frm.elevhigh.value)){
                    alert("Error: Please enter only numbers for elevation values");
                    return false;
                }
            }

            return true;
        }
  </script>
</head>
<body>

<?php
	$displayLeftMenu = (isset($collections_harvestparamsMenu)?$collections_harvestparamsMenu:false);
	include($serverRoot.'/header.php');
	if(isset($collections_harvestparamsCrumbs)){
		if($collections_harvestparamsCrumbs){
			echo '<div class="navpath">';
			echo $collections_harvestparamsCrumbs.' &gt;&gt; ';
			echo '<b>'.$LANG['NAV_SEARCH'].'</b>';
			echo '</div>';
		}
	}
	else{
		?>
		<div class='navpath'>
			<a href="../index.php"><?php echo $LANG['NAV_HOME']; ?></a> &gt;&gt;
			<a href="index.php"><?php echo $LANG['NAV_COLLECTIONS']; ?></a> &gt;&gt;
			<b><?php echo $LANG['NAV_SEARCH']; ?></b>
		</div>
		<?php
	}
?>

<!-- INNER TEXT -->
  <div class="w3-container w3-content" style="max-width:1400px;margin-top:80px">
    <form name="harvestparams" id="harvestparams" action="list.php" method="post" onsubmit="return checkHarvestparamsForm(this);">

    <!-- Top Row -->
    <div class="w3-row">
      <div class="w3-col m12">
        <!-- Search -->
        <div class="w3-card w3-round w3-white w3-margin">
          <div class="w3-container">
		          <h1><?php echo $SEARCHTEXT['PAGE_HEADER']; ?></h1>
		          <p><?php echo $SEARCHTEXT['GENERAL_TEXT_1']; ?></p>
			               <input type='checkbox' name='showtable' id='showtable' value='1' onchange="changeTableDisplay();" /> Show results in table view
                <div style='float:right;margin:5px 10px;'>
                  <input type="submit" class="nextbtn" value="<?php echo isset($LANG['BUTTON_NEXT'])?$LANG['BUTTON_NEXT']:'Next >'; ?>" />
                  <button type="button" class="resetbtn" onclick='resetHarvestParamsForm(this.form);'><?php echo isset($LANG['BUTTON_RESET'])?$LANG['BUTTON_RESET']:'Reset Form'; ?></button>
                </div>
              </div>
            </div>

          <!-- Taxonomy -->
          <div class="w3-card w3-round w3-white w3-margin">
            <div class="w3-container w3-padding">
              <h2><?php echo $SEARCHTEXT['TAXON_HEADER']; ?></h2>
              <p>
                <input type='checkbox' name='thes' value='1' CHECKED />
                <?php echo $SEARCHTEXT['GENERAL_TEXT_2']; ?>
              </p>
              <div id="taxonSearch0" class="w3-row">
      					<select id="taxontype" name="type">
      						<option value='1'><?php echo $SEARCHTEXT['SELECT_1-1']; ?></option>
      						<option value='2'><?php echo $SEARCHTEXT['SELECT_1-2']; ?></option>
      						<option value='3'><?php echo $SEARCHTEXT['SELECT_1-3']; ?></option>
      						<option value='4'><?php echo $SEARCHTEXT['SELECT_1-4']; ?></option>
      						<option value='5'><?php echo $SEARCHTEXT['SELECT_1-5']; ?></option>
      					</select>:
				        <input id="taxa" type="text" name="taxa" value="" title="<?php echo $SEARCHTEXT['TITLE_TEXT_1']; ?>" style="width:80%"/>
				      </div>
            </div>
          </div>
        </div> <!-- end m12 col -->

      <!-- Begin two column -->
        <!-- Left Column -->
        <div class="w3-col m6">
          <!-- Collector -->
          <div class="w3-card w3-round w3-white w3-margin">
            <div class="w3-container w3-padding">
              <h2><?php echo $SEARCHTEXT['COLLECTOR_HEADER']; ?></h2>
              <div class="w3-container">
                <div class="w3-third">
                  <?php echo $SEARCHTEXT['COLLECTOR_LASTNAME']; ?>
                </div>
                <div class="w3-twothird">
                  <input type="text" id="collector" style="width:100%" name="collector" value="" title="<?php echo $SEARCHTEXT['TITLE_TEXT_1']; ?>" />
                </div>
              </div>
              <div class="w3-container">
                <div class="w3-third">
                  <?php echo $SEARCHTEXT['COLLECTOR_NUMBER']; ?>
                </div>
                <div class="w3-twothird">
                  <input type="text" id="collnum" style="width:100%" name="collnum" value="" title="<?php echo $SEARCHTEXT['TITLE_TEXT_2']; ?>" />
                </div>
              </div>
              <div class="w3-container">
                <div class="w3-third">
                  <?php echo $SEARCHTEXT['COLLECTOR_DATE']; ?>
                </div>
                <div class="w3-twothird">
                  <input type="text" id="eventdate1" style="width:48%" name="eventdate1" style="width:100px;" value="" title="<?php echo $SEARCHTEXT['TITLE_TEXT_3']; ?>" /> -
                  <input type="text" id="eventdate2" style="width:48%" name="eventdate2" style="width:100px;" value="" title="<?php echo $SEARCHTEXT['TITLE_TEXT_4']; ?>" />
                </div>
              </div>

              </div>
            </div>

            <!-- Specimen -->
            <div class="w3-card w3-round w3-white w3-margin">
              <div class="w3-container w3-padding">
                <h2><?php echo $SEARCHTEXT['SPECIMEN_HEADER']; ?></h2>
                <div>
                  <?php echo $SEARCHTEXT['CATALOG_NUMBER']; ?>
                  <input type="text" id="catnum" style="width:70%" name="catnum" value="" title="<?php echo $SEARCHTEXT['TITLE_TEXT_1']; ?>" />
                </div>
                <div>
                  <input type="checkbox" name="includeothercatnum" value="1" checked />
                  <?php echo $SEARCHTEXT['INCLUDE_OTHER_CATNUM']; ?>
                </div>
                <div>
                  <input type='checkbox' name='typestatus' value='1' />
                  <?php echo $SEARCHTEXT['TYPE']; ?>
                </div>
                <div>
                  <input type='checkbox' name='hasimages' value='1' />
                  <?php echo $SEARCHTEXT['HAS_IMAGE']; ?>
                </div>
                <div id="searchGeneticCheckbox">
                  <input type='checkbox' name='hasgenetic' value='1' />
                  <?php echo $SEARCHTEXT['HAS_GENETIC']; ?>
                </div>
              </div>
            </div>
          </div>

          <!-- Right Column -->
          <div class="w3-col m6">

<!-- Locality -->
<div class="w3-card w3-round w3-white w3-margin">
  <div class="w3-container w3-padding">
				<h2><?php echo $SEARCHTEXT['LOCALITY_HEADER']; ?></h2>
        <div class="w3-container">
          <div class="w3-third">
            <?php echo $SEARCHTEXT['COUNTRY_INPUT']; ?>
          </div>
          <div class="w3-twothird">
            <input type="text" id="country" style="width:100%" name="country" value="" title="<?php echo $SEARCHTEXT['TITLE_TEXT_1']; ?>" />
          </div>
        </div>
        <div class="w3-container">
          <div class="w3-third">
            <?php echo $SEARCHTEXT['STATE_INPUT']; ?>
          </div>
          <div class="w3-twothird">
            <input type="text" id="state" style="width:100%" name="state" value="" title="<?php echo $SEARCHTEXT['TITLE_TEXT_1']; ?>" />
          </div>
        </div>
        <div class="w3-container">
          <div class="w3-third">
            <?php echo $SEARCHTEXT['COUNTY_INPUT']; ?>
          </div>
          <div class="w3-twothird">
            <input type="text" id="county" style="width:100%"  name="county" value="" title="<?php echo $SEARCHTEXT['TITLE_TEXT_1']; ?>" />
          </div>
        </div>
        <div class="w3-container">
          <div class="w3-third">
            <?php echo $SEARCHTEXT['LOCALITY_INPUT']; ?>
          </div>
          <div class="w3-twothird">
            <input type="text" id="locality" style="width:100%" name="local" value="" />
          </div>
        </div>
        <div class="w3-container">
          <div class="w3-third">
            <?php echo $SEARCHTEXT['ELEV_INPUT_1']; ?>
          </div>
          <div class="w3-twothird">
            <input type="text" id="elevlow" style="width:46%" name="elevlow" value="" />
            <?php echo $SEARCHTEXT['ELEV_INPUT_2']; ?>
            <input type="text" id="elevhigh" style="width:46%" name="elevhigh" value="" />
          </div>
        </div>

            <?php
            if($QUICK_HOST_ENTRY_IS_ACTIVE) {
                ?>
                <div>
                    <?php echo $SEARCHTEXT['ASSOC_HOST_INPUT']; ?> <input type="text" id="assochost" size="43" name="assochost" value="" title="<?php echo $SEARCHTEXT['TITLE_TEXT_1']; ?>" />
                </div>
                <?php
            }
            ?>
          </div>
        </div>

        <!-- Lat/Long -->
        <div class="w3-card w3-round w3-white w3-margin">
          <div class="w3-container w3-padding">
            <h2><?php echo $SEARCHTEXT['LAT_LNG_HEADER']; ?></h2>

            <div style="clear:both;float:right;margin-top:8px;cursor:pointer;" onclick="openBoundingBoxMap();">
              <i class="fa fa-globe" title="<?php echo $SEARCHTEXT['LL_P-RADIUS_TITLE_1']; ?>"></i>
            </div>
            <div>
              <b><?php echo $SEARCHTEXT['LL_BOUND_TEXT']; ?></b>
              <div class="w3-container">
                <div class="w3-third">
        					<?php echo $SEARCHTEXT['LL_BOUND_NLAT']; ?>
                </div>
                <div class="w3-third">
                  <input type="text" id="upperlat" name="upperlat" style="width:100%" value="" onchange="checkUpperLat();" style="margin-left:9px;">
                </div>
                <div class="w3-third">
        					<select id="upperlat_NS" name="upperlat_NS" onchange="checkUpperLat();">
        						<option id="nlN" value="N"><?php echo $SEARCHTEXT['LL_N_SYMB']; ?></option>
        						<option id="nlS" value="S"><?php echo $SEARCHTEXT['LL_S_SYMB']; ?></option>
        					</select>
                </div>
      				</div>
              <div class="w3-container">
                <div class="w3-third">
                  <?php echo $SEARCHTEXT['LL_BOUND_SLAT']; ?>
                </div>
                <div class="w3-third">
                  <input type="text" id="bottomlat" name="bottomlat" style="width:100%" value="" onchange="javascript:checkBottomLat();" style="margin-left:7px;">
                </div>
                <div class="w3-third">
        					<select id="bottomlat_NS" name="bottomlat_NS" onchange="checkBottomLat();">
        						<option id="blN" value="N"><?php echo $SEARCHTEXT['LL_N_SYMB']; ?></option>
        						<option id="blS" value="S"><?php echo $SEARCHTEXT['LL_S_SYMB']; ?></option>
        					</select>
      				</div>
            </div>
            <div class="w3-container">
              <div class="w3-third">
                <?php echo $SEARCHTEXT['LL_BOUND_WLNG']; ?>
              </div>
              <div class="w3-third">
                <input type="text" id="leftlong" name="leftlong" style="width:100%" value="" onchange="javascript:checkLeftLong();">
              </div>
              <div class="w3-third">
      					<select id="leftlong_EW" name="leftlong_EW" onchange="checkLeftLong();">
      						<option id="llW" value="W"><?php echo $SEARCHTEXT['LL_W_SYMB']; ?></option>
      						<option id="llE" value="E"><?php echo $SEARCHTEXT['LL_E_SYMB']; ?></option>
      					</select>
              </div>
    				</div>
            <div class="w3-container">
              <div class="w3-third">
                <?php echo $SEARCHTEXT['LL_BOUND_ELNG']; ?>
              </div>
              <div class="w3-third">
                <input type="text" id="rightlong" name="rightlong" style="width:100%" value="" onchange="javascript:checkRightLong();" style="margin-left:3px;">
              </div>
              <div class="w3-third">
                <select id="rightlong_EW" name="rightlong_EW" onchange="checkRightLong();">
                  <option id="rlW" value="W"><?php echo $SEARCHTEXT['LL_W_SYMB']; ?></option>
                  <option id="rlE" value="E"><?php echo $SEARCHTEXT['LL_E_SYMB']; ?></option>
                </select>
              </div>
    				</div>
          </div>

          <div style="clear:both;float:right;margin-top:8px;cursor:pointer;" onclick="openPointRadiusMap();">
            <i class="fa fa-globe" title="<?php echo $SEARCHTEXT['LL_P-RADIUS_TITLE_1']; ?>"></i>
          </div>
    			<div>
    				<b><?php echo $SEARCHTEXT['LL_P-RADIUS_TEXT']; ?></b>
    				<div>
              <div class="w3-container">
                <div class="w3-third">
        					<?php echo $SEARCHTEXT['LL_P-RADIUS_LAT']; ?>
                </div>
                <div class="w3-third">
                  <input type="text" id="pointlat" name="pointlat" style="width:100%" value="" onchange="javascript:checkPointLat();" style="margin-left:11px;">
                </div>
                <div class="w3-third">
        					<select id="pointlat_NS" name="pointlat_NS" onchange="checkPointLat();">
        						<option id="N" value="N"><?php echo $SEARCHTEXT['LL_N_SYMB']; ?></option>
        						<option id="S" value="S"><?php echo $SEARCHTEXT['LL_S_SYMB']; ?></option>
        					</select>
        				</div>
              </div>
              <div class="w3-container">
                <div class="w3-third">
        					<?php echo $SEARCHTEXT['LL_P-RADIUS_LNG']; ?>
                </div>
                <div class="w3-third">
                  <input type="text" id="pointlong" name="pointlong" style="width:100%" value="" onchange="javascript:checkPointLong();">
                </div>
                <div class="w3-third">
        					<select id="pointlong_EW" name="pointlong_EW" onchange="checkPointLong();">
        						<option id="W" value="W"><?php echo $SEARCHTEXT['LL_W_SYMB']; ?></option>
        						<option id="E" value="E"><?php echo $SEARCHTEXT['LL_E_SYMB']; ?></option>
        					</select>
                </div>
              </div>
              <div class="w3-container">
                <div class="w3-third">
        					<?php echo $SEARCHTEXT['LL_P-RADIUS_RADIUS']; ?>
                </div>
                <div class="w3-third">
                  <input type="text" id="radiustemp" name="radiustemp" style="width:100%" value="" style="margin-left:15px;" onchange="updateRadius();">
                </div>
                <div class="w3-third">
        					<select id="radiusunits" name="radiusunits" onchange="updateRadius();">
        						<option value="km"><?php echo $SEARCHTEXT['LL_P-RADIUS_KM']; ?></option>
        						<option value="mi"><?php echo $SEARCHTEXT['LL_P-RADIUS_MI']; ?></option>
        					</select>
        					<input type="hidden" id="radius" name="radius" value="" />
                </div>
      				</div>
    			</div>
        </div>
      </div>
    </div> <!-- End Lat/Long -->
  </div> <!-- End Right Col -->
</div> <!-- End Second Row -->

<!--
			<div style="float:right;">
				<input type="submit" class="nextbtn" value="<?php //echo isset($LANG['BUTTON_NEXT'])?$LANG['BUTTON_NEXT']:'Next >'; ?>" />
			</div>
    -->
			<input type="hidden" name="reset" value="1" />
		</form>
    </div>
	<?php
	include($serverRoot.'/footer.php');
	?>
</body>
</html>
