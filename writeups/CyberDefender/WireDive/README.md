# WireDive

#### Scenario

WireDive is a combo traffic analysis exercise that contains various traces to help you understand how different protocols look on the wire where you can evaluate your DFIR skills against an artifact you usually encounter in today's case investigations as a security blue team member.

#### Challenge Files:

- dhcp.pcapng
- dns.pcapng
- https.pcapng
- network.pcapng
- secret_sauce.txt
- shell.pcapng
- smb.pcapng

#### File: dhcp.pcapng

- các gói tin dhcp cơ bản: D-O-R-A
    - DHCP Discover: client gửi quảng bá để tìm các dhcp server 0.0.0.0 → 255.255.255.255
    - DHCP Offer: server phản hồi lại cho client để đề xuất một cấu hình ip cụ thể.
    - DHCP Request: client quảng bá để chấp nhận đề xuất từ dhcp server đã chọn và thông báo cho các dhcp server khác.
    - DHCP ACK/NAK: server gửi cho client để chấp nhận hoặc từ chối cấp phát IP.

> *Q1: What IP address is requested by the client?*
> 
- trong bài này thì mình filter ra DHCP

![image.png](image.png)

- có gói tin DHCP release gửi từ client là thông báo cho dhcp server để giải phóng IP này. Dưới đó là các gói từ server lại đề xuất cho client để cấp phát IP và chấp nhận ACK.

`192.168.2.244`

> *Q2: What is the transaction ID for the DHCP release?*
> 

`0x9f8fa557`

> *Q3: What is the MAC address of the client?*
> 
- địa chỉ MAC của client và server nằm trong tầng data link

![image.png](image%201.png)

#### File: dns.pcapng

> *Q4: What is the response for the lookup for **flag.fruitinc.xyz**?*
> 

![image.png](image%202.png)

- mình thấy có query bản ghi TXT và có response cho bản ghi TXT này, bản ghi TXT chứa dữ liệu text.

![image.png](image%203.png)

`ACOOLDNSFLAG`

> *Q5: Which root server responds to the **google.com** query? Hostname.*
> 

![image.png](image%204.png)

