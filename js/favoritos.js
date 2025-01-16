document.addEventListener('DOMContentLoaded', function() {
    const atualizarFavorito = async (cardId, isFavorito) => {
        try {
            const response = await fetch('atualizar_favorito.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    card_id: cardId,
                    favorito: !isFavorito
                })
            });

            const data = await response.json();
            if (data.success) {
                location.reload();
            } else {
                console.error('Erro ao atualizar favorito');
            }
        } catch (error) {
            console.error('Erro:', error);
        }
    };

    // Event listeners
    document.querySelectorAll('.card-star').forEach(star => {
        star.addEventListener('click', function(e) {
            e.preventDefault();
            const cardId = this.dataset.card;
            const isFavorito = this.classList.contains('favorito');
            atualizarFavorito(cardId, isFavorito);
        });
    });
}); 