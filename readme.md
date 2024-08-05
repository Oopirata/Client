# Laravel Project Installation Guide

## Pendahuluan

Dokumentasi ini menjelaskan langkah-langkah untuk menginstalasi dan mengkonfigurasi proyek Laravel Anda, termasuk mengatur environment, menambahkan scheduler task, dan memastikan semuanya berjalan dengan baik.

## Langkah Instalasi

1. *Clone Repository*

    ```bash
    git clone https://github.com/Oopirata/Client.git
    cd Client
    ```

2. *Install Dependencies*

    ```bash
    composer install
    ```
    

3. *Setup Environment File*

    Setup file .env sesuai dengan kebutuhan seperti database connection dan juga data rumah sakit.
   ```bash
   HOSPITAL_NAME = 
   HOSPITAL_SERVER = 
   API_PASSWORD = 
    ```

5. *Generate Application Key*

    ```bash
    php artisan key:generate
    ```

## Setup Task Scheduler

1. **Setup File .bat**

    Sesuaikan file .bat untuk task scheduler sesuai dengan path di komputer Anda.

    *schedulerCpuQueries.bat*:

    ```bash
    @echo off
    cd {Your Path}\Client
    php artisan cpu:post
    php artisan queries:post
    ```

    *schedulerBackup.bat*:

    Sesuaikan pathnya dengan path di komputer client, lakukan juga untuk backup.bat.

2. *Import Task Scheduler di Windows*

    - Buka Task Scheduler di Windows.
    - Import task XML yang sudah disediakan (CPU Util & Queries.xml dan Backup Monitoring.xml).
      ![WhatsApp Image 2024-08-05 at 11 08 01_36aa7736](https://github.com/user-attachments/assets/662d20fd-a7f3-4655-936e-e247d5166b54)
    - Sesuaikan path untuk actionnya yaitu file .bat yang sudah disesuaikan di atas.
      ![WhatsApp Image 2024-08-05 at 11 10 15_db169d88](https://github.com/user-attachments/assets/e9937927-b012-4639-be89-62c90a4cfed9)
