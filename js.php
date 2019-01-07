<p>interface Loopback300<br />
 description # {ag1-hostname}-PTP MASTER #<br />
 ip address {ag1-loopback-300} 255.255.255.255<br />
 no ip redirects<br />
 no ip proxy-arp<br />
 ip mask-reply<br />
 no shutdown<br />
!<br />
interface Loopback301<br />
 description # {ag1-hostname}-PTP SLAVE #<br />
 ip address {ag1-loopback-301} 255.255.255.255<br />
 no ip redirects<br />
 no ip proxy-arp<br />
 ip mask-reply<br />
 no shutdown<br />
!<br />
{BLOCK-SEPARATOR}
interface TenGigabitEthernet{ag1-port-towards-master}<br />
 synchronous mode<br />
!<br />{interface-port-slave}
{BLOCK-SEPARATOR}
network-clock revertive<br />
network-clock synchronization automatic<br />
network-clock synchronization mode QL-enabled<br />
network-clock synchronization input-threshold QL-PRC<br />
network-clock input-source 1 interface TenGigabitEthernet{ag1-port-towards-master}<br />{network-clock-ag1-slave}
network-clock wait-to-restore 10 global<br />
network-clock log ql-changes<br />
!<br />
{BLOCK-SEPARATOR}
esmc process<br />
!<br />
<p>snmp-server enable traps netsync</p>
<p>snmp-server enable traps ptp</p>
<p>!</p>
{BLOCK-SEPARATOR}
platform ptp hybrid-bc downstream-enable<br />                  
{PTP-ES4-CONFIG}<br />
!<br />
ptp clock boundary domain 0 hybrid<br />
 output 1pps R0<br />
 time-properties persist 0<br />
 min-clock-class 7<br />
 clock-port AG1_Slave slave<br />
 delay-req interval -6<br />
 sync interval -6<br />
 transport ipv4 unicast interface Loopback301 negotiation<br />
 clock source {ag1-master-300-ip} 1<br />
 clock-port AG1_master master<br />
 sync interval -6<br />
 transport ipv4 unicast interface Loopback300 negotiation<br />
!<br />
{BLOCK-SEPARATOR}
{ip-route-from-ag1-slave}{ip-route-from-ag1-master}{ip-route-from-css-slave}
</p>
