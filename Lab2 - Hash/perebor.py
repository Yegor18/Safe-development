import hashlib, time

import multiprocessing as mp

alphabet = "abcdefghijklmnopqrstuvwxyz"


def gen_passw(idx):
    password = ""
    for i in range(5):
        password += alphabet[idx % len(alphabet)]
        idx = idx // len(alphabet)

    return password[::-1]


def crack_sha256_thread(idx, hashes, start, end):
    for i in range(start, end):
        password = gen_passw(i)
        hash = hashlib.sha256(password.encode('utf-8')).hexdigest()
        for h in hashes:
            if hash != h:
                continue
            print("Хэш {} найден: {}. Поток {}.".format(hash, password, idx))


def crack_sha256():
   

    hashes = ["1115dd800feaacefdf481f1f9070374a2a81e27880f187396db67958b207cbad",
              "3a7bd3e2360a3d29eea436fcfb7e44c735d117c42d1c1835420b6b9942dd4f1b",
              "74e1bb62f8dabb8125a58852b63bdf6eaef667cb56ac7f7cdba6d7305c50a22f"]

    N = 26**5-1
    n_threads = int(input("Количество потоков для SHA256:"))
    per_thread = N // n_threads
    start_time = time.perf_counter()
    threads = []
    start = 0
    for i in range(n_threads):
        end = start + per_thread
        t = mp.Process(target=crack_sha256_thread, args=(i, hashes, start, end))
        threads.append(t)
        t.start()
        start = end

    for thread in threads:
        thread.join()

    end_time = time.perf_counter()
    elapsed = end_time - start_time
    print("Время выполнения: {}.".format(elapsed))

def crack_md5_thread(idx, hashes, start, end):
    for i in range(start, end):
        password = gen_passw(i)
        hash = hashlib.md5(password.encode('utf-8')).hexdigest()
        for h in hashes:
            if hash != h:
                continue
            print("Хэш {} найден: {} потоком {}.".format(hash, password, idx))

def crack_md5():
    

    hashes = ["81d45c9cf678fbaa8d64a6f29a6f97e3",
              "1f3870be274f6c49b3e31a0c6728957f",
              "d9308f32f8c6cf370ca5aaaeafc0d49b"]
    N = 26**5-1
    n_threads = int(input("Количество потоков для MD5:"))
    per_thread = N // n_threads
    start_time = time.perf_counter()
    threads = []
    start = 0
    for i in range(n_threads):
        end = start + per_thread
        t = mp.Process(target=crack_md5_thread, args=(i, hashes, start, end))
        threads.append(t)
        t.start()
        start = end

    for thread in threads:
        thread.join()

    end_time = time.perf_counter()
    elapsed = end_time - start_time
    print("Время выполнения: {}.".format(elapsed))

if __name__ == '__main__':
    crack_sha256()
    crack_md5()