- gói tin 1 là query các root nameserver, sau đó được phản hồi lại danh sách các root nameserver
- sau đó client gửi query A [google.com](http://google.com) tới root nameserver là 192.203.230.10, sau đó mình lên [checkip.com.vn](http://checkip.com.vn) để kiểm tra hostname của 192.203.230.10

![image.png](image%205.png)

#### File: smb.pcapng

- Các gói tin cơ bản của SMB:
    - NEGOTIATE (Request/Response): client và server đàm phán phiên bản,… `0`
    - SESSION_SETUP (Request/Response):                                                        `1`
    - TREE_CONNECT (Request/Response): kết nối vào ổ đĩa/thư mục chia sẻ. `3`
    - TREE_DISCONNECT (Request/Response): ngắt kết nối khỏi thư mục chia sẻ `4`
    - CREATE (Request/Response): tạo mới hoặc mở một file/folder có sẵn.     `5`
    - READ (Request/Response): đọc file.                                                            `8`
    - WRITE (Request/Response): ghi dữ liệu vào file.                                         `9`
    - CLOSE (Request/Response): đóng file.                                                         `6`

> *Q6: What is the path of the file that is opened?*
> 
- mình filter mã lệnh để đọc file của smb là cmd = 8

![image.png](image%206.png)

`HelloWorld\TradeSecrets.txt`

> *Q7: What was the hex status code when the user **SAMBA\jtomato** logs in?*
> 
- filter các gói tin session setup để tìm **SAMBA\jtomato**

![image.png](image%207.png)

- sau khi session setup request thì server sẽ response một mã status, mình kiểm tra gói tin ngay sau đó là gói tin logon failure

![image.png](image%208.png)

`0xc000006d`

> *Q8: What is the tree that is being browsed?*
> 
- filter gói tin TREE_CONNECT bằng cmd = 3

![image.png](image%209.png)

- IPC$ là thư mục ảo ẩn dành cho các dịch vụ quản trị.

`\\192.168.2.10\public`

> *Q9: What is the flag in the file?*
> 
- download file TradeSecrets.txt và tìm được flag

`OneSuperDuperSecret`

#### **File: shell.pcapng**

> *Q10: What port is the shell listening on?*
> 

![image.png](image%2010.png)

- mình thấy ngay những gói tcp hand shark từ client kết nối tới server mở tại port 4444

`4444`

> *Q11: What is the port for the second shell?*
> 
- follow luôn stream của hội thoại này

![image.png](image%2011.png)

- mình thấy attacker có tải thêm netcat vào máy victim
- sau đó họ tạo một cổng mạng tại 9999 và truyền dữ liệu của /etc/password, khi attacker kết nối tới port này thì dữ liệu của /etc/password sẽ tự được truyền lên máy attacker.

`9999`

> *Q12: What version of netcat is installed?*
> 

![image.png](image%2012.png)

`1.10-41.1`

> *Q13: What file is added to the second shell*
> 

`/etc/passwd`

> *Q14: What password is used to elevate the shell?*
> 
- trong shell port 4444 mình đã thấy được attacker truyền password này để dùng sudo

![image.png](13b2dfcc-b7a8-448f-90df-4654041f8649.png)

`*umR@Q%4V&RC`

> *Q15: What is the codename of the target system's OS version?*
> 

![image.png](image%2013.png)

`bionic`

> *Q16: How many users are on the target system?*
> 
- follow theo stream của shell thứ 2 mình thấy được nó đã hiển thị nội dung của /etc/password

![image.png](5e7c344f-ef65-4e05-87a8-545cfab5852f.png)

- mỗi user là một dòng, tổng `31` dòng.

**File: network.pcapng**

> *Q17: What is the IPv6 NTP server IP?*
> 
- NTP- Network Time Protocol: là giao thức đồng bộ thời gian trên các máy, các máy sẽ gửi yêu cầu đến NTP server để đồng bộ thời gian.

![image.png](image%2014.png)

`2003:51:6012:110::dcf7:123`

> *Q18: What is the first IP address that is requested by the DHCP client?*
> 

![image.png](image%2015.png)

- client đã request một ip và bị từ chối, mở gói tin 1245 ra xem sẽ thấy được ip mà client đã yêu cầu.

![image.png](image%2016.png)

> *Q19: What is the first authoritative name server returned for the domain that is being queried?*
> 

![image.png](image%2017.png)

- mình thấy client query [blog.webernetz.net](http://blog.webernetz.net) tới dns server cục bộ và được response một bản ghi A cùng với các Authoritative name server khác, authorative name server là một dns server quản lý trực tiếp tên miền mà client vừa query.

`ns1.hans.hosteurope.de`

> *Q20: What is the number of the first VLAN to have a topology change occur?*
> 
- STP- Spanning Tree Protocol: giao thức để ngăn chặn vòng lặp trong mạng dùng switch. Khi một cổng trên switch thay đổi trạng thái, nó sẽ báo cho switch root cập nhật lại sơ đồ định tuyến (Topology) trên toàn hệ thống mạng.
- filter stp.flags.tc == 1 để lọc ra Topology Change: yes

![image.png](image%2018.png)

> *Q21: What is the port for CDP for **CCNP-LAB-S2**?*
> 
- CDP-Cisco Discovery Protocol: là giao thức độc quyền của Cisco để chia sẻ thông tin giữa các máy (như switch, Router, IP Phone, Firewall) và tự động khám phá ra các thiết bị Cisco khác đang cắm chung cây cáp.

![image.png](image%2019.png)

`GigabitEthernet0/2`

> *Q22: What is the MAC address for the root bridge for **VLAN 60**?*
> 
- filter vlan.id == 60 để lọc ra các vlan 60

![image.png](image%2020.png)

- toàn bộ đều là giao thức STP, kiểm tra nội dung gói tin

![image.png](image%2021.png)

- Root Identifier là định danh của switch root

`00:21:1b:ae:31:80`

> *Q23: What is the IOS version running on **CCNP-LAB-S2**?*
> 
- IOS-Internetwork Operating System: là một hệ điều hành để chạy các thiết bị mạng
- filter ra giao thức CDP

![image.png](image%2022.png)

`12.1(22)EA14`

> *Q24: What is the virtual IP address used for HSRP group 121?*
> 
- HSRP-Hot Standby Router Protocol: là giao thức dự phòng gateway, nó kết hợp nhiều router thành một vitural ip, các máy tính trong mạng chỉ cần cấu hình default gateway là vitural ip và các con router sẽ tự tính toán nên đi qua router nào.

filter hsrp2.group == 121 

![image.png](image%2023.png)

`192.168.121.1`

> *Q25: How many router solicitations were sent?*
> 
- router soliciations là gói tin yêu cầu định tuyến trên ipv6, thay thế cho dhcp trên ipv4.
- khi một thiết bị mới vào mạng thì nó sẽ chủ động gửi request để xin cấp ip
- gói tin này chạy trên icmpv6, nên mình filter icmpv6.type == 133,

![image.png](image%2024.png)

`3`

> *Q26: What is the management address of **CCNP-LAB-S2**?*
> 

```jsx
cdp contains "CCNP-LAB-S2"
```

![image.png](image%2025.png)

`192.168.121.20`

> *Q27: What is the interface being reported on in the first SNMP query?*
> 
- SNMP-Simple Network Management Protocol: là giao thức quản lý mạng đơn giản, chủ yếu đến admin cấu hình mạng trên các thiết bị mạng từ xa.

![image.png](image%2026.png)

- gói get-request là snmp managerment yêu cầu snmp agent gửi thông tin cấu hình

![image.png](image%2027.png)

![image.png](image%2028.png)

`FA0/1`

> *Q28: When was the NVRAM config last updated?*
> 
- NVRAM-Non-Volatile Random Access Memory: là bộ nhớ không khả biến thường lưu những cấu hình startup.
- ban đầu mình nghĩ đến giao thức snmp để quản lý các thiết bị mạng, nhưng mình tìm không ra.
- sau đó mình nghĩ đến giao thức tftp cũng thường dùng để lấy và nạp cấu hình vào NVRAM trên các thiết bị mạng.

![image.png](image%2029.png)

- mình thấy admin gửi cấu hình đến thiết bị mạng, sau đó thiết bị mạng gửi lại các thông tin xác nhận. Mình sẽ follow stream của thiết bị mạng gửi lại.

![image.png](f1b3f905-9f3d-45b7-acc4-40af7be5dbb6.png)

`2017-03-03 21:02`

> *Q29: What is the IPv6 of the RADIUS server?*
> 
- RADIUS-Remote Authentication Dial-In User: giao thức sử dụng máy chủ trung tâm để quản lý Authentication, Authorization, and Accounting (AAA) các thiết bị trong mạng.
- giao thức này chạy trên udp

![image.png](image%2030.png)

- follow udp stream thì mình thấy đoạn cấu hình radius server

![image.png](image%2031.png)

`2001:DB8::1812`

#### **File: https.pcapng**

bài này sử dụng https và trong file có secret_souce.txt, mình cần đưa file này vào TLS trên ws

> *Q30: What has been added to web interaction with **web01.fruitinc.xyz**?*
> 

![image.png](image%2032.png)

- follow http stream

![image.png](image%2033.png)

`y2*Lg4cHe@Ps`

> *Q31: What is the name of the photo that is viewed in slack?*
> 

![image.png](image%2034.png)

![image.png](image%2035.png)

`get_a_new_phone_today__720.jpg`

> *Q32: What is the username and password to login to 192.168.2.1?*
> 

![image.png](image%2036.png)

- tìm kiếm với từ khóa “password” thì mình nhận được cái gói tin form-urlencoded, là những form do người dùng gửi lên với định dạng url encode.
- follow http2 stream

![image.png](image%2037.png)

`admin:Ac5R4D9iyqD5bSh`

> *Q33: What is the certStatus for the certificate with a serial number of **07752cebe5222fcf5c7d2038984c5198**?*
> 

![image.png](image%2038.png)

- mình thấy có giao thức OCSP-Online Certificate Status Protocol: dùng để kiểm tra tính hợp lệ của chứng chỉ số theo thời gian thực.
- các gói request để kiểm tra tính hợp lệ của chứng chỉ, các gói response sẽ chứa tính hợp lệ đó là certStatus

![image.png](image%2039.png)

![image.png](image%2040.png)

`good`

> *Q34: What is the email of someone who needs to change their password?*
> 

![image.png](image%2041.png)

- có form khả năng là thay đổi email

![image.png](image%2042.png)

`jim.tomato@fruitinc.xyz`

> *Q35: A service is assigned to an interface. What is the interface, and what is the service?*
> 

![image.png](image%2043.png)

![image.png](image%2044.png)

- mình thấy ở đây có thể là các form, mỗi input cách nhau bởi chuỗi số kia
- mình thấy có input có name là interface và nội dung là lan, tức là interface ở đây là lan
- service là NTP-Network Time Protocol

`lan:ntp`