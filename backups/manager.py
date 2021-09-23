#!/usr/bin/env python3
# -*- coding: utf-8 -*-
'''ss-main从Bash版移植到Python版'''
'''
from util_core.utils import env_get as abc
表示从当前路径的的util_core目录导入utils.py文件中的函数env_get别名设置为abc
import abc 
表示导入当前目录的abc.py文件函数调用方式为abc.a()
'''
from util_core.utils import env_get, envSetup, port_is_use, pause, module_check, parsing_plugin_opts, random_num as ran_n
from util_core.shadowsocks import Shadowsocks_info_input as ss
from util_core.plugin import Obfs_plugin as obp, V2ray_plugin as v2p, Kcptun_plugin as kcp
from sys import argv
#import util_core.ss as ss
import subprocess


def main():
    #print("编写中还没有完成")
    #pause()
    #envSetup()
    #subprocess.Popen("pwd", shell=True).wait()
    result = subprocess.run("pwd",
                            shell=True,
                            check=True,
                            capture_output=True,
                            timeout=2,
                            universal_newlines=True)
    if 'python' in result.stdout:
        print(result.stdout)
    #print(env_get('PATH'))
    #print(ss.__doc__)
    #print(len(module_check()))
    #print(obp().get_obfs())
    #print(kcp().get_key())
    a = "\033[7;32;107m运行中\033[0m"
    #a = "\033[7;31;43m守护脚本未运行\033[0m"
    print('''\
=========== \033[1mShadowsocks-rust\033[0m 多端口管理脚本 by \033[{0};{1};{2}m爱翻墙的红杏\033[0m ===========
服务状态: {3}
  1. 用户列表->>
  2. 启动运行
  3. 停止运行
  4. 卸载删除
  5. 版本更新
  6. Language
  7. 高级功能->>'''.format(ran_n(1, 7), ran_n(30, 37), ran_n(40, 47), a))


if __name__ == "__main__":
    try:
        action = argv[1:][0]
    except IndexError as e:
        main()
    else:
        if action == 'start':
            Start()
        elif action == 'stop':
            Stop()
        elif action == 'restart':
            Stop()
            Start()
        elif action == 'daemon':
            Daemon()
        else:
            exit(1)
