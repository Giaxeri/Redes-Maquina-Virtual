<div align="center">

# Calculadora de Subredes IPv4

Aplicación web en **PHP** que calcula los parámetros de una red IPv4 a partir de una dirección IP y su máscara,
desplegada en un servidor **Apache** sobre una máquina virtual.

![PHP](https://img.shields.io/badge/PHP-777BB4?style=flat-square&logo=php&logoColor=white)
![Apache](https://img.shields.io/badge/Apache-D22128?style=flat-square&logo=apache&logoColor=white)
![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=flat-square&logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=flat-square&logo=css3&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=flat-square&logo=javascript&logoColor=black)

</div>

---

## Descripción

Proyecto académico de la asignatura de **Redes** (Ingeniería de Sistemas, Universidad El Bosque). El objetivo fue
construir una herramienta web de subnetting y desplegarla en una máquina virtual con un servidor web, aplicando
en la práctica los conceptos de direccionamiento IPv4.

A partir de una **dirección IP** y una **máscara de subred** en notación decimal punteada, la aplicación calcula:

| Resultado | Descripción |
|---|---|
| **Prefijo CIDR** | Número de bits de red (`/24`, `/26`, ...) |
| **Dirección de red** | `IP AND máscara`, bit a bit |
| **Dirección de broadcast** | Bits de red + todos los bits de host en `1` |
| **Hosts útiles** | `2^(bits de host) − 2` |
| **Rango de hosts** | Primera y última dirección asignable |
| **Clase** | A, B, C, D o E según el primer octeto |
| **Tipo** | Pública o privada (RFC 1918: `10/8`, `172.16/12`, `192.168/16`) |
| **Representación binaria** | Los 32 bits de la IP, con la porción de red y de host resaltadas en colores distintos |

## Cómo funciona

```
Formulario (IP + máscara)
        │  validación en el cliente (JavaScript, expresión regular IPv4)
        ▼
POST → index.php
        │  1. Convierte cada octeto a binario de 8 bits
        │  2. Operación AND bit a bit → dirección de red
        │  3. Cuenta los bits en 1 de la máscara → prefijo CIDR
        │  4. Rellena los bits de host con 1 → broadcast
        │  5. Calcula hosts, rango, clase y tipo
        ▼
Resultado renderizado en HTML
```

La aplicación es de **un solo archivo** (`index.php`) y usa una navegación por vistas con formularios `POST`
(`menu`, `calc` y `skills`), sin base de datos ni dependencias externas.

## Ejemplo

Entrada: `192.168.1.10` / `255.255.255.0`

```
Máscara:        255.255.255.0 /24
IP de red:      192.168.1.0
Broadcast:      192.168.1.255
Hosts útiles:   254
Rango:          192.168.1.1 - 192.168.1.254
Clase:          C
Tipo:           Privada
```

## Ejecución

### En local con XAMPP

1. Instala [XAMPP](https://www.apachefriends.org/) e inicia **Apache**.
2. Copia `index.php` en `C:\xampp\htdocs\calculadora-ipv4\`.
3. Abre `http://localhost/calculadora-ipv4/`.

### En una máquina virtual (Linux)

```bash
sudo apt update && sudo apt install -y apache2 php libapache2-mod-php
sudo cp index.php /var/www/html/
sudo systemctl restart apache2
```

Luego abre `http://<IP-de-la-VM>/index.php` desde el navegador del equipo anfitrión
(con la red de la VM en modo *puente* o con reenvío de puertos en modo NAT).

### Con el servidor integrado de PHP

```bash
php -S localhost:8000
```

## Estructura

```
.
├── index.php    # Interfaz, validación en el cliente y lógica de cálculo
└── README.md
```

## Autor

**Gianfranco Peniche Uribe** — Estudiante de Ingeniería de Sistemas, Universidad El Bosque
[GitHub @Giaxeri](https://github.com/Giaxeri)
