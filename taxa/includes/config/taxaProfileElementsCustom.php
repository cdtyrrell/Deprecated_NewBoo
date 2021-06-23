<?php
/*
 ******  Create custom plugins below to add to your taxon profile pages  ********************************************
 *
 * EXAMPLE PLUGIN:
 *
 * ob_start();
 * ?>
 * <div id="plugindiv">
 *      [PLUGIN HTML]
 * </div>
 * <?php
 * $pluginName = ob_get_clean();
 *
 *
 * *****  Once created, add the plugin to one of the page blocks using the taxaProfileTemplateCustom.php file  ********************************************
 *
 */

//ADD PLUGINS BELOW THIS LINE

ob_start();
// Scientific Name
echo "<div class='w3-card w3-round w3-white'>";
echo "\n\t\t\t<div class='w3-container'>";
if($taxonRank > 180){
  ?>
        <div id="scinameheader" class="<?php echo $styleClass; ?>">
        <span id="sciname" class="<?php echo $styleClass; ?>">
            <i><?php echo $spDisplay; ?></i>
        </span>
        <?php echo $taxonManager->getAuthor(); ?>
        <?php
        $parentLink = "index.php?taxon=".$taxonManager->getParentTid()."&cl=".$taxonManager->getClid()."&proj=".$projValue."&taxauthid=".$taxAuthId;
        echo "<a href='".$parentLink."'><img id='parenttaxonicon' src='../images/toparent.png' title='Go to Parent' /></a>";
        //If submitted tid does not equal accepted tid, state that user will be redirected to accepted
        if(($taxonManager->getTid() != $taxonManager->getSubmittedTid()) && $taxAuthId){
            echo '<span id="redirectedfrom"> ('.$LANG['REDIRECT'].': <i>'.$taxonManager->getSubmittedSciName().'</i>)</span>';
        }
        ?>
    </div>
    <?php
}
else{
    $displayName = $spDisplay;
    if($taxonRank == 180) $displayName = '<i>'.$displayName.'</i> spp. ';
    if($taxonRank > 140){
        $parentLink = "index.php?taxon=".$taxonManager->getParentTid()."&cl=".$taxonManager->getClid()."&proj=".$projValue."&taxauthid=".$taxAuthId;
        $displayName .= ' <a href="'.$parentLink.'">';
        $displayName .= '<img id="parenttaxonicon" src="../images/toparent.png" title="Go to Parent" />';
        $displayName .= '</a>';
    }
    echo "<p>" . $displayName . "</p>";
}

// Central Image
if(!$taxonManager->echoImages(0,1,0)){
  echo '<p class="w3-center">';
  if($isEditor){
      echo '<a href="admin/tpeditor.php?category=imageadd&tid='.$taxonManager->getTid().'"><b>'.$LANG['ADD_IMAGE'].'</b></a>';
  }
  else{
      echo $LANG['IMAGE_NOT_AVAILABLE'];
  }
    echo '</p>';
}
?>
</div></div>
<?php
$taxonTile = ob_get_clean();

ob_start();
$isTaxonEditor = false;
if($SYMB_UID){
    if($IS_ADMIN || array_key_exists("TaxonProfile",$USER_RIGHTS)){
        $isTaxonEditor = true;
    }
}
if($isTaxonEditor){
    ?>
        <a href="admin/tpeditor.php?tid=<?php echo $taxonManager->getTid(); ?>" <?php echo 'title="'.$LANG['EDIT_TAXON_DATA'].'"'; ?> >
          <i class="fa fa-pencil fa-fw w3-margin-left w3-text-theme"></i>Edit
        </a>
    <?php
}
$editButtonDiv = ob_get_clean();

