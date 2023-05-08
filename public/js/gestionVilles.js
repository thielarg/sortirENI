/*
* Utilisé pour la gestion des villes partie administrateur
* By Thierry Largeau

* GESTION DES VILLES
*/
$( document ).ready(function() {
    /* chargement de la page avec toutes les villes */
    rechercherVille('');

    /* puis appel de la fonction après chaque saisie dans le champs de saisie id=inputFiltrerVille de
        la page index.html.twig */
    $('#inputFiltrerVille').on('keyup', function () {
        rechercherVille($('#inputFiltrerVille').val());
    });

    /* fonction permettant de rechercher dynamiquement une ville via la saisie
    dans un champs de type input via Ajax/JS. Passage en argument, chaque
    caractère saisie les uns à la suite des autres
     */
    function rechercherVille(term) {
        $.ajax({
            method: "POST",
            url: "/sortirENI/public/ville/recherche", /* appel de VilleController route /ville/recherche */
            data: {'recherche': term},   /* passage de l'argument dans la data */
            success: function (reponse) {
                //console.log(reponse)
                /* chargement de l'entete du tableau id=table_ville de la page index.html.twig */
                var head = $('<tr><th>Nom</th><th>Code postal</th><th>Actions</th></tr>');
                $('#table_ville').html(' ');
                $('#table_ville').append(head);

                /* pour chaque reponse, affichage de la ligne de données et des boutons */
                for (var i = 0; i < reponse.length; i++) {
                    var ville = reponse[i];
                    var nouvelleLigne = $(
                        '<tr id="row' + ville["id"] + '">' +
                        '<td id="row_nom_ville' + ville["id"] + '"></td>' +
                        '<td id="row_code_postal_ville' + ville["id"] + '"></td>' +
                        '<td id="boutons"></td>' +
                        '</tr>');
                    $('#row_nom_ville' + ville["id"], nouvelleLigne).html(ville["nom"]);
                    $('#row_code_postal_ville' + ville["id"], nouvelleLigne).html(ville["code_postal"]);
                    $('#boutons', nouvelleLigne)
                        .append($('<input />')
                            .attr({
                                'type': 'button',
                                'value': 'Modifier',
                                'class': 'btn btn-outline-info m-2',
                                'id': 'mod_but' + ville["id"]
                            })
                            .on('click', {id: ville["id"]}, mod_row) /* appel de la fonction mod_row pour modifier */
                        )
                        .append($('<input />')
                            .attr({
                                'type': 'button',
                                'value': 'Sauvegarder',
                                'class': 'btn btn-outline-primary d-none m-2',
                                'id': 'save_but' + ville["id"]
                            })
                            .on('click', {id: ville["id"]}, save_row) /* appel de la fonction save_row pour sauvegarder */
                        )
                        .append($('<input />')
                            .attr({
                                'type': 'button',
                                'value': 'Supprimer',
                                'class': 'btn btn-outline-danger m-2',
                                'id': 'suppr_but' + ville["id"]
                            })
                                .on('click', {id: ville["id"]},confirmation)
                            //.on('click', {id: ville["id"]}, suppr_row)/* appel de la fonction suppr_row pour supprimer */
                        )
                    $('#table_ville').append(nouvelleLigne);
                }
                /* AJOUTER UNE VILLE */
                /* affichage d'une ligne pour ajouter une ville */
                var ligneAjout = $(
                    '<tr>\n' +
                    '<td><input type="text" class="form-control" id="new_nom_ville"></td>' +
                    '<td><input type="text" class="form-control" id="new_code_postal_ville"></td>' +
                    '<td>&nbsp;&nbsp;<input type="button" id="ajout_but" value="Ajouter" class="btn btn-outline-primary" /></td>' +
                    '</tr>'
                )
                $('#ajout_but', ligneAjout).on('click', function () {
                    var new_nom = $('#new_nom_ville').val();
                    var new_cp_ville = $('#new_code_postal_ville').val();
                    $.ajax({
                        method: "POST",
                        url: "/sortirENI/public/ville/ajouter", /* appel de VilleController route /ville/ajouter */
                        data: {'nom_ville': new_nom, 'cp_ville': new_cp_ville} /* passage des data */
                    }).done(function (response) { /* si ajout reussi */
                        $('#message').html(
                            '<div class="alert alert-success" role="alert">' +
                            response +
                            '</div>'
                        )
                        rechercherVille($('#inputFiltrerVille').val());
                    }).fail(function (xhr) { /* si ajout echec */
                        console.log(xhr)
                        $('#message').html(
                            '<div class="alert alert-danger" role="alert">' +
                            xhr.responseJSON.message +
                            '</div>'
                        )
                    })
                })
                /* ajout de la nouvelle ville à l'affichage de la liste existante */
                $('#table_ville').append(ligneAjout);
            }
        })
    }

    /*
    fonction permettant de modifier le nom et/ou le code postal de la ville choisie
     */
    function mod_row(event) {
        var no = event.data.id;
        $('#mod_but' + no).toggleClass('d-none');
        $('#save_but' + no).toggleClass('d-none');
        /* affichage des valeurs */
        var nom_ville = $('#row_nom_ville' + no).text()
        var code_postal = $('#row_code_postal_ville' + no).text()
        /* saisie des nouvelles valeurs*/
        $('#row_nom_ville' + no)
            .html(' ')
            .append($('<input />')
                .attr({
                    'id': 'input_nom_ville' + no,
                    'value': nom_ville
                })
            )
        $('#row_code_postal_ville' + no)
            .html(' ')
            .append($('<input />')
                .attr({
                    'id': 'input_code_postal_ville' + no,
                    'value': code_postal
                })
            )
    }

    /*
    fonction permettant de persister les nouvelles saisies en BDD
     */
    function save_row(event) {
        /* recuperation des éléments */
        var no = event.data.id;
        var nom_ville = $('#input_nom_ville' + no).val();
        var code_postal_ville = $('#input_code_postal_ville' + no).val();

        $.ajax({
            method: "POST",
            url: "/sortirENI/public/ville/modifier", /* appel de VilleController route /ville/modifier */
            data: {
                'id': no,
                'nom_ville': nom_ville,
                'code_postal_ville': code_postal_ville
            }                                   /* passage des data */
        }).done(function () {     /* modification avec succes */
            $('#message').html(
                '<div class="alert alert-success" role="alert">' +
                'Ville modifiée avec succes.' +
                '</div>'
            )
            rechercherVille($('#inputFiltrerVille').val());
        }).fail(function () {   /* echec de la modification */
            $('#message').html(
                '<div class="alert alert-danger" role="alert">' +
                'Un problème est survenu lors de la modification.' +
                '</div>'
            )
        })
        /* affichage des données modifiées */
        $('#row_nom_ville' + no)
            .html(nom_ville)
        $('#row_code_postal_ville' + no)
            .html(code_postal_ville)
        $('#mod_but' + no).toggleClass('d-none');
        $('#save_but' + no).toggleClass('d-none');
    }

    /*
    fonction boite de dialogue confirmation
    */
    function confirmation(event) {
        var no = event.data.id;
        var retour = confirm("Etes-vous sûr de vouloir supprimer cette ville ?");
        if (retour == true){
            suppr_row(no);
        }
    }

    /*
    fonction permettant de supprimer la ville selectionnnée
    */
    function suppr_row(no) {
        //var no = event.data.id;
        //console.log(no);
        $.ajax({
            method: "POST",
            url: "/sortirENI/public/ville/supprimer", /* appel de VilleController route /ville/supprimer */
            data: {
                'id': no
            }                       /* passage de la data */
        }).done(function () { /* suppression avec succès */
            $('#message').html(
                '<div class="alert alert-success" role="alert">' +
                'Ville supprimée avec succes.' +
                '</div>'
            )
            /* rechargement des villes en fonction du filtre */
            rechercherVille($('#inputFiltrerVille').val());
        }).fail(function () { /* suppression en echec */
            $('#message').html(
                '<div class="alert alert-danger" role="alert">' +
                'Un problème est survenu lors de la suppression de la ville [Ville liée à des lieux].' +
                '</div>'
            )
        })
    }

});