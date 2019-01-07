<?php
/*echo $source = "0-VRFL-VJIT";
$res = explode('-', $source, 2);
print_R($res);*/#EEF5F7
$date = '3-5-2017';
echo date('Y-m-d', strtotime($date));
exit;

$string = "one_two_abcd_three_four";
$parts = explode('_', $string);
$last = array_pop($parts);
$parts = array(implode('_', $parts), $last);

print_R($parts);

echo $ipv6 = '2405:200:105:200:7:2:601:4F03';
            echo '<br>'.$ipv4 = '49.44.1.77';
            $interface_ipv6 = CommonUtility::getCSSInterfaceIpv6Generation($ipv6 , $ipv4);
            echo '=='.$interface_ipv6;
            exit;
			
			$requestData['syslog_pri_ipv6_addr']
			$routerData['switch_loopback']
			$interface_ipv6 = CommonUtility::getCSSInterfaceIpv6Generation($requestData['syslog_pri_ipv6_addr'] , $routerData['switch_loopback']);
			
			
			$interface_ipv6 = CommonUtility::getCSSInterfaceIpv6Generation($requestData['syslog_pri_ipv6_addr'] , $routerData['switch_loopback']);
                        $westIntArr['{L3-WEST-RANWAN-IPV6}'] = $interface_ipv6;
?>