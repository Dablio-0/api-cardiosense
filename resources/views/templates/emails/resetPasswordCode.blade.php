<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinição de Senha</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #FFFFFF;
            margin: 0;
            padding: 0;
        }
        .email-container {
            width: 100%;
            background-color: #FFFFFF;
            padding: 20px;
        }
        .email-content {
            max-width: 600px;
            margin: 0 auto;
            background-color: #00AEEF;
            border-radius: 8px;
            padding: 40px;
            text-align: center;
        }
        .email-header {
            font-size: 25px;
            color: #FFFFFF;
            margin-bottom: 20px;
        }
        .email-body {
            font-size: 30px;
            color: #FFFFFF;
            line-height: 1.6;
        }
        .code-box {
            background-color: #FFFFFF;
            color: #00AEEF;
            font-size: 20px;
            font-weight: bold;
            padding: 10px;
            border-radius: 5px;
            margin: 20px 0;
            display: inline-block;
        }
        .email-footer {
            font-size: 20px;
            color: #FFFFFF;
            margin-top: 30px;
        }
        .btn-reset {
            background-color: #FFFFFF;
            color: #00AEEF;
            text-decoration: none;
            font-size: 16px;
            font-weight: bold;
            padding: 12px 25px;
            border-radius: 5px;
            margin-top: 20px;
            display: inline-block;
        }
        .btn-reset:hover {
            background-color: #00AEEF;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-content">
            <h1 class="email-header">Redefinição de Senha</h1>
            <p class="email-body">
                Olá, <br><br>
                Recebemos uma solicitação para redefinir sua senha. Por favor, use o código abaixo para redefinir sua senha no nosso sistema:
            </p>
            <div class="code-box">{{ $code }}</div>
            <p class="email-body">
                Se você não solicitou essa alteração, pode ignorar este e-mail.
            </p>
            <a href="{{ $url }}" class="btn-reset">Redefinir Senha</a>
            <p class="email-footer">
                Obrigado,<br>
                Sistema de Suporte <br>
            </p>
            <br>
            <img src="{{ asset('images/logo.jpeg') }}" alt="Logo" width="100">
            <h3 class="email-header">CardioSense - Precisão a Cada Batida</h3>
        </div>
    </div>
</body>
</html>
