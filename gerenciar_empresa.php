<?php
require_once 'conexao.php';

class GerenciadorEmpresa {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }

    /**
     * Busca os dados da empresa
     */
    public function getDadosEmpresa() {
        $sql = "SELECT * FROM empresa LIMIT 1";
        $result = $this->conn->query($sql);
        return $result->num_rows > 0 ? $result->fetch_assoc() : [];
    }

    /**
     * Gerencia áreas de atuação
     */
    public function gerenciarAreasAtuacao($post) {
        if (isset($post['addArea'])) {
            return $this->adicionarAreaAtuacao($post['nome']);
        } elseif (isset($post['delete_area'])) {
            return $this->removerAreaAtuacao($post['id']);
        }
        return true;
    }

    private function adicionarAreaAtuacao($nome) {
        try {
            $stmt = $this->conn->prepare("INSERT INTO areas_atuacao (nome) VALUES (?)");
            $stmt->bind_param("s", $nome);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Erro ao adicionar área de atuação: " . $e->getMessage());
            return false;
        }
    }

    private function removerAreaAtuacao($id) {
        try {
            $this->conn->begin_transaction();
            
            $stmt = $this->conn->prepare("DELETE FROM areas_atuacao WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("Erro ao remover área de atuação: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Gerencia tipos de serviços
     */
    public function gerenciarTiposServicos($post) {
        if (isset($post['addTipo'])) {
            return $this->adicionarTipoServico($post['tipo_servico']);
        } elseif (isset($post['delete'])) {
            return $this->removerTipoServico($post['id']);
        }
        return true;
    }

    private function adicionarTipoServico($tipo) {
        try {
            $stmt = $this->conn->prepare("INSERT INTO tipos_servicos (tipo_servico) VALUES (?)");
            $stmt->bind_param("s", $tipo);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Erro ao adicionar tipo de serviço: " . $e->getMessage());
            return false;
        }
    }

    private function removerTipoServico($id) {
        try {
            $this->conn->begin_transaction();
            
            // Remove registros dependentes
            $stmt = $this->conn->prepare("DELETE FROM servico_tipo_servico WHERE tipo_servico_id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            
            // Remove o tipo de serviço
            $stmt = $this->conn->prepare("DELETE FROM tipos_servicos WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("Erro ao remover tipo de serviço: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca todas as áreas de atuação
     */
    public function getAreasAtuacao() {
        return $this->conn->query("SELECT * FROM areas_atuacao");
    }

    /**
     * Busca todos os tipos de serviços
     */
    public function getTiposServicos() {
        return $this->conn->query("SELECT * FROM tipos_servicos");
    }

    /**
     * Busca todos os sócios
     */
    public function getSocios() {
        return $this->conn->query("SELECT * FROM socios ORDER BY nome");
    }

    /**
     * Processa informações de CNAE
     */
    public function processarCNAEs($empresa) {
        $cnae_principal = [
            'codigo' => $empresa['codigo_cnae'] ?? '',
            'descricao' => $empresa['descricao_cnae'] ?? ''
        ];

        $cnaes_secundarios = [];
        if (!empty($empresa['atividades_secundarias']) && !empty($empresa['descricoes_secundarias'])) {
            $codigos = explode(',', $empresa['atividades_secundarias']);
            $descricoes = explode('|||', $empresa['descricoes_secundarias']);
            
            foreach ($codigos as $index => $codigo) {
                if (isset($descricoes[$index])) {
                    $cnaes_secundarios[] = [
                        'codigo' => trim($codigo),
                        'descricao' => trim($descricoes[$index])
                    ];
                }
            }
        }

        return [
            'principal' => $cnae_principal,
            'secundarios' => $cnaes_secundarios
        ];
    }
}

// Inicialização
$gerenciador = new GerenciadorEmpresa($conn);

// Processamento de POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $gerenciador->gerenciarAreasAtuacao($_POST);
    $gerenciador->gerenciarTiposServicos($_POST);
}

// Busca dos dados
$empresa = $gerenciador->getDadosEmpresa();
$result_atuacao = $gerenciador->getAreasAtuacao();
$result = $gerenciador->getTiposServicos();
$resultSocios = $gerenciador->getSocios();
$cnaes = $gerenciador->processarCNAEs($empresa);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Empresa</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
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
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .info-item {
            background: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .info-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .info-label {
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 10px;
            font-size: 1.1em;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .info-label i {
            color: #3498db;
            width: 20px;
        }

        .info-value {
            color: #34495e;
            line-height: 1.6;
        }

        .address-info, .contact-info {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .address-line, .contact-line {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .address-line i, .contact-line i {
            color: #7f8c8d;
            width: 16px;
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

        /* Adicionar estilos para o mapa */
        .leaflet-popup-content-wrapper {
            border-radius: 8px;
            box-shadow: 0 3px 14px rgba(0,0,0,0.2);
        }

        .leaflet-popup-content {
            margin: 0;
            padding: 0;
        }

        .leaflet-popup-content h3 {
            color: #2c3e50;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }

        .leaflet-popup-content p {
            color: #666;
            line-height: 1.4;
        }

        /* Ajustar controles do mapa */
        .leaflet-control-zoom {
            border: none !important;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1) !important;
        }

        .leaflet-control-zoom a {
            background-color: white !important;
            color: #2c3e50 !important;
        }

        .leaflet-control-zoom a:hover {
            background-color: #f8f9fa !important;
        }

        /* Adicionar animação de fade-in */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .info-item {
            animation: fadeIn 0.3s ease-out forwards;
        }

        /* Estilizar valores vazios */
        .info-value:empty::after {
            content: 'Não informado';
            color: #95a5a6;
            font-style: italic;
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
                        <div class="info-label">
                            <i class="fas fa-building"></i> Razão Social
                        </div>
                        <div class="info-value">
                            <?php echo htmlspecialchars($empresa['razao_social'] ?? 'Não informado'); ?>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-id-card"></i> CNPJ
                        </div>
                        <div class="info-value">
                            <?php echo htmlspecialchars($empresa['cnpj'] ?? 'Não informado'); ?>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-store"></i> Nome Fantasia
                        </div>
                        <div class="info-value">
                            <?php echo htmlspecialchars($empresa['nome'] ?? 'Não informado'); ?>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-address-card"></i> CPF
                        </div>
                        <div class="info-value">
                            <?php echo htmlspecialchars($empresa['cpf'] ?? 'Não informado'); ?>
                        </div>
                    </div>
                </div>

                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-map-marked-alt"></i> Endereço
                        </div>
                        <div class="info-value address-info">
                            <div class="address-line">
                                <i class="fas fa-road"></i>
                                <?php echo htmlspecialchars($empresa['rua'] ?? '') . ', ' . 
                                     htmlspecialchars($empresa['numero'] ?? ''); ?>
                            </div>
                            <div class="address-line">
                                <i class="fas fa-map"></i>
                                <?php echo htmlspecialchars($empresa['bairro'] ?? ''); ?>
                            </div>
                            <div class="address-line">
                                <i class="fas fa-city"></i>
                                <?php echo htmlspecialchars($empresa['cidade'] ?? '') . '/' . 
                                     htmlspecialchars($empresa['estado'] ?? ''); ?>
                            </div>
                            <div class="address-line">
                                <i class="fas fa-mailbox"></i>
                                CEP: <?php echo htmlspecialchars($empresa['cep'] ?? ''); ?>
                            </div>
                            <div class="address-line">
                                <i class="fas fa-map-marker-alt"></i>
                                Coordenadas: <?php echo htmlspecialchars($empresa['coordenada'] ?? ''); ?>
                            </div>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-address-book"></i> Contato
                        </div>
                        <div class="info-value contact-info">
                            <div class="contact-line">
                                <i class="fas fa-envelope"></i>
                                <?php echo htmlspecialchars($empresa['email'] ?? 'Não informado'); ?>
                            </div>
                            <div class="contact-line">
                                <i class="fas fa-phone"></i>
                                <?php echo htmlspecialchars($empresa['telefone'] ?? 'Não informado'); ?>
                            </div>
                            <div class="contact-line">
                                <i class="fas fa-mobile-alt"></i>
                                <?php echo htmlspecialchars($empresa['celular'] ?? 'Não informado'); ?>
                            </div>
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
                                <th><i class="fas fa-user"></i> Nome</th>
                                <th><i class="fas fa-percentage"></i> Porcentagem Sociedade</th>
                                <th><i class="fas fa-money-bill-wave"></i> Porcentagem Comissão</th>
                                <th><i class="fas fa-cogs"></i> Ações</th>
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
                                                <i class="fas fa-money-bill"></i>
                                            </a>
                                            <a href="excluir_socio.php?id=<?php echo $socio['id']; ?>" 
                                               class="btn btn-danger"
                                               onclick="return excluirSocio(<?php echo $socio['id']; ?>)">
                                                <i class="fas fa-trash"></i>
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
                            <table id="areasTable">
                                <thead>
                                    <tr>
                                        <th><i class="fas fa-tag"></i> Nome</th>
                                        <th><i class="fas fa-cogs"></i> Ação</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Dados serão carregados dinamicamente -->
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
                            <table id="servicosTable">
                                <thead>
                                    <tr>
                                        <th><i class="fas fa-tools"></i> Tipo</th>
                                        <th><i class="fas fa-cogs"></i> Ação</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Dados serão carregados dinamicamente -->
                                </tbody>
                            </table>
                        </div>
                        <button class="btn btn-primary" onclick="openPopup('addServicoPopup')">
                            <i class="fas fa-plus"></i> Novo Serviço
                        </button>
                    </div>
                </div>
            </div>

            <!-- Adicionar nova seção para o mapa -->
            <div class="section-card">
                <h2 class="section-title"><i class="fas fa-map-marked-alt"></i> Localização</h2>
                <div id="map" style="height: 400px; width: 100%; border-radius: 8px;"></div>
            </div>
        </div>
    </div>

    <!-- Popups -->
    <div id="addAreaPopup" class="popup">
        <div class="popup-content">
            <h3><i class="fas fa-plus-circle"></i> Nova Área de Atuação</h3>
            <form id="formArea">
                <div class="form-group">
                    <label for="nome"><i class="fas fa-tag"></i> Nome da Área:</label>
                    <input type="text" id="nome" name="nome" required>
                </div>
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Salvar
                    </button>
                    <button type="button" class="btn btn-danger" onclick="closePopup('addAreaPopup')">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="addServicoPopup" class="popup">
        <div class="popup-content">
            <h3><i class="fas fa-plus-circle"></i> Novo Tipo de Serviço</h3>
            <form id="formServico">
                <div class="form-group">
                    <label for="tipo_servico"><i class="fas fa-tools"></i> Tipo de Serviço:</label>
                    <input type="text" id="tipo_servico" name="tipo_servico" required>
                </div>
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Salvar
                    </button>
                    <button type="button" class="btn btn-danger" onclick="closePopup('addServicoPopup')">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="addSocioPopup" class="popup">
        <div class="popup-content">
            <h3><i class="fas fa-user-plus"></i> Cadastrar Novo Sócio</h3>
            <form id="formSocio" method="POST" action="salvar_socios.php">
                <div class="form-group">
                    <label for="nome"><i class="fas fa-user"></i> Nome:</label>
                    <input type="text" id="nome" name="nome" required>
                </div>
                
                <div class="form-group">
                    <label for="porcentagem_sociedade"><i class="fas fa-percentage"></i> Porcentagem Sociedade:</label>
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
                    <label for="porcentagem_comissao"><i class="fas fa-money-bill-wave"></i> Porcentagem Comissão:</label>
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

    <!-- Adicionar Leaflet CSS e JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
          crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
            crossorigin=""></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
            Swal.fire({
                title: 'Confirmar exclusão',
                text: 'Tem certeza que deseja remover a logo?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sim, remover',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('remover_logo.php', {
                        method: 'POST'
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: 'Sucesso!',
                                text: 'Logo removida com sucesso',
                                icon: 'success',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Erro!',
                                text: 'Erro ao remover a logo: ' + data.message,
                                icon: 'error'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        Swal.fire({
                            title: 'Erro!',
                            text: 'Erro ao remover a logo',
                            icon: 'error'
                        });
                    });
                }
            });
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
                    Swal.fire({
                        title: 'Sucesso!',
                        text: 'Sócio cadastrado com sucesso!',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Erro!',
                        text: 'Erro ao cadastrar sócio: ' + data.message,
                        icon: 'error',
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'OK'
                    });
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                Swal.fire({
                    title: 'Erro!',
                    text: 'Erro ao cadastrar sócio',
                    icon: 'error',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'OK'
                });
            });
        });

        // Função para inicializar o mapa
        function initMap() {
            // Pegar as coordenadas da empresa do PHP
            const coordString = '<?php echo $empresa['coordenada'] ?? ""; ?>';
            let lat = -23.550520; // Coordenada padrão (São Paulo)
            let lng = -46.633308; // Coordenada padrão (São Paulo)

            // Se houver coordenadas salvas, usar elas
            if (coordString) {
                const coords = coordString.split(',');
                if (coords.length === 2) {
                    lat = parseFloat(coords[0]);
                    lng = parseFloat(coords[1]);
                }
            }

            // Criar o mapa
            const map = L.map('map').setView([lat, lng], 15);

            // Adicionar camada do OpenStreetMap
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            // Adicionar marcador
            const marker = L.marker([lat, lng]).addTo(map);

            // Adicionar popup com informações
            const popupContent = `
                <div style="padding: 10px;">
                    <h3 style="margin: 0 0 5px 0; font-size: 16px;"><?php echo htmlspecialchars($empresa['nome'] ?? ""); ?></h3>
                    <p style="margin: 0; font-size: 14px;">
                        <?php echo htmlspecialchars($empresa['rua'] ?? "") . ", " . 
                                 htmlspecialchars($empresa['numero'] ?? "") . "<br>" .
                                 htmlspecialchars($empresa['bairro'] ?? "") . ", " .
                                 htmlspecialchars($empresa['cidade'] ?? "") . "/" .
                                 htmlspecialchars($empresa['estado'] ?? ""); ?>
                    </p>
                </div>
            `;

            marker.bindPopup(popupContent);

            // Adicionar controle de zoom
            L.control.zoom({
                position: 'bottomright'
            }).addTo(map);

            // Adicionar escala
            L.control.scale({
                imperial: false,
                position: 'bottomleft'
            }).addTo(map);

            // Atualizar o tamanho do mapa quando a janela for redimensionada
            window.addEventListener('resize', function() {
                map.invalidateSize();
            });

            // Adicionar evento de clique no mapa para copiar coordenadas
            map.on('click', function(e) {
                const coords = e.latlng;
                const coordStr = `${coords.lat.toFixed(6)},${coords.lng.toFixed(6)}`;
                
                // Criar um elemento temporário para copiar o texto
                const el = document.createElement('textarea');
                el.value = coordStr;
                document.body.appendChild(el);
                el.select();
                document.execCommand('copy');
                document.body.removeChild(el);

                // Mostrar mensagem de confirmação
                alert(`Coordenadas copiadas: ${coordStr}`);
            });
        }

        // Inicializar o mapa quando a página carregar
        document.addEventListener('DOMContentLoaded', initMap);

        function excluirSocio(id) {
            Swal.fire({
                title: 'Confirmar exclusão',
                text: 'Tem certeza que deseja excluir este sócio?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sim, excluir',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'excluir_socio.php?id=' + id;
                }
            });
            return false;
        }

        // Função para carregar as áreas
        function carregarAreas() {
            fetch('buscar_areas.php')
                .then(response => response.json())
                .then(areas => {
                    const tbody = document.querySelector('#areasTable tbody');
                    tbody.innerHTML = '';
                    
                    if (areas.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="2">Nenhuma área cadastrada</td></tr>';
                        return;
                    }

                    areas.forEach(area => {
                        tbody.innerHTML += `
                            <tr>
                                <td>${area.nome}</td>
                                <td>
                                    <button onclick="excluirArea(${area.id})" class="btn btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                })
                .catch(error => {
                    console.error('Erro:', error);
                    Swal.fire({
                        title: 'Erro!',
                        text: 'Erro ao carregar áreas',
                        icon: 'error'
                    });
                });
        }

        // Função para excluir área
        function excluirArea(id) {
            Swal.fire({
                title: 'Confirmar exclusão',
                text: 'Tem certeza que deseja excluir esta área?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sim, excluir',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('id', id);

                    fetch('excluir_area.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: 'Sucesso!',
                                text: 'Área excluída com sucesso!',
                                icon: 'success',
                                timer: 1500,
                                showConfirmButton: false
                            });
                            carregarAreas();
                        } else {
                            throw new Error(data.message || 'Erro ao excluir área');
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            title: 'Erro!',
                            text: error.message,
                            icon: 'error'
                        });
                    });
                }
            });
        }

        // Manipular o formulário de adicionar área
        document.getElementById('formArea').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('salvar_area.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Sucesso!',
                        text: 'Área cadastrada com sucesso!',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    });
                    this.reset();
                    closePopup('addAreaPopup');
                    carregarAreas();
                } else {
                    throw new Error(data.message || 'Erro ao cadastrar área');
                }
            })
            .catch(error => {
                Swal.fire({
                    title: 'Erro!',
                    text: error.message,
                    icon: 'error'
                });
            });
        });

        // Carregar áreas quando a página carregar
        document.addEventListener('DOMContentLoaded', carregarAreas);

        // Função para carregar os serviços
        function carregarServicos() {
            fetch('buscar_servicos.php')
                .then(response => response.json())
                .then(servicos => {
                    const tbody = document.querySelector('#servicosTable tbody');
                    tbody.innerHTML = '';
                    
                    if (servicos.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="2">Nenhum serviço cadastrado</td></tr>';
                        return;
                    }

                    servicos.forEach(servico => {
                        tbody.innerHTML += `
                            <tr>
                                <td>${servico.tipo_servico}</td>
                                <td>
                                    <button onclick="excluirServico(${servico.id})" class="btn btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                })
                .catch(error => {
                    console.error('Erro:', error);
                    Swal.fire({
                        title: 'Erro!',
                        text: 'Erro ao carregar serviços',
                        icon: 'error'
                    });
                });
        }

        // Função para excluir serviço
        function excluirServico(id) {
            Swal.fire({
                title: 'Confirmar exclusão',
                text: 'Tem certeza que deseja excluir este serviço?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sim, excluir',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('id', id);

                    fetch('excluir_tipo_servico.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: 'Sucesso!',
                                text: 'Serviço excluído com sucesso!',
                                icon: 'success',
                                timer: 1500,
                                showConfirmButton: false
                            });
                            carregarServicos();
                        } else {
                            throw new Error(data.message || 'Erro ao excluir serviço');
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            title: 'Erro!',
                            text: error.message,
                            icon: 'error'
                        });
                    });
                }
            });
        }

        // Manipular o formulário de adicionar serviço
        document.getElementById('formServico').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('salvar_tipo_servico.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Sucesso!',
                        text: 'Serviço cadastrado com sucesso!',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    });
                    this.reset();
                    closePopup('addServicoPopup');
                    carregarServicos();
                } else {
                    throw new Error(data.message || 'Erro ao cadastrar serviço');
                }
            })
            .catch(error => {
                Swal.fire({
                    title: 'Erro!',
                    text: error.message,
                    icon: 'error'
                });
            });
        });

        // Carregar serviços quando a página carregar
        document.addEventListener('DOMContentLoaded', carregarServicos);
    </script>

</body>
</html>