import secrets

# Genera una clave secreta de 32 bytes en hexadecimal
secret_key = secrets.token_hex(32)

print(f"Generated secret key: {secret_key}")