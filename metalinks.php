<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<?php
  if(getcwd() == $SERVER_ROOT) {
    echo '<link rel="stylesheet" href="css/w3.css">'.PHP_EOL.'<link rel="stylesheet" href="css/w3-theme-light-green.css">';
  } else {
    echo '<link rel="stylesheet" href="../css/w3.css">'.PHP_EOL.'<link rel="stylesheet" href="../css/w3-theme-light-green.css">';
  }
?>

<link rel='stylesheet' href='https://fonts.googleapis.com/css?family=Open+Sans'>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<style>
html, body, h1, h2, h3, h4, h5 {font-family: "Open Sans", sans-serif}
</style>
