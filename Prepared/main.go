package main

import (
	"context"
	"net/http"
	"os"

	"github.com/jackc/pgx/v5"
)

var db *pgx.Conn

func main() {
	dsn := os.Getenv("DATABASE_URL") // пример: postgres://user:pass@db:5432/app?sslmode=disable
	if dsn == "" {
		panic("set DATABASE_URL")
	}

	var err error
	db, err = pgx.Connect(context.Background(), dsn)
	if err != nil {
		panic(err)
	}
	defer db.Close(context.Background())

	http.HandleFunc("/login", loginHandler)
	http.ListenAndServe(":8080", nil)
}

func loginHandler(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		w.WriteHeader(http.StatusMethodNotAllowed)
		return
	}
	// парсим application/x-www-form-urlencoded
	if err := r.ParseForm(); err != nil {
		http.Error(w, `{"success":false}`, http.StatusBadRequest)
		return
	}
	login := r.PostFormValue("login")
	password := r.PostFormValue("password")
	if login == "" || password == "" {
		http.Error(w, `{"success":false}`, http.StatusBadRequest)
		return
	}

	var stored string
	err := db.QueryRow(r.Context(),
		`SELECT password FROM users WHERE login = $1`, login).
		Scan(&stored)

	w.Header().Set("Content-Type", "application/json; charset=utf-8")
	if err != nil || stored != password {
		w.WriteHeader(http.StatusUnauthorized)
		w.Write([]byte(`{"success":false}`))
		return
	}
	w.Write([]byte(`{"success":true}`))
}
