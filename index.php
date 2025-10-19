<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Calculadora IPv4</title>

  <style>
    /* ======== ESTILOS GENERALES ======== */
    * { box-sizing: border-box; }

    /* Fondo y formato del cuerpo */
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

    /* Contenedor principal de la calculadora */
    .container {
      background: #ffffff;
      width: 90%;
      max-width: 600px;
      padding: 30px;
      border-radius: 16px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
      text-align: center;
      animation: fadeIn 0.7s ease;
    }

    /* Animación al cargar */
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-10px); }
      to { opacity: 1; transform: translateY(0); }
    }

    /* Título principal */
    h1 { color: #333; margin-bottom: 20px; font-weight: 600; }

    /* Formulario de entrada */
    form { text-align: left; margin-bottom: 20px; }

    label { display: block; font-weight: 600; color: #444; margin-top: 10px; margin-bottom: 5px; }

    input {
      width: 100%;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 15px;
    }

    /* Botón de calcular */
    button {
      width: 100%;
      padding: 12px;
      background: #007bff;
      border: none;
      border-radius: 6px;
      color: white;
      font-size: 16px;
      margin-top: 15px;
      cursor: pointer;
      transition: background 0.3s ease;
    }

    button:hover { background: #0056b3; }

    /* Sección de resultados */
    .resultado {
      background: #f9f9f9;
      border-radius: 10px;
      padding: 20px;
      text-align: left;
      margin-top: 20px;
      border-left: 4px solid #007bff;
    }

    .resultado p { margin: 8px 0; color: #333; }

    /* Estilo de la IP en binario */
    .binario {
      background: #eee;
      padding: 10px;
      border-radius: 6px;
      font-family: monospace;
      margin-top: 10px;
      overflow-x: auto;
      white-space: nowrap;
    }

    /* Colores para los bits de red y host */
    .red { color: #e53935; font-weight: bold; }
    .host { color: #43a047; font-weight: bold; }

    /* Mensaje de error */
    .error { color: #d32f2f; font-weight: bold; margin-top: 10px; }
  </style>

  <script>
    /* ======== VALIDACIÓN DEL FORMATO IPv4 ======== */

    // Esta función verifica que el valor tenga el formato correcto "x.x.x.x"
    // donde cada x es un número entre 0 y 255.
    function validarIPv4(valor) {
      const regex = /^(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}$/;
      return regex.test(valor);
    }

    // Esta función se ejecuta al presionar "Calcular"
    function validarFormulario() {
      const ip = document.querySelector('input[name="ip"]').value.trim();
      const mask = document.querySelector('input[name="mask"]').value.trim();
      const errorDiv = document.getElementById("mensaje-error");
      errorDiv.innerHTML = "";

      // Validar IP
      if (!validarIPv4(ip)) {
        errorDiv.textContent = "Error: La dirección IP no tiene un formato válido (x.x.x.x)";
        return false;
      }

      // Validar máscara
      if (!validarIPv4(mask)) {
        errorDiv.textContent = "Error: La máscara de subred no tiene un formato válido (x.x.x.x)";
        return false;
      }

      // Si todo está bien, se envía el formulario
      return true;
    }
  </script>
</head>

<body>

  <div class="container">
    <h1>Calculadora IPv4</h1>

    <!-- Formulario con validación de IP y máscara -->
    <form method="post" onsubmit="return validarFormulario()">
      <label>Dirección IP:</label>
      <input type="text" name="ip" placeholder="Ejemplo: 192.168.1.10" required>

      <label>Máscara de subred:</label>
      <input type="text" name="mask" placeholder="Ejemplo: 255.255.255.0" required>

      <button type="submit">Calcular</button>

      <!-- Aquí se mostrará el mensaje de error si el formato no es válido -->
      <div id="mensaje-error" class="error"></div>
    </form>

<?php
// =================== LÓGICA PHP ===================

if ($_SERVER["REQUEST_METHOD"] == "POST") {

  // Función auxiliar: convierte un número decimal a binario de 8 bits
  function dec2bin8($num) {
    return str_pad(decbin($num), 8, "0", STR_PAD_LEFT);
  }

  // Se capturan los datos ingresados por el usuario
  $ip = $_POST["ip"];
  $mask = $_POST["mask"];

  // Se separan los octetos en arreglos
  $ipParts = array_map('intval', explode('.', $ip));
  $maskParts = array_map('intval', explode('.', $mask));

  // Se convierten a binario los 4 octetos y se concatenan
  $ipBin = implode('', array_map('dec2bin8', $ipParts));
  $maskBin = implode('', array_map('dec2bin8', $maskParts));

  // Se calcula la IP de red (operación AND bit a bit)
  $networkBin = '';
  for ($i = 0; $i < 32; $i++) {
    $networkBin .= $ipBin[$i] & $maskBin[$i];
  }

  // Contamos los bits de red (cantidad de 1s en la máscara)
  $maskBits = substr_count($maskBin, '1');

  // Se genera la IP de broadcast (bits de host en 1)
  $broadcastBin = substr($networkBin, 0, $maskBits) . str_repeat('1', 32 - $maskBits);

  // Conversión de binario a decimal para mostrar las direcciones
  $networkDec = join('.', array_map('bindec', str_split($networkBin, 8)));
  $broadcastDec = join('.', array_map('bindec', str_split($broadcastBin, 8)));

  // Cálculo de cantidad de hosts útiles: 2^(bits host) - 2
  $hostBits = 32 - $maskBits;
  $hostsUtiles = ($hostBits > 1) ? pow(2, $hostBits) - 2 : 0;

  // Determinar la clase de IP según el primer octeto
  $clase = '';
  $primero = $ipParts[0];
  if ($primero >= 1 && $primero <= 126) $clase = "A";
  elseif ($primero >= 128 && $primero <= 191) $clase = "B";
  elseif ($primero >= 192 && $primero <= 223) $clase = "C";
  elseif ($primero >= 224 && $primero <= 239) $clase = "D";
  else $clase = "E";

  // Determinar si la IP es pública o privada
  $tipo = "Pública";
  if ($primero == 10 ||
      ($primero == 172 && $ipParts[1] >= 16 && $ipParts[1] <= 31) ||
      ($primero == 192 && $ipParts[1] == 168)) {
    $tipo = "Privada";
  }

  // Calcular rango de IPs útiles (primera y última dirección de host)
  $networkNum = bindec($networkBin);
  $broadcastNum = bindec($broadcastBin);
  $startIP = long2ip($networkNum + 1);
  $endIP = long2ip($broadcastNum - 1);

  // Representación binaria coloreada (red = rojo, host = verde)
  $binarioRedHost = '';
  for ($i = 0; $i < 32; $i++) {
    $b = $ipBin[$i];
    if ($i < $maskBits) $binarioRedHost .= "<span class='red'>$b</span>";
    else $binarioRedHost .= "<span class='host'>$b</span>";
    if (($i + 1) % 8 == 0 && $i != 31) $binarioRedHost .= ".";
  }

  // =================== RESULTADOS ===================
  echo "<div class='resultado'>
    <p><strong>IP ingresada:</strong> $ip</p>
    <p><strong>Máscara:</strong> $mask /$maskBits</p>
    <p><strong>IP de Red:</strong> $networkDec</p>
    <p><strong>IP de Broadcast:</strong> $broadcastDec</p>
    <p><strong>Cantidad de hosts útiles:</strong> $hostsUtiles</p>
	    <p><strong>Rango de IPs útiles:</strong> $startIP - $endIP</p>
    <p><strong>Clase de IP:</strong> $clase</p>
    <p><strong>Tipo de IP:</strong> $tipo</p>
    <p><strong>Porción de red y host (binario):</strong></p>
    <div class='binario'>$binarioRedHost</div>
  </div>";
}
?>
  </div>

</body>
</html>
