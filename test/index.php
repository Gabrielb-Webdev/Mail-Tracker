<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario de Dominios</title>
    <style>
        /* CSS aquí */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f4f4f9;
        }
        .container {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 100%;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        label {
            margin-bottom: 8px;
            font-weight: bold;
        }
        input {
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #0056b3;
        }
        /* Loader styles */
        .loader {
            display: none;
            border: 4px solid #f3f3f3;
            border-radius: 50%;
            border-top: 4px solid #3498db;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }
        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }
        .result-list {
            margin-top: 20px;
            list-style: none;
            padding: 0;
        }
        .result-list li {
            margin-bottom: 10px;
        }
        .valid-email {
            color: green;
            font-weight: bold;
        }
        .invalid-email {
            color: red;
        }
    </style>
</head>

<body>
    <div class="container">
        <form id="domain-form">
            <label for="name-input">Nombre:</label>
            <input type="text" id="name-input" name="name" required>
            <label for="surname-input">Apellido:</label>
            <input type="text" id="surname-input" name="surname" required>
            <label for="domain-input">Dominio:</label>
            <input type="text" id="domain-input" name="domain" required>
            <button type="submit">Validar</button>
        </form>
        <br>
        <div class="loader" id="loader"></div>
        <ul class="result-list" id="result-list"></ul>
        <button id="download-csv" style="display: none;">Descargar CSV</button>
    </div>

    <script>
        document.getElementById('domain-form').addEventListener('submit', function (event) {
            event.preventDefault();
            const formData = new FormData(event.target);

            // Mostrar el loader
            document.getElementById('loader').style.display = 'block';
            document.getElementById('result-list').innerHTML = ''; // Limpiar resultados previos

            fetch('validate.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json' // Asegúrate de aceptar JSON
                }
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    document.getElementById('loader').style.display = 'none';

                    if (data.status === "valido") {
                        document.getElementById('download-csv').style.display = 'block';
                    }

                    // Mostrar resultados de verificación de correo electrónico
                    const resultList = document.getElementById('result-list');
                    data.emails.forEach(email => {
                        const li = document.createElement('li');
                        if (email === data.valid_email) {
                            li.textContent = `✔️ ${email} es válido y puede recibir correos.`;
                            li.className = 'valid-email';
                        } else {
                            li.textContent = `❌ ${email} no es válido o no puede recibir correos.`;
                            li.className = 'invalid-email';
                        }
                        resultList.appendChild(li);
                    });

                    alert(`Dominio: ${data.status}\nPosibles correos: ${data.emails.join(', ')}\nCorreo válido: ${data.valid_email || 'Ninguno'}`);
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('loader').style.display = 'none';
                    alert('Error al verificar los correos: ' + error.message);
                });
        });

        document.getElementById('download-csv').addEventListener('click', function () {
            window.location.href = 'validate.php?action=download_csv';
        });
    </script>
</body>

</html>
