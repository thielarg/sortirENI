/*
Utilisé pour les listes liées
By Thierry Largeau
*/

/* sur changement de la données dans la liste deroulante de ville ...*/
$(document).on('change', '#sortie_ville', function () {
    /* chargement des lieux de la ville concernée */
    chargerListeLieux();
})

/* fonction permettant de recuperer les lieux en fonction de la ville selectionnée */
function chargerListeLieux(){
    $.ajax({
        method: "POST",
        url: "/sortir/public/ajax/rechercheLieuByVille", /* appel de la fonction rechercheLieuByVille du controlleur AjaxController*/
        data: {
            'ville_id' : $('#sortie_ville').val() //recupération de la ville dans la data
        }
    }).done(function (response) {
        $('#sortie_lieu').html(''); //initialisation de la liste des lieux
        //chargement des lieux fournis dans la response, dans la liste deroulante
        for(var i = 0 ; i < response.length ; i++) {
            var lieu = response[i];
            let option = $('<option value="'+lieu["id"]+'">'+lieu["nom"]+'</option>');
            $('#sortie_lieu').append(option);
        }
    })
}