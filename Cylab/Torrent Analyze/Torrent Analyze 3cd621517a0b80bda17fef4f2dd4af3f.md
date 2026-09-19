# Torrent Analyze

- torrent là giao thức P2P dùng để chia sẻ file trực tiếp từ các người dùng với nhau thay vì từ một server
    - peers: các máy tham gia và chia sẻ file
    - leechers: máy đang cần tải đầy đủ file
    - seeds: máy đã tải đầy đủ file và cần share cho những máy chưa tải đầy đủ file đó (leechers).
- các gói tin trong torrent:
    - get_peers: đây là gói một leechers muốn hỏi ai đang có file này không, file đó được biểu diễn bằng info_hash.
    - annouce_peer: gói này bắt đầu tải file khi đã tìm thấy peer có file này, gói này cũng chứa info_hash.
    - find_node: gói này để tìm ip của các máy lân cận nhằm mở rộng phạm vi tìm kiếm.
    - ping: kiểm tra trạng thái
- lọc giao thức torrent.

![image.png](Torrent%20Analyze/image.png)

- máy 192.168.73.132 đang tìm kiếm rất nhiều máy trên internet với info_hash là e2467cbf021192c241367b892230dc1e05c0580e
- tìm kiếm info_hash này trên google thì ra được đây là file [**ubuntu-19.10-desktop-amd64.iso**](https://elemental-unicorn.medium.com/picoctf-2022-forensics-torrent-analyze-daba843dc8f3). [[1](https://hackmd.io/@SBK6401/BynwYfvxT)]

`picoCTF{ubuntu-19.10-desktop-amd64.iso}`