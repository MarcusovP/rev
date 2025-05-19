#!/usr/bin/env python3
import socket, threading, queue

HOST = "127.0.0.1"
PORT_RANGE = (1, 65535)
THREADS = 200
TIMEOUT = 0.5

ports_q = queue.Queue()
open_services = []

def worker():
    while True:
        port = ports_q.get()
        try:
            s = socket.socket()
            s.settimeout(TIMEOUT)
            s.connect((HOST, port))
            print(f"[+] {port} open")
            try:
                # баннер-граб
                s.sendall(b"\r\n")
                banner = s.recv(1024).decode(errors="ignore").strip()
                if banner:
                    print(f"    Banner: {banner}")
                else:
                    print("    Banner: <none>")
            except:
                pass
            open_services.append((port, banner if 'banner' in locals() else ''))
        except:
            pass
        finally:
            s.close()
            ports_q.task_done()

# наполняем очередь
for p in range(PORT_RANGE[0], PORT_RANGE[1] + 1):
    ports_q.put(p)

# стартуем воркеры
for _ in range(THREADS):
    t = threading.Thread(target=worker, daemon=True)
    t.start()

ports_q.join()
print("\nScan complete. Found services:")
for port, banner in open_services:
    print(f" - Port {port}: {banner}")
