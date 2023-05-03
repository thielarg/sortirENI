/*
* Utilisé pour la gestion des sites partie administrateur
* By Thierry Largeau

* GESTION DES SITES
*/
$(document).ready(function() {
    /* chargement de la page avec tous les sites */
    rechercherSite('');

    /* puis appel de la fonction après chaque saisie dans le champs de saisie id=inputFiltrerSite de
    la page index.html.twig */
    $('#inputFiltrerSite').on('keyup', function(){
        rechercherSite( $('#inputFiltrerSite').val());
    });

    /* fonction permettant de rechercher dynamiquement un site via la saisie
    dans un champs de type input via Ajax/JS. Passage en argument, chaque
    caractère saisie les uns à la suite des autres
     */
    function rechercherSite(term){
        $.ajax({
            method: "POST",
            url: "/sortirENI/public/site/recherche", /* appel de SiteController route /site/recherche */
            data: { 'recherche' : term }, /* passage de l'argument dans la data */
            success: function (reponse){
                //console.log(reponse)
                /* chargement de l'entete du tableau id=table_site de la page index.html.twig */
                var head = $('<tr><th>Nom</th><<th>Actions</th></tr>');
                $('#table_site').html(' ');
                $('#table_site').append(head);

                /* pour chaque reponse, affichage de la ligne de données et des boutons */
                for(var i = 0 ; i < reponse.length ; i++){
                    var site = reponse[i];
                    var nouvelleLigne = $(
                        '<tr id="row'+site["id"]+'">' +
                        '<td id="row_nom_site'+site["id"]+'"></td>' +
                        '<td id="boutons"></td>' +
                        '</tr>');
                    $('#row_nom_site'+site["id"], nouvelleLigne).html(site["nom"]);
                    $('#boutons', nouvelleLigne)
                        .append($('<input />')
                            .attr({
                                'type': 'button',
                                'value' : 'Modifier',
                                'class' : 'btn btn-outline-info m-2',
                                'id' : 'mod_but'+site["id"]
                            })
                            .on('click', { id : site["id"]}, mod_row)
                        )
                        .append($('<input />')
                            .attr({
                                'type': 'button',
                                'value' : 'Sauvegarder',
                                'class' : 'btn btn-outline-primary d-none m-2',
                                'id' : 'save_but'+site["id"]
                            })
                            .on('click', {id : site["id"]} , save_row)
                        )
                        .append($('<input />')
                            .attr({
                                'type': 'button',
                                'value' : 'Supprimer',
                                'class' : 'btn btn-outline-danger m-2',
                                'id' : 'suppr_but'+site["id"]
                            })
                            .on('click', {id : site["id"]} , suppr_row)
                        )
                    $('#table_site').append(nouvelleLigne);
                }
                /* AJOUTER UN SITE */
                /* affichage d'une ligne pour ajouter un site */
                var ligneAjout = $(
                    '<tr>\n' +
                    '<td><input type="text" class="form-control" id="new_nom_site"></td>' +
                    '<td>&nbsp;&nbsp;<input type="button" id="ajout_but" value="Ajouter" class="btn btn-outline-primary" /></td>' +
                    '</tr>'
                )
                $('#ajout_but', ligneAjout).on('click', function () {
                    var new_nom = $('#new_nom_site').val();
                    $.ajax({
                        method: "POST",
                        url: "/sortirENI/public/site/ajouter",
                        data: {'nom_site': new_nom}
                    }).done(function (response) {
                        $('#message').html(
                            '<div class="alert alert-success" role="alert">' +
                            response +
                            '</div>'
                        )
                        rechercherSite( $('#inputFiltrerSite').val());
                    }).fail(function (xhr) {
                        console.log(xhr)
                        $('#message').html(
                            '<div class="alert alert-danger" role="alert">' +
                                xhr.responseJSON.message +
                            '</div>'
                        )
                    })
                })
                $('#table_site').append(ligneAjout);
            }
        })
    }

    /*
    fonction permettant de modifier le nom du site choisi
     */
    function mod_row(event)
    {
        var no = event.data.id;
        $('#mod_but'+no).toggleClass('d-none');
        $('#save_but'+no).toggleClass('d-none');
        var nom_site = $('#row_nom_site'+no).text()
        $('#row_nom_site'+ no)
        .html(' ')
        .append($('<input />')
            .attr({
                'id' : 'input_nom_site' + no,
                'value' : nom_site
            })
        )
    }

    /*
    fonction permettant de persister la nouvelle saisie en BDD
     */
    function save_row(event)
    {
        var no = event.data.id;
        var nom_site = $('#input_nom_site'+no).val();

        $.ajax({
            method: "POST",
            url: "/sortirENI/public/site/modifier",
            data: {
                'id': no ,
                'nom_site' : nom_site
            }
        }).done(function () {
            $('#message').html(
                '<div class="alert alert-success" role="alert">' +
                'Site modifié avec succes.' +
                '</div>'
            )
            rechercherSite( $('#inputFiltrerSite').val());
        }).fail(function () {
            $('#message').html(
                '<div class="alert alert-danger" role="alert">' +
                'Un problème est survenu lors de la modification.' +
                '</div>'
            )
        })

        /* affichage des données modifiées */
        $('#row_nom_site'+ no)
            .html(nom_site)
        $('#mod_but'+no).toggleClass('d-none');
        $('#save_but'+no).toggleClass('d-none');
    }

    /*
    fonction permettant de supprimer le site selectionnné
    */
    function suppr_row(event) {
        var no = event.data.id;
        $.ajax({
            method: "POST",
            url: "/sortirENI/public/site/supprimer",
            data: {
                'id': no
            }
        }).done(function () {
            $('#message').html(
                '<div class="alert alert-success" role="alert">' +
                'Site supprimé avec succes.' +
                '</div>'
            )
            rechercherSite( $('#inputFiltrerSite').val());
        }).fail(function () {
            $('#message').html(
                '<div class="alert alert-danger" role="alert">' +
                'Un problème est survenu lors de la suppression du site [Site lié à des sorties].' +
                '</div>'
            )
        })
    }
});