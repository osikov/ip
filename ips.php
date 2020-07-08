<?php
    include ".inc.php";

    $fip = "";
    if (array_key_exists('fip',$_REQUEST))
        $fip = $_REQUEST['fip'];
    $fzone = "";
    if (array_key_exists('fzone',$_REQUEST))
        $fzone = $_REQUEST['fzone'];
    
echo "
<form method=post>
<table border=2 cellpadding=5>
<tr><td>IP: <input type=text name=fip value='$fip'></td>
<td>Zone: <input list='zones' name='fzone' value='$fzone'>";

echo "<datalist id='zones'>";
$res = $db->query("select distinct zone from ip");
while($row=$res->fetch_assoc())
{
    echo "<option value='{$row['zone']}'>";
}
echo "</datalist>";

echo "</td>
<td>Note: <input type=text name=fnote></td>
<td><input type=submit value='GO'></td>
</tr>
</table>
</form>
";

    $filter = "";
    if ($fip != "")
    {
        if (strpos($fip, "/") !== false)
        {
            $net=strtok($fip," /");
            $mask=strtok(" /");
            
            $filter = "ip&(~((1<<(32-$mask))-1))=inet_aton('$net')&(~((1<<(32-$mask))-1))";
        }
        else
        {
            $filter = "inet_ntoa(ip) like '$fip"."%'";
        }
    }

    if ($fzone != "")
    {
        if ($filter != "") $filter .= " and ";
        
        $filter .= "zone='$fzone'";
    }
    
    if ($filter != "" )
    {

    $res = $db->query("select inet_ntoa(ip) ip, zone, note from ip where $filter");
    echo "<table>";
    while($row = $res->fetch_assoc())
    {
        echo "<tr><td>{$row['ip']}</td><td>{$row['zone']}</td><td>{$row['note']}</td></tr>";
    }
    echo "</table>";
    
    }
?>