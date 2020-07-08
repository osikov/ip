<?php
    include ".inc.php";

    function gen_ips($net, $bm)
    {
        $ips = array();

        if ($bm <= 0 || $bm > 32)
            return $ips;

        $mask = (1<<(32-$bm))-1;
        $ipl = ip2long($net) & (~$mask);
        
        for ($i = 0; $i <= $mask; $i++)
        {
            $ip = $ipl + $i;
            $ips[] = "".(($ip>>24)&0xff).".".(($ip>>16)&0xff).".".(($ip>>8)&0xff).".".($ip&0xff);
        }
        
        return $ips;
    }

    if (array_key_exists('del',$_REQUEST))
    {
        $st = $db->prepare("delete from ip where ip in (".
            "select ip.ip from ip join ".
            "(select inet_aton(net) net,(1<<(32-bitmask))-1 mask from network where id=?) t1".
            " on ip.ip&(~t1.mask)=t1.net".
            ")");
        $st->bind_param("i",$_REQUEST['del']);
        $st->execute();

        $st = $db->prepare("delete from network where id=?");
        $st->bind_param("i",$_REQUEST['del']);
        $st->execute();
        if ($st->affected_rows != 1)
        {
            show_error("Failed to delete network");
        }
    }

    if (array_key_exists('net',$_REQUEST))
    {
        $net_str = $_REQUEST['net'];
        $net = strtok($net_str, "/ ");
        $bm = strtok("/ ");
        $zone = $_REQUEST['zone'];

        $ips = gen_ips($net,$bm);
        if (count($ips) == 0)
        {
            show_error("Bad network");
            echo "<a href='{$_SERVER['PHP_SELF']}'>CONTINUE</a>";
            die();
        }
        foreach($ips as $ip)
        {
            $st=$db->prepare("insert into ip (ip,zone) values(inet_aton(?),?)");
            $st->bind_param("ss",$ip,$zone);
            $st->execute();
        }
    
        $st = $db->prepare("insert into network (net,bitmask,zone) values (?,?,?)");
        $st->bind_param("sis", $net, $bm, $zone);
        $st->execute();
        
        reload();
    }

    $res = $db->query("select * from network");
    echo "<table border=2 cellpadding=5>";
    echo "<tr><th>Network</th><th>Zone</th></tr>";
    while($row = $res->fetch_assoc())
    {
        echo "<tr><td><p>".$row['net']." / ".$row['bitmask']."</p></td>";
        echo "<td>".$row['zone']."</td>";
        echo "<td><a href='networks.php?del=".$row['id']."'>DELETE</a></td></tr>";
    }    
    echo "</table>";
?>
<br>
<form method=post action='networks.php'>
Network: <input type=text name=net value='x.x.x.x/x'> 
Zone: <input type=text name=zone value=''>
<input type=submit value='Add'>
</form>
