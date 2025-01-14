function openPopupTipo() {
    document.getElementById("addPopupTipo").style.display = "block";
}

function closePopupTipo() {
    document.getElementById("addPopupTipo").style.display = "none";
}

// Close the popup if the user clicks outside of it
window.onclick = function(event) {
    var popup = document.getElementById("addPopupTipo");
    if (event.target == popup) {
        popup.style.display = "none";
    }
}

// Adicionar event listener para a tecla ESC
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closePopup();
    }
});