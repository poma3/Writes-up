# DISKO 4

- tìm thấy file bị xóa là dont-delete.gz
    
    ```jsx
    ┌──(phong㉿kali)-[~/Downloads]
    └─$ fls disko-4.dd 4
    d/d 22: private
    d/d 24: sysstat
    d/d 26: stunnel4
    d/d 28: mysql
    d/d 30: inetsim
    d/d 32: installer
    r/r 519123:     vmware-vmsvc-root.2.log
    ...
    r/r 603186:     dpkg.log.2.gz
    r/r * 532021:   dont-delete.gz
    ```
    
- tương tự như bài 3
    
    ```jsx
    ┌──(phong㉿kali)-[~/Downloads]
    └─$ icat disko-4.dd 532021 > flag.gz
    
    ┌──(phong㉿kali)-[~/Downloads]
    └─$ gunzip flag.gz
    
    ┌──(phong㉿kali)-[~/Downloads]
    └─$ cat flag
    Here is your flag
    picoCTF{d3l_d0n7_h1d3_w3ll_31fd9de3}
    ```
    

`picoCTF{d3l_d0n7_h1d3_w3ll_31fd9de3}`