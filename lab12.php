<?php
class Form {
    private function createAttributes($attrs) {
        $attributes = '';
        foreach ($attrs as $name => $value) {
            $attributes .= ' ' . $name . '="' . htmlspecialchars($value) . '"';
        }
        return $attributes;
    }

    public function input($attrs = []) {
        $attrs['type'] = $attrs['type'] ?? 'text';
        return '<input' . $this->createAttributes($attrs) . '>';
    }

    public function password($attrs = []) {
        $attrs['type'] = 'password';
        return $this->input($attrs);
    }

    public function submit($attrs = []) {
        $attrs['type'] = 'submit';
        return $this->input($attrs);
    }

    public function textarea($attrs = []) {
        $value = $attrs['value'] ?? '';
        unset($attrs['value']);
        return '<textarea' . $this->createAttributes($attrs) . '>' . htmlspecialchars($value) . '</textarea>';
    }

    public function open($attrs = []) {
        return '<form' . $this->createAttributes($attrs) . '>';
    }

    public function close() {
        return '</form>';
    }
}

class SmartForm extends Form {
    public function input($attrs = []) {
        $name = $attrs['name'] ?? '';
        if ($name && isset($_REQUEST[$name])) {
            $attrs['value'] = $_REQUEST[$name];
        }
        return parent::input($attrs);
    }

    public function textarea($attrs = []) {
        $name = $attrs['name'] ?? '';
        if ($name && isset($_REQUEST[$name])) {
            $attrs['value'] = $_REQUEST[$name];
        }
        return parent::textarea($attrs);
    }
}

class Cookie {
    public static function set($name, $value, $expire = 0, $path = '/', $domain = '', $secure = false, $httponly = false) {
        setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
        $_COOKIE[$name] = $value;
    }

    public static function get($name) {
        return $_COOKIE[$name] ?? null;
    }

    public static function del($name) {
        if (isset($_COOKIE[$name])) {
            setcookie($name, '', time() - 3600);
            unset($_COOKIE[$name]);
        }
    }
}

class Session {
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function set($name, $value) {
        $_SESSION[$name] = $value;
    }

    public function get($name) {
        return $_SESSION[$name] ?? null;
    }

    public function del($name) {
        if (isset($_SESSION[$name])) {
            unset($_SESSION[$name]);
        }
    }

    public function has($name) {
        return isset($_SESSION[$name]);
    }
}

class Log {
    private $logFile;
    private $entries = [];

    public function __construct($filename = 'app.log') {
        $this->logFile = $filename;
        if (file_exists($filename)) {
            $this->entries = file($filename, FILE_IGNORE_NEW_LINES);
        }
    }

    public function save($message, $level = 'INFO') {
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[$timestamp] [$level] $message" . PHP_EOL;
        $this->entries[] = $logEntry;
        file_put_contents($this->logFile, $logEntry, FILE_APPEND);
    }

    public function getEntries($limit = null) {
        if ($limit === null) {
            return $this->entries;
        }
        return array_slice($this->entries, -$limit);
    }

