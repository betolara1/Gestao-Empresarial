<?php
/**
 * Gerenciamento de Empresa
 * 
 * Este arquivo contém as funções e lógica para gerenciar informações da empresa,
 * áreas de atuação, tipos de serviços e sócios.
 */

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