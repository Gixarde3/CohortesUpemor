<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restauración de contraseña</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root{
            --principal: #F50003;
            --principal-hover: #920a0c;
        }
        *{
            font-family: 'Open Sans', sans-serif;
            box-sizing: border-box;
        }
        main{
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        h1{
            margin: 0;
            font-size: 32px;
            text-align: center;
        }
        p{
            margin:0;
            font-family: "Open Sans";
            font-size: 16px;
            line-height: 1.57143;
            text-align: center;
            width: 100%;
        }
        a{
            text-decoration: none;
            color: white;
        }
        .accept{
            background-color: #2E3092;
            color: white;
            padding: 0.5rem;
            width: auto;
            cursor: pointer;
        }
        .accept:hover{
            background-color: var(--principal-hover);
        }
        .aclaracion{
            font-size: 12px;
            color: #8c8c8c;
        }
        img{
            max-width: 100%;
        }
    </style>
</head>
<body>
    <main>
        <!-- TODO Mail respuesta enviada -->
        <div style="text-align: center; padding: 20px;">
            <img src="{{ $message->embed(public_path() . '/images/mail/logo.png') }}" alt="Logo de la UPEMOR" style="max-width: 100%;">
        </div>
        <h1 style="text-align: center; color: #F50003;">Se ha enviado una solicitud de restauración de contraseña</h1>
        <div style="text-align: center; margin-top: 20px; margin-bottom: 20px;">
            <a href="{{ $url }}" style="background-color: #F50003; color: white; padding: 10px 20px; text-decoration: none; margin-bottom:1rem;">Cambiar contraseña</a>
        </div>
        <p class="aclaracion">Si el enlace no funcion copia y pega este</p>
        <p class="aclaracion">{{ $url }}</p>
        
    </main> 
</body>
</html>