    public function clear() {
        $this->entries = [];
        file_put_contents($this->logFile, '');
    }
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Демонстрація PHP класів</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1, h2 {
            color: #333;
        }
        .section {
            margin-bottom: 30px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background: #f9f9f9;
        }
        .form-example {
            background: #fff;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-top: 10px;
        }
        .code {
            background: #f0f0f0;
            padding: 10px;
            border-radius: 5px;
            overflow-x: auto;
            font-family: monospace;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Демонстрація роботи PHP класів</h1>
        
        <div class="section">
            <h2>1. Клас Form</h2>
            <p>Приклад використання базової форми:</p>
            <div class="form-example">
                <?php
                $form = new Form();
                echo $form->open(['action' => 'submit.php', 'method' => 'POST']);
                echo $form->input(['type' => 'text', 'name' => 'username', 'placeholder' => "Ім'я користувача"]);
                echo $form->password(['name' => 'password', 'placeholder' => 'Пароль']);
                echo $form->submit(['value' => 'Увійти']);
                echo $form->close();
                ?>
            </div>
            <div class="code">
                <pre>$form = new Form();
echo $form->open(['action' => 'submit.php', 'method' => 'POST']);
echo $form->input(['type' => 'text', 'name' => 'username', 'placeholder' => "Ім'я користувача"]);
echo $form->password(['name' => 'password', 'placeholder' => 'Пароль']);
echo $form->submit(['value' => 'Увійти']);
echo $form->close();</pre>
            </div>
        </div>

        <div class="section">
            <h2>2. Клас SmartForm</h2>
            <p>Форма зі збереженням значень після відправки:</p>
            <div class="form-example">
                <?php
                $smartForm = new SmartForm();
                echo $smartForm->open(['action' => $_SERVER['PHP_SELF'], 'method' => 'POST']);
                echo $smartForm->input(['name' => 'email', 'placeholder' => 'Email']);
                echo $smartForm->textarea(['name' => 'message', 'placeholder' => 'Повідомлення']);
                echo $smartForm->submit(['value' => 'Надіслати']);
                echo $smartForm->close();
                ?>
            </div>
            <div class="code">
                <pre>$smartForm = new SmartForm();
echo $smartForm->open(['action' => $_SERVER['PHP_SELF'], 'method' => 'POST']);
echo $smartForm->input(['name' => 'email', 'placeholder' => 'Email']);
echo $smartForm->textarea(['name' => 'message', 'placeholder' => 'Повідомлення']);
echo $smartForm->submit(['value' => 'Надіслати']);
echo $smartForm->close();</pre>
            </div>
        </div>

        <div class="section">
            <h2>3. Клас Cookie</h2>
            <p>Робота з куками:</p>
            <?php
            Cookie::set('demo_cookie', 'test_value', time() + 3600);
            $cookieValue = Cookie::get('demo_cookie');
            ?>
            <table>
                <tr>
                    <th>Дія</th>
                    <th>Код</th>
                    <th>Результат</th>
                </tr>
                <tr>
                    <td>Встановлення куки</td>
                    <td>Cookie::set('demo_cookie', 'test_value', time() + 3600)</td>
                    <td>Кука встановлена</td>
                </tr>
                <tr>
                    <td>Отримання значення куки</td>
                    <td>Cookie::get('demo_cookie')</td>
                    <td><?php echo htmlspecialchars($cookieValue); ?></td>
                </tr>
                <tr>
                    <td>Видалення куки</td>
                    <td>Cookie::del('demo_cookie')</td>
                    <td><?php Cookie::del('demo_cookie'); echo "Кука видалена"; ?></td>
                </tr>
            </table>
        </div>

        <div class="section">
            <h2>4. Клас Session</h2>
            <p>Робота з сесіями:</p>
            <?php
            $session = new Session();
            $session->set('demo_session', 'session_value');
            $sessionValue = $session->get('demo_session');
            $hasSession = $session->has('demo_session') ? 'Так' : 'Ні';
            ?>
            <table>
                <tr>
                    <th>Дія</th>
                    <th>Код</th>
                    <th>Результат</th>
                </tr>
                <tr>
                    <td>Встановлення змінної сесії</td>
                    <td>$session->set('demo_session', 'session_value')</td>
                    <td>Змінна встановлена</td>
                </tr>
                <tr>
                    <td>Отримання значення</td>
                    <td>$session->get('demo_session')</td>
                    <td><?php echo htmlspecialchars($sessionValue); ?></td>
                </tr>
                <tr>
                    <td>Перевірка наявності</td>
                    <td>$session->has('demo_session')</td>
                    <td><?php echo $hasSession; ?></td>
                </tr>
                <tr>
                    <td>Видалення змінної</td>
                    <td>$session->del('demo_session')</td>
                    <td><?php $session->del('demo_session'); echo "Змінна видалена"; ?></td>
                </tr>
            </table>
        </div>

        <div class="section">
            <h2>5. Клас Log</h2>
            <p>Робота з логами:</p>
            <?php
            $log = new Log('demo.log');
            $log->clear();
            $log->save('Перший запис у лог', 'INFO');
            $log->save('Другий запис у лог', 'WARNING');
            $log->save('Третій запис у лог', 'ERROR');
            $entries = $log->getEntries();
            ?>
            <table>
                <tr>
                    <th>Дія</th>
                    <th>Код</th>
                </tr>
                <tr>
                    <td>Запис у лог</td>
                    <td>$log->save('Перший запис у лог', 'INFO')</td>
                </tr>
                <tr>
                    <td>Отримання записів</td>
                    <td>$log->getEntries()</td>
                </tr>
            </table>
            <h3>Останні записи у лозі:</h3>
            <div class="code">
                <?php foreach ($entries as $entry): ?>
                    <div><?php echo htmlspecialchars($entry); ?></div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</body>
</html>