import os
import time
import psutil
import mysql.connector
from datetime import datetime

# Database connection
DB_CONFIG = {
    'host': 'localhost',
    'user': 'localhost',
    'password': ' ',
    'database': 'etms_db'
}

def log_to_db(user_id, activity_type, details):
    """Logs activity to the database."""
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor()
        sql = """INSERT INTO employee_activity_log (user_id, activity_type, details, timestamp) 
                 VALUES (%s, %s, %s, %s)"""
        cursor.execute(sql, (user_id, activity_type, details, datetime.now()))
        conn.commit()
        cursor.close()
        conn.close()
    except Exception as e:
        print(f"Database error: {e}")


def monitor_applications(user_id):
    """Monitors running applications."""
    tracked_apps = set()
    while True:
        for proc in psutil.process_iter(['pid', 'name']):
            if proc.info['name'] and proc.info['name'] not in tracked_apps:
                tracked_apps.add(proc.info['name'])
                log_to_db(user_id, 'Application Opened', proc.info['name'])
        time.sleep(5)


def monitor_file_access(user_id, watch_dir):
    """Monitors file access in a specific directory."""
    before = {f: os.stat(f).st_mtime for f in os.listdir(watch_dir)}
    while True:
        time.sleep(5)
        after = {f: os.stat(f).st_mtime for f in os.listdir(watch_dir)}
        for file in after:
            if file not in before:
                log_to_db(user_id, 'File Created', file)
            elif before[file] != after[file]:
                log_to_db(user_id, 'File Modified', file)
        before = after


def monitor_commands(user_id):
    """Monitors terminal/command line commands (Linux only)."""
    log_file = "/home/user/.bash_history"
    while True:
        with open(log_file, "r") as f:
            lines = f.readlines()
        if lines:
            log_to_db(user_id, 'Command Executed', lines[-1].strip())
        time.sleep(5)


if __name__ == "__main__":
    user_id = 1  # Replace with dynamic user ID after clock-in check
    watch_directory = "/path/to/watch"  # Adjust as needed
    
    from threading import Thread
    
    Thread(target=monitor_applications, args=(user_id,)).start()
    Thread(target=monitor_file_access, args=(user_id, watch_directory)).start()
    Thread(target=monitor_commands, args=(user_id,)).start()
