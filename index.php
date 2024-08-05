<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validador de Correos Electrónicos y Dominios</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            text-align: center;
        }

        input[type="email"], input[type="text"] {
            width: 80%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        button {
            padding: 10px 20px;
            border-radius: 5px;
            border: none;
            background-color: #007bff;
            color: white;
            cursor: pointer;
        }

        button:disabled {
            background-color: #cccccc;
        }

        #loader, #domain-loader {
            display: none;
            margin: 10px;
        }

        .form-section {
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <h1>Validador de Correos Electrónicos</h1>
    <div class="form-section">
        <form id="emailForm">
            <input type="email" id="email" placeholder="Ingrese el correo electrónico" required>
            <button type="submit">Validar</button>
        </form>
        <div id="loader">Verificando...</div>
        <div id="result"></div>
    </div>

    <h1>Validador de Dominio</h1>
    <div class="form-section">
        <form id="domainForm">
            <input type="text" id="domain" placeholder="Ingrese el dominio (ej: example.com)" required>
            <button type="submit">Validar Dominio</button>
        </form>
        <div id="domain-loader">Verificando dominio...</div>
        <div id="domain-result"></div>
    </div>

    <script>
        // Validación de correo electrónico
        document.getElementById('emailForm').addEventListener('submit', function (e) {
            e.preventDefault();

            const email = document.getElementById('email').value;
            const loader = document.getElementById('loader');
            const resultDiv = document.getElementById('result');

            // Mostrar el loader
            loader.style.display = 'block';
            resultDiv.innerHTML = ''; // Limpiar resultado previo

            fetch('Email_v.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'email=' + encodeURIComponent(email),
            })
            .then(response => response.json())
            .then(data => {
                loader.style.display = 'none'; // Ocultar el loader
                resultDiv.innerHTML = data.error ? data.error : `El correo es ${data.valid ? 'válido y puede recibir correos' : 'inválido o no puede recibir correos'}.`;
            })
            .catch(error => {
                loader.style.display = 'none';
                resultDiv.innerHTML = 'Ocurrió un error en la validación.';
                console.error('Error:', error);
            });
        });

        // Validación de dominio
        document.getElementById('domainForm').addEventListener('submit', function (e) {
            e.preventDefault();

            const domain = document.getElementById('domain').value;
            const loader = document.getElementById('domain-loader');
            const resultDiv = document.getElementById('domain-result');

            // Mostrar el loader
            loader.style.display = 'block';
            resultDiv.innerHTML = ''; // Limpiar resultado previo

            fetch('Domain_v.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'domain=' + encodeURIComponent(domain),
            })
            .then(response => response.json())
            .then(data => {
                loader.style.display = 'none'; // Ocultar el loader
                if (data.error) {
                    resultDiv.innerHTML = data.error;
                } else if (data.exists) {
                    resultDiv.innerHTML = `El dominio existe: <a href="http://${domain}" target="_blank">${domain}</a>`;
                } else {
                    resultDiv.innerHTML = 'El dominio no existe o no es accesible.';
                }
            })
            .catch(error => {
                loader.style.display = 'none';
                resultDiv.innerHTML = 'Ocurrió un error en la validación del dominio.';
                console.error('Error:', error);
            });
        });
    </script>
</body>
</html>
