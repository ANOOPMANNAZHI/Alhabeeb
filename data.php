<?php
try {$dbuser = 'postgres';
$dbpass = 'P@ssw0rd';
$host = '172.68.2.26';
$dbname='plms_live_new';

$dbh = new PDO("pgsql:host=$host;dbname=$dbname", $dbuser, $dbpass, array(
PDO::ATTR_PERSISTENT => true));	
}catch (PDOException $e) {
echo "Error : " . $e->getMessage() . "<br/>";
die();
}
$i=0;
$sql = 'SELECT * FROm receiptpdc';
foreach ($dbh->query($sql) as $row) 
{
$id=	$row['receiptno'];
$email=	$row['deposit'];

$sql1 = "UPDATE receipts_generation SET receipts_generation_receipt_date='".$email."' WHERE receipts_generation_receipt_no='".$id."'";
$dbh->query($sql1);

}
//echo $i;
?>