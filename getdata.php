<?php
$result = $connection->query("select * from company");
echo "<ol>";
while ($row = $result->fetch()) {
	echo "<li>";
	echo $row["name"]."</li>";
}
echo "</ol>";
?>