# 💼 GestorTech - Sistema de Gestão Empresarial

## 📋 Sobre o Projeto

GestorTech é um sistema web completo para gestão empresarial, desenvolvido em PHP com interface JavaScript e CSS. O sistema oferece funcionalidades para gerenciamento de clientes, empresas, despesas fixas, serviços e pagamentos.

## 🚀 Tecnologias Utilizadas

- **Backend:** PHP (85.1%)
- **Frontend:** JavaScript (10.0%)
- **Estilização:** CSS (4.9%)
- **Banco de Dados:** MySQL

## 📊 Funcionalidades Principais

- Gestão de Clientes
- Gestão de Empresas
- Controle de Despesas Fixas
- Gerenciamento de Serviços
- Sistema de Pagamentos
- Dashboard Administrativo

## 📁 Estrutura do Projeto
```plaintext
GestorTech/
├── css/                    # Arquivos de estilo
├── js/                     # Scripts JavaScript
├── php/                    # Arquivos PHP
├── cadastro/
│   ├── cliente.php        # Cadastro de clientes
│   ├── empresa.php        # Cadastro de empresas
│   ├── despesas_fixas.php # Cadastro de despesas
│   └── servicos.php       # Cadastro de serviços
├── atualizar/
│   ├── cliente.php        # Atualização de clientes
│   ├── empresa.php        # Atualização de empresas
│   ├── despesa_fixa.php   # Atualização de despesas
│   └── servico.php        # Atualização de serviços
├── editar/
│   ├── cliente.php        # Edição de clientes
│   ├── empresa.php        # Edição de empresas
│   ├── despesa_fixa.php   # Edição de despesas
│   └── servico.php        # Edição de serviços
├── conexao.php            # Configuração do banco de dados
├── dashboard.php          # Painel administrativo
└── db.sql                # Estrutura do banco de dados
```


## ⚙️ Pré-requisitos

- PHP 7.4+
- MySQL 5.7+
- Servidor Web (Apache/Nginx)
- Navegador web moderno

## 🔧 Instalação

1. Clone o repositório:
   ```bash
   git clone https://github.com/betolara1/GestorTech-Gestao-Empresarial.git
```

2. Importe o banco de dados:

```shellscript
mysql -u seu_usuario -p sua_senha < db.sql
```


3. Configure a conexão com o banco:

1. Abra o arquivo `conexao.php`
2. Atualize as credenciais do banco de dados



4. Configure o servidor web:

1. Aponte o document root para a pasta do projeto
2. Configure as permissões necessárias





## 💻 Módulos do Sistema

### 1. Gestão de Clientes

- Cadastro completo
- Histórico de serviços
- Controle de pagamentos
- Atualização de dados


### 2. Gestão de Empresas

- Cadastro empresarial
- Informações fiscais
- Controle de filiais
- Relatórios empresariais


### 3. Controle de Despesas

- Cadastro de despesas fixas
- Acompanhamento mensal
- Relatórios financeiros
- Previsão orçamentária


### 4. Gestão de Serviços

- Cadastro de serviços
- Precificação
- Agendamento
- Histórico de execução


## 📊 Dashboard

O painel administrativo oferece:

- Visão geral do negócio
- Gráficos e estatísticas
- Indicadores de desempenho
- Relatórios gerenciais


## 🔐 Segurança

- Autenticação de usuários
- Níveis de acesso
- Registro de atividades
- Backup automático


## 🤝 Contribuições

Contribuições são bem-vindas! Para contribuir:

1. Faça um Fork do projeto
2. Crie uma branch para sua feature (`git checkout -b feature/NovaFeature`)
3. Commit suas alterações (`git commit -m 'Adiciona nova feature'`)
4. Push para a branch (`git push origin feature/NovaFeature`)
5. Abra um Pull Request


## 📄 Licença

Este projeto está sob a licença MIT. Veja o arquivo `LICENSE` para mais detalhes.

## 👤 Autor

- GitHub: [@betolara1](https://github.com/betolara1)


## 🔍 Suporte

Para suporte:

- Abra uma issue no GitHub
- Consulte a documentação
- Entre em contato com o desenvolvedor


---

⭐️ Se este projeto te ajudou, considere dar uma estrela no GitHub!
