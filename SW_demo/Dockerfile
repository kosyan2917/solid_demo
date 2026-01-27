FROM python:3.11-slim

WORKDIR /app
ENV PYTHONDONTWRITEBYTECODE=1
ENV PYTHONUNBUFFERED=1

COPY main.py /app/
COPY templates /app/templates

RUN pip install --no-cache-dir flask psycopg2-binary werkzeug

EXPOSE 3228
CMD ["python", "/app/main.py"]
