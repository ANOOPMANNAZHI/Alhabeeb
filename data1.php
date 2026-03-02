<?php
try {
$dbuser = 'postgres';
$dbpass = 'P@ssw0rd';
$host = '172.68.0.26';
$dbname='plms_live_new';
//$dbuser = 'postgres';
//$dbpass = 'postgres';
//$host = 'localhost';
//$dbname='plms23102020';


$dbh = new PDO("pgsql:host=$host;dbname=$dbname", $dbuser, $dbpass, array(
PDO::ATTR_PERSISTENT => true));	
}catch (PDOException $e) {
echo "Error : " . $e->getMessage() . "<br/>";
die();
}
$i=0;
$arry=array(12417,12424,12419,12431,12432,12433,12434,12435,12436,12439,12445,12486,12485,12475,12528,12450,12506,12549,12454,12487,12548,12514,12489,12457,12556,12519,12458,12484,12459,12460,12503,12461,12462,12463,12533,12465,12534,12466,12467,12535,12468,12532,12479,12469,12536,12493,12512,12557,12513,12524,12561,12525,12456,12526,12562,12563,12383,12392);
$arry1=array(73,40,73,40,40,40,40,40,40,47,73,73,73,40,40,47,40,47,112,112,112,73,112,47,40,40,47,73,112,47,47,112,112,47,47,112,47,112,112,45,112,73,45,47,45,73,40,40,40,47,40,112,73,112,112,112,47,47);
//$sql = 'SELECT * FROm receiptpdc';
foreach ($arry as $row) 
{

//	echo $row; exit;
//$id=	$row['receiptno'];
//$email=	$row['deposit'];
echo $sql="INSERT INTO public.sales (sales_enquiry_id,sales_type,work_flow_processes_code,created_by,created_at,sales_notes,refer_back) VALUES (".$row.",1,108,85,'2020-10-23 16:42:25','Approved',0) RETURNING id";
$dbh->query($sql);
$x=$dbh->lastInsertId();
$y= $arry1[$i];
//$sql1=select nextval('sales_id_seq');
//foreach ($dbh->query($sql1) as $row1) 
//{
//echo $x=$row1;
//}

//$sql1 = "UPDATE receipts_generation SET receipts_generation_receipt_date='".$email."' WHERE receipts_generation_receipt_no='".$id."'";
//$dbh->query($sql1);
//SELECT currval('sales_id_seq');
  echo $x1= "INSERT INTO public.sales_users (sales_id, user_id, role_id, status) values (".$x.",".$y.",2,1)";
   $dbh->query($x1);
echo	$x2="INSERT INTO public.sales_users (sales_id, user_id, role_id, status) values (".$x.",null,5,1)";
	$dbh->query($x2);
echo	$x3="INSERT INTO public.sales_users (sales_id, user_id, role_id, status) values (".$x.",null,6,1)";
	$dbh->query($x3);
echo	$x4="INSERT INTO public.sales_users (sales_id, user_id, role_id, status) values (".$x.",null,7,1)";
	$dbh->query($x4);
	echo $x5="INSERT INTO public.sales_users (sales_id, user_id, role_id, status) values (".$x.",null,8,1)";
	$dbh->query($x5);
$i++;
echo $i; 
}
//echo $i;
?>