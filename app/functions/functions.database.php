<?php

/**
 * SQLite database file handling
 *
 * Since 2.4 all core tables live in one file (data/database/swiftyedit.sqlite3).
 * Older installations have three files (content, user, posts). They are merged
 * once, on the first request that runs the new code - no matter if the update
 * came through the ACP updater, the installer or by uploading the files
 * manually. If the merge fails, the site keeps running on the three old files
 * and the dashboard shows the reason.
 *
 * Used by app/database.php and install/inc.update.php - no other se_* functions
 * are available at that point.
 */

/**
 * after a failed merge, the next attempt waits this long (seconds),
 * so a failing merge doesn't slow down every single request
 */
const SE_DB_MERGE_RETRY_AFTER = 600;

/**
 * decide which SQLite file(s) to use, merge the legacy files if needed
 *
 * @param string $target path of the single database file
 * @param array{content: string, user: string, posts: string} $legacy paths of the old files
 * @return array{mode: string, error: string}
 *   mode 'single' = use $target, 'legacy' = use the three old files, 'none' = nothing installed
 */
function se_db_prepare_sqlite(string $target, array $legacy): array {

    if (is_file($target)) {
        return ['mode' => 'single', 'error' => ''];
    }

    if (!is_file($legacy['content'])) {
        return ['mode' => 'none', 'error' => ''];
    }

    $dir = dirname($target);
    $failed_marker = $target.'.failed';

    // a recent attempt failed - don't try again on every request
    if (is_file($failed_marker) && (time() - (int) filemtime($failed_marker)) < SE_DB_MERGE_RETRY_AFTER) {
        return ['mode' => 'legacy', 'error' => (string) file_get_contents($failed_marker)];
    }

    if (!is_writable($dir)) {
        return ['mode' => 'legacy', 'error' => 'directory is not writable: '.basename($dir).'/'];
    }

    // only one request merges, parallel requests wait for it and
    // then use the new file
    $lock_file = $dir.'/.swiftyedit-merge.lock';
    $lock = @fopen($lock_file, 'c');
    if ($lock === false) {
        return ['mode' => 'legacy', 'error' => 'cannot create lock file in '.basename($dir).'/'];
    }

    flock($lock, LOCK_EX);
    clearstatcache(true, $target);

    if (is_file($target)) {
        // merged by another request while this one was waiting
        flock($lock, LOCK_UN);
        fclose($lock);
        return ['mode' => 'single', 'error' => ''];
    }

    $error = se_db_merge_sqlite_files($target, $legacy);

    if ($error === '') {
        if (is_file($failed_marker)) {
            @unlink($failed_marker);
        }
        // requests that still wait for the lock hold their own handle and
        // find $target after they get it - new requests don't need the lock
        @unlink($lock_file);
    } else {
        @file_put_contents($failed_marker, $error);
    }

    flock($lock, LOCK_UN);
    fclose($lock);

    return ['mode' => ($error === '' ? 'single' : 'legacy'), 'error' => $error];
}

/**
 * build $target from the legacy files: content is copied as a whole,
 * the tables of user and posts are added to it
 *
 * Everything is built in a temporary file first, the row count of every table
 * is checked and only then the file gets its final name. The legacy files are
 * only read, never changed.
 *
 * @param string $target
 * @param array{content: string, user: string, posts: string} $legacy
 * @return string empty on success, otherwise the error message
 */
