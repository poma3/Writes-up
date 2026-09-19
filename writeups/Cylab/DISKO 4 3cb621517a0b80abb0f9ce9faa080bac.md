# DISKO 4

- dùng fls tìm thấy file flag.gz
    
    ```jsx
    ┌──(phong㉿kali)-[~/Downloads]
    └─$ fls disko-3.dd
    d/d 4:  log
    v/v 3225859:    $MBR
    v/v 3225860:    $FAT1
    v/v 3225861:    $FAT2
    V/V 3225862:    $OrphanFiles
    
    ┌──(phong㉿kali)-[~/Downloads]
    └─$ fls disko-3.dd 4
    d/d 22: private
    d/d 24: sysstat
    d/d 26: stunnel4
    d/d 28: mysql
    d/d 30: inetsim
    d/d 32: installer
    r/r 519123:     vmware-vmsvc-root.2.log
    r/r 519125:     kern.log.4.gz
    r/r 519127:     Xorg.0.log
    r/r 519130:     vmware-network.4.log
    r/r 519132:     boot.log
    r/r 519134:     syslog.3.gz
    r/r 519137:     vmware-vmtoolsd-root.log
    r/r 522627:     daemon.log
    r/r 522628:     flag.gz
    ```
    
- mình sẽ dùng icat để trích xuất file này ra mà không cần mount file .img này vào máy
    
    ```jsx
    ┌──(phong㉿kali)-[~/Downloads]
    └─$ icat disko-3.dd 522628 > flag.gz
    
    ┌──(phong㉿kali)-[~/Downloads]
    └─$ gunzip flag.gz
    
    ┌──(phong㉿kali)-[~/Downloads]
    └─$ cat flag
    Here is your flag
    picoCTF{n3v3r_z1p_2_h1d3_7e0a17da}
    ```
    

`picoCTF{n3v3r_z1p_2_h1d3_7e0a17da}`