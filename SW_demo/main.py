import os
import time
import uuid
from decimal import Decimal

import psycopg2
from psycopg2.extras import RealDictCursor
from flask import Flask, redirect, render_template, request, send_from_directory, url_for
from werkzeug.utils import secure_filename

app = Flask(__name__)
BASE_DIR = os.path.abspath(os.path.dirname(__file__))
UPLOAD_DIR = os.path.join(BASE_DIR, "uploads")
DEFAULT_USER_ID = 1
_db_ready = False


def get_db():
    dsn = os.environ.get(
        "DATABASE_URL",
        os.environ.get(
            "POSTGRES_DSN",
            "dbname=sw_demo user=sw_user password=sw_pass host=db",
        ),
    )
    return psycopg2.connect(dsn)


def init_db():
    with get_db() as conn, conn.cursor() as cur:
        cur.execute(
            """
            CREATE TABLE IF NOT EXISTS users (
                id SERIAL PRIMARY KEY,
                username TEXT NOT NULL,
                avatar_path TEXT,
                balance NUMERIC(12, 2) NOT NULL DEFAULT 0
            );
            """
        )
        cur.execute(
            """
            CREATE TABLE IF NOT EXISTS transfers (
                id SERIAL PRIMARY KEY,
                from_user_id INTEGER NOT NULL REFERENCES users(id),
                to_user_id INTEGER REFERENCES users(id),
                to_username TEXT NOT NULL,
                amount NUMERIC(12, 2) NOT NULL,
                message TEXT NOT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT NOW()
            );
            """
        )
        cur.execute(
            "ALTER TABLE transfers ADD COLUMN IF NOT EXISTS to_user_id INTEGER;"
        )
        cur.execute(
            """
            DO $$
            BEGIN
                IF NOT EXISTS (
                    SELECT 1 FROM pg_constraint WHERE conname = 'transfers_to_user_fk'
                ) THEN
                    ALTER TABLE transfers
                    ADD CONSTRAINT transfers_to_user_fk
                    FOREIGN KEY (to_user_id) REFERENCES users(id);
                END IF;
            END $$;
            """
        )
        cur.execute(
            """
            UPDATE transfers t
            SET to_user_id = u.id
            FROM users u
            WHERE t.to_user_id IS NULL AND t.to_username = u.username;
            """
        )
        users = [
            (1, "user1", Decimal("1000.00")),
            (2, "user2", Decimal("500.00")),
            (3, "evil", Decimal("777.00")),
        ]
        for user_id, username, balance in users:
            cur.execute("SELECT id FROM users WHERE id = %s;", (user_id,))
            if cur.fetchone() is None:
                cur.execute(
                    "INSERT INTO users (id, username, balance) VALUES (%s, %s, %s);",
                    (user_id, username, balance),
                )


def ensure_db():
    global _db_ready
    if _db_ready:
        return
    for _ in range(30):
        try:
            init_db()
            _db_ready = True
            return
        except Exception:
            time.sleep(1)
    init_db()
    _db_ready = True


@app.before_request
def _ensure_db():
    ensure_db()


@app.route("/")
def index():
    return redirect(url_for("profile"))


@app.route("/profile")
def profile():
    active_user_id = request.args.get("user_id", str(DEFAULT_USER_ID))
    try:
        active_user_id = int(active_user_id)
    except Exception:
        active_user_id = DEFAULT_USER_ID

    with get_db() as conn, conn.cursor(cursor_factory=RealDictCursor) as cur:
        cur.execute("SELECT * FROM users WHERE id = %s;", (active_user_id,))
        user = cur.fetchone()
        cur.execute("SELECT id, username FROM users ORDER BY id;")
        users = cur.fetchall()
        cur.execute(
            """
            SELECT
                t.id,
                t.from_user_id,
                t.to_user_id,
                t.to_username,
                t.amount,
                t.message,
                t.created_at,
                u.username AS from_username
            FROM transfers t
            JOIN users u ON u.id = t.from_user_id
            WHERE t.from_user_id = %s OR t.to_user_id = %s
            ORDER BY t.created_at DESC;
            """,
            (active_user_id, active_user_id),
        )
        transfers = cur.fetchall()
    return render_template(
        "profile.html",
        user=user,
        users=users,
        transfers=transfers,
        active_user_id=active_user_id,
    )


