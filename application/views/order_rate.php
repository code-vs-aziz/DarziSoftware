<?php  

//echo $_POST['Design'];
if(isset($_POST['Design']))
{
$design = $_POST['Design'];
foreach($style as $styles){
 	 	if($styles->DNO ==  $design)
		{
	 	echo $styles->rate;
		}
		else
		{
		echo '0';
		}
		
}
}
elseif(isset($_POST['embroidery']))
{
	$embroideryno = $_POST['embroidery'];
foreach($embroidery as $embroidery){
 	 	if($embroidery->ENO ==  $embroideryno)
		{
	 	echo $embroidery->rate;
			}
			else
		{
		echo '0';
		}
}
}
elseif(isset($_POST['suit']))
{
	 
	$suitno = $_POST['suit'];
foreach($suit as $suit){
 	 	if($suit->SNO ==  $suitno)
		{
	 	echo $suit->rate;
	}
		else
		{
		echo '0';
		}
}
}

?>