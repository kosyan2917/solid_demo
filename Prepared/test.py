import requests

data = {
    "login": (19 + 4).to_bytes(4, 'big').decode()
,
    "password": "aboba"
}

requests.post('http://localhost:3228/login', data=data)