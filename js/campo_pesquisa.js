document.getElementById('search').addEventListener('input', function() {
    var searchValue = this.value.toLowerCase();
    var rows = document.querySelectorAll('#servicos-table tbody tr');
    
    rows.forEach(function(row) {
        var text = row.textContent.toLowerCase();
        if(text.includes(searchValue)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});