ob_start();
    // if($clValue){
    //     echo "<legend>";
    //     echo $LANG['SPECIES_WITHIN'].' <b>'.$taxonManager->getClName().'</b>&nbsp;&nbsp;';
    //     if($taxonManager->getParentClid()){
    //         echo '<a href="index.php?taxon='.$taxonValue.'&cl='.$taxonManager->getParentClid().'&taxauthid='.$taxAuthId.'" title="'.$LANG['GO_TO'].' '.$taxonManager->getParentName().' '.$LANG['CHECKLIST'].'"><img id="parenttaxonicon" src="../images/toparent.png" title="Go to Parent" /></a>';
    //     }
    //     echo "</legend>";
    // }
    //echo '<div class="w3-row-padding w3-center">';
        if($sppArr = $taxonManager->getSppArray()){
            $cnt = 0;
            ksort($sppArr);
            foreach($sppArr as $sciNameKey => $subArr){
                echo '<div class="w3-quarter w3-container w3-margin-bottom w3-center">';
                echo "<a href='index.php?taxon=".$subArr["tid"]."&taxauthid=".$taxAuthId.($clValue?"&cl=".$clValue:"")."'>";
                // Image
                if(array_key_exists("url",$subArr)){
                    $imgUrl = $subArr["url"];
                    if(array_key_exists("imageDomain",$GLOBALS) && substr($imgUrl,0,1)=="/"){
                        $imgUrl = $GLOBALS["imageDomain"].$imgUrl;
                    }
                    if($subArr["thumbnailurl"]){
                        $imgUrl = $subArr["thumbnailurl"];
                        if(array_key_exists("imageDomain",$GLOBALS) && substr($subArr["thumbnailurl"],0,1)=="/"){
                            $imgUrl = $GLOBALS["imageDomain"].$subArr["thumbnailurl"];
                        }
                    }
                    echo '<img style="width:100%;" class="w3-hover-opacity" src="'.$imgUrl.'" title="'.$subArr['caption'].'" alt="Image of '.$sciNameKey.'" />';
                    echo '<p id="imgphotographer" title="'.$LANG['PHOTOGRAPHER'].': '.$subArr['photographer'].'">';
                    echo '</p>';
                }
                elseif($isEditor){
                    echo '<p><a href="admin/tpeditor.php?category=imageadd&tid='.$subArr['tid'].'">'.$LANG['ADD_IMAGE'].'!</a></p>';
                }
                else{
                    echo '<p>'.$LANG['IMAGE_NOT_AVAILABLE'].'</p>';
                }

                echo '<div class="w3-container w3-white w3-center">';
                echo "<i>".$sciNameKey."</i>";
                echo "</a></div>";

                //Display thumbnail map
                if(array_key_exists("map",$subArr) && $subArr["map"]){
                    echo '<img src="'.$subArr['map'].'" title="'.$spDisplay.'" alt="'.$spDisplay.'" style="width:100%;" />';
                }
                elseif($taxonManager->getRankId()>140){
                    echo '<p>'.$LANG['MAP_NOT_AVAILABLE'].'</p>';
                }
                echo "</div>".PHP_EOL;
                $cnt++;
            }
        }
        ?>
        <!-- <div class="clear"><hr></div>-->
<?php
$imgBoxDiv = ob_get_clean();

ob_start();
if($descArr = $taxonManager->getDescriptions()){
    if(isset($PORTAL_TAXA_DESC)){
        $tempArr = array();
        $descIndex = 0;
        foreach($descArr as $dArr){
            foreach($dArr as $id => $vArr){
                if($vArr["caption"] == $PORTAL_TAXA_DESC){
                    if($descArr[$descIndex]){
                        $tempArr = $descArr[$descIndex][$id];
                        unset($descArr[$descIndex][$id]);
                        array_unshift($descArr[$descIndex],$tempArr);
                    }
                    $descIndex++;
                }
            }
        }
    }
    ?>
    <div id="desctabs" class="w3-card w3-round">
            <?php
            $capCnt = 1;
            foreach($descArr as $dArr){
                foreach($dArr as $id => $vArr){
                    $cap = $vArr["caption"];
                    if(!$cap){
                        $cap = $LANG['DESCRIPTION'].' #'.$capCnt;
                        $capCnt++;
                    }
                    echo '<a href="#tab'.$id.'" class="selected">'.$cap.'</a>';
                    echo '<!-- Drop Down Blocks -->'.PHP_EOL;
                    echo '<div class="w3-white">'.PHP_EOL;
                    echo '<button onclick="myFunction(\'tab'.$id.'\')" class="w3-button w3-block w3-theme-l1 w3-left-align">'.$cap.'</button>'.PHP_EOL; //need to escape 'tab.$id'
                    echo '<div id="tab'.$id.'" class="w3-hide w3-container">'.PHP_EOL;

                    if($vArr["source"]){
                        echo '<p>';
                        if($vArr["url"]){
                            echo '<a href="'.$vArr['url'].'" target="_blank">';
                        }
                        echo $vArr["source"];
                        if($vArr["url"]){
                            echo "</a>";
                        }
                        echo '</p>';
                    }

                    $descArr = $vArr["desc"];
                    ?>
                    <p>
                        <?php
                        foreach($descArr as $tdsId => $stmt){
                            echo $stmt." ";
                        }
                        ?>
                    </p>
                    <?php
                    echo '</div>'.PHP_EOL;
                    echo '</div>'.PHP_EOL;
                    echo '<br>'.PHP_EOL;
                }
            }
            ?>
    </div>
    <?php
} else {
  echo '<div class="w3-white">'.PHP_EOL;

  echo '<button onclick="myFunction(\'nodesc\')" class="w3-button w3-block w3-theme-l1 w3-left-align">'.$LANG['DESCRIPTION'].'</button>'.PHP_EOL;
  echo '<div id="nodesc" class="w3-hide w3-container">';
    echo '<p>'.$LANG['DESCRIPTION_NOT_AVAILABLE'].'</p>';
  echo '</div></div>';
}
$descTabsDiv = ob_get_clean();

?>
