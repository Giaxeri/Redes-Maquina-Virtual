<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>App</title>

<style>
    body {
        margin: 0;
        padding: 0;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: linear-gradient(135deg, #74ABE2, #5563DE);
        height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .container {
        background: #ffffff;
        width: 90%;
        max-width: 700px;
        padding: 30px;
        border-radius: 16px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        animation: fadeIn 0.7s ease;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-10px); }
      to { opacity: 1; transform: translateY(0); }
    }

    h1 {
        text-align: center;
        margin-bottom: 20px;
        color: #333;
    }

    .menu button {
        width: 100%;
        padding: 12px;
        background: #007bff;
        color: white;
        border: none;
        margin-top: 10px;
        font-size: 18px;
        border-radius: 6px;
        cursor: pointer;
    }

    .menu button:hover { background: #0056b3; }

    /* ======== Estilos previos de la calculadora ======== */

    form { text-align: left; margin-bottom: 20px; }
    label { display: block; font-weight: 600; color: #444; margin-top: 10px; }
    input {
      width: 100%; padding: 10px; border: 1px solid #ccc;
      border-radius: 6px; font-size: 15px;
    }
    button { width: 100%; padding: 12px; background: #007bff; border: none;
             border-radius: 6px; color: white; font-size: 16px; margin-top: 15px; }
    .resultado {
      background: #f9f9f9; padding: 20px; border-radius: 10px;
      text-align: left; margin-top: 20px; border-left: 4px solid #007bff;
    }
    .binario { background: #eee; padding: 10px; border-radius: 6px; font-family: monospace; }
    .red { color: #e53935; font-weight: bold; }
    .host { color: #43a047; font-weight: bold; }
    .error { color: #d32f2f; font-weight: bold; margin-top: 10px; }
</style>

<script>
// Validación JS
function validarIPv4(valor) {
    const r = /^(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}$/;
    return r.test(valor);
}

function validarFormulario() {
    const ip = document.querySelector('input[name="ip"]').value.trim();
    const mask = document.querySelector('input[name="mask"]').value.trim();
    const errorDiv = document.getElementById("mensaje-error");
    errorDiv.innerHTML = "";

    if (!validarIPv4(ip)) {
        errorDiv.textContent = "Error: La dirección IP no es válida.";
        return false;
    }
    if (!validarIPv4(mask)) {
        errorDiv.textContent = "Error: La máscara no es válida.";
        return false;
    }
    return true;
}
</script>

</head>
<body>

<div class="container">

<?php
// Detección de secciones
$vista = $_POST["vista"] ?? "menu";

if ($vista == "menu") {
    echo '
    <h1>Menú Principal</h1>
    <form method="post" class="menu">
        <button name="vista" value="calc">Calculadora IPv4</button>
        <button name="vista" value="skills">Skills</button>
    </form>';
}

// ==================== SKILLS ====================
elseif ($vista == "skills") {
    echo '
    <h1>Skills</h1>
    <p style="font-size:22px; text-align:center;">Hola mundo 👋</p>

    <form method="post">
        <button name="vista" value="menu">Volver</button>
    </form>';
}

// ==================== CALCULADORA ====================
elseif ($vista == "calc") {

    echo '<h1>Calculadora IPv4</h1>';

    echo '
    <form method="post" onsubmit="return validarFormulario()">
        <input type="hidden" name="vista" value="calc">

        <label>Dirección IP:</label>
        <input type="text" name="ip" required placeholder="192.168.1.10">

        <label>Máscara de subred:</label>
        <input type="text" name="mask" required placeholder="255.255.255.0">

        <button type="submit">Calcular</button>
        <div id="mensaje-error" class="error"></div>
    </form>';

    if (isset($_POST["ip"]) && isset($_POST["mask"])) {

        function dec2bin8($n){ return str_pad(decbin($n),8,"0",STR_PAD_LEFT); }

        $ip = $_POST["ip"];
        $mask = $_POST["mask"];

        $ipParts = array_map('intval', explode('.', $ip));
        $maskParts = array_map('intval', explode('.', $mask));

        $ipBin = implode('', array_map('dec2bin8', $ipParts));
        $maskBin = implode('', array_map('dec2bin8', $maskParts));

        $networkBin = "";
        for ($i=0;$i<32;$i++) $networkBin .= $ipBin[$i] & $maskBin[$i];

        $maskBits = substr_count($maskBin,'1');
        $broadcastBin = substr($networkBin,0,$maskBits) . str_repeat('1',32-$maskBits);

        $networkDec = join('.', array_map('bindec', str_split($networkBin,8)));
        $broadcastDec = join('.', array_map('bindec', str_split($broadcastBin,8)));

        $hostBits = 32 - $maskBits;
        $hostsUtiles = ($hostBits>1) ? pow(2,$hostBits)-2 : 0;

        $primero = $ipParts[0];
        if ($primero<=126) $clase="A";
        elseif ($primero<=191) $clase="B";
        elseif ($primero<=223) $clase="C";
        elseif ($primero<=239) $clase="D";
        else $clase="E";

        $tipo = "Pública";
        if ($primero==10 || ($primero==172 && $ipParts[1]>=16 && $ipParts[1]<=31) || ($primero==192 && $ipParts[1]==168)) {
            $tipo = "Privada";
        }

        $networkNum = bindec($networkBin);
        $broadcastNum = bindec($broadcastBin);
        $startIP = long2ip($networkNum+1);
        $endIP = long2ip($broadcastNum-1);

        $binarioRedHost = "";
        for ($i=0;$i<32;$i++){
            $bit=$ipBin[$i];
            if ($i < $maskBits) $binarioRedHost .= "<span class='red'>$bit</span>";
            else $binarioRedHost .= "<span class='host'>$bit</span>";
            if(($i+1)%8==0 && $i!=31) $binarioRedHost.=".";
        }

        echo "
        <div class='resultado'>
            <p><strong>IP ingresada:</strong> $ip</p>
            <p><strong>Máscara:</strong> $mask /$maskBits</p>
            <p><strong>IP de Red:</strong> $networkDec</p>
            <p><strong>IP de Broadcast:</strong> $broadcastDec</p>
            <p><strong>Hosts útiles:</strong> $hostsUtiles</p>
            <p><strong>Rango:</strong> $startIP - $endIP</p>
            <p><strong>Clase:</strong> $clase</p>
            <p><strong>Tipo:</strong> $tipo</p>
            <p><strong>Binario:</strong></p>
            <div class='binario'>$binarioRedHost</div>
        </div>";
    }

    echo '
    <form method="post">
        <button name="vista" value="menu">Volver</button>
    </form>';

}

?>

</div>

</body>
</html>
