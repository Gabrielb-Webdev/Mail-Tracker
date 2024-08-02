from flask import Flask, request, jsonify, send_file, render_template, session
import dns.resolver
import smtplib
import io
import csv

app = Flask(__name__)
app.secret_key = '0c12ab34f5d6789a12cd34ef56b78c90d12e345678f9ab12cd34ef56b78c90d1'

# Ruta principal para el formulario
@app.route('/')
def index():
    return render_template('index.html')

# Ruta para validar los correos electrónicos
@app.route('/validate', methods=['POST'])
def validate():
    name = request.form.get('name')
    surname = request.form.get('surname')
    domain = request.form.get('domain')

    if not name or not surname or not domain:
        return jsonify({'error': 'Missing parameters'}), 400

    # Verificar si el dominio tiene registros MX
    status = verificar_registros_mx(domain)

    # Generar posibles correos electrónicos
    emails = generar_posibles_correos(name, surname, domain)

    # Verificar cuál de los correos generados es válido y puede recibir correos
    valid_email = verificar_correos_validos(emails)

    data = {
        'name': name,
        'surname': surname,
        'domain': domain,
        'status': status,
        'emails': emails,
        'valid_email': valid_email
    }

    # Guardar datos en sesión para generar CSV posteriormente
    session['csv_data'] = data

    return jsonify(data)

# Ruta para descargar el CSV
@app.route('/download_csv', methods=['GET'])
def download_csv():
    if 'csv_data' not in session:
        return "No hay datos para descargar.", 400

    data = session['csv_data']
    si = io.StringIO()
    cw = csv.writer(si)
    cw.writerow(['Name', 'Last Name', 'Domain', 'Domain Status', 'Email Valid'])
    cw.writerow([data['name'], data['surname'], data['domain'], data['status'], data['valid_email']])

    output = io.BytesIO()
    output.write(si.getvalue().encode('utf-8'))
    output.seek(0)

    return send_file(output, mimetype='text/csv', as_attachment=True, download_name='domain_validation.csv')

def verificar_registros_mx(domain):
    try:
        answers = dns.resolver.resolve(domain, 'MX', lifetime=5)  # Asegúrate de establecer un timeout
        if len(answers) > 0:
            return "valido"
        else:
            return "invalido"
    except Exception as e:
        return f"error: {str(e)}"

def generar_posibles_correos(nombre, apellido, dominio):
    posibles_correos = [
        f"{nombre}@{dominio}",
        f"{nombre}.{apellido}@{dominio}",
        f"{apellido}@{dominio}",
        f"{nombre}{apellido}@{dominio}",
        f"{nombre[0]}{apellido}@{dominio}",
        f"{nombre}{apellido[0]}@{dominio}",
        f"{nombre}_{apellido}@{dominio}",
        f"{apellido}_{nombre}@{dominio}",
        f"{nombre}-{apellido}@{dominio}",
        f"{apellido}-{nombre}@{dominio}"
    ]
    return posibles_correos[:10]

def verificar_correos_validos(emails):
    for email in emails:
        if verificar_correo_puede_recibir(email):
            return email  # Retorna el primer correo electrónico válido que puede recibir correos
    return None

def verificar_correo_puede_recibir(email):
    domain = email.split('@')[1]

    try:
        answers = dns.resolver.resolve(domain, 'MX', lifetime=5)
        if len(answers) == 0:
            return False
    except Exception as e:
        return False

    for answer in answers:
        mx_server = str(answer.exchange)
        try:
            connection = smtplib.SMTP(mx_server, 25, timeout=5)  # Ajusta el timeout
            connection.ehlo()
            connection.mail('gabrielbg21@hotmail.com')
            code, message = connection.rcpt(email)
            connection.quit()

            if code in (250, 251):
                return True
        except Exception as e:
            continue

    return False

if __name__ == '__main__':
    app.run(debug=True)
