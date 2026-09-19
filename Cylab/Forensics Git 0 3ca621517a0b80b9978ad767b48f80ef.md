# Forensics Git 0

- mount vào và vào thư mục git
    
    ```jsx
    └─$ ls -la
    total 4
    drwxr-sr-x 3 phong phong 1024 Nov 19  2025 .
    drwxr-sr-x 3 phong phong 1024 Nov 19  2025 ..
    drwxr-sr-x 8 phong phong 1024 Nov 19  2025 .git
    -rw-r--r-- 1 phong phong  104 Nov 19  2025 note.txt
    ```
    
- xem lịch sử git
    
    ```jsx
    ┌──(phong㉿kali)-[/run/…/home/ctf-player/Code/secrets]
    └─$ git log
    commit 327681bb38cf467cec328eec9707b240e3e74ced (HEAD -> master)
    Author: ctf-player <ctf-player@example.com>
    Date:   Wed Nov 19 08:49:27 2025 +0000
    
        Wrap this phrase in the flag format: g17_1n_7h3_d15k_041217d8
    ```
    

`picoCTF{g17_1n_7h3_d15k_041217d8}`