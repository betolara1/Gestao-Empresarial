let map;
let marker;

// Funções do Mapa
function initMap() {
    const defaultLocation = { lat: -14.235004, lng: -51.92528 };
    map = new google.maps.Map(document.getElementById("map"), {
        zoom: 4,
        center: defaultLocation,
    });
}

function atualizarMapa(latitude, longitude) {
    const position = { lat: parseFloat(latitude), lng: parseFloat(longitude) };
    map.setCenter(position);
    map.setZoom(15);

    if (marker) {
        marker.setMap(null);
    }

    marker = new google.maps.Marker({
        position: position,
        map: map,
        title: 'Localização'
    });
}

// Eventos jQuery para CEP e coordenadas
$(document).ready(function() {
    function buscarCoordenadas(cep) {
        cep = cep.replace(/[^0-9]/g, '');
        
        if (cep.length === 8) {
            $('#coordenada').val('Buscando coordenadas...');
            
            $.ajax({
                url: `https://brasilapi.com.br/api/cep/v2/${cep}`,
                method: 'GET',
                success: function(response) {
                    if (response.location && response.location.coordinates) {
                        const latitude = response.location.coordinates[1];
                        const longitude = response.location.coordinates[0];
                        $('#coordenada').val(`${latitude}, ${longitude}`);
                        
                        atualizarMapa(latitude, longitude);
                        
                        $.ajax({
                            url: 'atualizar_coordenadas.php',
                            method: 'POST',
                            data: {
                                cep: cep,
                                coordenada: `${latitude}, ${longitude}`
                            },
                            success: function(response) {
                                console.log('Coordenadas salvas com sucesso');
                            },
                            error: function() {
                                console.log('Erro ao salvar coordenadas');
                            }
                        });
                    } else {
                        $('#coordenada').val('Coordenadas não encontradas');
                    }
                },
                error: function() {
                    $('#coordenada').val('Erro ao buscar coordenadas');
                }
            });
        }
    }

    $('#cep').on('blur change', function() {
        buscarCoordenadas($(this).val());
    });
});

// Inicialização do mapa
window.initMap = initMap; 