<?php
// Configuração da conexão com o banco de dados
include 'conexao.php';
include 'php/gerenciar_empresa.php'
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minha Empresa</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <link rel="stylesheet" href="css/main.css">
            
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <div class="container">
            <h1>Minha Empresa</h1>
            <h2>Informações da Empresa</h2>

            <div class="form-row">
                <div class="form-group">
                    <label for="razao_social">Razão Social:</label>
                    <input type="text" id="razaoSocial" name="razaoSocial" value="<?php echo htmlspecialchars($empresa['razao_social'] ?? ''); ?>" disabled>
                </div>
                <div class="form-group">
                    <label for="cnpj" class="required">CNPJ:</label>
                    <input type="text" id="cnpj" name="cnpj" value="<?php echo htmlspecialchars($empresa['cnpj'] ?? ''); ?>" disabled>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="nome">Nome do Cliente:</label>
                    <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($empresa['nome'] ?? ''); ?>" disabled>
                </div>
                <div class="form-group">
                    <label for="cpf">CPF:</label>
                    <input type="text" id="cpf" name="cpf" value="<?php echo htmlspecialchars($empresa['cpf'] ?? ''); ?>" disabled>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="cep">CEP:</label>
                    <input type="text" id="cep" name="cep" value="<?php echo htmlspecialchars($empresa['cep'] ?? ''); ?>" disabled>
                </div>
                <div class="form-group">
                    <label for="rua">Rua:</label>
                    <input type="text" id="rua" name="rua" value="<?php echo htmlspecialchars($empresa['rua'] ?? ''); ?>" disabled>
                </div>
                <div class="form-group">
                    <label for="numero">Número:</label>
                    <input type="text" id="numero" name="numero" value="<?php echo htmlspecialchars($empresa['numero'] ?? ''); ?>" disabled>
                </div>
                <div class="form-group">
                    <label for="complemento">Complemento:</label>
                    <input type="text" id="complemento" name="complemento" value="<?php echo htmlspecialchars($empresa['complemento'] ?? ''); ?>" disabled>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="bairro">Bairro:</label>
                    <input type="text" id="bairro" name="bairro" value="<?php echo htmlspecialchars($empresa['bairro'] ?? ''); ?>" disabled>
                </div>
                <div class="form-group">
                    <label for="cidade">Cidade:</label>
                    <input type="text" id="cidade" name="cidade" value="<?php echo htmlspecialchars($empresa['cidade'] ?? ''); ?>" disabled>
                </div>
                <div class="form-group">
                    <label for="estado">Estado:</label>
                    <input type="text" id="estado" name="estado" value="<?php echo htmlspecialchars($empresa['estado'] ?? ''); ?>" disabled>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="email">E-mail:</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($empresa['email'] ?? ''); ?>" disabled>
                </div>
                <div class="form-group">
                    <label for="coordenada">Coordenada:</label>
                    <input type="text" id="coordenada" name="coordenada" value="<?php echo htmlspecialchars($empresa['coordenada'] ?? ''); ?>" disabled>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="telefone">Telefone:</label>
                    <input type="text" id="telefone" name="telefone" value="<?php echo htmlspecialchars($empresa['telefone'] ?? ''); ?>" disabled>
                </div>
                <div class="form-group">
                    <label for="celular">Celular:</label>
                    <input type="text" id="celular" name="celular" value="<?php echo htmlspecialchars($empresa['celular'] ?? ''); ?>" disabled>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="atividade_principal">Atividade Principal (CNAE)</label>
                    <div class="cnae-display">
                        <input 
                            type="text" 
                            id="atividade_principal_display" 
                            value="<?php 
                                if (!empty($empresa['codigo_cnae']) && !empty($empresa['descricao_cnae'])) {
                                    echo htmlspecialchars($empresa['codigo_cnae'] . ' - ' . $empresa['descricao_cnae']);
                                } else {
                                    echo 'Nenhum CNAE selecionado';
                                }
                            ?>" 
                            class="form-control"
                            disabled
                            style="width: 100%;"
                        >
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="atividades_secundarias">Atividades Secundárias (CNAE)</label>
                    <div class="cnaes-secundarios">
                        <?php
                        if (!empty($empresa['atividades_secundarias']) && !empty($empresa['descricoes_secundarias'])) {
                            $codigos = explode(',', $empresa['atividades_secundarias']);
                            $descricoes = explode('|||', $empresa['descricoes_secundarias']);
                            
                            echo '<div class="list-group">';
                            foreach ($codigos as $index => $codigo) {
                                if (isset($descricoes[$index])) {
                                    echo '<div class="list-group-item">';
                                    echo htmlspecialchars(trim($codigo) . ' - ' . trim($descricoes[$index]));
                                    echo '</div>';
                                }
                            }
                            echo '</div>';
                        } else {
                            echo '<p class="text-muted">Nenhuma atividade secundária cadastrada</p>';
                        }
                        ?>
                    </div>
                </div>
            </div>

            <a href="editar_empresa.php" class="btn">Editar Empresa</a>

            <br><br><br>
            <div class="form-row">
                <div class="form-group">
                    <h2>Logo</h2>
                    <form id="uploadForm" action="upload.php" method="POST" enctype="multipart/form-data">
                        <div class="company-logo">
                            <input type="file" name="logo" id="logo-input" accept="image/*" onchange="previewImage(event)" style="display: none;">
                            
                            <!-- Placeholder ou Logo -->
                            <?php if ($logoImage): ?>
                                <img id="logo-preview" 
                                    src="data:image/jpeg;base64,<?php echo base64_encode($logoImage); ?>" 
                                    alt="Logo" />
                            <?php else: ?>
                                <div id="logo-placeholder">LOGO</div>
                            <?php endif; ?>
                        </div>

                        <!-- Botão para alterar logo -->
                        <br>
                       
                    </form>
                    <div class="center">
                        <label for="logo-input" class="btn">Alterar Logo</label>
                    </div>
                </div>

                <div class="form-group">
                    <h1>Sócios</h1>
                    <table class="table table-striped table-bordered">
                        <thead class="thead-dark">
                            <tr>
                                <th>Nome</th>
                                <th>Porcentagem Sociedade</th>
                                <th>Porcentagem Comissão</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($resultSocios->num_rows > 0) {
                                while($row = $resultSocios->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . $row['nome'] . "</td>";
                                    echo "<td>" . number_format($row['porcentagem_sociedade'], 2) . "%</td>";
                                    echo "<td>" . number_format($row['porcentagem_comissao'], 2) . "%</td>";
                                    echo "<td>
                                            <a href='pro_labore.php?id=" . $row['id'] . "' class='btn btn-primary btn-sm'>Pró-Labore</a>
                                            <a href='excluir_socio.php?id=" . $row['id'] . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Tem certeza que deseja excluir este sócio?\")'>Excluir</a>
                                        </td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5' class='text-center'>Nenhum sócio cadastrado</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                    <a href="cadastro_socio.php" class="btn btn-primary mb-3">Cadastrar Sócios</a>
                </div>
            </div>
            

            <br><br>
            <!-- ADICIONAR NOVAS AREAS DE ATUAÇÃO-->
            <div class="form-row">
                <div class="form-group">
                <h2>Áreas de Atuação</h2>
                    <table border=1>
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($result_atuacao->num_rows > 0) {
                                while($row = $result_atuacao->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row["nome"]) . "</td>";
                                    echo "<td>
                                            <form method='post' style='display:inline;'>
                                                <input type='hidden' name='id' value='" . $row["id"] . "'>
                                                <input type='submit' name='delete_area' value='Excluir' class='btn btn-danger'>
                                            </form>
                                        </td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='2'>Nenhuma área de atuação cadastrada.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                    <div id="addPopup" class="popup">
                        <div class="popup-content">
                            <span class="close" onclick="closePopup()">&times;</span>
                            <h2>Adicionar Nova Área de Atuação</h2>
                            <form method="post">
                                <label for="nome">Nome:</label>
                                <input type="text" id="nome" name="nome" required>
                                <label></label>
                                <input type="submit" name="addArea" value="Adicionar" class="btn">
                            </form>
                        </div>
                    </div>
                    <button class="btn" onclick="openPopup()">Adicionar Nova Área</button>
                </div>
                
                <div class="form-group">
                    <!-- ADICIONAR NOVO TIPO DE SERVIÇO -->
                    <h2>Serviços Prestados</h2>
                    <table border=1>
                        <thead>
                            <tr>
                                <th>Tipo de Serviço</th>
                                <th>Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row["tipo_servico"]) . "</td>";
                                    echo "<td>
                                            <form method='post' style='display:inline;'>
                                                <input type='hidden' name='id' value='" . $row["id"] . "'>
                                                <input type='submit' name='delete' value='Excluir' class='btn btn-danger'>
                                            </form>
                                        </td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='2'>Nenhum tipo de serviço cadastrado.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>

                    <div id="addPopupTipo" class="popup">
                        <div class="popup-content">
                            <span class="close" onclick="closePopupTipo()">&times;</span>
                            <h2>Adicionar Novo Tipo de Serviço</h2>
                            <form method="post">
                                <label for="tipo_servico">Tipo de Serviço:</label>
                                <input type="text" id="tipo_servico" name="tipo_servico" required>
                                <label></label>
                                <input type="submit" name="addTipo" value="Adicionar" class="btn">
                            </form>
                        </div>
                    </div>
                    <button class="btn" onclick="openPopupTipo()">Adicionar Novo Tipo de Serviço</button>
                </div>
            </div>
        </div>
    </div>
    <script>
        function previewImage(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('logo-preview');
                    const placeholder = document.getElementById('logo-placeholder');
                    
                    if (preview) {
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                    }
                    if (placeholder) {
                        placeholder.style.display = 'none';
                    }
                }
                reader.readAsDataURL(file);
                
                // Upload automático
                const formData = new FormData(document.getElementById('uploadForm'));
                fetch('upload.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Logo atualizada com sucesso!');
                    } else {
                        alert('Erro ao atualizar logo: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro ao fazer upload da imagem');
                });
            }
        }
    </script>
    <script src="js/cep.js"></script>
    <script src="js/edita_empresa.js"></script>
    <script src="js/popup_area_atuacao.js"></script>
    <script src="js/popup_tipos_servico.js"></script>
</body>
</html>