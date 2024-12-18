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
        $command = "mysqldump -u {$this->dbUser} -p'{$this->dbPassword}' {$this->dbName} > {$backupFile}";

        exec($command, $output, $returnVar);

        if ($returnVar === 0) {
            Yii::info("Backup создан: {$backupFile}", 'backup');
        } else {
            Yii::error("Ошибка создания бэкапа. Код: {$returnVar}", 'backup');
        }
    }
}