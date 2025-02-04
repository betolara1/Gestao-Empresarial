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
            padding: 20px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .cards-section {
            display: grid;
            grid-template-columns: repeat(3, 1fr); /* 3 cards por linha */
            gap: 30px;
            padding: 20px 0;
            overflow-x: hidden;
        }

        .cards-section::-webkit-scrollbar {
            height: 8px;
        }

        .cards-section::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .cards-section::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        .cards-section::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        .card {
            width: 100%;
            max-width: none;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card-link {
            display: block;
            padding: 30px;
            text-decoration: none;
            color: inherit;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.2);
        }

        .card-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .card-icon {
            width: 60px;
            height: 60px;
            background: #f8f9fa;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 20px;
        }

        .card-icon i {
            font-size: 28px;
            color: #007bff;
        }

        .card-title {
            font-size: 1.4rem;
            font-weight: 600;
            color: #2c3e50;
            margin: 0;
        }

        .card-content p {
            font-size: 1.1rem;
            color: #6c757d;
            line-height: 1.6;
        }

        .card-star {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 1.5rem;
            color: #ddd;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .card-star.favorito {
            color: #ffd700;
        }

        .section-title {
            font-size: 1.8rem;
            color: var(--primary-color);
            margin: 40px 0 20px;
            padding-left: 20px;
            border-left: 5px solid var(--accent-color);
        }

        #favoritos-section:empty {
            display: none;
        }

        #favoritos-section:empty + .section-title {
            display: none;
        }

        /* Responsividade */
        @media (max-width: 1200px) {
            .cards-section {
                grid-template-columns: repeat(2, 1fr); /* 2 cards por linha */
            }
        }

        @media (max-width: 768px) {
            .cards-section {
                grid-template-columns: 1fr; /* 1 card por linha */
            }
            
            .card-link {
                padding: 20px;
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
            <h2 class="section-title">Favoritos</h2>
            <div id="favoritos-section" class="cards-section">
                <?php
                // Renderiza cards favoritos
                foreach ($favoritos as $card_id) {
                    if (isset($cards[$card_id])) {
                        renderCard($cards[$card_id], true);
                        unset($cards[$card_id]);
                    }
                }
                ?>
            </div>

            <h2 class="section-title">Todos os Cards</h2>
            <div id="todos-cards-section" class="cards-section">
                <?php
                // Renderiza cards restantes
                foreach ($cards as $card) {
                    renderCard($card, false);
                }
                ?>
            </div>
        </div>
    </main>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Função para atualizar favorito no banco de dados
            function atualizarFavorito(cardId, isFavorito) {
                return $.ajax({
                    url: 'atualizar_favorito.php',
                    method: 'POST',
                    data: {
                        card_id: cardId,
                        is_favorito: isFavorito
                    }
                });
            }

            // Função para mover card entre seções
            function moverCard(card, destino) {
                const clone = card.clone(true);  // Clone com eventos
                card.remove();
                destino.prepend(clone);
                clone.hide().fadeIn();
            }

            // Handler para clique na estrela
            $(document).on('click', '.card-star', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const $star = $(this);
                const $card = $star.closest('.card');
                const cardId = $star.data('card');
                const isFavorito = !$star.hasClass('favorito');
                
                atualizarFavorito(cardId, isFavorito)
                    .done(function() {
                        $star.toggleClass('favorito');
                        
                        if (isFavorito) {
                            // Mover para seção de favoritos
                            moverCard($card, $('#favoritos-section'));
                        } else {
                            // Mover para seção de todos os cards
                            moverCard($card, $('#todos-cards-section'));
                        }
                    })
                    .fail(function() {
                        alert('Erro ao atualizar favorito. Tente novamente.');
                    });
            });
        });
    </script>
</body>
</html>