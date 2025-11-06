package main

import (
	"context"
	"fmt"
	"net/http"

	"github.com/jackc/pgx/v5"
)

var db *pgx.Conn

func connect() {
	dsn := "postgres://appuser:apppass@127.0.0.1:5432/app?sslmode=disable"
	if dsn == "" {
		panic("set DATABASE_URL")
	}

	var err error
	db, err = pgx.Connect(context.Background(), dsn)
	if err != nil {
		panic(err)
	}
}
func main() {
	connect()
	fmt.Println("Started")
	http.HandleFunc("/login", loginHandler)
	http.ListenAndServe(":3228", nil)
	defer db.Close(context.Background())

}

func loginHandler(w http.ResponseWriter, r *http.Request) {
	r.Body = http.MaxBytesReader(w, r.Body, int64(1<<63-1))
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
	fmt.Println(len(login))
	fmt.Println(int32(len(login)))
	connect()
	var stored1 string
	err := db.QueryRow(r.Context(),
		`SELECT password FROM users WHERE login = '1`).
		Scan(&stored1)

	var stored string
	err = db.QueryRow(r.Context(),
		`SELECT COUNT(*) FROM users WHERE login = $1 AND password = $2`, login, password).
		Scan(&stored)
	if err != nil {
		fmt.Println(err)
	}

	w.Header().Set("Content-Type", "application/json; charset=utf-8")
	if err != nil || stored != "1" {
		w.WriteHeader(http.StatusUnauthorized)
		w.Write([]byte(`{"success":false}`))
		return
	}
	w.Write([]byte(`{"success":true}`))
}
