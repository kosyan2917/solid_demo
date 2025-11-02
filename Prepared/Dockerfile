# syntax=docker/dockerfile:1

########## build ##########
FROM golang:1.23-alpine AS builder
WORKDIR /app

COPY go.mod ./
RUN --mount=type=cache,target=/go/pkg/mod go mod download

COPY . .
RUN CGO_ENABLED=0 go build -trimpath -ldflags="-s -w" -o /server .

FROM scratch

ENV DATABASE_URL=postgres://user:pass@postgres:5432/app?sslmode=disable

EXPOSE 8080
USER 10001:10001
COPY --from=builder /server /server
ENTRYPOINT ["/server"]
