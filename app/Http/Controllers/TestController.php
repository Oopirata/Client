<?php

namespace App\Http\Controllers;

// use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Client;

use function GuzzleHttp\json_encode;

class TestController extends Controller
{
    //

    public function index(){

        $cpuquery = "WITH SpaceUsage AS (
                        SELECT 
                            SUM(CASE WHEN mf.[type] = 0 THEN mf.size * 8.0 / 1024 END) AS data_used_size,
                            SUM(CASE WHEN mf.[type] = 1 THEN mf.size * 8.0 / 1024 END) AS log_used_size
                        FROM sys.master_files mf
                        JOIN sys.databases d ON d.database_id = mf.database_id
                        WHERE d.[state] = 0
                    ),
                    DiskUsage AS (
                        SELECT 
                            SUM(CAST(mf.size * 8.0 / 1024 AS DECIMAL(18,2))) AS total_size,
                            SUM(CASE WHEN mf.[type] = 0 THEN CAST(mf.size * 8.0 / 1024 AS DECIMAL(18,2)) END) AS data_size,
                            (SELECT SUM(data_used_size) FROM SpaceUsage) AS used_data_size
                        FROM sys.master_files mf
                        JOIN sys.databases d ON d.database_id = mf.database_id
                    ),
                    RAMUsage AS (
                        SELECT
                            (total_physical_memory_kb / 1024) AS total_memory_mb,
                            ((total_physical_memory_kb - available_physical_memory_kb) / 1024) AS memory_in_use_mb,
                            (SELECT physical_memory_in_use_kb / 1024 FROM sys.dm_os_process_memory) AS sql_memory_mb
                        FROM sys.dm_os_sys_memory
                    ),
                    CPUUsage AS (
                        SELECT 
                            (100 - x.value('(./Record/SchedulerMonitorEvent/SystemHealth/SystemIdle/text())[1]', 'TINYINT')) AS cpu_total,
                            (cpu_sql / cpu_base * 100) AS cpu_sql
                        FROM (
                            SELECT TOP(1) [timestamp], x = CONVERT(XML, record)
                            FROM sys.dm_os_ring_buffers
                            WHERE ring_buffer_type = N'RING_BUFFER_SCHEDULER_MONITOR'
                            AND record LIKE '%<SystemHealth>%'
                            ORDER BY [timestamp] DESC
                        ) r
                        CROSS APPLY (
                            SELECT 
                                MAX(CASE WHEN counter_name = 'CPU usage %' THEN cntr_value END) AS cpu_sql,
                                MAX(CASE WHEN counter_name = 'CPU usage % base' THEN cntr_value END) AS cpu_base
                            FROM sys.dm_os_performance_counters
                            WHERE counter_name IN ('CPU usage %', 'CPU usage % base')
                            AND instance_name = 'default'
                        ) pc
                    )
                    SELECT
                        'server' AS server,
                        cpu.cpu_total AS cpu_utilization,
                        ISNULL(cpu.cpu_sql, 0) AS cpu_sql_util,
                        ram.total_memory_mb,
                        ram.memory_in_use_mb,
                        ram.sql_memory_mb,
                        disk.total_size AS disk_size,
                        disk.data_size AS data_size,
                        disk.used_data_size
                    FROM CPUUsage cpu
                    CROSS JOIN RAMUsage ram
                    CROSS JOIN DiskUsage disk;
                    ";
        $quer = "DECLARE @lastNmin INT;
                    SET @lastNmin = 10;

                    SELECT TOP 10
                        CONVERT(CHAR(100), SERVERPROPERTY('Servername')) AS Server,
                        dest.TEXT AS [Query],
                        SUM(deqs.execution_count) AS [Count],
                        MAX(deqs.last_execution_time) AS [Time]
                    FROM sys.dm_exec_query_stats AS deqs
                    CROSS APPLY sys.dm_exec_sql_text(deqs.sql_handle) AS dest
                    CROSS APPLY sys.dm_exec_plan_attributes(deqs.plan_handle) AS epa
                    WHERE epa.attribute = 'dbid'
                    AND DB_NAME(CONVERT(int, epa.value)) NOT IN ('master', 'tempdb', 'model', 'msdb')
                    AND deqs.last_execution_time >= DATEADD(MINUTE, -@lastNmin, GETDATE())
                    GROUP BY dest.TEXT, DB_NAME(CONVERT(int, epa.value))
                    ORDER BY SUM(deqs.execution_count) DESC;";

