<?php 
session_start();
require_once 'conexao.php';
require_once 'config/cards.php';

// Função para buscar favoritos
function getFavoritos($conn, $usuario_id) {
    try {
        $stmt = $conn->prepare("SELECT card_id FROM favoritos WHERE usuario_id = ? ORDER BY ordem");
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    } catch (Exception $e) {
        error_log("Erro ao buscar favoritos: " . $e->getMessage());
        return [];
    }
}

// Buscar favoritos do usuário
$usuario_id = $_SESSION['usuario_id'] ?? 1;
$favoritos = array_column(getFavoritos($conn, $usuario_id), 'card_id');

// Função para renderizar card
function renderCard($card, $isFavorito) {
    $cardId = htmlspecialchars($card['id']);
    $link = htmlspecialchars($card['link']);
    $icone = htmlspecialchars($card['icone']);
    $titulo = htmlspecialchars($card['titulo']);
    $descricao = htmlspecialchars($card['descricao']);
    $favoritoClass = $isFavorito ? 'favorito' : '';
    
    echo <<<HTML
    <div class="card">
        <a href="{$link}" class="card-link">
            <i class="card-star fas fa-star {$favoritoClass}" data-card="{$cardId}"></i>
            <div class="card-header">
                <div class="card-icon">
                    <i class="{$icone}"></i>
                </div>
                <h3 class="card-title">{$titulo}</h3>
            </div>
            <div class="card-content">
                <p>{$descricao}</p>
            </div>
        </a>
    </div>
    HTML;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de Controle</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #838282;
            --accent-color: #e74c3c;
            --text-color: #2c3e50;
            --sidebar-width: 250px;
            --border-color: #ddd;
            --success-color: #4CAF50;
            --error-color: #f44336;
            --primary-dark: #1e40af;
            --background-color: #ffffff;
            --sidebar-width: 280px;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.12);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.1);
            --shadow-lg: 0 10px 15px rgba(0,0,0,0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            line-height: 1.6;
            color: var(--text-color);
            background-color: var(--background-color);
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            overflow-y: auto;
        }

        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 2rem;
            max-width: calc(100% - var(--sidebar-width));
        }

        .container {
            max-width: 1200px;
            padding: 2rem;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            margin: 2rem auto;
        }

        h1, h2 {
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            text-align: center;
            font-weight: 700;
        }

        h1 {
            font-size: 2.5rem;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #eee;
        }

        h2 {
            font-size: 1.8rem;
            position: relative;
            padding-bottom: 0.5rem;
        }

        h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 4px;
            background-color: var(--accent-color);
            border-radius: 2px;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }

        .cards-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            padding: 20px;
        }

        .card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s;
            position: relative;
        }

        .card-link {
            display: block;
            padding: 20px;
            text-decoration: none;
            color: inherit;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .card-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .card-icon {
            width: 40px;
            height: 40px;
            background: #f8f9fa;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
        }

        .card-icon i {
            font-size: 20px;
            color: #007bff;
        }

        .card-title {
            margin: 0;
            font-size: 1.2rem;
            color: #2c3e50;
        }

        .card-content p {
            margin: 0;
            color: #6c757d;
        }

        .card-star {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 1.2rem;
            color: #ddd;
            cursor: pointer;
            z-index: 2;
        }

        .card-star.favorito {
            color: #ffd700;
        }

        /* Responsividade */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
            }
            .cards-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <main class="main-content">
        <div class="header">
            <h1>Painel de Controle</h1>
        </div>
        
        <div class="cards-container">
            <?php
            // Renderiza cards favoritos
            foreach ($favoritos as $card_id) {
                if (isset($cards[$card_id])) {
                    renderCard($cards[$card_id], true);
                    unset($cards[$card_id]);
                }
            }

            // Renderiza cards restantes
            foreach ($cards as $card) {
                renderCard($card, false);
            }
            ?>
        </div>
    </main>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/favoritos.js"></script>
</body>
</html>