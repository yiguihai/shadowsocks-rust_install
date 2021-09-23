#!/usr/bin/env python3
# -*- coding: utf-8 -*-

from re import compile, fullmatch
from util_core.utils import (random_port, random_str, port_is_use)


class Obfs_plugin:
    '''simple-obfs参数输入'''
    def __init__(self):
        self.obfs = ['http', 'tls']

    def get_obfs(self):
        while True:
            for index, obfs in enumerate(self.obfs):
                print("{}.{}".format(index + 1, obfs))
            try:
                choice = int(
                    input('Which network traffic obfuscation you\'d select:'))
                if 1 <= choice <= len(self.obfs):
                    return self.obfs[choice - 1]
                else:
                    raise ValueError
            except ValueError:
                print('input out of range!')


class Kcptun_plugin:
    '''kcptun参数输入'''
    def __init__(self):
        self.key = random_str(16)
        self.crypt = [
            'aes', 'aes-128', 'aes-192', 'salsa20', 'blowfish', 'twofish',
            'cast5', '3des', 'tea', 'xtea', 'xor', 'sm4', 'none'
        ]
        self.mode = ['fast3', 'fast2', 'fast', 'normal', 'manual']
        self.mtu = 1350
        self.sndwnd = 1024
        self.rcvwnd = 1024
        self.datashard = 10
        self.parityshard = 3
        self.dscp = 0
        self.nocomp = ['true', 'false']
        self.acknodelay = ['true', 'false']
        self.nodelay = 0
        self.interval = 30
        self.resen = 2
        self.nc = 1

    def get_key(self):
        while True:
            pattern = compile('[A-Za-z0-9]+')
            print('key:')
            key = input('(Default: {0}): '.format(self.key))
            if pattern.fullmatch(key) is not None:
                return key
            else:
                return self.key

    def get_crypt(self):
        while True:
            for index, crypt in enumerate(self.crypt):
                print("{}.{}".format(index + 1, crypt))
            try:
                choice = int(input('crypt:'))
                if 1 <= choice <= len(self.crypt):
                    return self.crypt[choice - 1]
                else:
                    raise ValueError
            except ValueError:
                print('input out of range!')

    def get_mode(self):
        while True:
            for index, mode in enumerate(self.mode):
                print("{}.{}".format(index + 1, mode))
            try:
                choice = int(input('mode:'))
                if 1 <= choice <= len(self.mode):
                    return self.mode[choice - 1]
                else:
                    raise ValueError
            except ValueError:
                print('input out of range!')

    def get_mtu(self) -> int:
        #https://www.v2ex.com/t/800024
        while True:
            try:
                print('mtu:')
                mtu = int(input('(Default: {0}): '.format(self.mtu)))
                if 1 <= mtu <= 1432:
                    return mtu
                else:
                    raise ValueError
            except ValueError:
                return self.mtu

    def get_sndwnd(self):
        while True:
            try:
                print('sndwnd:')
                sndwnd = int(input('(Default: {0}): '.format(self.sndwnd)))
                if 1 <= sndwnd:
                    return sndwnd
                else:
                    raise ValueError
            except ValueError:
                return self.sndwnd

    def get_rcvwnd(self) -> int:
        while True:
            try:
                print('rcvwnd:')
                rcvwnd = int(input('(Default: {0}): '.format(self.rcvwnd)))
                if 1 <= rcvwnd:
                    return rcvwnd
                else:
                    raise ValueError
            except ValueError:
                return self.rcvwnd

    def get_datashard(self) -> int:
        while True:
            try:
                print('datashard,ds:')
                datashard = int(
                    input('(Default: {0}): '.format(self.datashard)))
            except ValueError:
                return self.datashard
            else:
                return datashard

    def get_parityshard(self) -> int:
        while True:
            try:
                print('parityshard,ps:')
                parityshard = int(
                    input('(Default: {0}): '.format(self.parityshard)))
            except ValueError:
                return self.parityshard
            else:
                return parityshard

    def get_dscp(self) -> int:
        while True:
            try:
                print('dscp:')
                dscp = int(input('(Default: {0}): '.format(self.dscp)))
            except ValueError:
                return self.dscp
            else:
                return dscp

    def get_nocomp(self):
        while True:
            for index, nocomp in enumerate(self.nocomp):
                print("{}.{}".format(index + 1, nocomp))
            try:
                choice = int(input('nocomp:'))
                if 1 <= choice <= len(self.nocomp):
                    return self.nocomp[choice - 1]
                else:
                    raise ValueError
            except ValueError:
                print('input out of range!')

    def get_acknodelay(self):
        while True:
            for index, acknodelay in enumerate(self.acknodelay):
                print("{}.{}".format(index + 1, acknodelay))
            try:
                choice = int(input('acknodelay:'))
                if 1 <= choice <= len(self.acknodelay):
                    return self.acknodelay[choice - 1]
                else:
                    raise ValueError
            except ValueError:
                print('input out of range!')

    def get_nodelay(self) -> int:
        while True:
            try:
                print('nodelay:')
                nodelay = int(input('(Default: {0}): '.format(self.nodelay)))
            except ValueError:
                return self.nodelay
            else:
                return nodelay

    def get_interval(self) -> int:
        while True:
            try:
                print('interval:')
                interval = int(input('(Default: {0}): '.format(self.interval)))
            except ValueError:
                return self.interval
            else:
                return interval

    def get_resen(self) -> int:
        while True:
            try:
                print('resen:')
                resen = int(input('(Default: {0}): '.format(self.resen)))
            except ValueError:
                return self.resen
            else:
                return resen

    def get_nc(self) -> int:
        while True:
            try:
                print('nc:')
                nc = int(input('(Default: {0}): '.format(self.nc)))
            except ValueError:
                return self.nc
            else:
                return nc


class V2ray_plugin:
    '''v2ray-plugin参数输入'''
    def __init__(self):
        self.mode = [
            'websocket-http', 'websocket-tls', 'quic-tls', 'grpc', 'grpc-tls'
        ]
        self.path = random_str(12)
        self.servicename = 'GunService'

    def get_mode(self):
        while True:
            for index, mode in enumerate(self.mode):
                print("{}.{}".format(index + 1, mode))
            try:
                choice = int(input('Which Transport mode you\'d select:'))
                if 1 <= choice <= len(self.mode):
                    return self.mode[choice - 1]
                else:
                    raise ValueError
            except ValueError:
                print('input out of range!')

    def get_path(self):
        while True:
            pattern = compile('[A-Za-z0-9]+')
            print('Enter a URL path for websocket.')
            v2ray_path = input('(Default: {0}): '.format(self.v2ray_path))
            if pattern.fullmatch(v2ray_path) is not None:
                return v2ray_path
            else:
                return self.v2ray_path

    def get_servicename(self):
        while True:
            pattern = compile('[A-Za-z0-9]+')
            print(
                'Enter a Service name for grpc (Requires client support otherwise please leave the default)'
            )
            servicename = input('(Default: {0}): '.format(self.servicename))
            if pattern.fullmatch(servicename) is not None:
                return servicename
            else:
                return self.servicename
