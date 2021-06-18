<!-- Footer -->
<footer class="w3-container w3-theme-d3 w3-padding-16">
  <h5>www.americanbamboos.org</h5>
</footer>

<footer class="w3-container w3-theme-d5">
  <p>American Bamboos is built upon the <a href="https://symbiota.org/docs/" target="_blank">Symbiota</a> platform customized with theme elements from <a href="https://www.w3schools.com/w3css/default.asp" target="_blank">w3.css</a>, modified and maintained by <a href="https://github.com/cdtyrrell/American_Bamboos" target="_blank">Christopher D. Tyrrell</a></p>
</footer>

<script>
// Accordion
function myFunction(id) {
  var x = document.getElementById(id);
  if (x.className.indexOf("w3-show") == -1) {
    x.className += " w3-show";
    x.previousElementSibling.className += " w3-theme-d1";
  } else {
    x.className = x.className.replace("w3-show", "");
    x.previousElementSibling.className =
    x.previousElementSibling.className.replace(" w3-theme-d1", "");
  }
}

// Used to toggle the menu on smaller screens when clicking on the menu button
function openNav() {
  var x = document.getElementById("navDemo");
  if (x.className.indexOf("w3-show") == -1) {
    x.className += " w3-show";
  } else {
    x.className = x.className.replace(" w3-show", "");
  }
}
</script>