        $queq = "SELECT 
                A.[Server],  
                A.last_db_backup_date,  
                B.backup_start_date,  
                B.backup_size,  
                B.physical_device_name,   
                B.backupset_name
                FROM 
                ( 
                SELECT   
                    CONVERT(CHAR(100), SERVERPROPERTY('Servername')) AS Server, 
                    msdb.dbo.backupset.database_name,  
                    MAX(msdb.dbo.backupset.backup_finish_date) AS last_db_backup_date 
                FROM 
                    msdb.dbo.backupmediafamily  
                    INNER JOIN msdb.dbo.backupset ON msdb.dbo.backupmediafamily.media_set_id = msdb.dbo.backupset.media_set_id  
                WHERE 
                    msdb..backupset.type = 'D' 
                GROUP BY 
                    msdb.dbo.backupset.database_name  
                ) AS A 
                LEFT JOIN  
                ( 
                SELECT   
                    CONVERT(CHAR(100), SERVERPROPERTY('Servername')) AS Server, 
                    msdb.dbo.backupset.database_name,  
                    msdb.dbo.backupset.backup_start_date,  
                    msdb.dbo.backupset.backup_finish_date, 
                    msdb.dbo.backupset.expiration_date, 
                    msdb.dbo.backupset.backup_size,  
                    msdb.dbo.backupmediafamily.logical_device_name,  
                    msdb.dbo.backupmediafamily.physical_device_name,   
                    msdb.dbo.backupset.name AS backupset_name, 
                    msdb.dbo.backupset.description 
                FROM 
                    msdb.dbo.backupmediafamily  
                    INNER JOIN msdb.dbo.backupset ON msdb.dbo.backupmediafamily.media_set_id = msdb.dbo.backupset.media_set_id  
                WHERE 
                    msdb..backupset.type = 'D' 
                ) AS B 
                ON A.[server] = B.[server] AND A.[database_name] = B.[database_name] AND A.[last_db_backup_date] = B.[backup_finish_date] 
                ORDER BY  
                A.database_name ";

                $cpuquery = "WITH SpaceUsage AS (
                    SELECT 
                        SUM(CASE WHEN mf.[type] = 0 THEN mf.size * 8.0 / 1024 END) AS data_used_size,
                        SUM(CASE WHEN mf.[type] = 1 THEN mf.size * 8.0 / 1024 END) AS log_used_size
                    FROM sys.master_files mf
                    JOIN sys.databases d ON d.database_id = mf.database_id
                    WHERE d.[state] = 0
                ),
                DiskUsage AS (
                    SELECT 
                        SUM(CAST(mf.size * 8.0 / 1024 AS DECIMAL(18,2))) AS total_size,
                        SUM(CASE WHEN mf.[type] = 0 THEN CAST(mf.size * 8.0 / 1024 AS DECIMAL(18,2)) END) AS data_size,
                        (SELECT SUM(data_used_size) FROM SpaceUsage) AS used_data_size
                    FROM sys.master_files mf
                    JOIN sys.databases d ON d.database_id = mf.database_id
                ),
                RAMUsage AS (
                    SELECT
                        (total_physical_memory_kb / 1024) AS total_memory_mb,
                        ((total_physical_memory_kb - available_physical_memory_kb) / 1024) AS memory_in_use_mb,
                        (SELECT physical_memory_in_use_kb / 1024 FROM sys.dm_os_process_memory) AS sql_memory_mb
                    FROM sys.dm_os_sys_memory
                ),
                CPUUsage AS (
                    SELECT 
                        (100 - x.value('(./Record/SchedulerMonitorEvent/SystemHealth/SystemIdle/text())[1]', 'TINYINT')) AS cpu_total,
                        (cpu_sql / cpu_base * 100) AS cpu_sql
                    FROM (
                        SELECT TOP(1) [timestamp], x = CONVERT(XML, record)
                        FROM sys.dm_os_ring_buffers
                        WHERE ring_buffer_type = N'RING_BUFFER_SCHEDULER_MONITOR'
                        AND record LIKE '%<SystemHealth>%'
                        ORDER BY [timestamp] DESC
                    ) r
                    CROSS APPLY (
                        SELECT 
                            MAX(CASE WHEN counter_name = 'CPU usage %' THEN cntr_value END) AS cpu_sql,
                            MAX(CASE WHEN counter_name = 'CPU usage % base' THEN cntr_value END) AS cpu_base
                        FROM sys.dm_os_performance_counters
                        WHERE counter_name IN ('CPU usage %', 'CPU usage % base')
                        AND instance_name = 'default'
                    ) pc
                )
                SELECT
                    'server' AS server,
                    cpu.cpu_total AS cpu_utilization,
                    ISNULL(cpu.cpu_sql, 0) AS cpu_sql_util,
                    ram.total_memory_mb,
                    ram.memory_in_use_mb,
                    ram.sql_memory_mb,
                    disk.total_size AS disk_size,
                    disk.data_size AS data_size,
                    disk.used_data_size
                FROM CPUUsage cpu
                CROSS JOIN RAMUsage ram
                CROSS JOIN DiskUsage disk;
                ";

        $body= DB::connection('hospital')->select($quer);
        $body = $body[0];
        $body['name'] = env('HOSPITAL_NAME');
        dd($body);
        $body['server'] = env('HOSPITAL_SERVER');
        $body = json_encode($body);
        $headers = [
            'Content-Type' => 'application/json'
          ];
          $client = new Client();
          $request = new Request('POST', 'http://192.168.68.101/api/cpu', $headers, $body);
          dd($request->getBody()->getContents());
        $res = $client->sendAsync($request)->wait();
        dd($res->getBody()->getContents());
        
    }
}
