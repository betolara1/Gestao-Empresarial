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
        /* Estilos inline para garantir que funcionem */
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
    <link rel="stylesheet" href="css/main.css">
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