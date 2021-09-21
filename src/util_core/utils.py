#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import os
import socket
import ipaddress
import shutil
import platform
import random
import importlib.util
import configparser as ini
from util_core import downloader as dl
from util_core import (HOME_DIR, URL, URL2, common_install, common_remove)


def envSetup():
    #https://stackoverflow.com/a/1681244
    os.environ['PATH'] += os.pathsep + os.pathsep.join(
        [HOME_DIR + '/usr/bin', HOME_DIR + '/usr/sbin',
         os.getcwd()])


def isRoot():
    if os.getuid() != 0:
        exit('You must run this script as root!')


def isApt():
    if shutil.which('apt') is None:
        exit(
            'The script does not support the package manager in this operating system.'
        )


def isSystemctl():
    if shutil.which('systemctl'):
        if os.path.exists('/etc/systemd/system/ss-main.service') is False:
            exit('no found file')
    else:
        exit('No command systemctl found.')


def isPython3():
    ver = platform.python_version_tuple()
    if int(ver[0]) < 3 or int(ver[0]) == 3 and int(ver[1]) < 8:
        exit('Python version < 3.8')
    if shutil.which('pip3') is None:
        #subprocess.run([common_install, "python3-pip"])
        subprocess.Popen(common_install + " python3-pip", shell=True).wait()


def dirCheck():
    if os.path.isdir(HOME_DIR) is False:
        os.mkdir(HOME_DIR, 0o755)
    for i in [
            'conf', 'usr', 'ssl', 'web', 'usr/bin', 'usr/conf', 'usr/etc',
            'usr/html', 'usr/lib', 'usr/php', 'usr/sbin', 'usr/fastcgi_temp',
            'usr/client_body_temp'
    ]:
        if os.path.isdir(HOME_DIR + '/' + i) is False:
            os.mkdir(HOME_DIR + '/' + i, 0o755)
        if os.path.exists(HOME_DIR + '/' + i) is False:
            exit('Create directory ' + HOME_DIR + '/' + i + ' failed')


def aclCheck():
    if os.path.isfile(HOME_DIR + '/conf/server_block.acl') is False:
        dl.download(URL + '/acl/server_block.acl', HOME_DIR + '/conf')
    if os.path.exists(HOME_DIR + '/conf/server_block.acl') is False:
        exit('Download ' + HOME_DIR + '/conf/server_block.acl' + ' failed')


def confCheck():
    if os.path.isfile(HOME_DIR + '/conf/config.ini') is False:
        dl.download(URL + '/conf/config.ini', HOME_DIR + '/conf')
    if os.path.exists(HOME_DIR + '/conf/config.ini') is False:
        exit('Download ' + HOME_DIR + '/conf/config.ini' + ' failed')


def dlBinary():
    if os.path.isfile(HOME_DIR + '/conf/update') is False:
        dl.download(URL + '/version/update', HOME_DIR + '/conf')
    with open(HOME_DIR + '/conf/update', 'r') as fd:
        url = URL + '/usr/bin/kcptun.sh'
        for line in fd.read().splitlines():
            sha, file = line.split()
            dir, name = os.path.split(file)
            url += URL + '/usr/bin/' + name + ' '
        if os.path.exists(file) is False:
            dl.download(url, dir)
    with open(HOME_DIR + '/conf/update', 'r') as fd:
        for line in fd.read().splitlines():
            sha, file = line.split()
            if os.path.exists(file) is False:
                exit('Download ' + file + ' failed')


def port_is_use(ipv: int, port: int):
    #https://www.kite.com/python/answers/how-to-check-if-a-network-port-is-open-in-python
    if ipv == 4:
        t_socket = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
        u_socket = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
        addr = '0.0.0.0'
    elif ipv == 6:
        t_socket = socket.socket(socket.AF_INET6, socket.SOCK_STREAM)
        u_socket = socket.socket(socket.AF_INET6, socket.SOCK_DGRAM)
        addr = '::'
    else:
        exit(ipv)
    tcp_use, udp_use = False, False
    try:
        t_socket.bind((addr, int(port)))
    except OSError as err:
        tcp_use = True
    finally:
        t_socket.close()
    try:
        u_socket.bind((addr, int(port)))
    except OSError as err:
        udp_use = True
    finally:
        u_socket.close()
    return tcp_use or udp_use


def random_port(start_port: int = 1024, end_port: int = 65535):
    while True:
        random_port = random.randint(start_port, end_port)
        if not port_is_use(4, random_port) and not port_is_use(6, random_port):
            return random_port


def random_str(max: int):
    return (''.join(
        random.sample([
            'z', 'y', 'x', 'w', 'v', 'u', 't', 's', 'r', 'q', 'p', 'o', 'n',
            'm', 'l', 'k', 'j', 'i', 'h', 'g', 'f', 'e', 'd', 'c', 'b', 'a',
            '9', '8', '7', '6', '5', '4', '3', '2', '1', '0'
        ], max)))


def extract_ip(ipv: int = 4):
    #https://www.delftstack.com/howto/python/get-ip-address-python/
    if ipv == 4:
        try:
            st = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
            st.connect(('8.8.8.8', 1))
        except OSError as err:
            print("IPv4: {0}".format(err.strerror))
            exit()
    if ipv == 6:
        try:
            st = socket.socket(socket.AF_INET6, socket.SOCK_DGRAM)
            st.connect(('2001:4860:4860::8888', 1))
        except OSError as err:
            print("IPv6: {0}".format(err.strerror))
            exit()
    IP = st.getsockname()[0]
    st.close()
    return IP


def is_ipv4(ip):
    try:
        socket.inet_pton(socket.AF_INET, ip)
    except AttributeError:  # no inet_pton here, sorry
        try:
            socket.inet_aton(ip)
        except socket.error:
            return False
        return ip.count('.') == 3
    except socket.error:  # not a valid ip
        return False
    return True


def is_ipv6(ip):
    try:
        socket.inet_pton(socket.AF_INET6, ip)
    except socket.error:  # not a valid ip
        return False
    return True


def check_ip(ip):
    return is_ipv4(ip) or is_ipv6(ip)


def ipv4_or_ipv6(ip):
    if check_ip(ip):
        if is_ipv4(ip):
            return 4
        if is_ipv6(ip):
            return 6
    return False


def env_get(str):
    return os.getenv(str)


def pid():
    return os.getpid()


def is_global(ip):
    #https://www.geeksforgeeks.org/how-to-manipulate-ip-addresses-in-python-using-ipaddress-module/
    return ipaddress.ip_address(ip).is_global


def mod_check(name: str):
    #https://stackoverflow.com/questions/1051254/check-if-python-package-is-installed

    if name in sys.modules:
        #print(f"{name!r} already in sys.modules")
        return 0
    elif (spec := importlib.util.find_spec(name)) is not None:
        # If you choose to perform the actual import ...
        module = importlib.util.module_from_spec(spec)
        sys.modules[name] = module
        spec.loader.exec_module(module)
        #print(f"{name!r} has been imported")
        return 1
    else:
        #print(f"can't find the {name!r} module")
        return 2


def pause():
    input('Press any key to start...or Press Ctrl+D to cancel')
