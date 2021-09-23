#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import sys
import time
import daemon

with daemon.DaemonContext():
    f = open("/sdcard/test.log", 'w')
    while True:
        f.write('''
        Library to implement a well-behaved Unix daemon process.

This library implements the well-behaved daemon specification of PEP 3143, “Standard daemon process library”.

A well-behaved Unix daemon process is tricky to get right, but the required steps are much the same for every daemon program. A DaemonContext instance holds the behaviour and configured process environment for the program; use the instance as a context manager to enter a daemon state.
''')
        f.write("{0}{1}\n".format(time.ctime(time.time()), sys.os.getpid()))
        time.sleep(1)
