import json
from flask import Flask, request, render_template
app = Flask(__name__)

@app.route('/', methods=['GET', 'POST'])
def index():
    if request.method == "GET":
        return render_template("index.html")
    elif request.method == "POST":
        body = request.get_data(as_text=True)
        print(body)
        return json.loads(body)
    
app.run('0.0.0.0', port=22112, debug=True)
