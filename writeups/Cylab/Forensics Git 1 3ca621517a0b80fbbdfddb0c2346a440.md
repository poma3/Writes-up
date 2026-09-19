# Forensics Git 1

- kiểm tra git log
    
    ```jsx
    ┌──(phong㉿kali)-[/run/…/home/ctf-player/Code/secrets]
    └─$ git log
    commit 5fb8194539c770a830b8ba089a50778c07072b03 (HEAD -> master)
    Author: ctf-player <ctf-player@example.com>
    Date:   Wed Nov 19 09:20:05 2025 +0000
    
        Remove flag
    
    commit 177789af0b300e043ea8f54ea57d6cee352291ae
    Author: ctf-player <ctf-player@example.com>
    Date:   Wed Nov 19 09:20:05 2025 +0000
    
        Add flag
    ```
    
- dùng git show để xem phiên bản trước
    
    ```jsx
    ┌──(phong㉿kali)-[/run/…/home/ctf-player/Code/secrets]
    └─$ git show 177789a
    commit 177789af0b300e043ea8f54ea57d6cee352291ae
    Author: ctf-player <ctf-player@example.com>
    Date:   Wed Nov 19 09:20:05 2025 +0000
    
        Add flag
    
    diff --git a/flag.txt b/flag.txt
    new file mode 100644
    index 0000000..f150f47
    --- /dev/null
    +++ b/flag.txt
    @@ -0,0 +1 @@
    +picoCTF{g17_r3m3mb3r5_d4ddf904}
    \ No newline at end of file
    ```