<?php
ini_set('max_execution_time', '0');
error_reporting(E_ERROR | E_PARSE);
?><html>
<head>
<title>PHP E-mail verifier</title>
</head>
<body>
<h1 align="center">PHP E-mail verifier</h1>
<hr>
<?php
#error_reporting(E_ERROR | E_PARSE);
	require("email_validation.php");

	$validator=new email_validation_class;

	
	if(!function_exists("GetMXRR"))
	{
		
		$_NAMESERVERS=array();
		include("getmxrr.php");
	}
	

	$validator->timeout=10;

	$validator->data_timeout=0;

	$validator->localuser="info";

	$validator->localhost="phpclasses.org";

	$validator->debug=1;

	$validator->html_debug=1;

    $file = fopen($_POST["file"],"r");
	$email2=array();
	$j=0;
	while(! feof($file))
  {
	$email2[$j]=implode(fgetcsv($file));
	$j=$j+1;
  }  
    array_pop($email2);

##################################################################
function mailv($email,$validator,$fp)
		{
				if(($result=$validator->ValidateEmailBox($email))<0)
		{

			$ar=array($email,"INVALID");
			fputcsv($fp,$ar);
		}

		else
		{
			$result ? $ar=array($email,"VALID") : $ar=array($email,"INVALID");
			fputcsv($fp,$ar);
		}
		}
####################################################################	

$fp = fopen('persons.csv', 'w');
	
	$validator->exclude_address="";
	$validator->invalid_email_domains_file = 'invalidemaildomains.csv';
	$validator->invalid_email_servers_file = 'invalidemailservers.csv';
	$validator->email_domains_white_list_file = 'emaildomainswhitelist.csv';


	if(IsSet($email2))
	{

		$i=1;

		foreach ($email2 as $email) {
		mailv($email,$validator,$fp);
		echo "$i )  $email<br>";
		$i++;
		}
	}

$url = 'persons.csv'; 
	echo '<p><a href="download.php?file=' . urlencode($url) . '">Download</a></p>';
	
fclose($fp);
?>
<hr>
</body>
</html>
