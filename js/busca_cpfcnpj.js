//RETORNA O CPF/CNPJ DO CLIENTE SELECIONADO
function buscarCNPJCPF(clienteId) {
    if (clienteId) {
        const formData = new FormData();
        formData.append("buscar_cliente", true);
        formData.append("cliente_id", clienteId);

        fetch("cadastro_servicos.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById("cnpj_cpf").value = data.cnpj ? data.cnpj : data.cpf;
        })
        .catch(error => console.error('Erro ao buscar CNPJ/CPF:', error));
    }
}