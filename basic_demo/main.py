import os
from flask import Flask, g, jsonify, request, abort
from psycopg2.pool import SimpleConnectionPool
import psycopg2.extras

def create_app():
    app = Flask(__name__)

    app.config['DB_DSN'] = os.getenv(
        "DATABASE_URL",
        "dbname=app user=appuser password=apppass host=db port=5431"
    )

    app.db_pool = SimpleConnectionPool(
        minconn=1,
        maxconn=10,
        dsn=app.config['DB_DSN']
    )

    def get_db():
        if 'db_conn' not in g:
            g.db_conn = app.db_pool.getconn()
        return g.db_conn

    @app.teardown_appcontext
    def teardown_db(exception):
        conn = g.pop('db_conn', None)
        if conn is not None:
            app.db_pool.putconn(conn)

    def query_db(query, params=None, fetchone=False, commit=False):
        conn = get_db()
        with conn.cursor(cursor_factory=psycopg2.extras.DictCursor) as cur:
            cur.execute(query, params or ())
            result = None
            if cur.description:
                rows = cur.fetchall()
                result = rows[0] if fetchone else rows
            if commit:
                conn.commit()
            return result

    @app.get('/')
    def index():
        return "Works!"
    
    @app.post('/basic')
    def basic():
        product_id = request.get_json().get("id")
        if product_id:
            res = query_db(f"SELECT * FROM products WHERE id={product_id}")
            products = [
                {"id": r["id"], "name": r["name"], "description": r["description"]}
                for r in res
            ] 
            return jsonify(products)
        return jsonify({"error": "id must be set"}), 400

    @app.post('/error_visible')
    def error_visible():
        product_id = request.get_json().get("id")
        if product_id:
            try:
                res = query_db(f"SELECT * FROM products WHERE id={product_id}")
                products = [
                    {"id": r["id"], "name": r["name"], "description": r["description"]}
                    for r in res
                ] 
                return jsonify(products)
            except Exception as e:
                return jsonify({"error": str(e)}), 500
        return jsonify({"error": "id must be set"}), 400

    @app.post('/error_conditional')
    def error_conditional():
        product_id = request.get_json().get("id")
        if product_id:
            try:
                res = query_db(f"SELECT * FROM products WHERE id={product_id}")
                products = [
                    {"id": r["id"], "name": r["name"], "description": r["description"]}
                    for r in res
                ] 
                return jsonify(products)
            except Exception as e:
                return abort(500)
        return jsonify({"error": "id must be set"}), 400

    @app.post('/time_based')
    def time_based():
        product_id = request.get_json().get("id")
        if product_id:
            try:
                res = query_db(f"SELECT * FROM products WHERE id={product_id}")
                products = [
                    {"id": r["id"], "name": r["name"], "description": r["description"]}
                    for r in res
                ] 
                return jsonify(products)
            except Exception as e:
                return jsonify({}), 200
        return jsonify({"error": "id must be set"}), 400

    @app.post("/order_by_column")
    def order_by_column():
        order = request.get_json().get("order")
        if order:
            try:
                res = query_db(f"SELECT * FROM products ORDER BY {order}")
                products = [
                    {"id": r["id"], "name": r["name"], "description": r["description"]}
                    for r in res
                ] 
                return jsonify(products)
            except:
                return jsonify({}), 200
        return jsonify({"error": "order must be set"}), 400

    @app.post("/order_by")
    def order_by():
        order = request.get_json().get("order")
        if order:
            try:
                res = query_db(f"SELECT * FROM products ORDER BY id {order}")
                products = [
                    {"id": r["id"], "name": r["name"], "description": r["description"]}
                    for r in res
                ] 
                return jsonify(products)
            except:
                return jsonify({}), 200
        return jsonify({"error": "order must be set"}), 400


    app.query_db = query_db

    return app

app = create_app()




app.run('0.0.0.0', 5050, debug=True)