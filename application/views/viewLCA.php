<? $this->load->helper("linkeddata_helper"); ?>
<!DOCTYPE html>
<?=$metaDisplay;?>
<html>
<head>
	<title><?=$parts['quantitativeReference']['name'] ?> Footprinted</title>
	<?=$scripts;?>	
	<?=$styles;?>
	<?=$headerDisplay;?>
</head>

<body id="home">
	
	<div id ="contentwrap">	
		
	<?= $navigationDisplay;?>

	<div id="lca_background">
		
		<div id="lca_title"><h1><?=$parts['quantitativeReference']['name'] ?></h1></div>
		<? /*<p>Model of the production <? if(isset($parts['modeled']['process'])==true) { echo "(" . $parts['modeled']['process'] . ")" ; } ?> <? if(isset($parts['modeled']['product'])==true) { echo " of " . $parts['modeled']['product'] ; } ?></p> */?>	
		<div id="lca_unit"><h1><nr><?=$parts['quantitativeReference']['amount'] ?> <?=linkThis($parts['quantitativeReference']['unit'], $parts["tooltips"]) ?></nr></h1></div>	
			<div id="lca_impact">
			<h2><span>Impact Assessment</h2>	
			<? foreach ($parts['impactAssessments'] as $impactAssessment) {
				// Change color of the circle depending on the impact category	
				switch ($impactAssessment['impactCategoryIndicator']) {
				    case 'ossia:waste': $color = "#666"; $max = 10; break;
				    case 'ossia:CO2e': $color = "#333";	$max = 10; break;
				    case "ossia:energy": $color = "#227CAF"; $max = 500;	break;
					case "ossia:water":$color = "#45A3D8"; $max = 10; break;
					default: $color = "#45A3D8"; $max = 50;
				}
				// Change the size depending on the relative max
				$size = 2* round(50*$impactAssessment['amount']/$max);
				if ($size > 80) { $size = 80; }
				$margin = (100-$size)/2;
				$margintop = (100-$size)/6;
				// Create a circle
				echo '<div class="circle"><div style="width:'.$size.'px; height:'.$size.'px;margin-left:'.$margin.'px;margin-top:'.$margintop.'px; background:'.$color.'; -moz-border-radius: 40px; -webkit-border-radius:40px;"></div></div>';
				echo '<div class="nr"><h1 class="nr">' . round($impactAssessment['amount'],2) . '</h1></div>';
				echo '<div class="meta"><p class="unit">'. linkThis($impactAssessment['unit'], $parts["tooltips"], "label") .'</p><p class="category">';
				echo  $impactAssessment['impactCategory'] . " - " . $impactAssessment['impactCategoryIndicator'];
				echo "<p/></div>"; 
				
			}?>
			</div>
			
			<? if(is_array($parts['exchanges']  ) == true) { ?>
			<div id="lca_flows">
			<h2>Flows</h2>
			<? if ($totalinput != 0) { ?>
			<h3>Total material input:</h3> <h1 class="nr"><?=round($totalinput,2); ?> kg </h1>
			<? if($parts['quantitativeReference']['unit'] == "qudtu:Kilogram") { 
					$ratio = ($totalinput/$parts['quantitativeReference']['amount']);
					echo "<h3>Ratio input vs production</h3><h1 class='nr'>".round($ratio).":1</h1>";
				} ?>
			<? if ($totalinputliter != 0) { ?>
					<h3>Total water input:</h3> <h1 class="nr"><?=round($totalinputliter,2); ?> liters </h1>
			<? } ?>
			<h3>Material inputs breakdown:</h3>
			<? 
			if (isset($parts['Input']["Mass"]) == true) { 
			$color_input = array('8CC63F','85C61E','70A024','7FAA3A','8EB256','88D600','98EF00','8DEF00','8ECE20','72CC23','55CC23'); $i=0;
			foreach ($parts['Input']["Mass"] as $mass) {
					$width = round(100*$mass['amount']/$totalinput);
					if ($width == 0) { $width = 1; }
					echo '<div class="bar_background"><div style="height:20px;width:'.$width.'%;background-color:#'.$color_input[$i].';"></div></div>';
					echo "<div class='flow_text'><p><amount>" . $mass['amount'] . "</amount> " . linkThis($mass['unit'], $parts["tooltips"]); 
					echo "<b> ".$mass['name'] . "</b></p></div>";
					$i++; if ($i >10){ $i = 0;}
			}}?>
			
			<? 
			if (isset($parts['Input']["Liquid Volume"]) == true) { 
			$color_liquid = array('1a6eff','1B64CE','1753AA','133E7C','2C4C7C');$i = 0;	
			foreach ($parts['Input']["Liquid Volume"] as $volume) {
					$width = round(100*$volume['amount']/$totalinputliter);
					if ($width == 0) { $width = 1; }
					echo '<div class="bar_background"><div style="height:20px;width:'.$width.'%;background-color:#'.$color_liquid[$i].';"></div></div>';
					echo "<div class='flow_text'><p><amount>" . $volume['amount'] . "</amount> " . linkThis($volume['unit'], $parts["tooltips"]); 
					echo "<b> ".$volume['name'] . "</b></p></div>";
					$i++; if ($i >4){ $i = 0;}
			}}?>
			<? 
			if (isset($parts['Input']["Area"]) == true) { 
				foreach ($parts['Input']["Area"] as $land) {
					$width = round(100*$land['amount']/$totalinputland);
					if ($width == 0) { $width = 1; }
					echo '<div class="bar_background"><div style="height:20px;width:'.$width.'%;background-color:#381100;"></div></div>';
					echo "<div class='flow_text'><p><amount>" . $land['amount'] . "</amount> " . linkThis($land['unit'], $parts["tooltips"]); 
					echo "<b> ".$land['name'] . "</b></p></div>";
			}}?>

			<?}?>
			
			<? if ($totaloutput != 0) { ?>	
			<h3>Total output: <h1 class="nr"><?=round($totaloutput,2); ?> kg</h1></h3>
			<? if($parts['quantitativeReference']['unit'] == "qudtu:Kilogram") { 
					$ratio = ($totaloutput/$parts['quantitativeReference']['amount']);
					echo "<h3>Ratio output vs production</h3><h1 class='nr'>".round($ratio).":1</h1>";
				} ?>
			<? } ?>
			<h3>Material output breakdown:</h3>
			
			<?
			$color_mass = array('535B39','576033','576325','5D6325','4D512A','464925','3D3F26','4A4C32','4A513A','444F2D','3E4C20','525933','3A4229');
			$i = 0;
			if(isset($parts['Output']["Mass"]) == true) {
			foreach ($parts['Output']["Mass"] as $mass) {
					$width = round(100*$mass['amount']/$totaloutput);
					if ($width == 0) { $width = 1; }
					echo '<div class="bar_background"><div style="height:20px; width:'.$width.'%;background-color:#'.$color_mass[$i].';"></div></div>';
					echo "<div class='flow_text'><p><amount>" . round($mass['amount'],6) . "</amount> " . linkThis($mass['unit'], $parts["tooltips"]); 
					echo "<b> ".$mass['name'] . "</b></p></div>";
					$i++;
					if ($i >12){ $i = 0;}
			}}?>
			<?}?>
			<? 
			if (isset($parts['Output']["Liquid Volume"]) == true) {
				foreach ($parts['Output']["Liquid Volume"] as $volume) {
					$height = $volume['amount']*30;
					if ($height < 30) { 
						$height = 30; 
					}
					echo '<div class="bar_background"><div style="height:'.$height.'%;background-color:#002caa;"></div></div>';
					echo "<div class='flow_text'><p><amount>" . $volume['amount'] . "</amount> " . linkThis($volume['unit'], $parts["tooltips"]); 
					echo "<b> ".$volume['name'] . "</b></p></div>";
				}
			} ?>

			</div>					

			<? if (is_array($parts['geography']) == true ) {
				echo '<div id="map"><h2>Geography</h2>';
				
				foreach ($parts['geography'] as $geo) {
						echo '<p>Located in: <b>'.$geo['name'].'</b></p>';
						$map = "http://maps.google.com/maps/api/staticmap?sensor=false&size=400x400&center=".$geo['lat'].','.$geo['long']."&zoom=4&style=feature:road.local%7Celement:geometry%7Chue:0x00ff00%7Csaturation:100&style=feature:landscape%7Celement:geometry%7Clightness:-100&style=feature:poi.park%7Celement:geometry%7Clightness:-100";
						echo '<img src="'.$map.'" alt="'.$geo['name'].'"/>';
					}
				echo "</div>";
			 } ?>

			<div id="lca_meta">
			<h2>Reference</h2>
			<?
				foreach ($parts['bibliography'] as $record) {
					echo "<a href=\"" . $record['uri'] . "\" target='_blank'>";
					foreach ($record['authors'] as $author) {
						echo $author['lastName'] . ", " .$author['firstName'] . ";";
					}
					echo " ; " . $record["title"];
					echo "</a>";
				}
			?>
			<br/><br/>
			<h2>Export</h2>
			<p><?

				if(isset($links) == true) {

					echo $links;

				}

			?></p>
			<h2>Share</h2>
			<?php
			# Get the actual URL
				$thispage = "http://" . $_SERVER['HTTP_HOST']  . $_SERVER['REQUEST_URI'];
			?>
			<ul id="share-options">			
				<li id="facebook_share"><p>
					<?php 
					echo "<a href='http://facebook.com/sharer.php?u=";
					echo $thispage;
					echo "&t=Check+out+the+footprint+of+";
					echo $parts['quantitativeReference']['name'];
					echo "'";
					echo 'target="_blank">Post this to Facebook</a>';
					?></p></li>
				<li id="twitter_share"><p>
					<?php
					echo '<a href="http://twitter.com/home?status=Check+out+the+footprint+of+';
					echo $parts['quantitativeReference']['name'];
					echo "+at+";
					echo $thispage;
					echo '" target="_blank">Post this to Twitter</a>';
					?></p></li>
				<li id="linkedin_share"><p>
					<?php
					echo '<a href="http://www.linkedin.com/shareArticle?mini=true&url=';
					echo $thispage;
					echo "&title=";
					echo $parts['quantitativeReference']['name'];
					echo '" target="_blank">Post this to Linkedin</a>';
					?></p></li>
				<li id="delicious_share"><p><a href="http://delicious.com/save" onclick="window.open('http://delicious.com/save?v=5&noui&jump=close&url='+encodeURIComponent(location.href)+'&title='+encodeURIComponent(document.title), 'delicious','toolbar=no,width=550,height=550'); return false;">Post this to Delicious</a></p></li>
			</ul>
			
			</div>
			
			<div id="lca_comments">
			<?

				if(isset($comment) == true) {

					include_once('parts/comments.php');

				}

			?>
			</div>
			
			

	</div>
			<?=$footerDisplay;?>
		</div>

		<?=$scripts;?>	
		</body>
	</html>