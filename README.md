[![build](https://github.com/yiguihai/shadowsocks_install/actions/workflows/build.yml/badge.svg?branch=dev)](https://github.com/yiguihai/shadowsocks_install/actions?query=branch:dev)   
更多介绍与教程请查看[wiki](https://github.com/yiguihai/shadowsocks_install/wiki)   
### 使用方法
安装脚本
```Shell
wget --no-check-certificate -O /usr/local/bin/ss-main https://github.com/yiguihai/shadowsocks_install/raw/dev/usr/bin/ss-main  
chmod +x /usr/local/bin/ss-main
```
安装脚本(CDN)
```Shell
wget --no-check-certificate -O /usr/local/bin/ss-main https://cdn.jsdelivr.net/gh/yiguihai/shadowsocks_install@dev/usr/bin/ss-main
chmod +x /usr/local/bin/ss-main
```
运行脚本
```Shell
ss-main
```
查看状态
```Shell
systemctl status ss-main
```
取消开机自启
```Shell
systemctl disable ss-main
```
<details open>
  <summary>更新记录</summary>
  <table>
    <caption><i><b>2021年09月22日 00:33:00</b></i></caption>
    <thead>
      <tr>
        <th>项目</th>
        <th>更新详情</th>
      </tr>
    </thead>
    <tbody>
      <tr><td><a href=https://quic.nginx.org>nginx-quic</a></td><td><a href=https://hg.nginx.org/nginx-quic/rev/65191b9151a9>Configure: USE_OPENSSL_QUIC=YES implies USE_OPENSSL=YES.</a></td></tr>
<tr><td>v2ray-plugin</td><td><a href=https://github.com/teddysun/v2ray-plugin/commit/3ff2ec62aa479334d166d5d973edcfc2fd25ed42>Update comments</a></td></tr>
<tr><td>shadowsocks-rust</td><td><a href=https://github.com/shadowsocks/shadowsocks-rust/commit/5029279f7243f1ebccb04008626c44c830990118>Partially support OOCv1 shadowsocks server block</a></td></tr>
    </tbody>
  </table>
</details>
