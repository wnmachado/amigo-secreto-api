<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Código de Login</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #f8f9fa; padding: 30px; border-radius: 8px;">
        <h1 style="color: #2c3e50; margin-top: 0;">Olá!</h1>

        <p>Você solicitou um código de login para acessar o sistema de Amigo Secreto.</p>

        <div style="background-color: #fff; border: 2px solid #3498db; border-radius: 8px; padding: 20px; text-align: center; margin: 30px 0;">
            <p style="margin: 0; font-size: 14px; color: #7f8c8d; text-transform: uppercase; letter-spacing: 1px;">Seu código de acesso</p>
            <h2 style="margin: 10px 0; font-size: 36px; color: #3498db; letter-spacing: 5px; font-weight: bold;">{{ $code }}</h2>
        </div>

        <p style="color: #7f8c8d; font-size: 14px;">
            <strong>Importante:</strong> Este código é válido por <strong>10 minutos</strong> e pode ser usado apenas uma vez.
        </p>

        <p style="color: #7f8c8d; font-size: 14px; margin-top: 30px;">
            Se você não solicitou este código, ignore este e-mail.
        </p>
    </div>
</body>
</html>
