<?php
	//Quick and dirty header
	$pages = array();
	
	$p['name'] = "Dscan";
	$p['href'] = "/";
	$pages[] = $p;
    /*
	
	$p['name'] = "Localscan";
	$p['href'] = "/local";
	$pages[] = $p;
	
	$p['name'] = "Pastebin";
	$p['href'] = "/paste";
	$pages[] = $p;
	
	$p['name'] = "Shopping";
	$p['href'] = "/shopping";
	$pages[] = $p;
	
	$p['name'] = "Quickmath";
	$p['href'] = "/quickmath";
	$pages[] = $p;
     */
	
$active = "";
foreach($pages as $p) {
	if(strpos($_SERVER['REQUEST_URI'], $p['href']) !== false) {
		$active = $p['name'];
	}
}
?>
<nav class="navbar navbar-default" role="navigation">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="/">NOG8S Tools (courtesy of Capri's Tools)</a>
        </div>
        <div id="navbar" class="collapse navbar-collapse">
          <ul class="nav navbar-nav">
		  <?php foreach($pages as $p) { ?>
			<li<?php if($p['name'] == $active) { echo ' class="active"'; } ?>><a href="<?php echo $p['href']; ?>"><?php echo $p['name']; ?></a></li>
		  <?php } ?>
          </ul>
		  
          <?/*
			<ul class="nav navbar-nav navbar-right">
				<li class="dropdown">
					<a href="#" onclick="$('#dropdown').toggle();" id="dropdownT" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false">Themes <b class="caret"></b></a>
					<ul id="dropdown" class="dropdown-menu" role="menu" aria-labelledby="dropdownT">
						<li><a href="/switcher.php?theme=flatly">Flatly (Default)</a></li>
						<li><a href="/switcher.php?theme=darkly">Darkly</a></li>
						<li><a href="/switcher.php?theme=slate">Slate</a></li>
						<li><a href="/switcher.php?theme=cyborg">Cyborg</a></li>
					</ul>
				</li>
			</ul>
           */?>
        </div><!--/.nav-collapse -->
      </div>
    </nav>
