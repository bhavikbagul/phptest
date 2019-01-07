<?php
$finalNipString = '<p>interface GigabitEthernet0/0/8</p>' . PHP_EOL;
                $finalNipString .= '<p>description # To eNode-B-2 #</p>' . PHP_EOL;
                $finalNipString .= '<p>Dampening</p>' . PHP_EOL;
                $finalNipString .= '<p>mtu 9216</p>' . PHP_EOL;
                $finalNipString .= '<p>no ip address</p>' . PHP_EOL;
                $finalNipString .= '<p>no ip redirects</p>' . PHP_EOL;
                $finalNipString .= '<p>no ip unreachables</p>' . PHP_EOL;
                $finalNipString .= '<p>load-interval 30</p>' . PHP_EOL;
                $finalNipString .= '<p>media-type sfp</p>' . PHP_EOL;
                $finalNipString .= '<p>no shutdown</p>' . PHP_EOL;
                $finalNipString .= '<p>negotiation auto</p>' . PHP_EOL;
                $finalNipString .= '<p>service-policy input RJIL-QOS-ENB-UNI-IN-PARENT</p>' . PHP_EOL;
                $finalNipString .= '<p>service-policy output RJIL-QOS-ENB-UNI-OUT-PARENT</p>' . PHP_EOL;
                $finalNipString .= '<p>service instance 105 ethernet</p>' . PHP_EOL;
                $finalNipString .= '<p>description # To eNode-B-2 - R4G_Bearer #</p>' . PHP_EOL;
                $finalNipString .= '<p>encapsulation dot1q 105</p>' . PHP_EOL;
                $finalNipString .= '<p>rewrite ingress tag pop 1 symmetric</p>' . PHP_EOL;                
                $finalNipString .= '<p>bridge-domain 105</p>' . PHP_EOL;                
                $finalNipString .= '<p>!</p>' . PHP_EOL;
				
				echo $finalNipString;
				echo $gigport = addslashes('interface "GigabitEthernet0/0/8');
				echo $gigport = str_replace('/','fwdslash','interface GigabitEthernet0/0/8');
	if(preg_match("/$gigport/",str_replace('/','fwdslash',$finalNipString), $matches))
echo 'ddd';
else
echo 'fff';
	
?>