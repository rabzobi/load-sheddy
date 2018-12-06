<?php

$currentPage = trim($_SERVER['PHP_SELF']); 

?>	
<nav class="navbar navbar-default">
<div class="container-fluid">
	  <div class="navbar-header">
		<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
		  <span class="sr-only">Toggle navigation</span>
		  <span class="icon-bar"></span>
		  <span class="icon-bar"></span>
		  <span class="icon-bar"></span>
		</button>
		<a class="navbar-brand" href="index.php">Load sheddy</a>
	  </div>

	<div id="navbar" class="navbar-collapse collapse">
	<ul class="nav navbar-nav">
	  <li class="dropdown">
		<a href="#" class="dropdown-toggle" data-toggle="dropdown" 
		role="button" aria-haspopup="true" aria-expanded="false">Menu<span class="caret"></span></a>
		<ul class="dropdown-menu">
		  <li><a href="#page1.php">Item 1</a></li>		
		  <li><a href="#page2.php">Item 2</a></li>
		  <li><a href="#page3.php">Item 3</a></li>		  
		</ul>
	  </li>
	 
	</ul>
	</div><!--/.nav-collapse -->
</div><!--/.container-fluid -->
</nav>