function se_db_merge_sqlite_files(string $target, array $legacy): string {

    $tmp = $target.'.tmp';
    se_db_remove_sqlite_file($tmp);

    $expected_rows = [];

    try {
        foreach ($legacy as $name => $path) {
            if ($name !== 'content' && !is_file($path)) {
                // user/posts may be missing on very old or broken installations
                continue;
            }
            $src = se_db_open_sqlite($path);
            foreach (se_db_sqlite_tables($src) as $table) {
                $rows = (int) $src->query('SELECT COUNT(*) FROM "'.$table.'"')->fetchColumn();
                if (isset($expected_rows[$table])) {
                    // the same table in two files: take the one that holds data
                    if ($expected_rows[$table]['rows'] > 0 && $rows > 0) {
                        return "table $table exists in ".$expected_rows[$table]['from']." and $name, both with data";
                    }
                    if ($rows === 0) {
                        continue;
                    }
                }
                $expected_rows[$table] = ['from' => $name, 'rows' => $rows];
            }
            $src = null;
        }

        // content as a whole - VACUUM INTO reads a consistent state,
        // including changes that are still in the -wal file
        $src = se_db_open_sqlite($legacy['content']);
        $journal_mode = strtolower((string) $src->query('PRAGMA journal_mode')->fetchColumn());
        $src->exec('VACUUM INTO '.$src->quote($tmp));
        $src = null;

        $db = se_db_open_sqlite($tmp);

        foreach (['user', 'posts'] as $name) {
            if (!is_file($legacy[$name])) {
                continue;
            }

            $db->exec('ATTACH DATABASE '.$db->quote($legacy[$name]).' AS legacy');
            $db->beginTransaction();

            foreach (se_db_sqlite_tables($db, 'legacy') as $table) {
                if (($expected_rows[$table]['from'] ?? '') !== $name) {
                    continue;
                }
                // an empty duplicate from content makes room for the real table
                $db->exec('DROP TABLE IF EXISTS main."'.$table.'"');

                $create_sql = (string) $db->query("SELECT sql FROM legacy.sqlite_master WHERE type = 'table' AND name = ".$db->quote($table))->fetchColumn();
                $db->exec($create_sql);
                $db->exec('INSERT INTO main."'.$table.'" SELECT * FROM legacy."'.$table.'"');
            }

            // indexes, triggers and views of the copied tables
            $extras = $db->query("SELECT type, name, tbl_name, sql FROM legacy.sqlite_master WHERE type IN ('index', 'trigger', 'view') AND sql IS NOT NULL")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($extras as $extra) {
                if ($extra['type'] !== 'view' && ($expected_rows[$extra['tbl_name']]['from'] ?? '') !== $name) {
                    continue;
                }
                $exists = (int) $db->query("SELECT COUNT(*) FROM main.sqlite_master WHERE name = ".$db->quote($extra['name']))->fetchColumn();
                if ($exists > 0) {
                    throw new RuntimeException($extra['type'].' '.$extra['name'].' exists in content and '.$name);
                }
                $db->exec($extra['sql']);
            }

            // AUTOINCREMENT counters - may be higher than the highest id if rows were deleted
            $has_sequence = (int) $db->query("SELECT COUNT(*) FROM legacy.sqlite_master WHERE name = 'sqlite_sequence'")->fetchColumn();
            if ($has_sequence > 0) {
                $sequences = $db->query('SELECT name, seq FROM legacy.sqlite_sequence')->fetchAll(PDO::FETCH_ASSOC);
                foreach ($sequences as $sequence) {
                    if (($expected_rows[$sequence['name']]['from'] ?? '') !== $name) {
                        continue;
                    }
                    $db->exec('DELETE FROM main.sqlite_sequence WHERE name = '.$db->quote($sequence['name']));
                    $db->exec('INSERT INTO main.sqlite_sequence (name, seq) VALUES ('.$db->quote($sequence['name']).', '.(int) $sequence['seq'].')');
                }
            }

            $db->commit();
            $db->exec('DETACH DATABASE legacy');
        }

        // every table with the same number of rows as in its source file
        foreach ($expected_rows as $table => $source) {
            $rows = (int) $db->query('SELECT COUNT(*) FROM main."'.$table.'"')->fetchColumn();
            if ($rows !== $source['rows']) {
                throw new RuntimeException("table $table has $rows rows, expected ".$source['rows']);
            }
        }

        $check = (string) $db->query('PRAGMA quick_check')->fetchColumn();
        if ($check !== 'ok') {
            throw new RuntimeException('quick_check: '.$check);
        }

        $db = null;

        if (!rename($tmp, $target)) {
            throw new RuntimeException('cannot rename '.basename($tmp));
        }

        // VACUUM INTO doesn't carry over WAL mode - it is a property of the file
        if ($journal_mode === 'wal') {
            $db = se_db_open_sqlite($target);
            $db->query('PRAGMA journal_mode = wal')->fetchColumn();
            $db = null;
        }

    } catch (\Throwable $e) {
        $db = null;
        $src = null;
        se_db_remove_sqlite_file($tmp);
        return $e->getMessage();
    }

    return '';
}

/**
 * @param string $path
 * @return PDO
 */
function se_db_open_sqlite(string $path): PDO {
    $pdo = new PDO('sqlite:'.$path);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('PRAGMA busy_timeout = 5000');
    return $pdo;
}

/**
 * names of the user tables in a database (without SQLite's internal tables)
 *
 * @param PDO $pdo
 * @param string $schema 'main' or the name of an attached database
 * @return list<string>
 */
function se_db_sqlite_tables(PDO $pdo, string $schema = 'main'): array {
    $tables = $pdo->query('SELECT name FROM "'.$schema.'".sqlite_master WHERE type = \'table\' AND name NOT LIKE \'sqlite_%\' ORDER BY name')->fetchAll(PDO::FETCH_COLUMN);
    return array_map('strval', $tables);
}

/**
 * delete a database file together with its -journal/-wal/-shm files
 *
 * @param string $path
 * @return bool false if one of the files could not be deleted
 */
function se_db_remove_sqlite_file(string $path): bool {
    $ok = true;
    foreach (['', '-journal', '-wal', '-shm'] as $suffix) {
        if (is_file($path.$suffix) && !@unlink($path.$suffix)) {
            $ok = false;
        }
    }
    return $ok;
}

/**
 * the legacy files that are still on disk after the merge
 *
 * @param array{content: string, user: string, posts: string} $legacy
 * @return list<string>
 */
function se_db_legacy_files_left(array $legacy): array {
    $left = [];
    foreach ($legacy as $path) {
        if (is_file($path)) {
            $left[] = $path;
        }
    }
    return $left;
}
