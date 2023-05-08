/*
Utilisé pour la fenetre modale d'ajout d'un lieu
By Thierry Largeau
*/

/* fonction permettant de charger la liste déroulante selectVille
method = POST
url = appel de la methode ville_rechercher du controller AjaxController (route = /ajax/ville_rechercher)
*/
function chargerListeVilles(){
    $.ajax({
        method: "POST",
        url: "/sortirENI/public/ajax/ville_rechercher",
        data: {
            'recherche': ''
            },
            success: function (response) {
            /* pour chaque element en JSON de la response ... */
                for (var i = 0; i < response.length; i++) {
                /* je charge chaque option du select et je l'ajoute à l'id (selectVille)*/
                    var ville = response[i];
                    let option = $('<option value="' + ville['id'] + '">' + ville['nom'] + '</option>');
                    $('#selectVille').append(option)
                }
            }
        })
    }

/* appel de la fonction chargerListeVille */
chargerListeVilles();

/* sur clic du bouton Ajouter de la modale, appel de la fonction ajouterLieu */
$('#inputEnregistrerLieu').on('click', ajouterLieu);

$('#inputReinitialiser').on('click', reinitialiser);

/* fonction permettant d'ajouter le lieu
method = POST
url = appel de la methode ajouterAjax du controller LieuController (route = /lieu/ajouterAjax)
data = données id dans la modale
*/
function ajouterLieu(){
     $.ajax({
         method: "GET",
         url: "/sortirENI/public/lieu/ajouterAjax",
         data:{
             'ville_id': $('#selectVille').val(),
             'lieu_nom': $('#inputNomLieu').val(),
             'lieu_rue': $('#inputRueLieu').val(),
             'lieu_latitude': $('#inputLatitudeLieu').val(),
             'lieu_longitude': $('#inputLongitudeLieu').val()
         }
     }).done(function(response){
        /* ajout lieu avec succes */
        $('#modal_nouveau_lieu_message')
           .html('')
           .html(
              $('<div class="alert alert-success" role="alert">\n' +
                 'Lieu ajouté!' +
                  '</div>')
           );
        chargerListeLieux();
    }).fail(function(xhr){
        /* probleme ajout lieu */
        $('#modal_nouveau_lieu_message')
           .html('')
           .html(
               $('<div class="alert alert-danger" role="alert">\n' +
               'Oops un problème est survenu.' +
               '</div>')
           );
    })
}

function reinitialiser() {
    $('#inputNomLieu').val('');
    $('#inputRueLieu').val('');
    $('#inputLatitudeLieu').val('');
    $('#inputLongitudeLieu').val('');
}

