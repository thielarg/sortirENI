/*
Utilisé pour la fenetre modale d'ajout d'un lieu
By Thierry Largeau
*/

/* fonction permettant de charger la liste déroulante selectVille
method = POST
url = appel de la methode rechercherVille du controller AjaxController (route = /ajax/rechercherVille)
*/
function chargerListeVilles(){
    $.ajax({
        method: "POST",
        url: "/sortirENI/public/ajax/rechercherVille",
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

/*pour réinitialiser les valeurs */
$('#inputReinitialiser').on('click', reinitialiser);

/* fonction permettant d'ajouter le lieu
method = GET
url = appel de la methode ajouterLieu du controller AjaxController (route = /ajax/ajouterLieu)
data = données id dans la modale
*/
function ajouterLieu(){
     $.ajax({
         method: "GET",
         url: "/sortirENI/public/ajax/ajouterLieu",
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
        chargerListeLieux(); /*appel à la fonction chargerListeLieux de listesLiees.js, afin de reactualiser la liste des lieux*/
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

/* fonction permettant de réinitialiser les valeurs */
function reinitialiser() {
    $('#inputNomLieu').val('');
    $('#inputRueLieu').val('');
    $('#inputLatitudeLieu').val('');
    $('#inputLongitudeLieu').val('');
}

