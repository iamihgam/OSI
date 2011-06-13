<? $this->load->helper("linkeddata_helper"); ?>
<? $this->load->helper("impact_helper"); ?>
<!DOCTYPE html>
<?=$metaDisplay;?>
<html>
<head>
	<title>Footprinted.org Search</title>
	
	<?=$styles;?>
	<?=$headerDisplay;?>



</head>
<body id="home">
	<div id ="contentwrap">	
	<?= $navigationDisplay;?>
	
	<div id="columnwide">
   	

	<form action="/search/keyword/" method="post">
	<div id="searchinput"><input type="text" name="keyword" /></div>
	<div id="searchform"><input type="submit" value="Search by keyword" /></div>
	</form>
	<?
		if (isset($menu) == true) {
			echo "<p>Explore by categories: ";
			foreach ($menu as $menu_item) {
				echo '<a href="/search/category/'.$menu_item['uri'].'" />'.$menu_item['label'].'</a> / ';
			}
			echo "</p>";
		}
	?>
	<br/>

				<?
					if (isset($search_term) == true) {
						echo "<h1 class='about'>".$search_term;
					}
				if (isset($set) == true) {
					if (count($set) > 0) {
						echo ': '.count($set). ' footprints found.</h1>';
					} 
				}
				?>
				<?	
					if (isset($set) == true) {
						if (count($set) > 0) {
						foreach ($set as $row) {
							// Remove the footprinted part of the url
							$myString = str_replace ("http://footprinted.org/rdfspace/lca/", "", $row['uri']);
							echo '<div class="medium blue square" id="'.$myString.'">
							<div class="squareplace"><p>'.$row['geo'].'</p></div>
							<div class="squareyear"><p>'.$row['year'].'</p></div>
							<div class="squaretext"><p><a href="/lca/view/'.$myString .'">'.$row['label'].'</a></p></div>
							<div class="squareimpacts"><p>'.$row['impacts'].'</p></div>
							</div>';
						}
					} else {
						echo "No results found.";
					}
					}
				?>
	</div>
	<?=$footerDisplay;?>
</div>

	<script src="http://footprinted.org/assets/scripts/jquery/jquery-1.5.1.min.js"></script>
	<script src="http://footprinted.org/assets/scripts/jquery/jquery.masonry.min.js"></script>
	<script src="http://footprinted.org/assets/scripts/jquery/jquery-ui-1.7.2.min.js" type="text/javascript"></script>
	<script src="http://footprinted.org/assets/scripts/jquery/jquery-ui-1.8.11.custom.min.js" type="text/javascript"></script>
	
	<script>
	
	$(function() {
		$( ".blue" ).click(function(){
			if (typeof opensquare!="undefined"){
				opensquare.load('/lca/getName/'+opensquare.attr('id'));
				opensquare.removeClass("medium");
				opensquare.removeClass("grey");
                opensquare.addClass("small");
				opensquare.addClass("blue");
			}
			opensquare = $(this);
            $(this).removeClass("small");
			$(this).removeClass("blue");
            $(this).addClass("medium");
			$(this).addClass("grey");
			$('#columnleft').masonry({	  
				  itemSelector: '.square', columnWidth:10, });
			$(this).load('/lca/getImpacts/'+$(this).attr('id'));	
		});
		
	});	

	$(function() {
	    //Get Divs
	    //$('#leftcolumn > [square]').each(function(i) {
			// Get CO2
	//		co2 = ('/lca/getCO2/'+opensquare.attr('id'));
			// Scale
	//		side = Math.sqrt(co2*2500);
	//		$(this).width(side);
	//		$(this).height(side);
	//    });
	// Do Masonry
//	$('#columnleft').masonry({	  
		  itemSelector: '.square', columnWidth:10, });
//	});
	
	$( ".aaa" ).click(function(){
			thisone = $(this);
			$.getJSON('/lca/getCO2/'+$(this).attr('id'), function(data) {;
				side = Math.sqrt(data.CO2*2500);
				side = Math.round(side);
				if(side > 400){ side = 400; }
				thisone.width(side);
				thisone.height(side);

	// Do Masonry
	$('#columnleft').masonry({	  
		  itemSelector: '.square', columnWidth:10, });
		});
	});	});
	</script>
</body>
</html>