<?php
class Insecure {

    private $command;

    public function __construct($command)
    {
        $this->command = $command;
    }

    public function __wakeup(): void {
        echo "In __wakeup\n";
        echo system($this->command);
    }
}

$method  = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Ошибка загрузки файла.';
    } else {
        $uploadDir = __DIR__ . '/uploads';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $originalName = basename($_FILES['file']['name']);
        $targetPath   = $uploadDir . '/' . $originalName;

        if (move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {
            $message = 'Файл успешно загружен как: ' 
                . htmlspecialchars($originalName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        } else {
            $error = 'Не удалось сохранить загруженный файл.';
        }
    }
} else {
    if (isset($_GET['url']) && $_GET['url'] !== '') {
        $url = $_GET['url'];

    
        $data = file_get_contents($url, false, $context);

        if ($data === false) {
            $error = 'Не удалось прочитать файл по указанному URL.';
        } else {
            $content = $data;
            $message = 'Файл успешно прочитан по URL: ' 
                . htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Загрузка файла / Чтение по URL</title>
</head>
<body>
    <h1>index.php</h1>

    <?php if ($message): ?>
        <p style="color: green;"><?php echo $message; ?></p>
    <?php endif; ?>

    <?php if ($error): ?>
        <p style="color: red;"><?php echo $error; ?></p>
    <?php endif; ?>

    <h2>Загрузить файл (POST)</h2>
    <form method="post" enctype="multipart/form-data">
        <input type="file" name="file" required>
        <button type="submit">Загрузить</button>
    </form>

    <hr>

    <h2>Прочитать файл по URL (GET)</h2>
    <form method="get">
        <label>
            URL файла:
            <input name="url" size="60" 
                   value="<?php echo isset($_GET['url']) ? htmlspecialchars($_GET['url'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') : '' ?>">
        </label>
        <button type="submit">Прочитать</button>
    </form>

    <?php if ($content !== null): ?>
        <h3>Содержимое файла:</h3>
        <pre><?php echo htmlspecialchars($content, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></pre>
    <?php endif; ?>
</body>
</html>
