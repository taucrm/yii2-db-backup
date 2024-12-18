# Yii2 DB Backup Component

A Yii2 component for creating database backups using `mysqldump`. It organizes backups into monthly folders, making it easy to manage and retrieve backups by date.

---

## Installation

Install the package via Composer:

```bash
composer require taucrm/yii2-db-backup
```

---

## Configuration in Yii2 Advanced Template

To use this component in the **console** application of the Yii2 Advanced Template, follow these steps:

1. **Add the component to the `console/config/main.php` file**:

   ```php
   'components' => [
       'backupDatabase' => [
           'class' => 'taucrm\db\BackupDatabase',
           'dbName' => '',          // Optional: Database name
           'dbUser' => '',          // Optional: Database username
           'dbPassword' => '',      // Optional: Database password
           'backupPath' => '@console/runtime/backups', // Path to store backups
       ],
   ],
   ```

    - If `dbName`, `dbUser`, or `dbPassword` are not set, the component will automatically fetch them from `common/config/main-local.php`.

2. **Create a console command** to trigger the backup.

   In the `console/controllers` directory, create a file named `BackupController.php`:

   ```php
   <?php

   namespace console\controllers;

   use Yii;
   use yii\console\Controller;

   class BackupController extends Controller
   {
       /**
        * Runs the database backup.
        */
       public function actionRun()
       {
           Yii::$app->backupDatabase->runBackup();
           echo "Database backup completed successfully.\n";
       }
   }
   ```

---

## Running the Backup Manually

To manually trigger the backup, run the following command in your terminal:

```bash
php yii backup/run
```

---

## Automating Backups with Cron

To automate backups every hour, configure a cron job for the console command:

1. Open your crontab editor:

   ```bash
   crontab -e
   ```

2. Add the following line:

   ```bash
   0 * * * * /usr/bin/php /path/to/your/project/yii backup/run
   ```

    - **`/usr/bin/php`**: Path to the PHP executable.
    - **`/path/to/your/project`**: Full path to the root directory of your Yii2 Advanced project.

3. Save and exit.

---

## Backup Structure

Backups are organized into monthly folders for better management. The directory structure looks like this:

```
console/runtime/backups/
├── 2024-06/
│   ├── bpm_2024-06-18_09-00.sql
│   ├── bpm_2024-06-18_10-00.sql
├── 2024-07/
│   ├── bpm_2024-07-01_09-00.sql
```

- **Monthly folders**: Named as `YYYY-MM` (e.g., `2024-06`, `2024-07`).
- **Backup files**: Named with the format `bpm_YYYY-MM-DD_HH-MM.sql`.

---

## Automated Backup Workflow

1. The cron job triggers the **`backup/run`** console command every hour.
2. The component:
    - Creates a folder for the current month (e.g., `2024-06`) if it doesn't already exist.
    - Runs `mysqldump` to back up the database.
    - Saves the backup file with a timestamped name.
3. Backups are neatly organized into monthly folders for easy access and management.

---

## Example Use Cases

- Automate regular database backups on production servers.
- Store backups for each month separately to keep them organized.
- Easily integrate with other cron-based automation tools.

---