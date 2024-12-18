<?php

namespace taucrm\db;

use Yii;
use yii\base\Component;

class BackupDatabase extends Component
{
    public $dbName;
    public $dbUser;
    public $dbPassword;
    public $backupPath;

    public function init()
    {
        parent::init();

        // Загружаем параметры из main-local.php, если они не заданы явно
        if (empty($this->dbName) || empty($this->dbUser) || empty($this->dbPassword)) {
            $db = Yii::$app->db->dsn;

            if (preg_match('/dbname=([^;]*)/', $db, $matches)) {
                $this->dbName = $this->dbName ?: $matches[1];
            }

            $this->dbUser = $this->dbUser ?: Yii::$app->db->username;
            $this->dbPassword = $this->dbPassword ?: Yii::$app->db->password;
        }

        // Путь по умолчанию
        $this->backupPath = $this->backupPath ?: '@app/backups';
    }

    public function runBackup()
    {
        $currentMonth = date('Y-m');
        $currentDateTime = date('Y-m-d_H-i');
        $monthFolder = Yii::getAlias($this->backupPath) . DIRECTORY_SEPARATOR . $currentMonth;

        if (!file_exists($monthFolder)) {
            mkdir($monthFolder, 0777, true);
        }

        $backupFile = $monthFolder . DIRECTORY_SEPARATOR . "bpm_{$currentDateTime}.sql";

        try {
            $db = Yii::$app->db;
            $tables = $db->createCommand('SHOW TABLES')->queryColumn();

            $file = fopen($backupFile, 'w');

            foreach ($tables as $table) {
                // Удаление таблицы перед восстановлением
                fwrite($file, "DROP TABLE IF EXISTS `$table`;\n");

                // Получаем структуру таблицы
                $createTable = $db->createCommand("SHOW CREATE TABLE `$table`")->queryOne();
                fwrite($file, $createTable['Create Table'] . ";\n\n");

                // Получаем все строки из таблицы
                $rows = (new \yii\db\Query())->from($table)->all();
                foreach ($rows as $row) {
                    $row = array_map([$db, 'quoteValue'], $row);
                    $data = implode(', ', $row);
                    fwrite($file, "INSERT INTO `$table` VALUES ($data);\n");
                }
                fwrite($file, "\n\n");
            }

            fclose($file);

            Yii::info("Backup создан: {$backupFile}", 'backup');
        } catch (\Exception $e) {
            Yii::error("Ошибка создания бэкапа: " . $e->getMessage(), 'backup');
        }
    }
}
