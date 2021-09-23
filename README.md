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
    <caption><i><b>2021年09月23日 16:22:05</b></i></caption>
    <thead>
      <tr>
        <th>项目</th>
        <th>更新详情</th>
      </tr>
    </thead>
    <tbody>
      <tr><td>shadowsocks-rust</td><td><a href=https://github.com/shadowsocks/shadowsocks-rust/commit/afb94fced0ac6624fcb15d8e30bb9818427f21cf>📛 OOCv1: name -> remarks</a></td></tr>
    </tbody>
  </table>
</details>
