<?php
$con=mysqli_connect("localhost","root","mysqlpass","collca");

$filter = file_get_contents('php://input');

$query = <<<EOT
	select URTON_KHIPU_TYPE.INVESTIGATOR_NUM as UID, KHIPU_MAIN.NICKNAME, CORD_COUNT, KHIPU_MAIN.REGION,
	KHIPU_MAIN.PROVENANCE, x.CORD_ID, PENDANT_FROM as PARENT, cord.CORD_CLASSIFICATION as CLASS, x.VALUE,
	x.KNOTS, FULL_COLOR as COLOR, TWIST, FIBER, TERMINATION, ATTACHMENT_TYPE as ATTACHMENT, CORD_LENGTH as LENGTH,
	DECIMAL_NUM, CENSUS, ANOMALOUS, POSITIONAL, BANDED, SERIATED, SUMMING_INTERNAL, MATCHING_INTERNAL, LARGE_VALUES
	from (select * from (select CORD_ID as CID, sum(KNOT_VALUE_DECIMAL) as VALUE, GROUP_CONCAT(DIRECTION, TYPE_CODE,
	KNOT_VALUE_DECIMAL ORDER BY KNOT_ID) AS KNOTS from KNOT GROUP BY CORD_ID) as a RIGHT JOIN (select CORD_ID
	from cord group by CORD_ID) as b ON CID=b.CORD_ID) as x, (select KHIPU_ID, count(*) as CORD_COUNT from cord
	group by KHIPU_ID) as counts, ascher_cord_color, cord, KHIPU_MAIN, URTON_KHIPU_TYPE where x.CORD_ID =
	ascher_cord_color.CORD_ID and x.CORD_ID = cord.CORD_ID and cord.KHIPU_ID = KHIPU_MAIN.KHIPU_ID and cord.KHIPU_ID
	= counts.KHIPU_ID and KHIPU_MAIN.KHIPU_ID = URTON_KHIPU_TYPE.KHIPU_ID $filter limit 10000;
EOT;

if (mysqli_connect_errno()) {
	echo "Failed to connect to MySQL: " . mysqli_connect_error();
}
// if (!isset($_POST["query"])) {
// 	$query = file_get_contents('php://input');
// } else {
// 	$query = $_POST['query'];
// }

//echo $query;

$result = mysqli_query($con,$query);

$rows = array();
while($r = mysqli_fetch_assoc($result)) {
	$r['CORD_ID'] = (int) $r['CORD_ID'];
	$r['PARENT'] = (int) $r['PARENT'];
	$r['VALUE'] = (int) $r['VALUE'];
	$r['CORD_COUNT'] = (int) $r['CORD_COUNT'];
	$r['LENGTH'] = (float) $r['LENGTH'];
	$rows[] = $r;
}

echo json_encode($rows);

mysqli_close($con);
?>;
