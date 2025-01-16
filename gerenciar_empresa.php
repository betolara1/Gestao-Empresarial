<?php
require_once 'conexao.php';
require_once 'php/gerenciar_empresa.php';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Empresa</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/main.css">
    <style>
        .empresa-container {
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .section-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .info-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
        }

        .info-label {
            font-weight: bold;
            color: #495057;
            margin-bottom: 5px;
        }

        .info-value {
            color: #212529;
        }

        .table-responsive {
            overflow-x: auto;
            margin: 20px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
            background: white;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }

        th {
            background: #f8f9fa;
            font-weight: 600;
        }

        .btn-group {
            display: flex;
            gap: 10px;
            margin: 20px 0;
        }

        .btn {
            padding: 8px 16px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-primary {
            background: #007bff;
            color: white;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .company-logo {
            width: 150px;
            height: 150px;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 20px;
            border: 2px solid #dee2e6;
        }

        .company-logo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .section-title {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .popup {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s, visibility 0.3s;
        }

        .popup.active {
            display: flex;
            justify-content: center;
            align-items: center;
            opacity: 1;
            visibility: visible;
        }

        .popup-content {
            background: white;
            padding: 25px;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transform: scale(0.7);
            transition: transform 0.3s;
            position: relative;
        }

        .popup.active .popup-content {
            transform: scale(1);
        }

        .popup-content h3 {
            margin-bottom: 20px;
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }

        .popup-content .form-group {
            margin-bottom: 15px;
        }

        .popup-content label {
            display: block;
            margin-bottom: 5px;
            color: #495057;
            font-weight: 500;
        }

        .popup-content input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            transition: border-color 0.2s;
        }

        .popup-content input:focus {
            border-color: #3498db;
            outline: none;
        }

        .popup-content .btn-group {
            margin-top: 20px;
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        .company-logo-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;
            margin-bottom: 30px;
        }

        .company-logo {
            width: 150px;
            height: 150px;
            border-radius: 8px;
            overflow: hidden;
            border: 2px solid #dee2e6;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .company-logo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .logo-placeholder {
            color: #6c757d;
            font-size: 1.5rem;
            font-weight: bold;
        }

        .logo-actions {
            display: flex;
            gap: 10px;
        }

        .logo-actions .btn {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .logo-actions .btn i {
            font-size: 1.1em;
        }

        .input-group {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .input-group input {
            flex: 1;
        }

        .input-group-text {
            background: #f8f9fa;
            padding: 8px;
            border-radius: 4px;
            color: #495057;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <div class="empresa-container">
            <div class="section-card">
                <h1 class="section-title">Informações da Empresa</h1>
                
                <div class="company-logo-container">
                    <div class="company-logo">
                        <?php if ($logoImage): ?>
                            <img src="data:image/jpeg;base64,<?php echo base64_encode($logoImage); ?>" alt="Logo da Empresa" id="logoPreview">
                        <?php else: ?>
                            <div class="logo-placeholder" id="logoPreview">LOGO</div>
                        <?php endif; ?>
                    </div>
                    <div class="logo-actions">
                        <label for="logoInput" class="btn btn-primary">
                            <i class="fas fa-camera"></i> Alterar Logo
                        </label>
                        <?php if ($logoImage): ?>
                            <button class="btn btn-danger" onclick="removerLogo()">
                                <i class="fas fa-trash"></i> Remover
                            </button>
                        <?php endif; ?>
                        <input type="file" id="logoInput" accept="image/*" style="display: none;">
                    </div>
                </div>

                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Razão Social</div>
                        <div class="info-value"><?php echo htmlspecialchars($empresa['razao_social'] ?? ''); ?></div>
                        
                    </div>

                    <div class="info-item">
                        <div class="info-label">CNPJ</div>
                        <div class="info-value"><?php echo htmlspecialchars($empresa['cnpj'] ?? ''); ?></div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Nome Fantasia</div>
                        <div class="info-value"><?php echo htmlspecialchars($empresa['nome'] ?? ''); ?></div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">CPF</div>
                        <div class="info-value"><?php echo htmlspecialchars($empresa['cpf'] ?? ''); ?></div>
                    </div>
                </div>

                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Endereço</div>
                        <div class="info-value">
                            <?php 
                            echo htmlspecialchars($empresa['rua'] ?? '') . ', ' . 
                                 htmlspecialchars($empresa['numero'] ?? '') . ' - ' . 
                                 htmlspecialchars($empresa['bairro'] ?? '') . '<br>' .
                                 htmlspecialchars($empresa['cidade'] ?? '') . '/' . 
                                 htmlspecialchars($empresa['estado'] ?? '') . ' - ' .
                                 htmlspecialchars($empresa['cep'] ?? '');
                            ?>
                            <br>Coordenadas: <?php echo htmlspecialchars($empresa['coordenada'] ?? ''); ?>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Contato</div>
                        <div class="info-value">
                            Email: <?php echo htmlspecialchars($empresa['email'] ?? ''); ?><br>
                            Tel: <?php echo htmlspecialchars($empresa['telefone'] ?? ''); ?><br>
                            Cel: <?php echo htmlspecialchars($empresa['celular'] ?? ''); ?>
                        </div>
                    </div>
                </div>

                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Atividade Principal</div>
                        <div class="info-value">
                            <?php 
                            if (!empty($empresa['codigo_cnae']) && !empty($empresa['descricao_cnae'])) {
                                echo htmlspecialchars($empresa['codigo_cnae']) . ' - ' . 
                                     htmlspecialchars($empresa['descricao_cnae']);
                            }
                            ?>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Atividades Secundárias</div>
                        <div class="info-value">
                            <?php 
                            if (!empty($empresa['atividades_secundarias']) && !empty($empresa['descricoes_secundarias'])) {
                                $atividades = explode(',', $empresa['atividades_secundarias']);
                                $descricoes = explode('|||', $empresa['descricoes_secundarias']);
                                
                                foreach ($atividades as $index => $atividade) {
                                    if (isset($descricoes[$index])) {
                                        echo htmlspecialchars($atividade) . ' - ' . 
                                             htmlspecialchars($descricoes[$index]) . '<br>';
                                    }
                                }
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <div class="btn-group">
                    <a href="editar_empresa.php" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Editar Informações
                    </a>
                </div>
            </div>

            <div class="section-card">
                <h2 class="section-title">Sócios</h2>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Porcentagem Sociedade</th>
                                <th>Porcentagem Comissão</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($resultSocios->num_rows > 0): ?>
                                <?php while($socio = $resultSocios->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($socio['nome']); ?></td>
                                        <td><?php echo number_format($socio['porcentagem_sociedade'], 2); ?>%</td>
                                        <td><?php echo number_format($socio['porcentagem_comissao'], 2); ?>%</td>
                                        <td>
                                            <a href="pro_labore.php?id=<?php echo $socio['id']; ?>" class="btn btn-primary">
                                                <i class="fas fa-money-bill"></i> Pró-Labore
                                            </a>
                                            <a href="excluir_socio.php?id=<?php echo $socio['id']; ?>" 
                                               class="btn btn-danger"
                                               onclick="return confirm('Tem certeza que deseja excluir este sócio?')">
                                                <i class="fas fa-trash"></i> Excluir
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center">Nenhum sócio cadastrado</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="btn-group">
                    <button class="btn btn-primary" onclick="openPopup('addSocioPopup')">
                        <i class="fas fa-user-plus"></i> Cadastrar Novo Sócio
                    </button>
                </div>
            </div>

            <div class="section-card">
                <h2 class="section-title">Áreas de Atuação e Serviços</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <h3>Áreas de Atuação</h3>
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Nome</th>
                                        <th>Ação</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result_atuacao->num_rows > 0): ?>
                                        <?php while($area = $result_atuacao->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($area['nome']); ?></td>
                                                <td>
                                                    <form method="post" style="display:inline;">
                                                        <input type="hidden" name="id" value="<?php echo $area['id']; ?>">
                                                        <button type="submit" name="delete_area" class="btn btn-danger">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="2">Nenhuma área cadastrada</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <button class="btn btn-primary" onclick="openPopup('addAreaPopup')">
                            <i class="fas fa-plus"></i> Nova Área
                        </button>
                    </div>

                    <div class="info-item">
                        <h3>Tipos de Serviços</h3>
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Tipo</th>
                                        <th>Ação</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result->num_rows > 0): ?>
                                        <?php while($servico = $result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($servico['tipo_servico']); ?></td>
                                                <td>
                                                    <form method="post" style="display:inline;">
                                                        <input type="hidden" name="id" value="<?php echo $servico['id']; ?>">
                                                        <button type="submit" name="delete" class="btn btn-danger">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="2">Nenhum serviço cadastrado</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <button class="btn btn-primary" onclick="openPopup('addServicoPopup')">
                            <i class="fas fa-plus"></i> Novo Serviço
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Popups -->
    <div id="addAreaPopup" class="popup">
        <div class="popup-content">
            <h3>Nova Área de Atuação</h3>
            <form method="post">
                <div class="form-group">
                    <label for="nome">Nome da Área:</label>
                    <input type="text" id="nome" name="nome" required>
                </div>
                <div class="btn-group">
                    <button type="submit" name="addArea" class="btn btn-primary">Adicionar</button>
                    <button type="button" class="btn btn-danger" onclick="closePopup('addAreaPopup')">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <div id="addServicoPopup" class="popup">
        <div class="popup-content">
            <h3>Novo Tipo de Serviço</h3>
            <form method="post">
                <div class="form-group">
                    <label for="tipo_servico">Tipo de Serviço:</label>
                    <input type="text" id="tipo_servico" name="tipo_servico" required>
                </div>
                <div class="btn-group">
                    <button type="submit" name="addTipo" class="btn btn-primary">Adicionar</button>
                    <button type="button" class="btn btn-danger" onclick="closePopup('addServicoPopup')">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <div id="addSocioPopup" class="popup">
        <div class="popup-content">
            <h3>Cadastrar Novo Sócio</h3>
            <form id="formSocio" method="POST" action="salvar_socios.php">
                <div class="form-group">
                    <label for="nome">Nome:</label>
                    <input type="text" id="nome" name="nome" required>
                </div>
                
                <div class="form-group">
                    <label for="porcentagem_sociedade">Porcentagem Sociedade:</label>
                    <div class="input-group">
                        <input type="number" 
                               id="porcentagem_sociedade" 
                               name="porcentagem_sociedade" 
                               step="0.01" 
                               min="0" 
                               max="100" 
                               required>
                        <span class="input-group-text">%</span>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="porcentagem_comissao">Porcentagem Comissão:</label>
                    <div class="input-group">
                        <input type="number" 
                               id="porcentagem_comissao" 
                               name="porcentagem_comissao" 
                               step="0.01" 
                               min="0" 
                               max="100" 
                               required>
                        <span class="input-group-text">%</span>
                    </div>
                </div>

                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Salvar
                    </button>
                    <button type="button" class="btn btn-danger" onclick="closePopup('addSocioPopup')">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openPopup(id) {
            const popup = document.getElementById(id);
            popup.style.display = 'flex';
            // Pequeno delay para garantir que a transição funcione
            setTimeout(() => {
                popup.classList.add('active');
            }, 10);
        }

        function closePopup(id) {
            const popup = document.getElementById(id);
            popup.classList.remove('active');
            // Aguarda a transição terminar antes de esconder
            setTimeout(() => {
                popup.style.display = 'none';
            }, 300);
        }

        // Fecha o popup se clicar fora dele
        document.querySelectorAll('.popup').forEach(popup => {
            popup.addEventListener('click', (e) => {
                if (e.target === popup) {
                    closePopup(popup.id);
                }
            });
        });

        // Fecha o popup com a tecla ESC
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                document.querySelectorAll('.popup').forEach(popup => {
                    if (popup.classList.contains('active')) {
                        closePopup(popup.id);
                    }
                });
            }
        });

        // Função para manipular o upload da logo
        document.getElementById('logoInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Verifica o tamanho do arquivo (máximo 2MB)
                if (file.size > 2 * 1024 * 1024) {
                    alert('A imagem deve ter no máximo 2MB');
                    return;
                }

                // Verifica o tipo do arquivo
                if (!file.type.match('image.*')) {
                    alert('Por favor, selecione uma imagem válida');
                    return;
                }

                // Preview da imagem
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('logoPreview');
                    if (preview.tagName === 'IMG') {
                        preview.src = e.target.result;
                    } else {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.id = 'logoPreview';
                        preview.parentNode.replaceChild(img, preview);
                    }
                }
                reader.readAsDataURL(file);

                // Upload da imagem
                const formData = new FormData();
                formData.append('logo', file);

                fetch('upload_logo.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Erro ao fazer upload da logo: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro ao fazer upload da logo');
                });
            }
        });

        // Função para remover a logo
        function removerLogo() {
            if (confirm('Tem certeza que deseja remover a logo?')) {
                fetch('remover_logo.php', {
                    method: 'POST'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Erro ao remover a logo: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro ao remover a logo');
                });
            }
        }

        // Adicione este trecho para lidar com o formulário
        document.getElementById('formSocio').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('salvar_socios.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Sócio cadastrado com sucesso!');
                    location.reload();
                } else {
                    alert('Erro ao cadastrar sócio: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao cadastrar sócio');
            });
        });
    </script>
</body>
</html>