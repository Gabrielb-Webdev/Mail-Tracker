<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validador de Correos Electrónicos</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            text-align: center;
        }

        input[type="email"] {
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

        #loader {
            display: none;
            margin: 10px;
        }
    </style>
</head>
<body>
    <h1>Validador de Correos Electrónicos</h1>
    <form id="emailForm">
        <input type="email" id="email" placeholder="Ingrese el correo electrónico" required>
        <button type="submit">Validar</button>
    </form>
    <div id="loader">Verificando...</div>
    <div id="result"></div>

    <script>
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
    </script>
</body>
</html>
