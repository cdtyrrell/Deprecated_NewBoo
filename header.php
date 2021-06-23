<!-- Navbar -->
<div class="w3-top">
 <div class="w3-bar w3-theme-d2 w3-left-align w3-large">
  <a class="w3-bar-item w3-button w3-hide-medium w3-hide-large w3-right w3-padding-large w3-hover-white w3-large w3-theme-d2" href="javascript:void(0);" onclick="openNav()"><i class="fa fa-bars"></i></a>
  <!-- Home -->
  <a href="<?php echo $clientRoot; ?>/index.php" class="w3-bar-item w3-button w3-padding-large w3-theme-d4" title="Home">Home</a>
  <!-- icon: <i class="fa fa-home w3-margin-right"></i> -->
  <!-- Species -->
  <div class="w3-dropdown-hover w3-hide-small">
    <button class="w3-button w3-padding-large" title="Search">Species</button>
    <div class="w3-dropdown-content w3-card-4 w3-bar-block" style="width:150px">
      <a href="#" class="w3-bar-item w3-button">Browse</a>
      <a href="<?php echo $clientRoot; ?>/checklists/dynamicmap.php" class="w3-bar-item w3-button">Map Search</a>
      <a href="<?php echo $clientRoot; ?>/imagelib/search.php" class="w3-bar-item w3-button">Images</a>
    </div>
  </div>
  <!-- Specimens -->
  <div class="w3-dropdown-hover w3-hide-small">
    <button class="w3-button w3-padding-large" title="Search">Specimens</button>
    <div class="w3-dropdown-content w3-card-4 w3-bar-block" style="width:150px">
      <a href="<?php echo $clientRoot; ?>/collections/index.php" class="w3-bar-item w3-button">Search</a>
      <a href="<?php echo $clientRoot; ?>/collections/map/mapinterface.php" class="w3-bar-item w3-button">Map Search</a>
      <a href="<?php echo $clientRoot; ?>/imagelib/search.php" class="w3-bar-item w3-button">Images</a>
    </div>
  </div>
  <!-- Keys -->
  <a href="#" class="w3-bar-item w3-button w3-hide-small w3-padding-large w3-hover-white" title="Keys">Keys</a>
  <a href='<?php echo $clientRoot; ?>/sitemap.php' class="w3-bar-item w3-button w3-hide-small w3-padding-large w3-hover-white">Site Menu</a>

  <!-- Right Side -->
  <?php
  if($userDisplayName){
  ?>
  <div class="w3-dropdown-hover w3-hide-small w3-right">
    <button class="w3-button w3-padding-large" title="My Account">My Account</button>
    <div class="w3-dropdown-content w3-card-4 w3-bar-block" style="width:100px">
      <a href="<?php echo $clientRoot; ?>/profile/viewprofile.php" class="w3-bar-item w3-button">Profile</a>
      <a href="<?php echo $clientRoot; ?>/profile/index.php?submit=logout" class="w3-bar-item w3-button">Logout</a>
    </div>
  </div>
  <?php
  }
  else{
  ?>
  <div class="w3-dropdown-hover w3-hide-small w3-right">
    <button class="w3-button w3-padding-large" title="Account">Accounts</button>
    <div class="w3-dropdown-content w3-card-4 w3-bar-block" style="width:100px">

      <a href="<?php echo $clientRoot."/profile/index.php?refurl=".$_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']; ?>" class="w3-bar-item w3-button" title="Log In">Log In</a>
      <a href="<?php echo $clientRoot; ?>/profile/newprofile.php" class="w3-bar-item w3-button">Sign Up</a>
    </div>
  </div>

  <?php
  }
  ?>
 </div>
</div>

<!-- Navbar on small screens -->
<div id="navDemo" class="w3-bar-block w3-theme-d2 w3-hide w3-hide-large w3-hide-medium w3-large">
  <a href="<?php echo $clientRoot; ?>/index.php" class="w3-bar-item w3-button w3-padding-large">Home</a>
  <a href="#" class="w3-bar-item w3-button w3-padding-large">Browse Species</a>
  <a href="<?php echo $clientRoot; ?>/checklists/dynamicmap.php?interface=key" class="w3-bar-item w3-button w3-padding-large">Species Map Search</a>
  <a href="<?php echo $clientRoot; ?>/collections/index.php" class="w3-bar-item w3-button w3-padding-large">Search Specimens</a>
  <a href="<?php echo $clientRoot; ?>/collections/map/mapinterface.php" class="w3-bar-item w3-button w3-padding-large">Specimen Map Search</a>
  <a href="#" class="w3-bar-item w3-button w3-padding-large">Keys</a>
  <a href="<?php echo $clientRoot; ?>/imagelib/search.php" class="w3-bar-item w3-button w3-padding-large">Image Search</a>
  <a href="<?php echo $clientRoot; ?>/sitemap.php" class="w3-bar-item w3-button w3-padding-large">Site Menu</a>

  <?php
  if($userDisplayName){
  ?>
    <a href="<?php echo $clientRoot; ?>/profile/viewprofile.php" class="w3-bar-item w3-button w3-padding-large">Profile</a>
    <a href="<?php echo $clientRoot; ?>/profile/index.php?submit=logout" class="w3-bar-item w3-button w3-padding-large">Logout</a>
  <?php
  }
  else{
  ?>
  <a href="<?php echo $clientRoot."/profile/index.php?refurl=".$_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']; ?>" class="w3-bar-item w3-button w3-padding-large">Log In</a>
  <a href="<?php echo $clientRoot; ?>/profile/newprofile.php" class="w3-bar-item w3-button w3-padding-large">Sign Up</a>
  <?php
  }
  ?>

</div>