@app.route("/upload-avatar", methods=["POST"])
def upload_avatar():
    active_user_id = request.form.get("user_id", str(DEFAULT_USER_ID))
    try:
        active_user_id = int(active_user_id)
    except Exception:
        active_user_id = DEFAULT_USER_ID

    file = request.files.get("avatar")
    if not file or file.filename == "":
        return redirect(url_for("profile", user_id=active_user_id))

    os.makedirs(UPLOAD_DIR, exist_ok=True)
    filename = secure_filename(file.filename)
    unique_name = f"{uuid.uuid4().hex}_{filename}"
    file_path = os.path.join(UPLOAD_DIR, unique_name)
    file.save(file_path)

    with get_db() as conn, conn.cursor() as cur:
        cur.execute(
            "UPDATE users SET avatar_path = %s WHERE id = %s;",
            (f"/uploads/{unique_name}", active_user_id),
        )
    return redirect(url_for("profile", user_id=active_user_id))


@app.route("/transfer", methods=["POST"])
def transfer():
    active_user_id = request.form.get("user_id", str(DEFAULT_USER_ID))
    try:
        active_user_id = int(active_user_id)
    except Exception:
        active_user_id = DEFAULT_USER_ID

    to_username = request.form.get("to_username", "").strip()
    amount_raw = request.form.get("amount", "0").strip()
    message = request.form.get("message", "")

    if not to_username:
        return redirect(url_for("profile", user_id=active_user_id))

    try:
        amount = Decimal(amount_raw)
    except Exception:
        amount = Decimal("0")

    with get_db() as conn, conn.cursor() as cur:
        cur.execute(
            "SELECT id FROM users WHERE username = %s;",
            (to_username,),
        )
        row = cur.fetchone()
        if row is None:
            return redirect(url_for("profile", user_id=active_user_id))
        to_user_id = row[0]
        cur.execute(
            """
            INSERT INTO transfers (from_user_id, to_user_id, to_username, amount, message)
            VALUES (%s, %s, %s, %s, %s);
            """,
            (active_user_id, to_user_id, to_username, amount, message),
        )
        cur.execute(
            "UPDATE users SET balance = balance - %s WHERE id = %s;",
            (amount, active_user_id),
        )
        cur.execute(
            "UPDATE users SET balance = balance + %s WHERE id = %s;",
            (amount, to_user_id),
        )
    return redirect(url_for("profile", user_id=active_user_id))


@app.route("/transfer/<int:transfer_id>/delete", methods=["POST"])
def delete_transfer(transfer_id):
    active_user_id = request.form.get("user_id", str(DEFAULT_USER_ID))
    try:
        active_user_id = int(active_user_id)
    except Exception:
        active_user_id = DEFAULT_USER_ID

    with get_db() as conn, conn.cursor() as cur:
        cur.execute(
            """
            SELECT amount, from_user_id, to_user_id
            FROM transfers
            WHERE id = %s AND (from_user_id = %s OR to_user_id = %s);
            """,
            (transfer_id, active_user_id, active_user_id),
        )
        row = cur.fetchone()
        if row is None:
            return redirect(url_for("profile", user_id=active_user_id))

        amount, from_user_id, to_user_id = row
        cur.execute("DELETE FROM transfers WHERE id = %s;", (transfer_id,))
        if from_user_id is not None:
            cur.execute(
                "UPDATE users SET balance = balance + %s WHERE id = %s;",
                (amount, from_user_id),
            )
        if to_user_id is not None:
            cur.execute(
                "UPDATE users SET balance = balance - %s WHERE id = %s;",
                (amount, to_user_id),
            )

    return redirect(url_for("profile", user_id=active_user_id))


@app.route("/uploads/<path:filename>")
def uploads(filename):
    response = send_from_directory(UPLOAD_DIR, filename)
    response.headers["Service-Worker-Allowed"] = "/"
    return response


if __name__ == "__main__":
    init_db()
    app.run("0.0.0.0", 3228, debug=True)
