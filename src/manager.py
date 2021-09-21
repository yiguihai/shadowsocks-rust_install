#!/usr/bin/env python3
# -*- coding: utf-8 -*-

from util_core.utils import (env_get, envSetup, port_is_use, pause)
from util_core.ss import Shadowsocks_info_input as ss
from util_core.plugin import Obfs_plugin as obp
from util_core.plugin import V2ray_plugin as v2p
from util_core.plugin import Kcptun_plugin as kcp
#import util_core.ss as ss
"""
while True:
  a = input("身高:")
  if a.isdigit() and int(a) > 0 :
    break
"""
if __name__ == "__main__":
    #print("编写中还没有完成")
    #pause()
    #envSetup()
    #subprocess.Popen("pwd", shell=True).wait()
    #print(env_get('PATH'))
    #print(ss.__doc__)
    #print(obp().get_obfs())
    print(kcp().get_crypt())
