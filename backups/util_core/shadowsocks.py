#!/usr/bin/env python3
# -*- coding: utf-8 -*-

from re import compile, fullmatch
from util_core.utils import (random_port, random_str, port_is_use)


class Shadowsocks_info_input:
    '''shadowsocks-rust参数输入'''
    def __init__(self):
        self.server_port = random_port(1024, 65535)
        self.password = random_str(16)
        self.method = [
            'none', 'aes-128-gcm', 'aes-256-gcm', 'chacha20-ietf-poly1305'
        ]
        self.total = 10240

    def get_server_port(self):
        while True:
            try:
                print('Enter a port number.')
                port = int(input('(Default: {0}): '.format(self.server_port)))
                if 1 <= port <= 65535:
                    if port_is_use(4, port) or port_is_use(6, port):
                        #这里还需要加入判断是否在用户列表中
                        print('This port is occupied.')
                        continue
                    return port
                else:
                    raise ValueError
            except ValueError:
                return self.server_port
                #print('This is NOT a VALID port number.')

    def get_password(self):
        while True:
            pattern = compile('[A-Za-z0-9]+')
            print('Enter a shadowsocks password.')
            passwd = input('(Default: {0}): '.format(self.password))
            if pattern.fullmatch(passwd) is not None:
                return passwd
            else:
                return self.password

    def get_method(self):
        while True:
            for index, method in enumerate(self.method):
                print("{}.{}".format(index + 1, method))
            try:
                choice = int(input('please select shadowsocks method:'))
                if 1 <= choice <= len(self.method):
                    return self.method[choice - 1]
                else:
                    raise ValueError
            except ValueError:
                print('input out of range!')

    def get_total(self):
        while True:
            try:
                print('Enter a value for the traffic limit (MB).')
                total = int(input('(Default: {0}): '.format(self.total)))
                if 1 <= total:
                    return total
                else:
                    raise ValueError
            except ValueError:
                return self.total
